# Erply warehouse data collection service
A service which requests warehouse data (item amounts) from Erply API and stores it in a local MySQL database. 
After all data is loaded the first time, the next requests will require only the items which were updated since the last request.

### Usage
The data updating script is located in `scripts` folder. It can be registered as a cron task making request to Erply API every minute. 

Before starting to use the service
* set up your credentials in `src/Services/Config.php`;
* change variable values for database connection in `src/DB/DBConnection.php` (host, db, user, pass);
* run `composer install` in root folder (where `composer.json` is located).

In `public` folder you may find an example of using the data collected by the service.
* `api.php` - handles API requests to the service data
* `index.php` - an example of code on the front-end side which makes Ajax requests to `api.php`
