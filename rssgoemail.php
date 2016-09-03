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

	require_once(dirname(__FILE__).'/config.php');
	require_once(dirname(__FILE__).'/mail_utf8.php');
	require_once(dirname(__FILE__).'/vendor/autoload.php');
	
	$connect = mysql_connect($rge_config['dbHost'],$rge_config['dbUser'], $rge_config['dbPass']) or die("Cannot connect to database");


	if(!(mysql_select_db($rge_config['dbBase']))){
		die("Cannot select database");
	}
    // Call SimplePie
	$feed = new SimplePie();
	
	$feed->set_feed_url($rge_config['feedUrls']);
	
	$feed->enable_cache();
	$feed->set_cache_location($rge_config['cacheDir']);
	$feed->set_cache_duration($rge_config['cacheTime']);
	
	// Init feed
	$feed->init();
	// Make sure the page is being served with the UTF-8 headers.
	$feed->handle_content_type();
	$items = $feed->get_items();
	
	$accumulatedText = '';
	$accumulatedGuid = array();
	
	foreach($items as $item){
	
		$title = $item->get_title();
		$guid = md5($item->get_id());
		$date = $item->get_date('j.m.Y G:i');		
		$link = $item->get_link();
	
		// Check Row
		$query = mysql_query("SELECT * FROM " . $rge_config['dbTable'] . " WHERE guid='$guid'");
		$row = mysql_num_rows($query);
	
		// If row empty send email and happy blogging
		if( $row == 0){			
			$text = array();
			$text[] = $title . " " . $date;
			$text[] = $link;
			$accumulatedText .= implode ("\n", $text) . "\n\n";
			$accumulatedGuid[] = $guid;			
		}else{
			continue;
		}	
	}

	echo "Mailtest:<br /><br />". $accumulatedText;
	if (empty($accumulatedText)){
			echo "Nothing to send";
			return;
	}
	$send = mail_utf8($rge_config['emailTo'], $rge_config['emailFrom'], $rge_config['emailSubject'], $accumulatedText);	
        if($send){
		foreach($accumulatedGuid as $guid){
			mysql_query("INSERT INTO " . $rge_config['dbTable'] . "(guid) VALUES ('$guid')");	
		}
	}
	else{
		die("Email sending failed");	
	}
?>
