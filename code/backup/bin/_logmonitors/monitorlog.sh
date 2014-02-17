EMAILADDRESS="ops@cronrat.net"
EMAILSUBJECT="***** $HOSTNAME problem *****"
MESSAGEBODY="/tmp/monitorlog.txt"
SEARCHSTRING='error'
SYSLOG=/var/log/apache2/error.log
COMMAND="tail -f -n 0 $SYSLOG"

N=0
MAXLINES=10

ESCAPED=$(echo "$COMMAND" | sed 's/\//\\\//g')
CMD="ps awx | awk '/$ESCAPED/ && !/awk/ {print \$1'}"

for i in `eval ${CMD}`
do
        echo killing $i
        kill -9 $i
            if [ -e "$MESSAGEBODY" ] ; then
                let N=$(wc -l < $MESSAGEBODY )
            fi
done

$COMMAND | while read LINE
do
 if [ `echo $LINE | grep -c "$SEARCHSTRING"` -gt 0 ]
 then
        let N+=1
        echo $LINE >> $MESSAGEBODY
        if (( N > MAXLINES ))
        then
            mail -s "$EMAILSUBJECT" "$EMAILADDRESS" < $MESSAGEBODY
            echo "" > $MESSAGEBODY
            let N=0
        fi
 fi
done