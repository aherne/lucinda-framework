# Lucinda Framework API

A good web framework should be an integrated solution built of decoupled libraries united for a common purpose, that of creating an "abstract" web application other developers will build upon. Each of these libraries:

- should be able to be used as standalone unit for better testability and reusability (many web applications may only need a MVC library)
- should take complete ownership of its purpose (contain all and nothing but logic related to its purpose)
- should be designed on principles of performance, simplicity and elegance (so it's fast, easy and also pleasurable to work with)

Last but not least, a good framework does only what it absolutely needs to and nothing more! Its users are expected to append their own creative work upon that structure for their specific projects. 

In light of above, Lucinda Framework concerns only in integrating a number of standalone APIs, each implementing an aspect of a web application's logic (eg: security, mvc, templating), for the purpose of building a web app skeleton other developers will work on. Using this methodology, Lucinda Framework maintains simplicity of design (setup is done mainly in XML) and also makes sure no logic that is not explicitly needed will ever be ran. Taken together with absence of dependency injection container (a massive performance killer in all frameworks) and general adherence to aesthetic perfection principles, it predictably outperforms (in terms of both speed and ergonomics) any other fullstack framework written in PHP (23 times faster than Laravel according to Apache Benchmarks)!

More information here:<br/>
http://www.lucinda-framework.com