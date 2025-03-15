#!/bin/bash

set -e
LOG_FILE="/home/temp/container.log"

HOST_NAME=$(hostname)
echo "HOST_NAME: $HOST_NAME" | tee -a $LOG_FILE

echo "build env" | tee -a $LOG_FILE
/usr/local/bin/php /var/www/html/env.build.php

echo "cache clear" | tee -a $LOG_FILE
/usr/local/bin/php /var/www/html/bin/console cache:clear

#if [[ $HOST_NAME == *"worker"* ]]; then
  echo "worker job started" | tee -a $LOG_FILE
  /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
#else
  echo "web started" | tee -a $LOG_FILE
#fi

exec "$@"
