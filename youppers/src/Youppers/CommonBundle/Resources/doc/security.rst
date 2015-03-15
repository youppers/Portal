
http://www.oclc.org/developer/develop/solution-guides/android-authentication-tutorial-sample-app.en.html


http://blog.tankist.de/blog/2013/07/16/oauth2-explained-part-1-principles-and-terminology/


Public API access

Use of this key does not require any user action or consent, does not grant access to any account information, and is not used for authorization.

https://developers.google.com/console/help/new/?hl=en_US#usingkeys


Generate a client:

php app/console youppers:oauth-server:client:create \
	--redirect-uri="http://192.168.56.101/login" \
	--grant-type="authorization_code" \
	--grant-type="password" \
	--grant-type="refresh-token" \
	--grant-type="token" \
	--grant-type="client_credentials" \
	'client name'
	

Added a new client test1 with
	public id 68c23dbc-cb55-11e4-9357-0800273000da_2sdbtmt96c2s84wscw40o80w0sw00oko084sw0os48ow0cc8ss, 
	secret 1hl9qcrq2hs088ckkggcgc84c4wc4o8ccgccsckg4ko4ok040k
	
	
http://192.168.56.101/app_dev.php/oauth/v2/token?client_id=68c23dbc-cb55-11e4-9357-0800273000da_2sdbtmt96c2s84wscw40o80w0sw00oko084sw0os48ow0cc8ss&client_secret=1hl9qcrq2hs088ckkggcgc84c4wc4o8ccgccsckg4ko4ok040k&grant_type=client_credentials
{"access_token":"MjBkNWI5OWMxMmY1NDgxOTE3OTNlM2FlNWIyY2VjN2YxMWQ1M2EyMTJmMDM2ZDc0OTZkM2RkN2FiZjQxNjYxNg","expires_in":3600,"token_type":"bearer","scope":"youppers_app jsonrpc"}

http://192.168.56.101/app_dev.php/oauth/v2/token?client_id=68c23dbc-cb55-11e4-9357-0800273000da_2sdbtmt96c2s84wscw40o80w0sw00oko084sw0os48ow0cc8ss&client_secret=1hl9qcrq2hs088ckkggcgc84c4wc4o8ccgccsckg4ko4ok040k&username=prova&password=pr0va&grant_type=password
{"access_token":"ZmMzZDJkOTU0YzBjZjRhYmZlMjkzN2U5ZTYxZjM5MzAyM2E4NjYyMDA0Mjc4NDJhMGM4OGUyYTg5ZWMzNWQwMQ","expires_in":3600,"token_type":"bearer","scope":"youppers_app jsonrpc","refresh_token":"NzIwZDZhZjU2OTBlZWVhNTcwNjk2NzA1ZmFmOTc5NmZjYWYxYTFkMDVkMGIxYjdiZDA3MThiODlkNTExY2U2MA"}	


php app/console youppers:oauth-server:client:create \
	--grant-type="password" \
	--grant-type="refresh-token" \
	--grant-type="token" \
	--grant-type="client_credentials" \
	'client name'

	