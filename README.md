# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
? @ServletsAPI: getValidPage(); getPathParameters() @ Request uri object
? @ServletsAPI: allow named path parameters (this makes application SLOW!)
? @ServletsAPI: response content type should be configurable in route (<route ... content_type="..."/>
- @ServletsAPI: test on NGINX
? @Framework: use UserDetails instead of userID (?)  getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
- @Framework: refactor DiskReporter to retrieve exceptions severity from somewhere else
- @Framework: log hacking exceptions
- @NoSQLAPI: add support for redis clusters
- @Oauth2Client: add refresh token support @ Driver