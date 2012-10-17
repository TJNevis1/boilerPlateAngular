<?php
	require_once 'testClass.php';
	$user = new User();
	//$user->autoClear();
	
	/*
	 * NOTE ABOUT TEMPLATING
	 * For the javascript templating, pulling in via sammy (for example), use tmpl_homepage.php - which can be called on sammy's #/ route with loadPage('homepage');  The tmpl_header.php and tmpl_footer.php go well with this type of templating
	 * 
	 * The newer way I've been doing things, and the way I find cleaner - is doing it on the server side.  I would set a header and footer on the index page (as long as it doesn't change through the steps) and include tmpl_content.php in the middle
	 * Then tmpl_content.php will determine what content the user should see based on their PHP session variables and values
	*/

	
	if(isset($_GET['code'])):
		$user->accessTokenFromCode($_GET['code']);
		header('Location: ' . FACEBOOK_APP_URL); //Forward to Facebook tab
		
		//Redirect user if they get to the URL on the server - not on Facebook
  	elseif(!isset($_REQUEST['signed_request']) && isset($_SERVER['HTTP_REFERER']) && !strpos($_SERVER['HTTP_REFERER'], 'ignitesocialmedia.com')): //DON'T DO location.reload(true) IN JAVASCRIPT AGAIN, IF WE CAN AVOID IT
		//Weird issue that I just came across.  In my scripts.js file, I had location.reload(true); and then I got this error message in Chrome 'Refused to display document because display forbidden by X-Frame-Options' and it wouldn't refresh the page
		//Apparently, what is happening is the page is reloading and because the request isn't coming from Facebook, but just the iframe refreshing, there is no $_REQUEST['signed_request'] and the page gets forwarded to the Facebook tab, which is NOT the same domain - aka an X-Frame error
		
		//header('Location: ' . FACEBOOK_APP_URL); //Forward to Facebook tab
	endif;


	if(!isset($user->fb_uid))
		$user->getInfo();
	
	// echo '<pre>';
	// var_dump($user);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en" ng-app="myApp">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Facebook iframe Boiler Plate</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
        <script src="js/scripts.js" type="text/javascript" charset="utf-8"></script>
        <!-- Angular -->
        <script ng:autobind src="js/angular/angular.js"></script>
        <script src="js/app.js"></script>
        <script src="js/services.js"></script>
        <script src="js/controllers.js"></script>
        <script src="js/filters.js"></script>
        <script src="js/directives.js"></script>
        <!-- End Angular -->

        <link rel="stylesheet" type="text/css" href="styles/reset.css">
        <link rel="stylesheet" type="text/css" href="styles/styles.css">
        
        <script type="text/javascript">

            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-2140750-1']);
            _gaq.push(['_setDomainName', '.ignitesocialmedia.com']);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();

        </script>


        <div id="fb-root"></div>
        <script>
		  window.fbAsyncInit = function() {
		    FB.init({
		      appId      : '<? echo FACEBOOK_APP_ID ?>', // App ID
		      status     : true, // check login status
		      cookie     : true, // enable cookies to allow the server to access the session
		      xfbml      : true  // parse XFBML
		    });
		
		    // Additional initialization code here
		    
		    FB.Canvas.setSize();
		  };
		
		  // Load the SDK Asynchronously
		  (function(d){
		     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
		     if (d.getElementById(id)) {return;}
		     js = d.createElement('script'); js.id = id; js.async = true;
		     js.src = "//connect.facebook.net/en_US/all.js";
		     ref.parentNode.insertBefore(js, ref);
		   }(document));
		   
		   
		   //exclude_fbuids for the Facebook friend selector 
		   //var exclude_fbuids = <? //echo json_encode($user->invited_fbuids); ?>;
		</script>
        
        
        <?php
	        /*
	         * Fan Gate Script, Signed Request Parse
	         * ------------------------------------------------------- */	
	        function parse_signed_request($signed_request, $secret){
	            list($encoded_sig, $payload) = explode('.', $signed_request, 2);
	
	            //decode the data
	            $sig = base64_url_decode($encoded_sig);
	            $data = json_decode(base64_url_decode($payload), true);
	
	            if(strtoupper($data['algorithm']) !== 'HMAC-SHA256'){ error_log('Unknown algorithm. Expected HMAC-SHA256'); return null; }
	            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	            if($sig !== $expected_sig){ error_log('Bad Signed JSON signature!'); return null; }
	            return $data;
	        }
	
	        function base64_url_decode($input){ return base64_decode(strtr($input, '-_', '+/')); }
	
	        if(isset($_REQUEST['signed_request']))
	            $response = parse_signed_request($_REQUEST['signed_request'], FACEBOOK_SECRET);

	        //Check this variable to see if user is a fan of the page
	        if(isset($response))
				//$like_status = $response['page']['liked'];
	        	$like_status = ($response['page']['liked'] || (strpos($_SERVER['HTTP_REFERER'], 'ignitesocialmedia.com'))) ? true : false;
        ?>
    </head>
    <body>    	
    	
    	<div id="content" ng-view>
    		<? //include 'templates/tmpl_content.php'; ?>
    	</div>
    	
    </body>
</html>