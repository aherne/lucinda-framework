# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
? @ServletsAPI: getValidPage(); getPathParameters() @ Request uri object
? @ServletsAPI: allow named path parameters (this makes application SLOW!)
? @ServletsAPI: response content type should be configurable in route (<route ... content_type="..."/>
- @ServletsAPI: test on NGINX
* @ServletsAPI: update documentation that controlers are now (again) mandatory
- @ServletsAPI: update documentation that sendRedirect now uses 301 permanent redirect
? @Framework: use UserDetails instead of userID (?)  getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
- @Framework: log hacking exceptions
- @Framework: add error author & mail error reporting
- @NoSQLAPI: add support for redis clusters
- @Oauth2Client: add refresh token support @ Driver
- @ViewLanguageAPI: update documentation to specify that <import> tag does not allow expressions
- @ViewLanguageAPI: make tagName @ <tagLibrary:tagName> resolve into classes using ucwords (eg: <form:simple-select> points to FormSimpleSelectTag
- @ViewLanguageAPI: update documentation about support for nesting native php functions into expressions.
- @ViewLanguageAPI: update documentation that "condition" property @ if/elseif tags does not allow this syntax: <standard:if condition="${count(${asd})}"/>