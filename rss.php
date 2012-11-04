<?php
/* Merge multiple RSS feeds with SimplePie
*
* Just modify the path to SimplePie and
* modify the $feeds array with the feeds you want
*
* You should probably also change the channel title, link and description,
* plus I added a CC license you may not want
*
* Help from: http://www.webmaster-source.com/2007/08/06/merging-rss-feeds-with-simplepie/
*
*/
header('Content-Type: application/rss+xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';

//Your configuration
require_once('config.php');

// Your path to simplepie
include_once('autoloader.php'); // Include SimplePie
?>
<rss version="2.0"
xmlns:atom="http://www.w3.org/2005/Atom"
xmlns:content="http://purl.org/rss/1.0/modules/content/"
xmlns:dc="http://purl.org/dc/elements/1.1/"
>

<channel>
<title><?php echo $rge_config['feedTitle']; ?></title>
<atom:link href="<?php echo $rge_config['feedLink']; ?>" rel="self" type="application/rss+xml" />
<link><?php echo $rge_config['feedHome']; ?></link>
<description><?php echo $rge_config['feedDesc']; ?></description>
<language>de-de</language>
<copyright>Copyright <?php echo '2011-'.date("Y"); ?></copyright>
<!--<creativeCommons:license>http://creativecommons.org/licenses/by-nc-sa/3.0/</creativeCommons:license>-->

<?php
date_default_timezone_set($rge_config['timezone']);

$feed = new SimplePie(); // Create a new instance of SimplePie
// Load the feeds
$feed->set_feed_url($rge_config['feedUrls']);
$feed->set_cache_duration($rge_config['cacheTime']); // Set the cache time
$feed->set_cache_location($rge_config['cacheDir']); // Set the cache location
//$feed->enable_xml_dump(isset($_GET['xmldump']) ? true : false);
echo $feed->get_raw_data();
$success = $feed->init(); // Initialize SimplePie
$feed->handle_content_type(); // Take care of the character encoding
?>


<?php if ($success) {
$itemlimit=0;
foreach($feed->get_items() as $item) {
if ($itemlimit==$rge_config['maxItems']) { break; }
?>

<item>
<title><?php echo $item->get_title(); ?></title>
<link><?php echo $item->get_permalink(); ?></link>
<guid><?php echo $item->get_permalink(); ?></guid>
<pubDate><?php echo $item->get_date(DATE_RSS); ?></pubDate>
<dc:creator><?php if ($author = $item->get_author()) { echo $author->get_name()." at "; }; ?><?php if ($feed_title = $item->get_feed()->get_title()) {echo $feed_title;}?></dc:creator>
<description>
<?php echo htmlspecialchars($item->get_description()); ?>
</description>
<content:encoded><![CDATA[<?php echo $item->get_content(); ?>]]></content:encoded>
</item>
<?php
$itemlimit = $itemlimit + 1;
}
}
?>
</channel>
</rss>
