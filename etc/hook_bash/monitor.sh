#!/bin/bash
# bash history hooking Ver 1.06
 
if [ -f "/usr/local/curl/bin/curl" ]
then
    CURL="/usr/local/curl/bin/curl"
 
elif [ -f "/usr/bin/curl" ]
then
    CURL="/usr/bin/curl"
else
    CURL="curl"
fi
 
CURL_MINOR_VER=$($CURL --version | head -n 1 | cut -f2 -d" " | cut -d. -f2)
 
if [[ $CURL_MINOR_VER -ge 10 ]]
then
    CURLOPT="$CURL -k "
else
    CURLOPT="$CURL "
fi
 
CURRENT_COMMAND=$1
 
if [[ "x$SIMPLEX_SEUCRE_ID" == "x" ]]
then
    SIMPLEX_SEUCRE_ID=$(logname)
fi
 
if [ ! -n "$SSH_CLIENT" ]
then
    SSH_CLIENT="none none none"
fi
 
ID_VAR=$(id -u)
if [[ $ID_VAR = "0" ]]
then
    CALL_URL=" https://admin-log.simplexi.com/cmdlog.php"
else
    CALL_URL=" https://admin-log.simplexi.com/user_cmdlog.php"
fi
 
echo "$CURRENT_COMMAND" | xargs -i $CURLOPT -d "cmd=(`date +%Y/%m/%d\ %H:%M:%S.%N`) $SIMPLEX_SEUCRE_ID[$$] $SSH_CLIENT $TTY $USER : {}" $CALL_URL > /dev/null 2>&1