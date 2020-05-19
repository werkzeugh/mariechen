#!/bin/bash


#fetch database settings from php

#set php exec
if [ -d /Applications/MAMP ]
    then
    PHP=/Applications/MAMP/bin/php/current/bin/php
else
  PHP="php"
fi

envvars=`$PHP ./vendor/silverstripe/framework/cli-script.php BE/getDBConfig`
echo $envvars
$envvars

# idiomatic parameter and option handling in sh
while test $# -gt 0
do
    case "$1" in
        --import) 

            echo "IMPORT FROM LIVE:"
            echo
            echo "➜ on remote:"   
            echo "mysqldump -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD $SS_DATABASE_NAME | gzip > /tmp/dump.sql.gz"
            echo 
            echo "➜ on local:"
            echo "scp   root@$REMOTE_HOST_SSH:/tmp/dump.sql.gz /tmp/dump.sql.gz"
            echo "gzcat /tmp/dump.sql.gz | mysql -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD $SS_DATABASE_NAME"
            echo
        exit 0
            ;;
        --export) 

            echo "EXPORT TO LIVE:"
            echo
            echo "➜ on local:"   
            echo "mysqldump -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD $SS_DATABASE_NAME | gzip > /tmp/dump.sql.gz"
            echo "scp  /tmp/dump.sql.gz root@$REMOTE_HOST_SSH:/tmp/dump.sql.gz"
            echo 
            echo "➜ on remote:"
            echo "gzcat /tmp/dump.sql.gz | mysql -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD $SS_DATABASE_NAME"
            echo
        exit 0
            ;;
        --export) 

            echo "EXPORT TO LIVE:"
            echo
            echo "➜ on local:"   
            echo "mysqldump -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD $SS_DATABASE_NAME | gzip > /tmp/dump.sql.gz"
            echo "scp  /tmp/dump.sql.gz root@vserver1.werkzeugh.at:/tmp/dump.sql.gz"
            echo 
            echo "➜ on remote:"
            echo "gzcat /tmp/dump.sql.gz | mysql -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD $SS_DATABASE_NAME"
            echo
        exit 0
            ;;
    esac
    shift
done




    CMD="mysql -u $SS_DATABASE_USERNAME -p$SS_DATABASE_PASSWORD -h $SS_DATABASE_SERVER $SS_DATABASE_NAME"
    echo $CMD
    $CMD


