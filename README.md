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
