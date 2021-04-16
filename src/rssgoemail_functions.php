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
* Sends mail and handles GUIDs
*
* @param $text which contains placeholders
* @param $item SimpliePieItem
*
* @return text with placeholder replaced
*/
function performReplacements($rge_config, $text, $item){
            $replacements = array(
            "##FEED_COPYRIGHT##" => $item->get_feed()->get_copyright(),
            "##FEED_DESCRIPTION##" => $item->get_feed()->get_description(),
            "##FEED_LANGUAGE##" => $item->get_feed()->get_language(),
            "##FEED_LINK##" => $item->get_feed()->get_link(),
            "##FEED_TITLE##" => decodeHTMLtoUTF($item->get_feed()->get_title()),
            "##ITEM_AUTHOR_EMAIL##" => ($item->get_author()) ? $item->get_author()->get_email() : "",
            "##ITEM_AUTHOR_LINK##" => ($item->get_author()) ? $item->get_author()->get_link() : "",
            "##ITEM_AUTHOR_NAME##" => ($item->get_author()) ? $item->get_author()->get_name() : "",
            "##ITEM_COPYRIGHT##" => $item->get_copyright(),
            "##ITEM_CONTENT##" => $item->get_content(false),
            "##ITEM_DATE##" => $item->get_date($rge_config['dateFormat']),
            "##ITEM_DESCRIPTION##" => $item->get_description(false),
            "##ITEM_ENCLOSURE_LINK##" => $item->get_enclosure()->get_link(),
            "##ITEM_LINK##" => $item->get_link(),
            "##ITEM_TITLE##" => decodeHTMLtoUTF($item->get_title()),
        );
        return strtr($text, $replacements);

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

    if ($feed->error()) {
        foreach ($feed->error() as $key => $error) {
            $accumulatedText .= $rge_config['errorInFeed'] . " " . $rge_config['feedUrls'][$key] . "\n";
        }
    }

    foreach ($items as $item) {
        $guid = $item->get_id(true);



        // if was send before-> skip
        if (wasGUIDSent($rge_config, $pdo, $guid)) {
            continue;
        // if not send it
        } else {
            $accumulatedText .=  performReplacements($rge_config, $rge_config['emailBody'], $item);
            $accumulatedGuid[] = $guid;
        }
    }

    if (empty($accumulatedText)) {
            echo "Nothing to send\n";
            return;
    }
    sendMailAndHandleGUID($accumulatedText, $rge_config['emailSubject'], $rge_config, $pdo, $accumulatedGuid);
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
            $mail_text .= $rge_config['errorInFeed'] . " " . $rge_config['feedUrls'][$key] . "\n";
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
            $text = performReplacements($rge_config, $text, $item);
            if (empty($text)) {
                echo "Nothing to send for item with GUID $guid\n";
            }

            sendMailAndHandleGUID($text, performReplacements($rge_config, $rge_config['emailSubject'], $item), $rge_config, $pdo, $guid);
        }
    }
}
