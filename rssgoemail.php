<?php
/*
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
	//make sure no errors are shown even on CLI
	ini_set('display_errors', 0);
	ini_set('log_errors', 1);
	ini_set('error_log', 'log/error.log');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

	require_once(dirname(__FILE__).'/config/config.php');
	require_once(dirname(__FILE__).'/vendor/autoload.php');

    /**
    * sends a mail using PHPMailer
    * @param $rge_config rssgoemail configuration
    * @param $subject subject of the mail to send
    * @param $body body of the mail to send
    * @return bool true if mail was sent succesfully
    */
    function sendMail($rge_config, $subject, $body){
        $mail = new PHPMailer(true);
        try {
            //Server settings
            switch (strtolower($rge_config['emailBackend'])){
                case "mail": $mail->isMail(); break;
                case "smtp":
                    $mail->isSMTP();
                    $mail->Host       = $rge_config['SMTPHost'];
                    $mail->SMTPAuth   = $rge_config['SMTPAuth'];
                    $mail->Username   = $rge_config['SMTPUsername'];
                    $mail->Password   = $rge_config['SMTPPassword'];
                    switch (strtolower($rge_config['SMTPSecurity'])){
                        case "starttls":
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            break;
                        case "smtps":
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        default:
                            echo("Invalid config entry for SMTPSecurity {$rge_config['SMTPSecurity']}\n");
                    }
                    $mail->Port       = $rge_config['SMTPPort'];
                    break;
                default: echo("Invalid config entry for emailBackend {$rge_config['emailBackend']}\n");
            }

            //Recipients
            $mail->setFrom($rge_config['emailFrom']);
            $mail->addAddress($rge_config['emailTo']);

            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->CharSet = 'utf-8';

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
            return false;
        }
        return true;
    }

    /**
    * helper function that checks if multiple keys are in array
    * @param $array array to check
    * @param $keys keys to check
    * @return bool true if all keys exist in array
    */
    function array_keys_exists($array, $keys) {
        foreach($keys as $k) {
            if(!isset($array[$k])) {
            return false;
            }
        }
        return true;
    }


    /**
    * helper function to check config, print warnings, and return a config with required default values
    * @param $rge_conig rssgoemail config
    * @return $rge_conig rssgoemail config with default values as required
    */
    function checkConfig($rge_config){
        $smtp_config_requirements = array("SMTPHost", "SMTPAuth", "SMTPUsername", "SMTPPassword", "SMTPSecurity", "SMTPPort");

        if (!array_key_exists("notificationType", $rge_config)){
            echo("notificationType not given, setting default value summary!\n");
            $rge_config['notificationType'] = "summary";
        }

        if (!array_key_exists("emailSubjectFeedErrorPerItem", $rge_config)){
            echo("emailSubjectFeedErrorPerItem not given, setting default value!\n");
            $rge_config['emailSubjectFeedErrorPerItem'] = "RSS Summary - Feed Error";
        }

        if (!array_key_exists("emailBody", $rge_config)){
            echo("emailBody not given, setting default value!\n");
            $rge_config['emailBody'] = "##ITEM_TITLE## ##ITEM_DATE##
##ITEM_LINK##

";
        }

        if(!array_key_exists("emailBackend", $rge_config) == "smtp"){
            echo("emailBackend not given, setting default value mail!\n");
            $rge_config['emailBackend'] = "mail";
        }

        if(strtolower($rge_config['emailBackend']) == "smtp"){
                if (!array_keys_exists($rge_config, $smtp_config_requirements))
                   echo("Not all required SMTP variables were given!\n");
            }
        return $rge_config;
    }

    /**
    * decode from HTML to UTF8
    * @param $string string with HTML coding
    * @return string in UTF8 coding
    */
    function decodeHTMLtoUTF($HTMLString){
        //decode HTML entities in title to UTF8
		//run it two times to support double encoding, if for example "&uuml;" is encoded as "&amp;uuml;"
		$nr_entitiy_decode_runs = 2;
		for ($i=0; $i < $nr_entitiy_decode_runs; $i++){
			$UTFString = html_entity_decode($HTMLString, ENT_COMPAT | ENT_HTML401, "UTF-8");
		}
        return $UTFString;
    }

    /**
    * checks if given GUID was sent already
    * @param $rge_config rssgoemail config
    * @param $pdo PDO variable
    * @param $GUID to check
    * @return true if sent already
    */
    function wasGUIDSent($rge_config, $pdo, $GUID){
        // check if item has been sent already
		$stmt = $pdo->prepare("SELECT 1 FROM {$rge_config['dbTable']} WHERE guid=:guid");
		$stmt->execute(['guid' => $GUID]);

		// if so, return true
		if($stmt->fetch()){
			return true;
		// if not false
		}else{
            return false;
        }
    }

    /**
    * sets given GUID to state "sent already"
    * @param $rge_config rssgoemail config
    * @param $pdo PDO variable
    * @param $GUID
    */
    function setGUIDToSent($rge_config, $pdo, $GUID){
			$stmt = $pdo->prepare("INSERT INTO {$rge_config['dbTable']} (guid) VALUES (:guid)");
			$stmt->execute(['guid' => $GUID]);
    }

    /**
    * sends mail and handles GUIDs
    * @param $rge_config rssgoemail config,
    * @param $mail_text body of the mail
    * @param $mail_subject subject of the mail
    * @param $GUIDs can be array or single value
    */
    function sendMailAndHandleGUID($mail_text, $mail_subject, $rge_config, $GUIDs){
        $send = sendMail($rge_config, $mail_subject, $mail_text);
        if($send){
		    foreach(array($GUIDs) as $GUID){
			    setGUIDToSent($rge_config, $pdo, $GUID);
		    }
	    }
	    else{
		    die("Email sending failed");
	    }
    }

    /**
    * sends a RSS summary with all new items in one mails, feed errors are sent as part of that mail
    * @param $rge_config rssgoemail config,
    * @param $pdo PDO variable
    * @param $feed feeds to check
    */
    function notifySummary($rge_config, $pdo, $feed){

    $items = $feed->get_items();

	$accumulatedText = '';
	$accumulatedGuid = array();

    if ($feed->error()){
		foreach($feed->error() as $key => $error){
			$accumulatedText .= $rge_config['errorInFeed'] . " " . $rge_config['feedUrls'][$key] . "\n";
		}
	}

	foreach($items as $item){
		$title = decodeHTMLtoUTF($item->get_title());
		$guid = $item->get_id(true);
		$date = $item->get_date($rge_config['dateFormat']);
		$link = $item->get_link();
        $feed_title = $item->get_feed()->get_title();

        $replacements = array(
            "##ITEM_TITLE##" => $title,
            "##ITEM_DATE##" => $date,
            "##ITEM_LINK##" => $link,
            "##FEED_TITLE##" => $feed_title,
        );

		// if was send before-> skip
		if(wasGUIDSent($rge_config, $pdo, $guid)){
			continue;
		// if not send it
		}else{
            $accumulatedText .= strtr($rge_config['emailBody'], $replacements);
			$accumulatedGuid[] = $guid;
		}
	}

	if (empty($accumulatedText)){
			echo "Nothing to send\n";
			return;
	}
    sendMailAndHandleGUID($accumulatedText, $rge_config['emailSubject'], $rge_config, $accumulatedGuid);
}

    /**
    * sends one email per new RSS item, all feed errors are sent as separate mail
    * @param $rge_config rssgoemail config,
    * @param $pdo PDO variable
    * @param $feed feeds to check
    */
    function notifyPerItem($rge_config, $pdo, $feed){

    $items = $feed->get_items();

    if ($feed->error()){
        $mail_text = "";
        foreach($feed->error() as $key => $error){
			$mail_text .= $rge_config['errorInFeed'] . " " . $rge_config['feedUrls'][$key] . "\n";
		}
        $send = sendMail($rge_config, $rge_config['emailSubjectFeedErrorPerItem'], $mail_text);
	    if (!$send){
		    die("Email sending failed");
	    }
	}

    foreach($items as $item){

		$title = decodeTitle($item->get_title());
		$guid = $item->get_id(true);
		$date = $item->get_date($rge_config['dateFormat']);
		$link = $item->get_link();
        $feed_title = $item->get_feed()->get_title();

        $replacements = array(
            "##ITEM_TITLE##" => $title,
            "##ITEM_DATE##" => $date,
            "##ITEM_LINK##" => $link,
            "##FEED_TITLE##" => $feed_title,
        );

		// if was send before-> skip
		if(wasGUIDSent($rge_config, $pdo, $guid)){
			continue;
		// if not send it
		}else{
			$text = strtr($rge_config['emailBody'], $replacements);
        	if (empty($text)){
		        echo "Nothing to send for item with GUID $guid\n";
            }

            sendMailAndHandleGUID($text, strtr(rge_config['emailSubject'], $replacements), $rge_config, $guid);
		}
	}
}


	header("Content-Type: text/plain");

    $rge_config = checkConfig($rge_config);

    $charset = 'utf8mb4';

	$opt = [
    		//PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    		PDO::ATTR_EMULATE_PREPARES   => false,
	];
	$dsn = "mysql:host={$rge_config['dbHost']};dbname={$rge_config['dbBase']};charset=$charset";
	$pdo = new PDO($dsn, $rge_config['dbUser'], $rge_config['dbPass'], $opt);

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

    switch (strtolower($rge_config['notificationType'])){
        case "peritem": notifyPerItem($rge_config, $pdo, $feed); break;
        case "summary": notifySummary($rge_config, $pdo, $feed); break;
        default: die("Invalid config entry for notificationType {$rge_config['notificationType']}");
    }

    return;

?>
