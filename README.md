# Lucinda Framework API

This is the api that's going to be used by lucinda framework.

TODOS:
F @Framework: allow seting custom libraries path via constant
F @Framework: remember original page and redirect to it in case of unauthorized/ok cycle
F @Framework: add error author & mail error reporting (so RELEVANT users will be informed)
? @Framework: check if no-sql Couchbase can be converted to NoSQLServerDataSource
F @Framework: make it use composer
F @ServletsAPI: response content type should be configurable in route (<route ... format="..."/>
F @ServletsAPI: <routes ref="XML_PATH"/>
F @ErrorsAPI: if self::$objErrorHandler is not set, var dump exception
F @Oauth2Client: add refresh token support @ Driver
F @all APIs: add namespaces
F @all APIs: create different branches for each API so client sites won't be forced to update their code if a pull is done
+ @LocaleAPI: create another API for internationalization and localization

Documentation:
- show a dependency graph