<?php

/*  
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


$rge_config['emailTo']  = "email_blogging@domain.tld";
$rge_config['emailFrom']  = "rssgoemail@domain.tld";
$rge_config['emailSubject']  = "RSS Summary";

$rge_config['cacheDir'] = dirname(__FILE__)."/cache";
$rge_config['cacheTime'] = "86400";

$rge_config['readMore'] = "Read More";

// Add more URL(s) here.
$rge_config['feedUrls'] = array(
	'http://example.tld/feed',
	'http://subdomain.example.tld/feed'
);
?>
