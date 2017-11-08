# Lucinda Framework API

This framework initially grew out years of frustration while working with popular solutions and the style of inept developers they have created! IMHO, a good web framework should be an integrated solution built of decoupled libraries united for a common purpose, that of creating an "abstract" web application other developers will build upon. Each of these libraries:

- should be able to be used as standalone unit for better testability and reusability (case in point: many web applications in fact just need a MVC library and not a framework)
- should take complete ownership of its purpose (contain all and nothing but logic related to its purpose)
- should be designed on principles of performance, simplicity and elegance (so it's fast, easy and also pleasurable to work with)

Last but not least, a good framework does only what it absolutely needs to and nothing more! Its developers are expected to append their own creative work upon that structure for their projects.

As a "good framework", Lucinda Framework concerns only in integrating a number of standalone APIs, each implementing an aspect of a web application's logic (eg: security, mvc, templating), for the purpose of building a skeleton for a complete web application other developers will work on:

- **Servlets API**	High performance MVC library for PHP, loosely inspired by Servlets API and Spring MVC @ Java
- **View Language API**	Implements a templating language that acts like an extension of HTML, designed to eliminate PHP scripting in views.
- **Errors API**	Routes all uncaught errors inside application to a single handler.
- **Logging API**	Implements logging through an abstraction layer that hides loggers' complexity.
- **Web Security API**	Implements most common security patterns employed by web applications.
- **Oauth2 Client**	Implements communication with an OAuth2 provider that hides vendors complexity.
- **SQL Data Access API**	Implements communication with an SQL server through an abstraction layer that hides server complexity.
- **NoSQL Data Access API**	Implements communication with a NoSQL server through an abstraction layer that hides server complexity.
- **HTTP Caching API**	Implements request & response HTTP caching logic.

All above APIs must know nothing of each other in order to be usable as standalone units. *Framework is just this API that, unlike the former, must know of all libraries it integrates via XML*.

Framework uses Servlets API as an architectural foundation, adding its own layer of: configuration.xml tags, event listeners and view resolvers. Via event listeners, all settings in configuration.xml relevant to framework are passed to Lucinda Framework API, who acts like a buffer between the XML and integrated APIs. Inside those event listeners, APIs are loaded and configured according to XML settings by delegation to internal models. Using this methodology, Lucinda Framework maintains simplicity of design (setup is done mainly in XML) and also makes sure no logic that is not explicitly needed will ever be ran. Taken together with absence of dependency injection container (a massive performance killer in all frameworks), they are the main reasons why it outperforms any other true framework written in PHP!

More information here:<br/>
http://www.lucinda-framework.com
