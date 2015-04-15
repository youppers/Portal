#!/bin/bash

#  php app/console youppers:oauth-server:client:create --grant-type="password" --grant-type="refresh_token" --grant-type="token" --grant-type="client_credentials" test

client_id=4181e57a-cb6c-11e4-b4aa-0cc47a127a14_3jej301vyjy8s0ccwc4w4soo8wc4w4ko0wg00wos8kk4cwgs88
client_secret=2zxj9pzrsu4gsg4okcswg84c04gwg4g0oko48wog8c0ocoko8o
host=127.0.0.1

username=signoramaria
password=signoramaria

authendpoint=http://$host/oauth/v2/token
jsonendpoint=http://$host/app_dev.php/jsonrpc/

qrtextid=http://$host/qr/5eeed2c7-abb2-11e4-b4aa-0cc47a127a14?p1=a
qrtextwrong=htxxxtp:///qr1/5eeed2c7-abb2-11e4-b4aa-0cc47a127a14?p1=a
qrtexturl="http://m.marazzi.it/qr_code/BLEND?pid=592&merch=0000V4EE"
qrid=5eeed2c7-abb2-11e4-b4aa-0cc47a127a14
query1=testo non trovato
query2=73800

option1=
option2=
option3=

variant_id=05149cca-cccf-11e4-b4aa-0cc47a127a14
variant_id1=05149cca-cccf-11e4-b4aa-0cc47a127a14
variant_id2=05149cca-cccf-11e4-b4aa-0cc47a127a14
zone_id1=b50715bc-c98c-11e4-b4aa-0cc47a127a14
zone_id2=c6f6f8e4-c98c-11e4-b4aa-0cc47a127a14

pwd=$(dirname $0)
conf=$pwd/config
format=$pwd/format.php

if [ -f $conf ]; then 
	source $conf
else
	echo Add your configuration in $conf
	exit
fi

echo client_id=$client_id
echo client_secret=$client_secret

echo -------------- Request auth_token to $authendpoint: -------------- 

response=$(curl "$authendpoint?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials")
echo response=$response

access_token=$(echo $response|sed -n -e 's/{"access_token":"\([a-zA-Z0-9]*\)",.*/\1/p')
echo access_token=$access_token



echo -------------- Call Session.new  -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Session.new"}')
echo Response:
echo $response | php -f $format

session_id=$(echo $response|sed -n -e 's/.*"result":{"id":"\([a-zA-Z0-9\-]*\)",.*/\1/p')
echo session_id=$session_id

echo -------------- Request auth_token with username to $authendpoint: -------------- 

response=$(curl "$authendpoint?client_id=$client_id&client_secret=$client_secret&grant_type=password&username=$username&password=$password")
echo response=$response

access_token=$(echo $response|sed -n -e 's/{"access_token":"\([a-zA-Z0-9]*\)",.*/\1/p')
echo access_token=$access_token

echo -------------- Show the session after authentication ------------- 
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Session.read","params":{"sessionId":"'$session_id'"}}')
echo session_id=$force_session_id
echo $response | php -f $format

source $pwd/test_zones.sh

echo -------------- Add a variant to 1 zone -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.create","params":{"sessionId":"'$session_id'","items":[{"variantId":"'$variant_id1'","zoneId":"'$zone_id1'"}]}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- Add a variant to 1 zone duplicated -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.create","params":{"sessionId":"'$session_id'","items":[{"variantId":"'$variant_id1'","zoneId":"'$zone_id1'"}]}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- Add a variant to 2 zones -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.create","params":{"sessionId":"'$session_id'","items":[{"variantId":"'$variant_id2'","zoneId":"'$zone_id1'"},{"variantId":"'$variant_id2'","zoneId":"'$zone_id2'"}]}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- List all items -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.list","params":{"sessionId":"'$session_id'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

item_id=$(echo $response|sed -n -e 's/.*"result":\[{"id":"\([a-zA-Z0-9\-]*\)",.*/\1/p')
echo item_id=$item_id

echo -------------- List all items with details -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.list.details","params":{"sessionId":"'$session_id'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- List all items of variant 1 -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.list","params":{"sessionId":"'$session_id'","variantId":"'$variant_id1'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- List all items of variant 2 -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.list","params":{"sessionId":"'$session_id'","variantId":"'$variant_id2'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- Remove the first item -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.remove","params":{"sessionId":"'$session_id'","itemId":"'$item_id'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- Try to remove a wrong item -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.remove","params":{"sessionId":"'$session_id'","itemId":"'x-$item_id'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- List all items after remove -------------
request='{"id":"1","jsonrpc":"2.0","method":"Item.list","params":{"sessionId":"'$session_id'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo -------------- Try to show list of consultants before store selection -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Consultant.list","params":{"sessionId":"'$session_id'"}}')
echo $response | php -f $format 

echo -------------- Search a QR: should fail, dont exists  -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Qr.find","params":{"sessionId":"'$session_id'","text":"'$qrtextwrong'"}}')
echo qr=$qrtextwrong Response:
echo $response | php -f $format 

echo -------------- Search a QR with link url, should be OK  -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Qr.find","params":{"sessionId":"'$session_id'","text":"'$qrtexturl'"}}')
echo qr=$qrtexturl Response:
echo $response | php -f $format 

echo -------------- Search a QR with link id, should be OK  -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Qr.find","params":{"sessionId":"'$session_id'","text":"'$qrtextid'"}}')
echo qr=$qrtextid Response:
echo $response | php -f $format

echo -------------- Show list of consultants selectables for the session -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Consultant.list","params":{"sessionId":"'$session_id'"}}')
#echo consultant_id=$consultant_id
echo $response | php -f $format

echo -------------- Set the consultant for the session -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Session.update","params":{"sessionId":"'$session_id'","data":{"consultant":"'$consultant_id'"}}}')
echo consultant_id=$consultant_id
echo $response | php -f $format

echo -------------- Show a specific session ------------- 
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Session.read","params":{"sessionId":"'$force_session_id'"}}')
echo session_id=$force_session_id
echo $response | php -f $format

echo -------------- Search a product, return list  -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Product.search","params":{"sessionId":"'$session_id'","query":"'$query1'"}}')
echo qr=$query1 Response:
echo $response | php -f $format

echo -------------- Search a product, return list  -------------
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Product.search","params":{"sessionId":"'$session_id'","query":"'$query2'","limit":"2"}}')
echo qr=$query2 Response:
echo $response | php -f $format

echo -------------- Show a variant  -------------
request='{"id":"1","jsonrpc":"2.0","method":"Variant.read","params":{"sessionId":"'$session_id'","variantId":"'$variant_id'"}}'
echo Request=$request
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo variant_id=$variant_id Response:
echo $response | php -f $format

collection_id=$(echo $response|sed -n -e 's/.*"product_collection":{"id":"\([a-zA-Z0-9\-]*\)",.*/\1/p')
echo collection_id=$collection_id

echo -------------- Show a collection  -------------
echo '{"id":"1","jsonrpc":"2.0","method":"Collection.read","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'"}}'
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Collection.read","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'"}}')
echo collection_id=$collection_id Response:
echo $response | php -f $format

echo -------------- Show attributes of a collection  -------------
echo '{"id":"1","jsonrpc":"2.0","method":"Attributes.read","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'"}}'
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Attributes.read","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'"}}')
echo collection_id=$collection_id Response:
echo $response | php -f $format

echo -------------- List of variants with options  -------------
echo Request: "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Variant.list","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'","options":["'$option1'","'$option2'","'$option3'"]}}' 
response=$(curl "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Variant.list","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'","options":["'$option1'","'$option2'","'$option3'"]}}')
echo collection_id=$collection_id Response:
echo $response | php -f $format

product=$(echo $response|sed -n -e 's/.*"product":{"name":"\([^"]*\)",.*/\1/p')
echo product=$product

echo -------------- Send the session  -------------
request='{"id":"1","jsonrpc":"2.0","method":"Session.send","params":{"sessionId":"'$session_id'"}}' 
echo Request: $request  
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
echo $response | php -f $format

echo
echo End.
