# Lucinda Framework

Table of contents:

- [About](#about)
- [Installation](#installation)
   - [Bootstrapping](#bootstrapping)
     - Setting [.hosts file](#setting-hosts-file)
     - Setting [Apache2](#configuring-apache2) / [NGINX](#configuring-nginx) virtual host
     - Setting [development environment](#setting-development-environment)
   - [Configuration](#configuration)
     - [Automatic configuration](#automatic-configuration), by step-by-step installer
     - [Manual configuration](#manual-configuration), by developers
        - [Declarative API integration](#declarative-integration)
        - [Programmatic API integration](#programmatic-integration)
- [Project Structure](#project-structure)
- [Documentation](#documentation)
   - [index.php](#index-php)
   - [stdout.xml](#stdout-xml)
   - [stderr.xml](#stderr-xml)
   - [Attributes](#attributes)
- Tutorials

## About

Lucinda Framework 3.0 is an ultra high performance web application skeleton developed with simplicity and modularity at its foundation. In order to fulfil these goals, it functions as an XML-based contract of completely independent APIs, each designed for a particular aspect of a web application's logic:

| API | Description |
| --- | --- |
| [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) | handles HTTP requests into responses using MVC paradigm |
| [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) | handles errors or uncaught exceptions into reports and responses using MVC paradigm |
| [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0) | templates HTML views using a language extending HTML standard, similar to Java's EL & JSTL |
| [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) | logs messages/exceptions to a storage medium |
| [SQL Data Access API](https://github.com/aherne/php-sql-data-access-api/tree/v3.0.0) | connects to SQL vendors (eg: MySQL), executes queries and parses results on top of PDO |
| [NoSQL Data Access API](https://github.com/aherne/php-nosql-data-access-api/tree/v3.0.0) | connects to NoSQL key-value stores (eg: Redis), executes operations (eg: get) and retrieves results |
| [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) | performs authentication and authorization on different combinations |
| [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/v3.0.0) | communicates with OAuth2 Providers (eg: Facebook) in order to retrieve remote resources owned by client |
| [HTTP Headers API](https://github.com/aherne/headers-api) | encapsulates HTTP Request/Response headers according to ISO standards and applies Cache/CORS validation when demanded |
| [Internationalization API](https://github.com/aherne/php-internationalization-api/tree/v3.0.0) | makes it possible for HTML views to be translated automatically to client's locale |

Framework logic thus becomes strictly one of integrating all above functionalities for a common goal, that of providing an integrated platform for programmers to start developing on. To further modularity and ease of update even further, framework itself is broken into THREE APIs, each with its own repo:

| API | Description |
| --- | --- |
| [Framework Skeleton API](https://github.com/aherne/lucinda-framework/tree/v3.0.0) | contains  "mobile" part of framework logic, the project skeleton developers will start working on once framework was installed. Once installed, logic inside becomes owned by developers, so it cannot be updated. |
| [Framework Engine API](https://github.com/aherne/lucinda-framework-engine/tree/v2.0.0) | contains  "fixed" part of framework logic, from binding classes required in APIs "conversation as well to general purpose classes. All logic inside is owned by framework, so open for composer update but not open for change by developers. |
| [Framework Configurer API](https://github.com/aherne/lucinda-framework-configurer/tree/v2.0.0) | contains console step-by-step configurer as well as files going to be copied on developers' project once process completes. All logic inside is owned by framework, as in above, but choices of configuration are in developer's hands. |

As its composing APIs, framework is PHP 7.1+ and PSR4 autoload compliant, using unit testing (for engine) as well as functional testing (for skeleton and configurer) to insure stability.

![diagram](https://www.lucinda-framework.com/public/images/svg/lucinda-framework.svg?version=1.0.0)

## Installation

To install framework, open console and run:

```console
cd YOUR_WEB_ROOT
git clone -b v3.0.0 https://github.com/aherne/lucinda-framework YOUR_PROJECT_NAME
cd YOUR_PROJECT_NAME
composer update
mkdir -m 777 compilations # if your OS is UNIX-based (eg: Mac, Linux)
mkdir compilations # if your OS is Windows-based
```

After you've finished [bootstrapping](#bootstrapping) and have a YOUR_HOST_NAME ready, pointing to YOUR_WEB_ROOT/YOUR_PROJECT_NAME, open your browser at http://YOUR_HOST_NAME and follow steps described there to configure your project.


## Bootstrapping

Bootstrapping is the process by which all requests to YOUR_HOST_NAME are *routed* to YOUR_WEB_ROOT/YOUR_PROJECT_NAME according to following rules:

- any request NOT pointing to static resources (eg: images) is *rerouted* to index.php file to be handled by framework
- anything else is to be served directly by web server without framework mediation

This process requires you to perform two steps, regardless of operating system:

- registering YOUR_HOST_NAME in [.hosts](#setting-hosts-file) file
- creating a virtual host on your [Apache2](#configuring-apache2) / [NGINX](#configuring-nginx) web server to perform bootstrapping
- setting [development environment](#setting-development-environment)

After you're all done, simply restart web server and go to http://YOUR_HOST_NAME and follow steps described there.

### Setting .hosts file

Now you will need a virtual host that makes sure all requests to YOUR_HOST_NAME point to YOUR_WEB_ROOT/YOUR_PROJECT_NAME, so first open .hosts file (/etc/hosts @ Unix, C:\Windows\System32\drivers\etc\hosts @ Windows) and add this line:

```console
127.0.0.1 YOUR_HOST_NAME
```

### Configuring Apache2

If you're using **Apache2**, after you've made sure *mod_rewrite* and *mod_env* are enabled, open general vhosts configuration file or create a separate vhost file then write:

```console
<VirtualHost *:80>
    # sets site domain name (eg: www.testing.local)
    ServerName YOUR_HOST_NAME
    # sets location of site on disk (eg: /var/www/html/testing)
    DocumentRoot YOUR_WEB_ROOT/YOUR_PROJECT_NAME
    # delegates rerouting to htaccess file above
    <Directory YOUR_WEB_ROOT/YOUR_PROJECT_NAME>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

This enables **.htaccess** in project root, which has following lines:

```console
# informs Apache2 web server you are going to reroute requests
RewriteEngine on
# turns off directory listing
Options -Indexes
# makes 404 responses to public (images, js, css) files handled by web server
ErrorDocument 404 default
# lets web server allow Authorization request header
RewriteRule .? - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
# redirects all requests, except those pointing to public files, to bootstrap
RewriteCond %{REQUEST_URI} !^/public
RewriteCond %{REQUEST_URI} !^/favicon.ico
RewriteRule ^(.*)$ index.php
```

If you have no control on web server and .htaccess is disabled, sysadmins should put above lines in virtualhost.

### Configuring NGINX

If you're using **NGINX**, open general vhosts configuration file or create a separate vhost file then write:

```console
server {
    listen 80;
    listen [::]:80 ipv6only=on;
    # sets location of site on disk (eg: /var/www/html/testing)
    root YOUR_WEB_ROOT/YOUR_PROJECT_NAME;
    # sets location of bootstrap file
    index index;
    # sets site domain name (eg: www.testing.local)
    server_name YOUR_HOST_NAME;
    # redirects all requests, except those pointing to public files, to bootstrap
    location / {
        rewrite ^/(.*)$ /index;
    }
    location /public/ {
    }
    location /favicon.ico {
    }
    # configures PHP-FPM to handle requests
    location ~ \$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\)(/.+)$;
        # location of PHP-FPM socket file (eg: /var/run/php/php7.0-fpm.sock)
        fastcgi_pass unix:SOCKET_FILE_LOCATION;
        fastcgi_index index;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SERVER_ADMIN NA;
        fastcgi_param SERVER_SIGNATURE nginx/$nginx_version;
        include fastcgi_params;
    }
}
```

### Setting Development Environment

Development environment is a **mandatory** string value that uniquely identifies a machine against others running the same project. It must be set on web server directly, so framework's job is just to retrieve it.

To set development environment in **Apache2**, open *.htaccess* file and append this line:

```console
SetEnv ENVIRONMENT local
```

To set development environment in **NGINX**, edit PHP-FPM configuration file (eg: /etc/php/7.2/fpm/php-fpm.conf) then append this line:

```console
env[ENVIRONMENT] = local
```
Above directives set your development environment as *local* (your personal workstation).

## Configuration

In order to enforce performance and modularity, **project starts with MVC abilities only**! Framework itself is the marriage contract between [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) and [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0), handling request-response and exception-response flows, with an ability of other APIs (functionalities) to be hooked [declaratively](#declarative-integration) (through XML) or at [runtime](#programmatic-integration) (through event listeners).

### Automatic Configuration

A console step-by-step configurer is available in your project root, performing all integration needed to make your project fully featured:

```console
cd YOUR_WEB_ROOT/YOUR_PROJECT_NAME
php configure.php project
```

Option combinations given by configurer are limited for most common scenarios, though. Whenever your scenario is not covered, doing it manually based on manual configuration remains the only viable choice only choice!

### Manual configuration

As mentioned above, there are two ways in which framework is awarded abilities:

- through XML files that configure the MVC apis ([declarative integration](#declarative-integration))
- through listeners that execute when lifecycle events are reached ([programmatic integration](#programmatic-integration))

This is done automatically by console step-by-step configurer, but can be done by developers directly to gain more options.

#### Declarative Integration

MVC APIs have some tags holding attributes that point to class names and paths. Each of them offers a potential hook point to join other APIs or developers' own implementation:

| API | XML File | XML Tag | Tag Attribute | Class Prototype | Description |
| --- | --- | ---  | --- | --- | --- |
| [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) | [stderr.xml](#stderr-xml) | [reporter](https://github.com/aherne/errors-api#reporters) | class | [Lucinda\STDERR\Reporter](https://github.com/aherne/errors-api#abstract-class-reporter) | reports error to a storage medium |
| [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) | [stderr.xml](#stderr-xml) | [resolver](https://github.com/aherne/errors-api#resolvers) | class | [Lucinda\STDERR\ViewResolver](https://github.com/aherne/errors-api#abstract-class-viewresolver) | resolves view into response by format |
| [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) | [stderr.xml](#stderr-xml) | [exceptions/exception](https://github.com/aherne/errors-api#exceptions) | controller | [Lucinda\STDERR\Controller](https://github.com/aherne/errors-api#abstract-class-controller) | handles an exception route |
| [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [format](https://github.com/aherne/php-servlets-api#formats) | class | [Lucinda\STDOUT\ViewResolver](https://github.com/aherne/php-servlets-api#abstract-class-viewresolver) | resolves view into response by format |
| [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [route](https://github.com/aherne/php-servlets-api#routes) | controller | [Lucinda\STDOUT\Controller](https://github.com/aherne/php-servlets-api#abstract-class-controller) | handles an URI route |
| [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [session](https://github.com/aherne/php-servlets-api#session) | handler | [\SessionHandlerInterface](https://www.php.net/manual/en/class.sessionhandlerinterface.php) | handles session by storage medium |

Whatever is hooked there will be integrated by MVC APIs automatically, whenever interpreter reaches respective section. APIs to integrate, on the other hand, provide their own hook points as well:

| API | XML File | XML Tag | Tag Attribute | Class Prototype | Description |
| --- | --- | --- | --- | --- | --- |
| [HTTP Headers API](https://github.com/aherne/headers-api) |  [stdout.xml](#stdout-xml) | [headers](https://github.com/aherne/headers-api#headers) | cacheable* | [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php) | generates ETag and LastModified header values for response |
| [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [logger](https://github.com/aherne/php-logging-api/tree/v3.0.0#configuration) | class | [Lucinda\Logging\AbstractLoggerWrapper](https://github.com/aherne/php-logging-api/blob/v3.0.0/src/AbstractLoggerWrapper.php) | writes message/exception to a storage medium |
| [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [form](https://github.com/aherne/php-security-api/tree/v3.0.0#security) | dao | [Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authentication/DAO/UserAuthenticationDAO.php) | authenticates user in database by form |
| [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [form](https://github.com/aherne/php-security-api/tree/v3.0.0#security) | throttler | [Lucinda\WebSecurity\Authentication\Form\LoginThrottler](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authentication/Form/LoginThrottler.php) | throttles failed login attempts against brute-force attacks |
| [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [oauth2](https://github.com/aherne/php-security-api/tree/v3.0.0#security) | dao | [Lucinda\WebSecurity\Authentication\OAuth2\VendorAuthenticationDAO](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authentication/OAuth2/VendorAuthenticationDAO.php) | authenticates user in database by oauth2 provider |
| [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [by_dao](https://github.com/aherne/php-security-api/tree/v3.0.0#security) | page_dao | [Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authorization/DAO/PageAuthorizationDAO.php) | gets rights for route requested in database |
| [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) | [stdout.xml](#stdout-xml) | [by_dao](https://github.com/aherne/php-security-api/tree/v3.0.0#security) | user_dao | [Lucinda\WebSecurity\Authorization\DAO\UserAuthorizationDAO](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authorization/DAO/PageAuthorizationDAO.php) | authorizes user to route requested in database |

\*: attribute added by Lucinda framework

#### Programmatic Integration

[STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) also allows you to manually integrate APIs or user code through [event listeners](https://github.com/aherne/php-servlets-api#binding-events) in **[index.php]()** file via *addEventListener* method. Syntax:

```php
$object->addEventListener(EVENT_TYPE, CLASS_NAME);
```

Following event types are available, corresponding to a prototype  respective listener must extend, triggered on a lifecycle event:

| Event Type | Class Prototype | Triggered |
| --- | --- | --- |
| Lucinda\STDOUT\EventType::START | [Lucinda\STDOUT\EventListeners\Start](https://github.com/aherne/php-servlets-api#abstract-class-eventlisteners-start) | Before [stdout.xml](#stdout-xml) is read. |
| Lucinda\STDOUT\EventType::APPLICATION	| [Lucinda\STDOUT\EventListeners\Application](https://github.com/aherne/php-servlets-api#abstract-class-eventlisteners-application)	| After [stdout.xml](#stdout-xml) is read, before request is read |
| Lucinda\STDOUT\EventType::REQUEST | [Lucinda\STDOUT\EventListeners\Request](https://github.com/aherne/php-servlets-api#abstract-class-eventlisteners-request) | After request is read, before controller runs |
| Lucinda\STDOUT\EventType::RESPONSE | [Lucinda\STDOUT\EventListeners\Response](https://github.com/aherne/php-servlets-api#abstract-class-eventlisteners-response) | After view resolver runs, before response is outputted |
| Lucinda\STDOUT\EventType::END | [Lucinda\STDOUT\EventListeners\End](https://github.com/aherne/php-servlets-api#abstract-class-eventlisteners-end)	 | After response is outputted |

Example:

```php
$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "ErrorListener");
```

Event listeners will be *run* in the order they are set once respective lifecycle event is reached. All event listeners will be located in [application/listeners](https://github.com/aherne/lucinda-framework/tree/v3.0.0/application/listeners) folder!

## Project Structure

Any project using this framework will use following file/folder structure:

 * [*.htaccess*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/.htaccess): Apache2 configuration file
 * [*composer.json*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/composer.json): framework or your project composer dependencies definitions
 * [*configure.php*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/configure.php): step-by-step console configurer adapting framework to your project needs
 * [*index.php*](#index-php): bootstrap PHP file starting framework
 * [*stderr.xml*](#stderr-xml): configures [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) to manage exception-response flow. 			
 * [*stdout.xml*](#stdout-xml): configures [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to manage request-response flow.
 * **application**: contains framework skeleton logic and user code built on its foundation
    * **cacheables**: stores classes implementing [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php)
       * [*DateCacheable*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/cacheables/DateCacheable.php): binds to [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) and [NoSQL Data Access API](https://github.com/aherne/php-nosql-data-access-api/tree/v3.0.0) for generating a *LastModified* response header value based on response body<br/><small>Requires: [*HttpHeadersListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpHeadersListener.php) + [*NoSQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/NoSQLDataSourceInjector.php)</small>
       * [*EtagCacheable*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/cacheables/EtagCacheable.php): binds to [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) for generating an *Etag* response header value based on response body.<br/><small>Requires: [*HttpHeadersListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpHeadersListener.php)</small>
    * **controllers**: stores classes implementing [Lucinda\STDOUT\Controller](https://github.com/aherne/php-servlets-api#abstract-class-controller) (or [Lucinda\Framework\RestController](https://github.com/aherne/lucinda-framework-engine/tree/v2.0.0/#RestController), if your project is RESTful) and [Lucinda\STDERR\Controller](https://github.com/aherne/errors-api#abstract-class-controller), of whom following are provided by framework
       * [*ErrorsController*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/controllers/ErrorsController.php): implements [Lucinda\STDERR\Controller](https://github.com/aherne/errors-api#abstract-class-controller) to handle all errors/exceptions, unless specifically handled
       * [*SecurityPacketController*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/controllers/SecurityPacketController.php): implements [Lucinda\STDERR\Controller](https://github.com/aherne/errors-api#abstract-class-controller) to specifically handle [Lucinda\WebSecurity\SecurityPacket](https://github.com/aherne/php-security-api/tree/v3.0.0#handling-securitypacket) exceptions thrown when authentication/authorization requires state change<br/><small>Requires: [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php)</small>
    * **handlers**: stores classes implementing [\SessionHandlerInterface](https://www.php.net/manual/en/class.sessionhandlerinterface.php)
       * [*NoSQLSessionHandler*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/handlers/NoSQLSessionHandler.php): binds to [NoSQL Data Access API](https://github.com/aherne/php-nosql-data-access-api/tree/v3.0.0) for saving session into a key-value store for distributed applications<br/><small>Requires: [*NoSQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/NoSQLDataSourceInjector.php)</small>
    * **listeners**: stores event listeners (to be used in this order, if you need any!), each awarding an ability
       * [*LoggingListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/LoggingListener.php): binds [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) to gain logging ability
       * [*SQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SQLDataSourceInjector.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to gain SQL querying ability
       * [*NoSQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/NoSQLDataSourceInjector.php): binds [NoSQL Data Access API](https://github.com/aherne/php-nosql-data-access-api/tree/v3.0.0) to gain NoSQL key-value store querying abilities
       * [*ErrorListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/ErrorListener.php): informs [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) to use same response format as requested resource
       * [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) and
[OAuth2 Client API](https://github.com/aherne/oauth2client/tree/v3.0.0) to gain authentication/authorization abilities
       * [*LocalizationListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/LocalizationListener.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to [Internationalization API](https://github.com/aherne/php-internationalization-api/tree/v3.0.0) in order to gain automated translation ability for views
       * [*HttpHeadersListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpHeadersListener.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to [HTTP Headers API](https://github.com/aherne/headers-api) in order to gain HTTP headers abilities
       * [*HttpCorsListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpCorsListener.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to [HTTP Headers API](https://github.com/aherne/headers-api) in order to validate CORS requests and render response immediately<br/><small>Requires: [*HttpHeadersListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpHeadersListener.php)</small>
       * [*HttpCachingListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpCachingListener.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) to [HTTP Headers API](https://github.com/aherne/headers-api) in order to validate cache header requests and respond with headers whenever possible<br/><small>Requires: [*HttpHeadersListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpHeadersListener.php)</small>
    * **loggers**: stores classes implementing [Lucinda\Logging\AbstractLoggerWrapper](https://github.com/aherne/php-logging-api/blob/v3.0.0/src/AbstractLoggerWrapper.php)
       * [*FileLogger*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/loggers/FileLogger.php): uses [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) to log into files
       * [*SyslogLogger*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/loggers/SyslogLogger.php): uses [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) to log into syslog
    * **models**: stores your project's business logic
        * **dao**: stores data access object classes working with databases
           * [*NoSqlLoginThrottler*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/models/dao/NoSqlLoginThrottler.php): implements [Lucinda\WebSecurity\Authentication\Form\LoginThrottler](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authentication/Form/LoginThrottler.php), using SQL database to track failed logins<br/><small>Requires: [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php) + [*SQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SQLDataSourceInjector.php)</small>
           * [*SqlLoginThrottler*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/models/dao/NoSqlLoginThrottler.php): implements [Lucinda\WebSecurity\Authentication\Form\LoginThrottler](https://github.com/aherne/php-security-api/blob/v3.0.0/src/Authentication/Form/LoginThrottler.php), using NoSQL database to track failed logins<br/><small>Requires: [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php) + [*NoSQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/NoSQLDataSourceInjector.php)</small>
        * [*Attributes*](#attributes): extends [Lucinda\STDOUT\Attributes](https://github.com/aherne/php-servlets-api/tree/v3.0.0#configuring-shared-variables) to contain data detected by project event listeners, accessible in subsequent event listeners and controllers via getters.
        * [*EmergencyHandler*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/models/EmergencyHandler.php): a [Lucinda\STDERR\ErrorHandler](https://github.com/aherne/errors-api/blob/v2.0.0/src/ErrorHandler.php) to be used when errors are encountered as [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) is handling
        * [*getRemoteResource*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/composer.json): a procedural function that makes it extremely easy to retrieve a resource from a connected OAuth2 provider<br/><small>Requires: [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php)</small>
        * [*SQL*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/composer.json):  a procedural function that makes it extremely easy to query an SQL server using prepared statements and handle results<br/><small>Requires: [*SQLDataSourceInjector*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SQLDataSourceInjector.php)</small>
        * [*translate*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/composer.json): a procedural function that makes it extremely easy to automatically translate sections in views by keyword <br/><small>Requires: [*LocalizationListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/LocalizationListener.php)</small>
    * **renderers**: stores classes implementing [Lucinda\STDERR\ViewResolver](https://github.com/aherne/errors-api#abstract-class-viewresolver)
        * [*HtmlRenderer*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/renderers/HtmlRenderer.php): binds [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) with [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0) to resolve *html* views
        * [*JsonRenderer*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/renderers/JsonRenderer.php): binds [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) with [Lucinda\Framework\Json](https://github.com/aherne/lucinda-framework-engine/tree/v2.0.0/#Json) to resolve *json* views
    * **reporters**: stores classes implementing [Lucinda\STDERR\Reporter](https://github.com/aherne/errors-api#abstract-class-reporter)
        * [*FileReporter*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/reporters/FileReporter.php): binds [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) with [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) to report error in file
        * [*SyslogReporter*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/reporters/SyslogReporter.php): binds [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) with [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) to report error in syslog
    * **resolvers**: stores classes implementing [Lucinda\STDOUT\ViewResolver](https://github.com/aherne/php-servlets-api#abstract-class-viewresolver)
        * [*HtmlResolver*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/resolvers/HtmlResolver.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) with [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0) to resolve *html* views
        * [*JsonResolver*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/resolvers/JsonResolver.php): binds [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) with [Lucinda\Framework\Json](https://github.com/aherne/lucinda-framework-engine/tree/v2.0.0/#Json) to resolve *json* views
    * **tags**: stores [tag libraries](https://www.lucinda-framework.com/view-language/tags) for [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0)
    * **views**: stores [templates/views](https://github.com/aherne/php-view-language-api/tree/v3.0.0#examples) for [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0)
 * **compilations**: contains [compilations](https://github.com/aherne/php-view-language-api/tree/v3.0.0#compilation) for [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0)
 * **public**: contains your static project files (images, js, css)
 * **vendor**: contains APIs pulled based on [*composer.json*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/composer.json) above

## Documentation

### index.php<a name="index-php"></a>

All requests to your project, except those pointing to static files (eg: public folder), will be [bootstrapped](#bootstrapping) to [**index.php**](https://github.com/aherne/lucinda-framework/blob/v3.0.0/index.php) file, whose only job is to start the framework and manage your application in following steps:

* loads composer autoloader
* detects development environment registered on web server (see ) into **ENVIRONMENT** constant
* registers [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) as handler of exception-response flow based on:
   * **[stderr.xml](#stderr-xml)** file where API is configured
   * **ENVIRONMENT** detected above
   * **[EmergencyHandler](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/models/EmergencyHandler.php)** instance to handle errors that may occur during handling process itself
* lets above stand in suspended animation unless an error/exception is thrown below
* registers [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) as handler of request-response flow based on:
   * **[stdout.xml](#stdout-xml)** file where API is configured
   * **ENVIRONMENT** detected above
   * **[Attributes](#attributes)** instance to make values set by event listeners available to controllers or subsequent event listeners
* registers event listeners that execute when a [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) lifecycle event is reached (**[ErrorListener](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/ErrorListener.php)** added by default)
* starts request-response handling

### stderr.xml<a name="stderr-xml"></a>

This file is required to configure [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) as well as providing an integration platform of other APIs via following tags:

- [*application*](https://github.com/aherne/errors-api#application): stores application settings for [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0)
- [*exceptions*](https://github.com/aherne/errors-api#exceptions): stores routes for [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0), each mapping an exception thrown by APIs employed by framework
- [*resolvers*](https://github.com/aherne/errors-api#resolvers): stores view resolver definitions for [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0)
- [*reporters*](https://github.com/aherne/errors-api#reporters): stores reporters for [STDERR MVC API](https://github.com/aherne/errors-api/tree/v2.0.0) per **ENVIRONMENT**, binding to [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0)

### stdout.xml<a name="stdout-xml"></a>

This file is required to configure [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) as well as providing an integration platform of other APIs via following tags:

- [*application*](https://github.com/aherne/php-servlets-api#application): stores application settings for [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0)
- [*routes*](https://github.com/aherne/php-servlets-api#routes): stores routes for [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0), [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) or [HTTP Headers API](https://github.com/aherne/headers-api), each mapping a request URI
- [*formats*](https://github.com/aherne/php-servlets-api#formats): stores view resolver definitions for [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0)
- [*session*](https://github.com/aherne/php-servlets-api#session): stores rules to create session with for [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0)
- [*cookies*](https://github.com/aherne/php-servlets-api#cookies): stores rules to create cookies with for [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0)
- [*templating*](https://github.com/aherne/php-view-language-api#configuration): stores html templating settings for [View Language API](https://github.com/aherne/php-view-language-api/tree/v3.0.0)
- [*loggers*](https://github.com/aherne/php-logging-api#configuration): stores loggers for [Logging API](https://github.com/aherne/php-logging-api/tree/v3.0.0) per **ENVIRONMENT**
- [*sql*](https://github.com/aherne/php-sql-data-access-api#configuration): stores SQL database servers to connect to for [SQL Data Access API](https://github.com/aherne/php-sql-data-access-api/tree/v3.0.0) per **ENVIRONMENT**
- [*nosql*](https://github.com/aherne/php-nosql-data-access-api#configuration): stores NoSQL database servers to connect to for [NoSQL Data Access API](https://github.com/aherne/php-nosql-data-access-api/tree/v3.0.0) per **ENVIRONMENT**
- [*security*](https://github.com/aherne/php-security-api#security): stores authentication and authorization settings for [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0) for
- [*users*](https://github.com/aherne/php-security-api#users): stores list of site users for [Web Security API](https://github.com/aherne/php-security-api/tree/v3.0.0)
- [*oauth2*](https://github.com/aherne/oauth2client#configuration): stores list of project accounts for [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/v3.0.0) per **ENVIRONMENT**
- [*internationalization*](https://github.com/aherne/php-internationalization-api#configuration): stores internationalization and localization settings for [Internationalization API](https://github.com/aherne/php-internationalization-api/tree/v3.0.0)
- [*headers*](https://github.com/aherne/headers-api#configuration): stores header policies for [HTTP Headers API](https://github.com/aherne/headers-api)

To integrate functionalities not present in framework, developers are free to add any other tag here, provided they are bound to an event listener.

### Attributes

As event listeners are running, they may have detected information useful for subsequent listeners and controllers. [STDOUT MVC API](https://github.com/aherne/php-servlets-api/tree/v3.0.0) uses [Lucinda\STDOUT\Attributes](https://github.com/aherne/php-servlets-api/tree/v3.0.0#configuring-shared-variables) to store this information and makes latter object available throughout entire request-response phase. Event listeners will thus record information using *setters* so it becomes available afterwards using *getters*

Framework extends above with [Attributes](https://github.com/aherne/php-servlets-api/tree/v3.0.0/application/models/Attributes.php), which adds following getters relevant for developers:


| Method | Arguments | Returns | Description | Event Listener |
| --- | --- | --- | --- | --- |
| getHeaders | void | [\Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api#initialization)\|NULL | Gets class to use in reading request headers received and writing response headers | [*HttpHeadersListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/HttpHeadersListener.php) |
| getLogger | void | [\Lucinda\Logging\Logger](https://github.com/aherne/php-logging-api/tree/v3.0.0#logging)\|NULL | Gets class to log messages with | [*LoggingListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/LoggingListener.php) |
| getUserId | void | string\|integer | Gets logged in user id | [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php) |
| getCsrfToken | void | string\|NULL | Gets token to send as 'csrf' POST param when logging in by form | [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php) |
| getAccessToken | void | string\|NULL | Gets token to be remembered and presented by client for stateless authentication as 'Authorization' header | [*SecurityListener*](https://github.com/aherne/lucinda-framework/blob/v3.0.0/application/listeners/SecurityListener.php) |

As they are developing their own event listeners, developers are expected to modify this class and add setters&getters for any information they want to put in transport layer.
