# Local development

Obtain a shell inside the container :

    docker exec -it api bash

## Initial setup

    docker exec -u $(whoami) -it api php artisan telescope:install
    docker exec -u $(whoami) -it api ln -s .env.dev .env
    docker exec -u $(whoami) -it api composer install
    docker exec -u $(whoami) -it api php artisan migrate
    docker exec -u $(whoami) -it api php artisan db:seed
    docker exec -u $(whoami) -it api php artisan ide-helper:generate
    docker exec -u $(whoami) -it api php artisan ide-helper:models
    docker exec -u $(whoami) -it api php artisan ide-helper:meta
    docker exec -u $(whoami) -it api php artisan ide-helper:eloquent

## Wipe database access & seed again

    docker exec -u $(whoami) -it api php artisan db:wipe && docker exec -u $(whoami) -it api php artisan migrate &&  docker exec -u $(whoami) -it api php artisan db:seed

# Unit / Features tests

    docker exec -u $(whoami) -it api php artisan test
    docker exec -u $(whoami) -it api php artisan test tests/Feature/APIUserOrg1_AD_Org2_D_Org3_VTest.php
    docker exec -u $(whoami) -it api php artisan test --filter=APIUserOrg1_AD_Org2_D_Org3_VTest
    docker exec -u $(whoami) -it api php artisan test --filter=APIUserOrg1_AD_Org2_D_Org3_VTest::testApiConfConnectorFromOrganization2PrimeReactTreeDb
    docker exec -u $(whoami) -it api php artisan test --filter=APIUserOrg1_AD_Org2_D_Org3_VTest::testApiConfConnectorFromOrganization*
    docker exec -u $(whoami) -it api php artisan test --filter=testApiConfConnectorFromOrganization*


# PhpStorm
## Recommended plugins
* Laravel IDEA or Laravel
* Tree of Usage
* DQL
* PHP Annotations

## Database plugin configuration

Use the MariaDB module, container IP : `172.20.0.50`
