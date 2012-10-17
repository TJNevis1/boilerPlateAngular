<?php
// Start counting time for the page load
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

// Include SimplePie
// Located in the parent directory
include_once('simplepie.inc');
include_once('idna_convert.class.php');

// Create a new instance of the SimplePie object
$feed = new SimplePie();

//$feed->force_fsockopen(true);

// Make sure that page is getting passed a URL
//if (isset($_GET['feed']) && $_GET['feed'] !== '')
//{
	// Strip slashes if magic quotes is enabled (which automatically escapes certain characters)
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
	{
		$_GET['feed'] = stripslashes($_GET['feed']);
	}
	
	// Use the URL that was passed to the page in SimplePie
	//$feed->set_feed_url($_GET['feed']);
	$feed->set_feed_url("http://blog.haircuttery.com/feed/");
	
	// XML dump
	$feed->enable_xml_dump(isset($_GET['xmldump']) ? true : false);
//}

// Allow us to change the input encoding from the URL string if we want to. (optional)
if (!empty($_GET['input']))
{
	$feed->set_input_encoding($_GET['input']);
}

// Allow us to choose to not re-order the items by date. (optional)
if (!empty($_GET['orderbydate']) && $_GET['orderbydate'] == 'false')
{
	$feed->enable_order_by_date(false);
}

// Allow us to cache images in feeds.  This will also bypass any hotlink blocking put in place by the website.
if (!empty($_GET['image']) && $_GET['image'] == 'true')
{
	$feed->set_image_handler('./handler_image.php');
}

// We'll enable the discovering and caching of favicons.
$feed->set_favicon_handler('./handler_image.php');

// Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and 
// all that other good stuff.  The feed's information will not be available to SimplePie before 
// this is called.
$success = $feed->init();

// We'll make sure that the right content type and character encoding gets set automatically.
// This function will grab the proper character encoding, as well as set the content type to text/html.
$feed->handle_content_type();

// When we end our PHP block, we want to make sure our DOCTYPE is on the top line to make 
// sure that the browser snaps into Standards Mode.
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
<title>SimplePie: Demo</title>

<link rel="stylesheet" href="./for_the_demo/sIFR-screen.css" type="text/css" media="screen">
<link rel="stylesheet" href="./for_the_demo/sIFR-print.css" type="text/css" media="print">
<link rel="stylesheet" href="simplepie.css" type="text/css" media="screen, projector" />

<script type="text/javascript" src="./for_the_demo/sifr.js"></script>
<script type="text/javascript" src="./for_the_demo/sifr-config.js"></script>
<script type="text/javascript" src="./for_the_demo/sleight.js"></script>

</head>

<body>
			<?php
			
			
			
			
			function returnImage ($text) {
			    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
			    //echo $text;
			    $pattern = "/<img[^>]+\>/i";
			    preg_match($pattern, $text, $matches);
			    $text = $matches[0];
			    return $text;
			}
			
			////////////////////////////////////////////////////////////////
			//Filter out image url only
			
			function scrapeImage($text) {
			    $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/'; 
			    
			    preg_match($pattern, $text, $link);
			    
			    $link = $link[1];
			    $link = urldecode($link);
			    return $link;
			}
			
			
			
			// Check to see if there are more than zero errors (i.e. if there are any errors at all)
			if ($feed->error())
			{
				// If so, start a <div> element with a classname so we can style it.
				echo '<div class="sp_errors">' . "\r\n";

					// ... and display it.
					echo '<p>' . htmlspecialchars($feed->error()) . "</p>\r\n";

				// Close the <div> element we opened.
				echo '</div>' . "\r\n";
			}
			?>

			<!-- Here are some sample feeds. -->
		
		<div id="sp_results">

			<!-- As long as the feed has data to work with... -->
			<?php if ($success): ?>
				
				<?php 
				for($i = 1; $i <= 2; $i++):
				//foreach($feed->get_items() as $item): 
				$item = $feed->get_item($i);
				$image = scrapeImage(returnImage($item->get_content()));
				$image = ($image == '') ? 'https://apps.ignitesocialmedia.com/php/facebook/haircuttery/fanexclusives/images/blog_default.jpg' : $image; //If there is no image, use a default image				
				?>
				
				<div class="entry">
					<div class="title"><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></div>
					<div class="image"><img src="<?php echo $image; ?>" /></div>
					<div class="excerpt">
						<?php echo substr($item->get_content(), 0, 300); ?>... 
						<span class="readmore"><a href="<?php echo $item->get_permalink(); ?>">Read More >></a></span> 
					</div>
				</div>
				<?php endfor; ?>

			<!-- From here on, we're no longer using data from the feed. -->
			<?php endif; ?>

		</div>



	</div>

</div>

</body>
</html>
