# rssgoemail
rssgoemail is a script that watches multiple RSS (or Atom) feeds and sends out an email digest if a new entry is found.
It is a fork of the script by Abdul Ibad which used to live at http://ibad.bebasbelanja.com.
It uses [SimplePie](https://github.com/simplepie/simplepie) for RSS handling and [PHPMailer](https://github.com/PHPMailer/PHPMailer) for email sending.

## Requirements
* PHP > 5.6
* a MySQL database
* a cronjob

## Installation
### From Git
* Clone this repo `git clone https://github.com/e-dschungel/rssgoemail`
* Install dependencies using composer `composer install --no-dev`
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
|$rge_config['emailBody']| template for mail body, for placeholder see below, in summary mode this should end with an empty line as this is attached over and over to create the mail text|
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
|placeholder|description|
|---|---|
|##FEED_COPYRIGHT##  | copyright of the feed|
|##FEED_DESCRIPTION## | description of the feed|
|##FEED_LANGUAGE## | language of the feed|
|##FEED_LINK## | language of the feed|
|##FEED_TITLE##| title of the feed|
|##ITEM_AUTHOR_EMAIL##| email address of the item author|
|##ITEM_AUTHOR_LINK##| link to the item author|
|##ITEM_AUTHOR_NAME##| name of the item author|
|##ITEM_COPYRIGHT##  | copyright of the item|
|##ITEM_CONTENT## | content of the item, does not fall back to description if not given|
|##ITEM_DATE##| date of the RSS item (in format given by $rge_config['dateFormat'])|
|##ITEM_DESCRIPTION## | description of the item, does not fall back to content if not given|
|##ITEM_ENCLOSURE_LINK##| URL of the media in enclosure tag|
|##ITEM_LINK##| URL of the RSS item|
|##ITEM_TITLE##| title of the RSS item|


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
* migration from older versions: add new configuration variables from `config.dist.php` to your `config.php`, although sane default values will be used

### Version 0.3.1
* added new placeholders ##FEED_COPYRIGHT##, ##FEED_DESCRIPTION##, ##FEED_LANGUAGE##, ##FEED_LINK##, ##ITEM_AUTHOR_EMAIL##, ##ITEM_AUTHOR_LINK##, ##ITEM_AUTHOR_NAME##, ##ITEM_COPYRIGHT##, ##ITEM_CONTENT##, ##ITEM_DESCRIPTION##, ##ITEM_ENCLOSURE_LINK##
* update to PHPMailer 6.3.0

### Version 0.3.2
* cleanup release: no functional changes, only codingstyle improvements

### Version 0.3.3
* updated PHPMailer to 6.4.0

### Version 0.3.4
* avoid crashes if part of placeholder (like author or enclosure) is missing
* fixed checking of config parameters

### Version 0.3.5
* updated PHPMailer to 6.4.1

### Version 0.3.6
* updated PHPMailer to 6.5.0

### Version 0.3.7
* updated PHPMailer to 6.5.1

### Version 0.3.8
* updated PHPMailer to 6.5.3

### Version 0.3.9
* updated SimplePie to 1.5.7

### Version 0.3.10
* updated SimplePie to 1.5.8

### Version 0.3.11
* updated PHPMailer to 6.5.4

### Version 0.3.12
* use PHPStan
* fixed errors found by PHPStan
* fixed issue in perItem mode
* updated PHPMailer to 6.6.0
