<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

/**
* Sends a mail using PHPMailer
*
* @param $rge_config rssgoemail configuration
* @param $subject subject of the mail to send
* @param $body body of the mail to send
*
* @return bool true if mail was sent succesfully
*/
function sendMail($rge_config, $subject, $body)
{
    $mail = new PHPMailer(true);
    try {
        //Server settings
        switch (strtolower($rge_config['emailBackend'])) {
            case "mail":
                    $mail->isMail();
                break;
            case "smtp":
                $mail->isSMTP();
                $mail->Host       = $rge_config['SMTPHost'];
                $mail->SMTPAuth   = $rge_config['SMTPAuth'];
                $mail->Username   = $rge_config['SMTPUsername'];
                $mail->Password   = $rge_config['SMTPPassword'];
                switch (strtolower($rge_config['SMTPSecurity'])) {
                    case "starttls":
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        break;
                    case "smtps":
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        break;
                    default:
                        echo("Invalid config entry for SMTPSecurity {$rge_config['SMTPSecurity']}\n");
                }
                $mail->Port       = $rge_config['SMTPPort'];
                break;
            default:
                    echo("Invalid config entry for emailBackend {$rge_config['emailBackend']}\n");
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
* Helper function that checks if multiple keys are in array
*
* @param $array array to check
* @param $keys keys to check
*
* @return bool true if all keys exist in array
*/
function allArrayKeysExist($array, $keys)
{
    foreach ($keys as $k) {
        if (!isset($array[$k])) {
            return false;
        }
    }
    return true;
}


/**
* Helper function to check config, print warnings, and return a config with required default values
*
* @param $rge_config rssgoemail config
*
* @return $rge_config rssgoemail config with default values as required
*/
function checkConfig($rge_config)
{
    $smtp_config_requirements = array(
        "SMTPHost",
        "SMTPAuth",
        "SMTPUsername",
        "SMTPPassword",
        "SMTPSecurity",
        "SMTPPort",
    );

    if (!array_key_exists("notificationType", $rge_config)) {
        echo("notificationType not given, setting default value summary!\n");
        $rge_config['notificationType'] = "summary";
    }

    if (!array_key_exists("emailSubjectFeedErrorPerItem", $rge_config)) {
        echo("emailSubjectFeedErrorPerItem not given, setting default value!\n");
        $rge_config['emailSubjectFeedErrorPerItem'] = "RSS Summary - Feed Error";
    }

    if (!array_key_exists("emailBody", $rge_config)) {
        echo("emailBody not given, setting default value!\n");
        $rge_config['emailBody'] = "##ITEM_TITLE## ##ITEM_DATE##
##ITEM_LINK##

";
    }

    if (!array_key_exists("emailBackend", $rge_config)) {
        echo("emailBackend not given, setting default value mail!\n");
        $rge_config['emailBackend'] = "mail";
    }

    if (strtolower($rge_config['emailBackend']) == "smtp") {
        if (!allArrayKeysExist($rge_config, $smtp_config_requirements)) {
            echo("Not all required SMTP variables were given!\n");
        }
    }
    return $rge_config;
}

/**
* Decode from HTML to UTF8
*
* @param $HTMLString string with HTML coding
*
* @return string in UTF8 coding
*/
function decodeHTMLtoUTF($HTMLString)
{
    //decode HTML entities in title to UTF8
    //run it two times to support double encoding, if for example "&uuml;" is encoded as "&amp;uuml;"
    $nr_entitiy_decode_runs = 2;
    for ($i = 0; $i < $nr_entitiy_decode_runs; $i++) {
        $UTFString = html_entity_decode($HTMLString, ENT_COMPAT | ENT_HTML401, "UTF-8");
    }
    return $UTFString;
}

/**
* Checks if given GUID was sent already
*
* @param $rge_config rssgoemail config
* @param $pdo PDO variable
* @param $GUID to check
*
* @return true if sent already
*/
function wasGUIDSent($rge_config, $pdo, $GUID)
{
    // check if item has been sent already
    $stmt = $pdo->prepare("SELECT 1 FROM {$rge_config['dbTable']} WHERE guid=:guid");
    $stmt->execute(['guid' => $GUID]);

    // if so, return true
    if ($stmt->fetch()) {
        return true;
        // if not false
    } else {
        return false;
    }
}

/**
* Sets given GUID to state "sent already"
*
* @param $rge_config rssgoemail config
* @param $pdo PDO variable
* @param $GUID unique ID of item
*
* @return void
*/
function setGUIDToSent($rge_config, $pdo, $GUID)
{
        $stmt = $pdo->prepare("INSERT INTO {$rge_config['dbTable']} (guid) VALUES (:guid)");
        $stmt->execute(['guid' => $GUID]);
}

/**
* Sends mail and handles GUIDs
*
* @param $mail_text body of the mail
* @param $mail_subject subject of the mail
* @param $rge_config rssgoemail config
* @param $pdo PDO variable
* @param $GUIDs unique ID, can be array or single value
*
* @return void
*/
function sendMailAndHandleGUID($mail_text, $mail_subject, $rge_config, $pdo, $GUIDs)
{
    $send = sendMail($rge_config, $mail_subject, $mail_text);
    if ($send) {
        foreach ((array)$GUIDs as $GUID) {
            setGUIDToSent($rge_config, $pdo, $GUID);
        }
    } else {
        die("Email sending failed");
    }
}

/**
* Translates a string and passes it through a sprintf.
*
* @param  string   $string  The format string.
*
* @return string   The translated string
*/
function text($string)
{
  require_once dirname(__FILE__) . '../tmpl/language.php';

  $args = func_get_args();

  // translate the string
  $args[0] = strtr($string, $languages[LANG]);

  if(count($args) > 1) {
    // perform sprintf on string
    return call_user_func_array('sprintf', $args);
  }
  else {
    return $args[0];
  }
}

/**
* Insert content into template file and return inserted content
*
* @param  array    $rge_config  rssgoemail config
* @param  string   $tmpl        Template to use (available: item, email)
* @param  object   $item        SimpliePieItem
* @param  string   $html        Items body html
*
* @return string   Templated content on success, false otherwise
*/
function performTemplating($rge_config, $tmpl, $item, $html='')
{
  if ($rge_config['templateType'] == 'tmpl') {

    switch ($tmpl) {
      case 'item':
        $tmpl_file = $rge_config['itemTmpl'];
        break;

      case 'email':
        $tmpl_file = $rge_config['emailTmpl'];
        break;
      
      default:
        $tmpl_file = false;
        break;
    }

    if($tmpl_file == false || !file_exists($tmpl_file))
    {
      echo 'Template not found: '. $tmpl;
      return false;
    }

    $content = feedReplacements($item, $rge_config['dateFormat']);
    $content['##EMAIL_BODY##'] = $html;


    ob_start();
    include $tmpl_file;
    $txt = ob_get_contents();
    ob_end_clean();
    
    return $txt;
  }
  elseif ($rge_config['templateType'] == 'string' && !empty($tmpl)) {

    return strtr($tmpl, feedReplacements($item, $rge_config['dateFormat']));
  }
  else {
    echo 'Unknown template type: '. $rge_config['templateType'];

    return false;
  }
}

/**
* Sends a RSS summary with all new items in one mails, feed errors are sent as part of that mail
*
* @param  object   $item        SimpliePieItem
* @param  string   $dateFormat  Format of the date
*
* @return array  List with available feed content
*/
function feedReplacements($item, $dateFormat)
{
  return array(
    "##FEED_COPYRIGHT##" => ($item->get_feed()) ? $item->get_feed()->get_copyright() : "",
    "##FEED_DESCRIPTION##" => ($item->get_feed()) ? $item->get_feed()->get_description() : "",
    "##FEED_LANGUAGE##" => ($item->get_feed()) ? $item->get_feed()->get_language() : "",
    "##FEED_LINK##" => ($item->get_feed()) ? $item->get_feed()->get_link() : "",
    "##FEED_TITLE##" => decodeHTMLtoUTF(($item->get_feed()) ? $item->get_feed()->get_title() : ""),
    "##ITEM_AUTHOR_EMAIL##" => ($item->get_author()) ? $item->get_author()->get_email() : "",
    "##ITEM_AUTHOR_LINK##" => ($item->get_author()) ? $item->get_author()->get_link() : "",
    "##ITEM_AUTHOR_NAME##" => ($item->get_author()) ? $item->get_author()->get_name() : "",
    "##ITEM_COPYRIGHT##" => $item->get_copyright(),
    "##ITEM_CONTENT##" => $item->get_content(false),
    "##ITEM_DATE##" => $item->get_date($dateFormat),
    "##ITEM_DESCRIPTION##" => $item->get_description(false),
    "##ITEM_ENCLOSURE_LINK##" => ($item->get_enclosure()) ? $item->get_enclosure()->get_link() : "",
    "##ITEM_LINK##" => $item->get_link(),
    "##ITEM_TITLE##" => decodeHTMLtoUTF($item->get_title()),
  );
}

/**
* Sends a RSS summary with all new items in one mails, feed errors are sent as part of that mail
*
* @param $rge_config rssgoemail config,
* @param $pdo PDO variable
* @param $feed feeds to check
*
* @return void
*/
function notifySummary($rge_config, $pdo, $feed)
{
    $items = $feed->get_items();

    $accumulatedText = '';
    $accumulatedGuid = array();

    if (!$feed->error() && count($items) < 1) {
      echo "Nothing to send\n";
      return;
    }

    if ($feed->error()) {
        foreach ($feed->error() as $key => $error) {
            $accumulatedText .= text('errorInFeed') . " " . $rge_config['feedUrls'][$key] . "\n";
        }
    }

    foreach ($items as $item) {
        $guid = $item->get_id(true);

        // if was send before-> skip
        if (wasGUIDSent($rge_config, $pdo, $guid)) {
            continue;
        // if not send it
        } else {
            $accumulatedText .=  performTemplating($rge_config, 'item', $item)
            $accumulatedGuid[] = $guid;
        }
    }

    if (empty($accumulatedText)) {
        echo "Nothing to send\n";
        return;
    }

    $emailText = performTemplating($rge_config, 'email', $items[0], $accumulatedText);

    sendMailAndHandleGUID($emailText, $rge_config['emailSubject'], $rge_config, $pdo, $accumulatedGuid);
}

/**
* Sends one email per new RSS item, all feed errors are sent as separate mail
*
* @param $rge_config rssgoemail config,
* @param $pdo PDO variable
* @param $feed feeds to check
*
* @return void
*/
function notifyPerItem($rge_config, $pdo, $feed)
{
    $items = $feed->get_items();

    if ($feed->error()) {
        $mail_text = "";
        foreach ($feed->error() as $key => $error) {
            $mail_text .= text('errorInFeed') . " " . $rge_config['feedUrls'][$key] . "\n";
        }
        $send = sendMail($rge_config, $rge_config['emailSubjectFeedErrorPerItem'], $mail_text);
        if (!$send) {
            die("Email sending failed");
        }
    }

    foreach ($items as $item) {
        $guid = $item->get_id(true);

        // if was send before-> skip
        if (wasGUIDSent($rge_config, $pdo, $guid)) {
            continue;
        // if not send it
        } else {
            $text = performTemplating($rge_config, 'item', $item);
            $text = performTemplating($rge_config, 'email', $item, $text);
            if (strlen($text) === 0) {
                echo "Nothing to send for item with GUID $guid\n";
                continue;
            }
            $subject = performReplacements($rge_config, $rge_config['emailSubject'], $item);
            sendMailAndHandleGUID($text, $subject, $rge_config, $pdo, $guid);
        }
    }
}
