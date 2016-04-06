# wp-add-to-homescreen
> Make your WordPress sites to appear in mobile home screens.

[![Build Status](https://travis-ci.org/mozilla/wp-add-to-homescreen.svg?branch=master)](https://travis-ci.org/mozilla/wp-add-to-homescreen)

## Installation

To test this plugin, follow these steps.

First clone the repository.

Once inside the repository you will need [composer](https://getcomposer.org) and [bower](http://bower.io/) installed. Go to the folder `wp-add-to-homescreen` inside the repository folder and run:

```
$ composer install --optimize-autoloader
$ bower install
```

Now you can copy (or symlink) the `wp-add-to-homescreen` folder inside your WordPress plugins directory.

Once installed, activate the plugin from the _Plugins_ menu in the _Dashboard_.

## Running tests

Install dependencies:
```bash
./install-wp-tests.sh MYSQL_DATABASE_NAME MYSQL_USER MYSQL_PASSWORD localhost latest
```

Run tests:
```bash
make test
```
