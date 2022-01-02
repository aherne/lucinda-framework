# Lucinda Framework

Lucinda Framework 4.0 is an ultra high performance web application skeleton developed with simplicity and modularity at its foundation. In order to fulfil these goals, it functions as an XML-based contract of 24 completely independent APIs, each designed for a particular aspect of a web application's logic:

![diagram](https://www.lucinda-framework.com/lucinda-framework.svg)

APIs that take part in this dance are:

| API | Description |
| --- | --- |
| [Abstract MVC API](https://github.com/aherne/mvc) | handles any type of requests into responses using MVC paradigm |
| [STDOUT MVC API](https://github.com/aherne/php-servlets-api) | handles HTTP requests into responses on top of [Abstract MVC API](https://github.com/aherne/mvc) |
| [STDERR MVC API](https://github.com/aherne/errors-api) | handles errors or uncaught exceptions into reports and responses on top of [Abstract MVC API](https://github.com/aherne/mvc) |
| [View Language API](https://github.com/aherne/php-view-language-api) | templates HTML views using a language extending HTML standard, similar to Java's EL & JSTL |
| [Logging API](https://github.com/aherne/php-logging-api) | logs messages/exceptions to a storage medium |
| [SQL Data Access API](https://github.com/aherne/php-sql-data-access-api) | connects to SQL vendors (eg: MySQL), executes queries and parses results on top of PDO |
| [NoSQL Data Access API](https://github.com/aherne/php-nosql-data-access-api) | connects to NoSQL key-value stores (eg: Redis), executes operations (eg: get) and retrieves results |
| [Web Security API](https://github.com/aherne/php-security-api) | performs authentication and authorization on different combinations |
| [OAuth2 Client API](https://github.com/aherne/oauth2client) | communicates with OAuth2 Providers (eg: Facebook) in order to retrieve remote resources owned by client |
| [HTTP Headers API](https://github.com/aherne/headers-api) | encapsulates HTTP Request/Response headers according to ISO standards and applies Cache/CORS validation when demanded |
| [Internationalization API](https://github.com/aherne/php-internationalization-api) | makes it possible for HTML views to be translated automatically to client's locale |

Framework logic thus becomes strictly one of integrating all above functionalities for a common goal, that of providing an integrated platform for programmers to start developing on. To further modularity and ease of update even further, framework itself is broken into THREE APIs, each with its own repo:

| API | Description |
| --- | --- |
| [Framework Skeleton API](https://github.com/aherne/lucinda-framework) | contains  "mobile" part of framework logic, the project skeleton developers will start working on once framework was installed. Once installed, logic inside becomes owned by developers, so it cannot be updated. |
| [Framework Engine API](https://github.com/aherne/lucinda-framework-engine) | contains  "fixed" part of framework logic, from binding classes required in APIs "conversation as well to general purpose classes. All logic inside is owned by framework, so open for composer update but not open for change by developers. |
| [Framework Configurer API](https://github.com/aherne/lucinda-framework-configurer) | contains console step-by-step configurer as well as files going to be copied on developers' project once process completes. All logic inside is owned by framework, as in above, but choices of configuration are in developer's hands. |

As its composing APIs, framework itself is PHP 8.1+ and PSR4 autoload compliant, using unit testing (for engine) as well as functional testing (for skeleton and configurer) to insure stability.

Thanks to its modular and harmonic design, it's both extremely easy to use (as [tutorials](https://www.lucinda-framework.com/tutorials) show) and exceptionally fast (as [benchmarks](https://www.lucinda-framework.com/benchmarks) show: **75 times faster than Laravel** using factory settings for both). To learn more, go to:

**[https://www.lucinda-framework.com](https://www.lucinda-framework.com)**
