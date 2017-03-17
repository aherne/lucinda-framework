# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
? @ServletsAPI: getValidPage(); getPathParameters() @ Request uri object
? @ServletsAPI: allow named path parameters (this makes application SLOW!)
- @ServletsAPI: response content type should be configurable in route (<route ... content_type="..."/>
- @Framework: use UserDetails instead of userID (?)  getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
- @Framework: redirect should take into consideration response content type