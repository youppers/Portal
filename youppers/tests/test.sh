#!/bin/bash

client_id=25a39ff1-cd63-11e4-a2aa-080027980c59_4wkty9e55jms840w488k0wgs0ss0sgs0so0gg80kwso8kggok0
client_secret=iklbvfyfexkcgs48o4oks80gsc8skg4g8k4ckcwo40wk8kkks
host=127.0.0.1

authendpoint=http://$host/oauth/v2/token
jsonendpoint=http://$host/app_dev.php/jsonrpc/
qrtextid=http://$host/qr/5eeed2c7-abb2-11e4-b4aa-0cc47a127a14?p1=a
qrtextwrong=htxxxtp:///qr1/5eeed2c7-abb2-11e4-b4aa-0cc47a127a14?p1=a
qrtexturl="http://m.marazzi.it/qr_code/BLEND?pid=592&merch=0000V4EE"
qrid=5eeed2c7-abb2-11e4-b4aa-0cc47a127a14

echo client_id=$client_id
echo client_secret=$client_secret

echo Request auth_token to $authendpoint:

response=$(curl "$authendpoint?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials")
echo response=$response

access_token=$(echo $response|sed -n -e 's/{"access_token":"\([a-ZA-Z0-9]*\)",.*/\1/p')
echo access_token=$access_token

response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Session.new"}')
echo response=$response

session_id=$(echo $response|sed -n -e 's/.*{"id":"\([a-ZA-Z0-9\-]*\)",.*/\1/p')
echo session_id=$session_id

qrid=5eeed2c7-abb2-11e4-b4aa-0cc47a127a14

response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Qr.find","params":{"sessionId":"'$session_id'","text":"'$qrtextwrong'"}}')
echo qr=$qrtextwrong response=$response

response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Qr.find","params":{"sessionId":"'$session_id'","text":"'$qrtexturl'"}}')
echo qr=$qrtexturl response=$response

response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Qr.find","params":{"sessionId":"'$session_id'","text":"'$qrtextid'"}}')
echo qr=$qrtextid response=$response

echo
echo End.
