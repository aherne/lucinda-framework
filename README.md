# admin_template

This is a base website administration tool, to be "extended" into real-life admin tools using these steps:

1. create YOUR_SITE_ADMIN_REPO at github. 
2. git clone https://github.com/hliscorp/admin_template.git. This creates admin_template folder which contains repo files.
3. git clone YOUR_SITE_ADMIN_REPO. This creates YOUR_SITE_ADMIN_REPO folder which contains repo files.
4. copy all contents of admin_template folder into YOUR_SITE_ADMIN_REPO folder, except GIT files.
5. update configuration.xml now present in YOUR_SITE_ADMIN_REPO folder with your specific:
	- COMPILATIONS_PATH: disk path to compilations folder for local, dev & live (this is a requirement of templating engine used by admin)
	- REMEMBER_ME_CODE: 40 char long encryption password to be used for securing remember me cookie (use https://www.random.org/strings/ to generate one)
	- AUTHORIZATION_CODE: 40 char long code all requests to api.datamother.com will be signed with (must be same as the one used by site's syncs). 
	- SERVER_NAME: name of server that's going to be received as $_SERVER["SERVER_NAME"] (eg: dev.casinofreak.com)
6. create a new database, open *dump.sql* file, then import that sql into your db. This will create all the tables your admin tool will fundamentally need.
7. create a mysql user that has access to above database for INSERT/SELECT/UPDATE/DELETE operations
8. update configuration.xml above with:
	- USERNAME: name of user created above, for local/dev/live environments
	- PASSWORD: password of user created above, for local/dev/live environments
	- SCHEMA: name of database created above, for local/dev/live environments
	- PARENT_SCHEMA: name of database used by site admin rules over, for local/dev/live environments
9. give above mysql user INSERT/SELECT/UPDATE/DELETE rights for PARENT_SCHEMA: 
```sql
grant select, insert, update, delete on {PARENT_SCHEMA}.* to '{NAME}'@'localhost' identified by '{PASSWORD}'
``` 
9. update public/img/logo.png with your site's logo

That's all! You can now login using:

Username: lucian@hliscorp.com
Password: hliscorp
