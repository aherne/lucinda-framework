# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
? @ServletsAPI: allow named path parameters (this makes application SLOW!)
F @ServletsAPI: response content type should be configurable in route (<route ... content_type="..."/>
! @ServletsAPI: test on NGINX
D @ServletsAPI: update documentation that controlers are now (again) mandatory
F @Framework: use UserDetails instead of userID (?)  getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
F @Framework: add error author & mail error reporting
D @Framework: in documentation, make it clear that 401,403,404 pages have a $context variable available to use in redirection (if they were called from SecurityListener)
F @Framework: make ViewLanguageResolver work with customizable view extension (today it's .php always)
! @Framework: checks will fail if <login/> tag comes with no parameters (all defaults). if($xml->login) must be changed to...
! @Framework: convert header("location everywhere to Response::sendRedirect(...);
F @NoSQLAPI: add support for redis clusters
F @Oauth2Client: add refresh token support @ Driver
F @ViewLanguageAPI: make tagName @ <tagLibrary:tagName> resolve into classes using ucwords (eg: <form:simple-select> points to FormSimpleSelectTag
D @ViewLanguageAPI: update documentation to specify that tag names cannot contain expressions
D @ViewLanguageAPI: update documentation about support for nesting native php functions into expressions.
D @ViewLanguageAPI: update documentation that "condition" property @ if/elseif tags does not allow this syntax: <standard:if condition="${count(${asd})}"/>