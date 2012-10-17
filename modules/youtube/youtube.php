<?php

/*
* YOU MUST INCLUDE THE CAROUSEL MODULE FOR THIS TO WORK AS WELL
*/

function youTubeify($playlist_id){
	

    //First, get the playlist
    $json = file_get_contents("https://gdata.youtube.com/feeds/api/playlists/".$playlist_id."?v=2&alt=json");

    if ($json === false):
      $fp = fopen('results.json', 'r');
      $json = fread($fp, filesize('results.json'));
      fclose($fp);
    else:
      // Write to JSON file
      $fp = fopen('results.json', 'w');
      fwrite($fp, $json);
      fclose($fp);
    endif;
 
    //second, convert the resulting json into PHP
    $result = json_decode($json);

    // Blow up the URL of the 1st video in the feed for an iframe embed
    $mainURL = $result->feed->entry[0]->link[0]->href;
    $urlPieces = explode('=', $mainURL);
    $videoID = explode('&', $urlPieces[1]);

    //Setup the HTML file
    $videos = '<div class="video-carousel"><div class="vidcontainer-car">';
    $videos .= '<iframe class="youtube-player" type="text/html" width="426" height="259" src="http://www.youtube.com/embed/'.$videoID[0].'" frameborder="0"></iframe></div>';
    $videos .= "<div id='carousel_container'><div id='left_scroll'><img src='http://ignitesma.s3.amazonaws.com/facebook/microsoft/bing/bingdiscover/images/arrow-left.png' /></div><div id='carousel_inner'><ul id='carousel_ul'>";

    //Grab URLSs of each video and the thumbnail URL
    foreach($result->feed->entry as $value){
        $thumbs[] = array($value->link[0]->href, $value->{'media$group'}->{'media$thumbnail'}[2]->url);
    }

    array_push($thumbs, $thumbs[0]);
    unset($thumbs[0]);
    //unset($thumbs[1]);
    array_shift($thumbs);

    //Explode the URLS to get ID of each video
    foreach ($thumbs as $key=>$value){
        $urlPieces = explode('=', $value[0]);
        $videoID = explode('&', $urlPieces[1]);
        $thumbs[$key][0] = $videoID[0];
    }

    //Add the sub-videos to the HTML
    foreach ($thumbs as $value){
        
        $videos.= '<li><a href="#"><img src="'.$value[1].'" rel="'.$value[0].'"/></a></li>';
    }

    $videos .= "</ul></div><div id='right_scroll'><img src='http://ignitesma.s3.amazonaws.com/facebook/microsoft/bing/bingdiscover/images/arrow-right.png' /></div></div></div>";

    return $videos;
}

/*

 // DEBUG EXAMPLE
 $list = youTubeify('E5FB4A0878A3CA81');
 print_r($list);

*/

 ?>
 