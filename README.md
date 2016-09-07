# rssgoemail
rssgoemail is a script that watches multiple RSS (or Atom) feeds and sends out an email digest if a new entry is found.
It is a fork of the script by Abdul Ibad which used to live at http://ibad.bebasbelanja.com

## Requirements
* PHP > 5.3
* a MySQL database
* a cronjob

## Installation
### From Git
* Clone this repo `git clone https://github.com/e-dschungel/rssgoemail`
* Install simplepie using composer `composer install`
* Create database using `config/rssgoemail.sql`
* Rename 'config/config.dist.php' to 'config/config.php' and edit it according to your needs, see below
* Add a cronjob which accesses 'rssgoemail.php' regularly

## Configuration
|variable|description|
|---|---|
|$rge_config['dbHost']| hostname of the database, localhost is very common|
|$rge_config['dbUser']| user used to connect to the database|
|$rge_config['dbPass']| password used to connect to the database|
|$rge_config['dbBase']| name of the database|
|$rge_config['dbTable']| name of the table|
|$rge_config['emailTo']| email adress of the reciepent of the email digest|
|$rge_config['emailFrom']| email adress shown as sender of the digest|
|$rge_config['emailSubject']| subject of the email digest|
|$rge_config['cacheDir']| cache dir, needs to be writeable|
|$rge_config['cacheTime']| cache time in seconds|
|$rge_config['feedUrls']| array with URLs of RSS or Atom feeds to be watched|
