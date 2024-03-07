# Calor

## Project frameworks

- Magento 2.4.3-p1

## Technical Requirements

 - PHP 7.4.x
 - Ngnix 1.8 or Apache 2.4
 - MySQL 8.0
 - Varnish
 - Elasticsearch 7.16+
 - SSL coverage
 - Composer 2.x
 - Node 14
 - Detailed specs can be found at [Magento 2.4 tech requirements](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html)

## Local setup for main website

**Database Setup**

Setup a new database named

 - calor

Also obtain a copy of the database and import the dump into the newly created database

**SSL Setup**

    Set up an SSL for calor.test

**Clone Repo**

    git clone git@bitbucket.org:selesti/calor.git

**Setup Env**

    cp app/etc/env.example.php app/etc/env.php

  Update env with the database credentials

**Setup Auth.json**

    Obtain a copy of the auth.json file and place in root

**Magento Install**

    composer install

**Test frontend**

   - Go to https://www.calor.test

**Test backend**

   Go to https://www.calor.test/admin_1gnye3/ and login

## Frontend Compilation

    npm ci
    grunt exec:calor
    grunt less:calor
    grunt watch

Additionally you can run the bottom 3 commands together

    composer grunt-watch


## Environments

**Local**

Frontend URL: https://www.calor.test  
Admin URL: https://www.calor.test/admin_1gnye3  

## Magento Guides

 - [Frontend User Guide](https://docs.magento.com/m2/ce/user_guide/getting-started.html)
 - [Development Guide](https://devdocs.magento.com/#/individual-contributors)

## Useful Magento commands

Upgrade

    php bin/magento setup:upgrade

Flush Cache

    php bin/magento cache:flush

Cache Enable

    php bin/magento cache:enable

Cache Status

    php bin/magento cache:status

Reindex

    php bin/magento indexer:reindex

