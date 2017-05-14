# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
? @ServletsAPI: allow named path parameters (this makes application SLOW!)
F @ServletsAPI: response content type should be configurable in route (<route ... content_type="..."/>
F @ServletsAPI: use UserDetails instead of userID (?)  getSecurityContext() @ Request object (which contains: UserDetails & csrf token)
F @Framework: add error author & mail error reporting
F @Framework: make ViewLanguageResolver work with customizable view extension (today it's .php always)
! @Framework: checks will fail if <login/> tag comes with no parameters (all defaults). if($xml->login) must be changed to...
F @NoSQLAPI: add support for redis clusters
F @Oauth2Client: add refresh token support @ Driver
F @Framework: remember original page and redirect to it in case of unauthorized/ok cycle
  if persistence method is session
      authentication result = unauthorized
        record originally requested page as "original_page"
          redirect to login
      (client logins)
      authentication result = ok
        original page is not empty, change callback_uri with original_page and unset original_page variable
