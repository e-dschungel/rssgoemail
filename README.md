# rssgoemail
rssgoemail is a script that watches multiple RSS feeds and sends out an email if a new entry is posted.
It is a fork of the script by Abdul Ibad with used to live at http://ibad.bebasbelanja.com

## Requirements
* PHP > 5.3
* a MySQL database
* a cronjob

## Installation
### From Git
* Clone this repo `git clone https://github.com/e-dschungel/rssgoemail`
* Install simplepie using composer `composer install`
* Create database using `config/rssgoemail.sql`
* Rename 'config/config.dist.php' to 'config/config.php' and edit it according to your needs
