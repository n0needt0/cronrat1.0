<?php namespace Lasdorf\CronratApi;

use Lasdorf\CronratApi\CronratBase;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Redis as Redis;
use Illuminate\Support\Facades\Config as Config;
use Illuminate\Log;


Class CronratApi extends CronratBase{

    public function __construct(){

    }

    /**
     * Refreshes all active accounts from db into redis
     */
    public static function refresh_accounts_from_db($ratkey=false)
    {
        $cnt = 0;
        if(!$ratkey)
        {
            //refresh all accounts
            $res = DB::table("users")->where('activated',1)->get();
            foreach($res as $row)
            {
                self::set_account($row);
                $cnt++;
            }
        }
         else
        {
            //refresh only one
            $res = DB::table("users")->where('activated',1)->where('cronrat_code', $ratkey)->get();
            foreach($res as $row)
            {
                self::set_account($row);
                $cnt = 1;
            }
        }
        return $cnt;
    }

    /**
    * check for key in redis
    * @param string $ratkey
    */
    public static function ttl($ratkey)
    {
        $result = Redis::ttl($ratkey);

        if($result == false)
        {
            \Log::info("miss $ratkey");
            return false;
        }
        \Log::info("hit $ratkey");
        return $result;
    }


    /**
     * check for key in redis
     * @param string $ratkey
     */
    public static function lookup($ratkey)
    {
        $result = Redis::get($ratkey);

        if($result == false)
        {
            \Log::info("miss $ratkey");
            return false;
        }
        \Log::info("hit $ratkey");
        return unserialize($result);
    }

    public static function lookup_pattern($pattern)
    {
        $keys = Redis::keys($pattern);

        $res = array();
        foreach($keys as $i=>$key)
        {
             $res[$key] = self::lookup($key);
        }
        return $res;
    }

    /**
     * Set key value in redis
     * @param string $ratkey
     * @param mixed $value
     * @param int $ttlsec
     */
    public static function store($ratkey, $value, $ttlsec)
    {
        if( Redis::set($ratkey, serialize($value)) )
        {

            if( Redis::expire($ratkey, $ttlsec) )
            {
                \Log::info("stored $ratkey for $ttlsec sec");
                return true;
            }
              else
            {
                \Log::info("failed set expire on $ratkey for $ttlsec sec");
                 return false;
            }
        }
         \Log::info("failed store $ratkey for $ttlsec sec");
         return false;
    }

    /**
     * Returns account data or false
     * @param strings $ratkey
     * @return mixed <boolean, mixed>
     */
    public static function get_account($ratkey)
    {
        if(!$res = self::lookup("account::$ratkey"))
        {
            return false;
        }
        //return account info
        return $res;
    }

    public static function set_account($user)
    {
        $levels = Config::get('cronrat.levels');

        if(empty($levels[$user->cronrat_level]))
        {
            $acct = $levels[1];
        }
         else
        {
            $acct = $levels[$user->cronrat_level];
        }

        $ratkey = $user->cronrat_code;
        $acct['email'] = $user->email;

        return self::store("account::$ratkey", $acct, 60 * 60 * 24 * 30); //lets initially store user account to flush out in 30 days
    }



    /**
     * @param string $ratkey
     * retrieves all status rats for account
     */
    public static function get_account_live_rats($ratkey,$keysonly=true)
    {
        $pattern = $ratkey . '::status::*';

        if($keysonly)
        {
            return Redis::keys($pattern);
        }
          else
        {
            return self::lookup_pattern($pattern);
        }
    }

     /**
     * @param string $ratkey
     * retrieves all rat specs for account
     */
    public static function get_account_expected_rats($ratkey,$keysonly=true)
    {
        $pattern = $ratkey . '::specs::*';

        if($keysonly)
        {
            return Redis::keys($pattern);
        }
          else
        {
            return self::lookup_pattern($pattern);
        }
    }

    private static function set_rat($ratkey, $ratname, $crontab, $allow, $toutc, $emailto, $urlto, $nextrun)
    {
         //set status key , this is signifies that rat is running
         $ttl = $nextrun - time() + $allow;

         $r = self::store($ratkey . '::status::' . $ratname, time(), $ttl);

         //set specs array, this is what tells us what we expect should be alive and what to do if not
         $spec = array('nextrun'=>$nextrun, 'email'=>$emailto, 'url'=>$urlto, 'crontab'=>$crontab, 'toutc'=>$toutc, 'allow'=>$allow);

         //this tracks specs of a job , these are alive for 30 days

         $s = self::store($ratkey . '::specs::' . $ratname, $spec, 30 * 24 * 60 * 60);

         if( $r && $s)
         {
             return true;
         }

         return false;
    }

   public static function delete_rat($ratid)
    {
        $rediskey = str_replace('::::', '::status::', $ratid);
         //set status key , this is sgnifies that rat is running
         Redis::del($rediskey);
         $rediskey = str_replace('::::', '::specs::', $ratid);
         Redis::del($rediskey);
         $rediskey = str_replace('::::', '::dead::', $ratid);
         Redis::del($rediskey);
    }



    /**
     * @param string $ratkey
     * @param string $ratname
     * @param string $crontab
     * @param string $allow
     * @param string $toutc
     * @param string $emailto
     * @param string $urlto
     * @param string $nextrun
     * @throws \Exception
     * @return boolean
     */

    public static function check_set_rat($ratkey, $ratname, $crontab, $allow, $toutc, $emailto, $urlto, $nextrun)
    {
        //this function sets rat key ast ttl and rat spec key at ttl of 48 hr.
        //this also sets index key as index::$ratkey = 1 of ttl of 48 hr
        //rat key may eventually expire, then we will need to

        //see if user exists
        if(!$acct = self::get_account($ratkey))
        {
            //see if it is in database
            if(!self::refresh_accounts_from_db($ratkey))
            {
                throw new \Exception("Invalid account");
                return false;
            }
            $acct = self::get_account($ratkey);
        }

        if($emailto && $emailto != $acct['email'] && !$acct['emailto'])
        {
            throw new \Exception("Alternative Email feature is disabled. Upgrade account!");
            return false;
        }

        if(!$emailto)
        {
            $emailto = $acct['email'];
        }

       if(!filter_var($emailto, FILTER_VALIDATE_EMAIL))
       {
            throw new \Exception("Invalid email");
            return false;
        }

        //this is new rat, so more work required
        $user_rats = self::get_account_live_rats($ratkey);

        if(count($user_rats) >= $acct['ratlimit'])
        {
            throw new \Exception("Too many Rats, upgrade account");
            return false;
        }

        if( ($nextrun - time()) < $acct['ttlmin'] )
        {
            throw new \Exception("Time too short upgrade account");
            return false;
        }

        if($urlto && !$acct['urlto'])
        {
            throw new \Exception("Cannot use url pull feature. Upgrade account");
            return false;
        }

        //set url
        return self::set_rat($ratkey, $ratname, $crontab, $allow, $toutc, $emailto, $urlto, $nextrun);
    }

    public static function get_expected_rats()
    {
        $pattern = '*::specs::*';
        return self::lookup_pattern($pattern);
    }

    public static function get_dead_rats()
    {
        $pattern = '*::dead::*';
        return self::lookup_pattern($pattern);
    }

    public static function get_live_rats()
    {
        $pattern = '*::status::*';
        return self::lookup_pattern($pattern);
    }

    public static function remove_dead_rat($deadratkey)
    {
        Redis::del($deadratkey);
    }

    public static function mark_dead($deadratkey, $data)
    {
        return self::store($deadratkey, $data, 7 * 24 * 60 * 60);
    }
}