<?php
	//For testing
	ini_set('display_errors', 1);
	error_reporting(E_ALL);	
	
	require_once 'db/mongo_functions.php';
	require_once 'lib/php-sdk/src/facebook.php';
	require_once 'modules/membership/userClass.php';
	
	define('FACEBOOK_APP_ID', '289115227859220');
	define('FACEBOOK_SECRET', '9fecd80c25f734491e0d5e8fdd4b83c9');
	define('FACEBOOK_REDIRECT_URI', 'http://apps.ignitesocialmedia.com/php/facebook/samsung/angrybirds/TEST/');
	define('FACEBOOK_APP_URL', 'https://www.facebook.com/pages/TJ_App-Test/216657098407910?sk=app_289115227859220');
	define('MONGO_DATABASE', 'test');
	define('USER_COLLECTION', 'user');
	define('USER_ID_TYPE', 'fb_uid');
	session_name('Samsung_TEST'); //Rename this to keep sessions between apps on the same server from meshing
	session_start();
	


	class User extends UserBase{
		 /**
		  * @param string, code
		  * @return the value of the return of the registration() method (which is the return of the getInfo() method)
		  * 
		  * When a user is first connecting with our app, we need to convert the code given and get an access token - we don't necessarily have to save it to the database, but I choose to
		  * We then call the registration_refresh() method to fill in the user's profile
		 */
		 public function accessTokenFromCode($code){
		 	$facebook = new Facebook(array(
				'appId' => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_SECRET
			));
	
		 	$access_token = file_get_contents('https://graph.facebook.com/oauth/access_token?client_id=' . FACEBOOK_APP_ID . '&client_secret=' . FACEBOOK_SECRET . '&redirect_uri=' . FACEBOOK_REDIRECT_URI . '&code=' . $code);
			parse_str($access_token, $params);

			if(isset($params['access_token'])):
				$facebook->setAccessToken($params['access_token']);
				$this->access_token = $params['access_token'];
			endif;

			return $this->registration_refresh($facebook);
		 }
		
		
		
		/**
		 * @param object, facebook object <optional>
		 * @return the value of the return of the getInfo() method
		 */
		 public function registration_refresh($facebook){			
			$user_id = $facebook->getUser();

			if($user_id):
				$current_user = $this->mongoDB->findOne(USER_COLLECTION, array('fb_uid' => $user_id));

				if(!$current_user):
					$this->remove('fb_uid');  //Defaults to 0 when the user isn't connected...we want to write their actual FB_UID now that they are connected, so clear/reset the 0
					
					$user_basic_data = $facebook->api('/me?fields=first_name,last_name');

					//Project specific default properties and values to save for a new user
					$this->fb_uid 		= $user_id;
					$this->access_token = $facebook->getAccessToken();
					$this->checkins_counted = false;
					$this->first_name 	= $user_basic_data['first_name'];
					$this->invited_fbuids = array();
					$this->last_name 	= $user_basic_data['last_name'];
					$this->registered 	= false; //Entered email address for sweepstakes
					$this->sweepstakes_entries = 1;
					$this->tiles = array();
					$this->tiles_left = 3; //Amount of tiles they can uncover initially

					$this->update($this); //Save profile data
									
					//Check for sent invitations and award their friends more tiles to unlock
					$invitations = $this->mongoDB->mongo_query('invitations', array('to_fbuid' => $user_id, 'redeemed' => false));
					$invitations = iterator_to_array($invitations);
					
					//foreach, give tiles to from_fbuid and mark the invitation(s) as redeemed
					foreach($invitations as $invite){
						$this->mongoDB->mongo_insert_update(USER_COLLECTION, array('fb_uid' => $invite['from_fbuid']), array('tiles_left' => 1), '$inc');
						$this->mongoDB->mongo_insert_update('invitations', array('_id' => $invite['_id']), array('redeemed' => true, 'redeemed_time' => date('m/d/Y h:i:sA', time())));
					}
				endif;
				
			else:
				//Go to login url?  
			endif;
			
			return $this->getInfo($facebook); //Grab the newest information
		 }



		/**
		 * @param object, facebook object <optional>
		 */
		public function getInfo($facebook = null){
			//Add a second optional parameter that is update = true (by default) but if it's false, don't do the $facebook and setting all the session variables and database calls, just return what is currently there for the user in the session
			if(!$facebook)
				$facebook = new Facebook(array(
					'appId' => FACEBOOK_APP_ID,
					'secret' => FACEBOOK_SECRET
				));
				

			$user_id = $facebook->getUser();
			

			if(isset($this->fb_uid) && $this->fb_uid != $user_id) //Don't show another user's information, clear session variables if the current FB_UID doesn't match the session FB_UID
				foreach($this as $key => $value)
					$this->remove($key);
				
			
			//Not necessary...I think if the $user_id is not 0, that means the access token is implicitly set for the $facebook object.
			//This would come into play IF you do Facebook stuff when the user is not currently on the site
			// if($this->access_token)  //The access_token is set in accessTokenFromCode, this way we can grab the user's data right away instead of after a refresh.  Then we set it later if it's available just to be more explicit.
				// $facebook->setAccessToken($this->access_token);


			if($user_id):
				$this->fb_uid = $user_id;

				$user_id = USER_ID_TYPE;
				$active_user = $this->mongoDB->findOne(USER_COLLECTION, array(USER_ID_TYPE => $this->$user_id));

				if($active_user):
					unset($active_user['_id']); //Remove the Mongo ID
					unset($active_user['tiles']); //No need to store all this in the session

					foreach($active_user as $key => $value)
						$this->$key = $value;
					

					if($this->access_token != $facebook->getAccessToken()): //Keep their session/saved token up to date - Canvas and page tab apps refresh the access_token each time the user comes to the app, use this token - For websites, you should direct the user through the loginUrl() to keep their session fresh
						//If your app details on Facebook has a namespace, your access token on your website, canvas app and page tab app are all the same - ex) AAAEG8uJ0qRQBAMztZBynHGRlkZASZAiQPDBsZBdfJlx78iwvMxOhoGpZC9iPPHJZCPQPQgXyWtPzj84XTN6ImnlW1IVAQaWq4MYbh8fz18ZBAZDZD
						//If you don't, then your website and canvas app are the same (NOT ALWAYS), but the page tab app access token is formatted differently - ex) 289115227859220|9fecd80c25f734491e0d5e8fdd4b83c9
						$new_token = $facebook->getAccessToken();
						$this->remove('access_token');
						$this->access_token = $new_token;

						$this->update(array('access_token' => $new_token));
					endif;
				
			
					//Good place to check values in the user's profile and execute other tasks
					//if(!$active_user['checkins_counted']) //If checkins_counted is false, evaluate the user's checkins and award additional tiles
						//$this->evaluate_checkins($facebook);
				endif;
			else:
				//For OFF Facebook apps - I was having an issue that if you started on a sammy page, #/, index.php#/, then Facebook would spaz out and just go to Facebook.com.  If you go to getLoginUrl() or the manually pasted in URL, then it works fine
				//If calling the getUser() method in the Facebook SDK results in 0, quickly redirect the user to Facebook to make sure they are logged in - Facebook will redirect back to the passed redirect_uri and we will try to getInfo() again on the index.php page
				//header('Location:' . str_replace('%2Findex.php', '/', $facebook->getLoginUrl())); //If the user is on index.php, the SDK will make the redirect_uri as index.php.  However, as I'm finding out, Facebook authorization requires EXACT matches, so I'm making sure everything is just /
				//header('Location: https://www.facebook.com/dialog/oauth?client_id=316178028479612&redirect_uri=http://apps.ignitesocialmedia.com/php/facebook/samsung/angrybirds/&scope=user_status,user_photos');
				//header('Location: index.php'); //Refresh the page
				//header('Location: ' . $facebook->getLoginUrl(array('scope' => 'user_status,user_photos')));
				
				//ON Facebook
				//It doesn't seem to be as much of an issue...the FB_UID is always available, so this step isn't needed for ON Facebook apps
			endif;

			return $this;
		}
		  
		  
		  
		/**
		* 
		*/
		public function autoClear(){		
			//----------------------------------------------
			// Start a new week
			//----------------------------------------------
			//Known start times of each week
			//Set in DB, but also use a .txt file for the next time to update...read that..if time to update, clear  
			$next_update_time = file_get_contents('next_update_time.txt');			
			$current_gmt_time = gmmktime();

			if($current_gmt_time > $next_update_time):	
				//Mark this week as week_passed
				if($next_update_time) //Will be NULL when testing/clearing manually
					$this->mongoDB->mongo_insert_update('week_times',  array('gmt_end' => (int)$next_update_time), array('week_passed' => true));	
							
				//Get new next_update_time and save it
				$new_next_update_time = $mongoDB->mongo_query('week_times', array('week_passed' => false), 1, array(), 0, array('gmt_start' => 1));  //Can't use findOne because we have to sort
				$new_next_update_time = iterator_to_array($new_next_update_time);
				$new_next_update_time = current($new_next_update_time);
				file_put_contents('next_update_time.txt', $new_next_update_time['gmt_end']);
	
				$this->mongoDB->mongo_insert_update('user', array(), array('checkins_counted' => false)); //Reset the users, so when they come back, their checkins can be evaluated
			endif;
			//----------------------------------------------
			// END Start a new week
			//----------------------------------------------
		}
		
		
		
		/**
		 * 
		 */
		 public static function bitly($url){
		 	$login = 'ignite';
			$appkey = 'R_9bf6ad7ead5f80f70e3584046634449b';
			$version = '2.0.1';
			
			$response = file_get_contents('http://api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode($url) . '&login=' . $login . '&apiKey=' . $appkey . '&format=json');
			
			$json = json_decode($response, true);
			
			return $json['results'][$url]['shortUrl'];
		 }
	



	}
	  
?>