<?php

/**
    Copyright 2012 e-dschungel https://github.com/e-dschungel
    Copyright 2009 Abdul Ibad (loopxcrack[at]yahoo.co.uk)
    http://ibad.bebasbelanja.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$rge_config['dbHost'] = "localhost";
$rge_config['dbUser'] = "";
$rge_config['dbPass'] = "";
$rge_config['dbBase'] = "rssgoemail";
$rge_config['dbTable'] = "rssgoemail";

$rge_config['notificationType'] = "summary";
$rge_config['templateType'] = "string";

$rge_config['emailTo']  = "email_blogging@domain.tld";
$rge_config['emailFrom']  = "rssgoemail@domain.tld";
$rge_config['emailSubject']  = "RSS Summary";
$rge_config['emailSubjectFeedErrorPerItem']  = "RSS Summary - Feed Error";
$rge_config['emailBody'] = "##ITEM_TITLE## ##ITEM_DATE##
##ITEM_LINK##

";

$rge_config['emailBackend'] = "mail";
$rge_config['errorInFeed'] = "The following feed contains errors:";
$rge_config['dateFormat'] = "m/d/y h:i a";

$rge_config['cacheDir'] = dirname(__FILE__) . "/../cache";
$rge_config['cacheTime'] = "1800";
$rge_config['logFile'] = dirname(__FILE__) . "/../log/mail.log";

$rge_config['SMTPHost'] = "smtp.example.com";
$rge_config['SMTPAuth'] = true;
$rge_config['SMTPUsername'] = "exampleUser";
$rge_config['SMTPPassword'] = "examplePassword";
$rge_config['SMTPSecurity'] = "smtps";
$rge_config['SMTPPort'] = 465;

// Add more URL(s) here.
$rge_config['feedUrls'] = array(
    'http://example.tld/feed',
    'http://subdomain.example.tld/feed'
);

// The following configs are only used,
// if templateType = tmpl
//--------------------------------------

// Language of the feed
$rge_config['language'] = "EN";

// Template paths
$rge_config['emailTmpl'] = dirname(__FILE__) . "/../tmpl/email.php";
$rge_config['itemTmpl'] = dirname(__FILE__) . "/../tmpl/item.php";

// URL to the header image
$rge_config['feedImg'] = 'https://via.placeholder.com/600x166.jpg';

// Email adress for contact
$rge_config['feedContact'] = 'info@example.org';

// URL to the unsubscribe form
$rge_config['feedUnsubscrUrl'] = 'http://example.tld/feed/logout';
