# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
F @ServletsAPI: response content type should be configurable in route (<route ... extension="..."/>
F @ServletsAPI: <routes ref="XML_PATH"/>
F @Framework: remember original page and redirect to it in case of unauthorized/ok cycle
F @ErrorsAPI: if self::$objErrorHandler is not set, var dump exception
F @Framework: add error author & mail error reporting (so RELEVANT users will be informed)
F @NoSQLAPI: add support for redis clusters
F @Oauth2Client: add refresh token support @ Driver
F @SecurityAPI: finish refactoring of Token authentication (synchronizer & JWT)
+ Caching API: client of NoSQLAPI
	+ HTTP caching

Documentation:
- write docs for remaining api
- integrate framework documentation with apis
- show a dependency graph