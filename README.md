![RootDB](https://www.rootdb.fr/assets/logo_name_blue_500x250.png)

# RootDB

[RootDB](https://www.rootdb.fr) is a self-hosted reporting webapp.

# Local development, with docker images.

## Obtain a shell inside the container :

```bash
docker exec -it dev-rootdb-api bash
```

## Wipe database access & seed again

```bash
docker exec -u $(whoami) -it api php artisan db:wipe && docker exec -u $(whoami) -it api php artisan migrate &&  docker exec -u $(whoami) -it api php artisan db:seed
```

## Unit / Features tests

```bash
docker exec -u $(whoami) -it api php artisan test
docker exec -u $(whoami) -it api php artisan test tests/Feature/APIUserOrg1_AD_Org2_D_Org3_VTest.php
docker exec -u $(whoami) -it api php artisan test --filter=APIUserOrg1_AD_Org2_D_Org3_VTest
docker exec -u $(whoami) -it api php artisan test --filter=APIUserOrg1_AD_Org2_D_Org3_VTest::testApiConfConnectorFromOrganization2PrimeReactTreeDb
docker exec -u $(whoami) -it api php artisan test --filter=APIUserOrg1_AD_Org2_D_Org3_VTest::testApiConfConnectorFromOrganization*
docker exec -u $(whoami) -it api php artisan test --filter=testApiConfConnectorFromOrganization*
```

# Jetbrains, PhpStorm

## Recommended plugins

* Laravel IDEA or Laravel
* Tree of Usage
* DQL
* PHP Annotations

## Database plugin configuration

Use the MariaDB module, container IP of container `rootdb-db-api` : `172.20.0.50`
