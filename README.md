# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
- @ ServletsAPI: getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
- @ ServletsAPI: getValidPage(); getPathParameters() @ Request uri object
	- allow named path parameters (this makes application SLOW!)
- @ ServletsAPI: replace "extension" with format 
- @ Framework: use UserDetails instead of userID (?)
+ @ Framework: error handler (add support for storing errors in db, logging, etc)
1. refresh should take into consideration response content type
2. refresh should include full path starting with / (!!!)
+ @ Framework: add errors api @ APPLICATION LISTENER after environment is detected
@ServletsAPI: response content type should be settable in route (<route ... content_type="..."/>