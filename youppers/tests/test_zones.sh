#!/bin/bash

echo -------------- Show list of zones -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Zone.list","params":{"sessionId":"'$session_id'"}}')
echo $response | php -f $format 

echo -------------- Add a zone -------------
zonename=$(cat /dev/urandom | tr -dc 'a-zA-Z' | fold -w 8 | head -n 1)
zonename="Zona_$zonename"
echo $zonename
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Zone.create","params":{"sessionId":"'$session_id'","zoneName":"'$zonename'"}}')
echo $response | php -f $format 

exit

echo "-------------- Try to add a zone dupicated (profile) -------------"
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Zone.create","params":{"sessionId":"'$session_id'","zoneName":"'$zonename'"}}')
echo $response | php -f $format 

echo "-------------- Try to add a zone dupicated (common) -------------"
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Zone.create","params":{"sessionId":"'$session_id'","zoneName":"Camera1"}}')
echo $response | php -f $format 

echo "-------------- Show list of zones (after add) -------------"
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Zone.list","params":{"sessionId":"'$session_id'"}}')
echo $response | php -f $format 