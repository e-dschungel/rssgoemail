<?php

/*
    Copyright 2012-2021 e-dschungel https://github.com/e-dschungel
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

require_once dirname(__FILE__) . '/config/config.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/src/rssgoemail_functions.php';

if (!isset($rge_config)) {
    throw new Exception('$rge_config is not set');
}

if($rge_config['allowCors']) {
  cors();
} else {
  header("Content-Type: text/plain");
}

switch($_SERVER['REQUEST_METHOD'])
{
  case 'GET': $request = &$_GET; break;
  case 'POST': $request = &$_POST; break;
  default:
}

// Switch to preview mode
if(isset($request['preview']) && $request['preview'] == true && isset($request['email']))
{
  // Check entered email address
  if(!filter_var($request['email'], FILTER_VALIDATE_EMAIL))
  {
    die("Please enter a valid email address to enter prieview mode.");
  }
  else
  {
    define('EMAIL_PREVIEW', $request['email']);
  } 
}

$rge_config = checkConfig($rge_config);
define('LANG', $rge_config['language']);

$charset = 'utf8mb4';

$opt = [
        //PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
];
$dsn = "mysql:host={$rge_config['dbHost']};dbname={$rge_config['dbBase']};charset=$charset";
$pdo = new PDO($dsn, $rge_config['dbUser'], $rge_config['dbPass'], $opt);

// Call SimplePie
$feed = new \SimplePie\SimplePie();
$feed->set_feed_url($rge_config['feedUrls']);
$feed->enable_cache();
$feed->set_cache_location($rge_config['cacheDir']);
$feed->set_cache_duration($rge_config['cacheTime']);

// Init feed
$feed->init();

// Make sure the page is being served with the UTF-8 headers.
$feed->handle_content_type();

switch (strtolower($rge_config['notificationType'])) {
    case "peritem":
        notifyPerItem($rge_config, $pdo, $feed);
        break;
    case "summary":
        notifySummary($rge_config, $pdo, $feed);
        break;
    default:
        die("Invalid config entry for notificationType {$rge_config['notificationType']}");
}

return;

/**
* Set cors header correctly

*
* @return void
*/
function cors()
{
  // Allow from any origin
	if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Content-Type: text/plain; charset=UTF-8");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
  } else {
      header("Content-Type: text/plain; charset=UTF-8");
  }

  // Access-Control headers are received during OPTIONS requests
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
          // may also be using PUT, PATCH, HEAD etc
          header("Access-Control-Allow-Methods: GET, POST");         

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
          header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
  }
}
