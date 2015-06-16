#!/bin/bash

client_id=4181e57a-cb6c-11e4-b4aa-0cc47a127a14_3jej301vyjy8s0ccwc4w4soo8wc4w4ko0wg00wos8kk4cwgs88
client_secret=2zxj9pzrsu4gsg4okcswg84c04gwg4g0oko48wog8c0ocoko8o
host=127.0.0.1

qrtextid=http://$host/qr/5eeed2c7-abb2-11e4-b4aa-0cc47a127a14?p1=a

variant_id=05149cca-cccf-11e4-b4aa-0cc47a127a14

productId=c2-11e4-a84b-0800273000da-K311261
storeId=762d6fdb-aba8-11e4-b4aa-0cc47a127a14
boxId=1879dd78-abb0-11e4-b4aa-0cc47a127a14

pwd=$(dirname $0)
conf=$pwd/config

if [ -f $conf ]; then 
	source $conf
else
	echo Add your configuration in $conf
	exit
fi

authendpoint=http://$host/oauth/v2/token
#jsonendpoint=http://$host/app_dev.php/jsonrpc/
jsonendpoint=http://$host/jsonrpc/

echo client_id=$client_id
echo client_secret=$client_secret

# -------------- Request auth_token to $authendpoint: --------------
response=$(curl "$authendpoint?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials")
access_token=$(echo $response|sed -n -e 's/{"access_token":"\([a-zA-Z0-9]*\)",.*/\1/p')

# -------------- Show a variant  -------------
request='{"id":"1","jsonrpc":"2.0","method":"Variant.read","params":{"sessionId":"'$session_id'","variantId":"'$variant_id'"}}'
response=$(curl "$jsonendpoint?access_token=$access_token" -d $request)
collection_id=$(echo $response|sed -n -e 's/.*"product_collection":{"id":"\([a-zA-Z0-9\-]*\)",.*/\1/p')
echo collection_id=$collection_id

for i in {1..10}
do

# -------------- Show a variant  -------------
request='{"id":"1","jsonrpc":"2.0","method":"Variant.read","params":{"sessionId":"'$session_id'","variantId":"'$variant_id'"}}'
tvr=$(curl -s -w "%{time_total}\n" -o /dev/null "$jsonendpoint?access_token=$access_token" -d $request)

# -------------- Show a collection  -------------
tcr=$(curl -s -w "%{time_total}\n" -o /dev/null "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Collection.read","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'"}}')

# -------------- Show attributes of a variant  -------------
#request='{"id":"1","jsonrpc":"2.0","method":"Attributes.read","params":{"variantId":"2c897a91-dec5-11e4-b4aa-0cc47a127a14"}}'
#tar=$(curl -s -w "%{time_total}\n" -o /dev/null "$jsonendpoint?access_token=$access_token" -d $request)

# -------------- Show attributes of a collection  -------------
tar=$(curl -s -w "%{time_total}\n" -o /dev/null "$jsonendpoint?access_token=$access_token" -d '{"id":"1","jsonrpc":"2.0","method":"Attributes.read","params":{"sessionId":"'$session_id'","collectionId":"'$collection_id'"}}')

# -------------- Find boxes for a product in a store  -------------
request='{"id":"1","jsonrpc":"2.0","method":"Box.list","params":{"productId":"'$productId'","storeId":"'$storeId'"}}'
tbl=$(curl -s -w "%{time_total}\n" -o /dev/null "$jsonendpoint?access_token=$access_token" -d $request)

echo tvr=$tvr tcr=$tcr tar=$tar tbl=$tbl
done

echo End.
