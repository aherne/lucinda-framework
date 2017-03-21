# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
? @ServletsAPI: getValidPage(); getPathParameters() @ Request uri object
? @ServletsAPI: allow named path parameters (this makes application SLOW!)
- @ServletsAPI: response content type should be configurable in route (<route ... content_type="..."/>
- @Framework: use UserDetails instead of userID (?)  getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
- @Framework: redirect should take into consideration response content type
- @ Framework: handling errors by SecurityListener
??? Investigate setting $_ENV attributes

- @ServletsAPI: test on NGINX
- @NoSQLAPI: add support for redis clusters
- @Oauth2Client: add refresh token support @ Driver
- @Framework: determine error severity by type of exception 