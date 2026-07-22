### DEV ENV
```
php artisan db:populate
```

### Cleaning DB and Admin password
```
php artisan users:manage
```

### Redis
Redis is used as a cache for some db queries but also as a categories tree storage.
To get a main category with second level of children use the key:
```
ams:category:main
```
To get a category tree of 2nd or deeper level:
```
ams:category:CAT_ID
```
The tree of all categories is under the key:
```
ams:category:all
```
But it's a quite big amount of data so try not to use it. 

### DB
#### resources_files table
disksize and resolution fields for single record are calculated and filled up when they are querried from db and since it's a read-only app there will be no need to update this value again

### Building on Jenkins
Configure Pipeline on Jenkins using "Pipeline script from SCM" with Jenkinsfile.
- set credentials in Jenkins credentials with id=AMS_BB_CREDS for accessing Bitbucket (using ssh or app keys)
- set credentials in Jenkins credentials with id=NEXUS_CREDS for Nexus


### todo
#### app
- create a Strategy Class for downloading files
    - default one should use php (Storage methods) because there are no x-sendfile for windows apache (64 bit)
- generate zip and store them in storage dir (or temp dir?) - create another storage in filesystem ?
  - file download:
    1. queue -> create file to download, broadcast, notification, download
- create a script for clearing storage dir from old files (https://laravel.com/docs/10.x/scheduling)
- create users management for admin accounts
- how to protect assets opened using direct link, e.g. images
 
#### env
look at scripts in composer.json
- configure phpcpd (copy paste detector): https://semaphoreci.com/blog/7-continuous-integration-tools-for-php-laravel
- laravel dusk
- psalm
- phpmd (php mess detector)


### problems solving
- if there is 401 during testing - check if admin user is assigned to AdminGroup group in tests db (assignment might be deleted during testing - \Tests\AMSTestCase::checkAdminPrivileges should repair this during unit tests)
