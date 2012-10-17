<?php 

function rss($url) {

  // CURL url for data
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $string = curl_exec($ch);
  curl_close($ch);

  /* ---- USEFUL WHEN YOU HAVE NAME SPACES THAT CAUSE PROBLEMS

  // Remove the XML namespace opening tags
  $string = str_replace('<dc:', '<', $string);
  // Remove the XML namespace closing tags
  $string = str_replace('</dc:', '</', $string);
  // For good measure, remove anything that has to do with XML namespace 
  $string = str_replace('xmlns:dc', 'nonsense', $string);

  ------------------------------------------------------------ */
 
  // Assign curl results to XML object
  $xmlObject = @simplexml_load_string($string, 'SimpleXMLElement');

  // Encode the XML object data for storage in case CURL fails
  $json = json_encode($xmlObject);

  // If the CURL to the RSS feed fails then open the back up json of the most recent data
  if ($string === false):
     $fp = fopen('results.json', 'r');
     $json = fread($fp, filesize('results.json'));
     fclose($fp); 

  // If it doesn't fail, then write to results file creating a new back-up and use the current feed data
   else:
     $fp = fopen('results.json', 'w');
     fwrite($fp, $json);
     fclose($fp);
   endif;

  // Decode JSON back into array format
  $json = json_decode($json);
  return $json;

}



?>