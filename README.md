# SCMP API Team Assessment

To install the skeleton project, you will need [Composer].

   [Composer]: <https://getcomposer.org/>

```
composer install
composer drupal-install
```

The installer will ask for a `sudo` password, to make sure that Drupal's `settings.php` file is created with the right permissions.

You can run the skeleton project using PHP's built in webserver, using the router script in the `web` directory.

```
cd web
php -S localhost:8080 .ht.router.php
```

You can then visit the website at [http://localhost:8080/](http://localhost:8080/).  The default username and password are both `admin`.  

Adding a virtual host in MAMP for Mac
Open your host file by pasting the following in your terminal:

```
vi /etc/hosts
```
You will see something like below. Add a "d8-api.com" domain name. Just make one up, it will only be locally known to your computer. Be sure to match the ip address with the one before “localhost”. Saving this file will prompt you for your password.

```
127.0.0.1 d8-api.com
```
vhost file
Next, open up your MAMP vhost conf file:

```
vi /Applications/MAMP/conf/apache/extra/httpd-vhosts.conf
```

Paste the following content:

```
<VirtualHost *:80>
 DocumentRoot "/Applications/MAMP/htdocs/d8-api/web"
 ServerName d8-api.com
</VirtualHost>
```

Restart Mamp

Create articles and verify with below sample URL.

Open ```Postman tool``` and select basic auth. Apply user name and password. Add header key is ```Content-Type``` and value is ```application/json```
1) The details of a single Article - http://d8-api.com/api/article/1 (http://d8-api.com/api/article/<node_id>)
2) A list of all Articles published on a given day - http://d8-api.com/api/articles-by-date/29-11-2018 (http://d8-api.com/api/articles-by-date/<publication_date>)
3) A list of all Articles published in a given date range - http://d8-api.com/api/articles-by-date-range/01-11-2018/30-11-2018 (http://d8-api.com/api/articles-by-date-range/<publication_from_date>/<publication_to_date>)
4) A list of the N(10) most recent articles published under a given Topic - http://d8-api.com/api/articles-by-topic/Cricket (http://d8-api.com/api/articles-by-topic/<term_name>)

linter-drupalcs plugin for atom editor used to follow drupal best practice and coding standard.
Verified all the above mentioned endpoint URLs with positive and negative scenario.
