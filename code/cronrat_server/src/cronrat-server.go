/**
* This is a server part of a cron rat.
It receives HTTP request, checks if request is valid and stores data in redis.
*
*/
package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"github.com/bmizerany/pat"
	"github.com/garyburd/redigo/redis"
	"io"
	"io/ioutil"
	"log"
	"net/http"
	"os"
	"reflect"
	"regexp"
	"runtime"
	"strconv"
	"time"
)

//this is log file writer
var logFile *os.File

var err error

type Configuration struct {
	ThisHost  string
	HttpHost  string
	HttpPort  string
	RedisHost string
	RedisPort string
	RedisDb   string
	LogFile   string
	CoreCnt   string
	KeySize   string
}

var GRatRegex = regexp.MustCompile(`...`)

var GConf = &Configuration{"localhost", "127.0.0.1", "8081", "127.0.0.1", "6379", "0", "./cronrat.log", "1", "8"}

func (q *Configuration) FromJSON(file string) error {

	//Reading JSON file
	J, err := ioutil.ReadFile(file)
	if err != nil {
		panic(err)
	}

	var data = &q
	//Umarshalling JSON into struct
	return json.Unmarshal(J, data)
}

var GRedisPool = &redis.Pool{}

var logdebug *bool = flag.Bool("debug", false, "enable debug logging")
var Configfile *string = flag.String("Configfile", "cronrat-server.json", "Config json file location")
var help *bool = flag.Bool("help", false, "Show options")

func debug(format string, args ...interface{}) {
	if *logdebug {
		log.Printf("DEBUG "+format, args)
	}
}

func init() {
	//parse options
	flag.Parse()
	if *help {
		flag.PrintDefaults()
		os.Exit(1)
	}
}

func dump(t interface{}) string {
	s := reflect.ValueOf(t).Elem()
	typeOfT := s.Type()
	res := ""

	for i := 0; i < s.NumField(); i++ {
		f := s.Field(i)
		res = fmt.Sprint(res, fmt.Sprintf("%s %s = %v\n", typeOfT.Field(i).Name, f.Type(), f.Interface()))
	}

	return res
}

func main() {
	//
	if err := GConf.FromJSON(*Configfile); err != nil {
		log.Println("parse config file error: ", err)
		os.Exit(1)
	}

	CoreCnt, err := strconv.Atoi(GConf.CoreCnt)
	if err == nil {
		if CoreCnt > 0 {
			runtime.GOMAXPROCS(CoreCnt)
		}
	}

	logFile, err := os.OpenFile(GConf.LogFile, os.O_RDWR|os.O_CREATE|os.O_APPEND, 0666) //

	if err != nil {
		log.Fatal(fmt.Sprintf("Log file error: %s", GConf.LogFile), err)
		return
	}
	defer logFile.Close()

	log.SetOutput(logFile)

	debug(dump(GConf))

	//set global compiled regex
	GRatRegex, err = regexp.Compile(fmt.Sprintf("^[A-Za-z0-9_]{%s}$", GConf.KeySize))

	if err != nil {
		log.Fatal("Can not compile reg expresion: ", err)
	}

	//set redispool
	GRedisPool = &redis.Pool{
		MaxIdle:   3,
		MaxActive: 10, // max number of connections
		Dial: func() (redis.Conn, error) {
			c, err := redis.Dial("tcp", GConf.RedisHost+":"+GConf.RedisPort)
			if err != nil {
				log.Fatal("Can not create Redis pool: ", err)
			}
			return c, err
		},
	}

	mux := pat.New()
	mux.Get("/r/:id", http.HandlerFunc(ratHandle))
	mux.Get("/health", http.HandlerFunc(healthHandle))

	http.Handle("/", mux)

	log.Println("Listening " + GConf.HttpHost + ":" + GConf.HttpPort)

	err = http.ListenAndServe(GConf.HttpHost+":"+GConf.HttpPort, nil)

	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}

func serve404(w http.ResponseWriter) {
	w.WriteHeader(http.StatusNotFound)
	w.Header().Set("Content-Type", "text/plain; charset=utf-8")
	io.WriteString(w, "Not Found")
}

func ratHandle(w http.ResponseWriter, r *http.Request) {
	//write watcher into chan it will stop the old one if already runnng
	id := r.URL.Query().Get(":id")

	if GRatRegex.MatchString(id) != true {
		debug("No rat " + id)
		serve404(w)
		return
	}
	
	if setRat(w, id) == true {
		w.Write([]byte(fmt.Sprint("ok:", id)))
	}else{
		w.Write([]byte(fmt.Sprint("invalid:", id)))
	} 
	 
	  
	return
}

func setRat(w http.ResponseWriter, id string) bool {
	rconn := GRedisPool.Get() // get a client from the pool
	defer rconn.Close()       // close the client so the connection gets reused

	validkey := fmt.Sprint(id + ":VALID")
	ratkey := fmt.Sprint(id + ":RAT")

	//if valid rat, it will return TTL value
	exists, err := redis.String(rconn.Do("GET", validkey))

	if err != nil {
		debug("Invalid: %s", validkey)
		return false;
	} else {

		debug("Fetched %s=%s", validkey, exists)

		if exists != "" {

			TTL := 300
			var ttl int

			if ttl, err = strconv.Atoi(exists); err != nil || ttl < TTL {
				ttl = TTL
			}

			debug(fmt.Sprintf("SET: %s, to expire: %d", ratkey, ttl))

			rconn.Do("SET", ratkey, int32(time.Now().Unix()))
			rconn.Do("EXPIRE", ratkey, ttl)
		}
	}
	return true;
}

func healthHandle(w http.ResponseWriter, r *http.Request) {
	rconn := GRedisPool.Get() // get a client from the pool
	defer rconn.Close()       // close the client so the connection gets reused

	//check general redis connectivity
	t := fmt.Sprintf("%d",time.Now().Unix())
	key := fmt.Sprint("HEARTBIT")
	//if valid rat, it will return TTL value
	rconn.Do("SET", key, t)
	res, err := redis.String(rconn.Do("GET", key))
	
	if err != nil || res != t {
		log.Printf("ERROR: HEALTH:%s, %v",t,res)
		w.Write([]byte("notok"))
		return
	}
	
	//TODO
	//check last run of bucket timers
	//check capacity
		
	//good all the way
	w.Write([]byte("ok"))
	return
}
