<?php 
date_default_timezone_set('America/New_York'); 

/**
* Formats a timestamp nicely with an adaptive "x units of time ago" message.
* Based on the original Twitter JavaScript badge. Only handles past dates.
* @return string Nicely-formatted message for the timestamp.
* @param $time Output of strtotime() on your choice of timestamp.
*/
function niceTime($time) {
  $delta = time() - $time;
  if ($delta < 60) {
    return 'less than a minute ago';
  } else if ($delta < 120) {
    return 'about a minute ago';
  } else if ($delta < (45 * 60)) {
    return floor($delta / 60) . ' minutes ago';
  } else if ($delta < (90 * 60)) {
    return 'about an hour ago.';
  } else if ($delta < (24 * 60 * 60)) {
    return 'about ' . floor($delta / 3600) . ' hours ago';
  } else if ($delta < (48 * 60 * 60)) {
    return '1 day ago.';
  } else {
    return floor($delta / 86400) . ' days ago';
  }
}

/**
* Uses curl to ping the Twitter API, if the twitter API fails, read from the TEMP json file
* Parse out results into HTML
* @return string formatted HTML of tweets
* @param string twitter search query (pass array for multiple queries)
* @param int number of tweets to be returned, defaults to 10
*/

function getTweets($query, $num = 10) {

     if(is_array($query)){
       $query = implode(' OR ', $query);
     }     

	   $query = urlencode($query);

    //Query twitter for the search
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://search.twitter.com/search.json?q=$query&rpp=$num");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    curl_close($ch);

    // if the tweet CURL fails, read from the temp JSON file so it doesn't appear blank
    if ($json === false):
      $fp = fopen('tweets.json', 'r');
      $json = fread($fp, filesize('tweets.json'));
      fclose($fp);

    else:
      // Write to JSON file
      $fp = fopen('tweets.json', 'w');
      fwrite($fp, $json);
      fclose($fp);
    endif;
 
    //convert the resulting json into PHP
    $result = json_decode($json);

    // --------- DEBUG -------
    /*
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    */
    
 
    //third, build up the html output
    $s = '<ul>';

    foreach ($result->results as $item) {
    	  // profile image
    	  $image = $item->profile_image_url;

        //handle any special characters
        $text = htmlentities($item->text, ENT_QUOTES, 'utf-8');

        //user
        $user = $item->from_user;
 
        //build the metadata part
        $meta = niceTime(strtotime(str_replace("+0000", "", $item->created_at)));
 
        //parse the tweet text into html
        $text = preg_replace('@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '<a href="$1">$1</a>', $text);
        $text = preg_replace('/@(\w+)/', '<a href="http://twitter.com/$1">@$1</a>', $text);
        $text = preg_replace('/\s#(\w+)/', ' <a href="http://search.twitter.com/search?q=%23$1">#$1</a>', $text);
 
        //assemble everything, modify this line if you need to change the structure of the html output
        $s .= '<li><img class="author" src="'.$image.'" align="left"/><p><span class="author">'.$user.'</span></p><p>' . $text . "</p>" . '<p class="date"><span class="timeline">' . $meta . "</span></p></li>\n";
    }
	
	$s .= '</ul>';
 
    return $s;
}

?>