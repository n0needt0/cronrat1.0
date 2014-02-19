#!/bin/bash

COMMAND="php /var/www/cronrat/artisan command:ratcheck --env=production"; 
FILE="/tmp/$(basename $0).$RANDOM.txt"; 
FILE2="/tmp/$(basename $0).$RANDOM.txt"; 
ENTRY="*/5 * * * * $COMMAND"; 
crontab -l > $FILE 2> /dev/null; 
cat $FILE | grep -v "$COMMAND" > $FILE2; 
echo "$ENTRY" >> $FILE2; 
crontab $FILE2;
rm $FILE $FILE2
