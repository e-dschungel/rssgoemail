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
* Rename `config/config.dist.php` to `config/config.php` and edit it according to your needs, see below
* Add a cronjob which accesses `rssgoemail.php` regularly

### From ZIP file
* Download `rssgoemail.zip` (NOT `Source Code (zip)` or `Source Code (tar.gz)`)  from https://github.com/e-dschungel/rssgoemail/releases/latest
* Extract and upload it to your webserver
* Create database using `config/rssgoemail.sql`
* Rename `config/config.dist.php` to `config/config.php` and edit it according to your needs, see below
* Add a cronjob which accesses `rssgoemail.php` regularly

## Configuration
|variable|description|
|---|---|
|$rge_config['dbHost']| hostname of the database, localhost is very common|
|$rge_config['dbUser']| user used to connect to the database|
|$rge_config['dbPass']| password used to connect to the database|
|$rge_config['dbBase']| name of the database|
|$rge_config['dbTable']| name of the table|
|$rge_config['notificationType']| sets the notification type: "summary" one mail for all new RSS items, or "perItem" for one mail per new RSS item|
|$rge_config['emailTo']| email adress of the recipient of the email digest, multiple recipients can be given separated by comma, e.g. $rge_config['emailTo'] = "user1@example.com, user2@example.com";|
|$rge_config['emailFrom']| email adress shown as sender of the digest|
|$rge_config['emailSubject']| subject of the email digest, in perItem mode placeholders (see below) can be used|
|$rge_config['emailSubjectFeedErrorPerItem']| subject of mail containing feed errors (only used in perItem mode)|
|$rge_config['emailBody']| template for mail body, for placeholder see below, in summary mode this should end with an empty newline as this si repeated to create the mail text|
|$rge_config['emailBackend']| can be "mail" or "smtp", "mail" uses sendmail as before, "smtp" uses SMTP. If "smtp" is used all SMTP variables must be set|
|$rge_config['errorInFeed']| warning which is shown when the feed contains errors|
|$rge_config['dateFormat']| format of date and time, formatting specifiers like PHP's [date function](https://secure.php.net/manual/function.date.php)|
|$rge_config['cacheDir']| cache dir, needs to be writeable|
|$rge_config['cacheTime']| cache time in seconds, during this time no changes from feeds are recognized as the cached version is used|
|$rge_config['feedUrls']| array with URLs of RSS or Atom feeds to be watched|
|$rge_config['SMTPHost']| SMTP hostname|
|$rge_config['SMTPAuth']| use SMTP authentication? true or false|
|$rge_config['SMTPUsername']| SMTP username|
|$rge_config['SMTPPassword']| SMTP password|
|$rge_config['SMTPSecurity']| type of SMTP security setting, can be "starttls" or "smtps"|
|$rge_config['SMTPPort']| SMTP port|

## Placeholder
|##ITEM_TITLE##| title of the RSS item|
|##ITEM_DATE##| date of the RSS item (in format given by $rge_config['dateFormat'])|
|##ITEM_LINK##| URL of the RSS item|
|##FEED_TITLE##| title of the feed|

## Changelog
### Version 0.1
* first public release (of this fork)

### Version 0.2
* upgrade to SimplePie 1.5
* switch to PDO for database access to make script compatible to PHP 7
* use SimplePie's internal hash functions (avoids duplicated emails)
* fix for special characters in feed title

### Version 0.2.1
* decreased default cache time to 1800 seconds, improved documentation on cacheTime

### Version 0.3
* switch to PHPMailer to handle mail sending, this allows SMTP instead of PHPs mail function
* notification type can be configured: "summary" one mail for all new RSS items, or "perItem" for one mail per new RSS item
* mail body can be customized using placeholders
* update to latest SimplePie 1.5.6
