<?php

	//include_once(dirname(__FILE__) . '../db/mongo_functions.php'); //Fixes the relative path issue, so any file in any folder can include userClass.php and not have an issue with relative file paths
	//include_once(dirname(__FILE__) . '../lib/php-sdk/src/facebook.php');

	class UserBase{
		/**
		 * 
		 */
		//Using magic __construct, __get and __set methods to create the needed class variables
		public function __construct(){						
			if(isset($_SESSION)) //Go through each $_SESSION variable and set it to a User-class variable
				foreach($_SESSION as $key => $value)
					$this->$key = $value;
			
			$this->mongoDB = new MongoFunctions(MONGO_DATABASE, 'localhost');
			
			return $this;
		}
		
		
		
		/**
		 * 
		 */
		public function __get($var){
			return $this->$var;
		}
		
		
		
		/**
		 * 
		 */
		public function __set($key, $val){
			$this->$key = $val;			
			$_SESSION[$key] = $val;
			
			return true;
		}
		
		
		
		/**
		 * 
		 */
		public function remove($key){
			unset($this->$key);
			unset($_SESSION[$key]);
			
			return $this;
		}
		
		
		
		/**
		 * 
		 */
		public function logout(){
			session_destroy();
			
			return true;
		}
		
		
		
		/**
		 * @param string, $table - The collection to update - calling it table to match MongoFunctions (for now)
		 * @param array, $insert - The array to send to the mongo_insert_update() method
		 * @param array, $where - The match of the record to update, defaults to 'fb_uid' => $this->fb_uid.  I couldn't set the default in the method header, so it's set on the first 2 lines of the method
		 * @param string, $type - To insert or update data you can use the default of '$set', but you can also do '$push', '$inc', etc
		 * 
		 * I noticed that most of my queries and updates had a $where statement of 'fb_uid' => $this->fb_uid, so when updating the user's data, you can call this method that defaults to that $where statement, however you can override it if you'd like
		 */
		 public function update($insert, $table = null, $where = null, $type = '$set'){
		 	if(!isset($table))
				$table = USER_COLLECTION;
			
			$user_id = USER_ID_TYPE;
		 	if(!isset($where))
				$where = array(USER_ID_TYPE => $this->$user_id); //$this->$user_id will grab the actual, evaluated, value of $this->fb_uid or $this->user_id, for example

			if(!is_array($insert)) //If it's an array, it will work fine - you'll actually get a fatal error when you try to clone an array
				$insert = clone $insert; //I found that if I passed $this as $insert, it would include $this->mongoDB (which I don't want to save to the database - and Mongo actually has a problem saving it), but when I do unset($insert->mongoDB), PHP removes it from $this as well, because $insert is a reference of $this.  To get around it, I made a COPY of $this using clone(), modified it how I wanted and then saved that
	
			if(isset($insert->mongoDB))	unset($insert->mongoDB);
			
			
			//Facebook tacks on some values that end up getting saved in our session, but we don't really need to save them - so if applicable, remove them
			$app_code = 'fb_' . FACEBOOK_APP_ID . '_code';		if(isset($insert->$app_code)) 	unset($insert->$app_code);
			$app_access_token = 'fb_' . FACEBOOK_APP_ID . '_access_token';		if(isset($insert->$app_access_token)) 	unset($insert->$app_access_token);
			$app_user_id = 'fb_' . FACEBOOK_APP_ID . '_user_id';		if(isset($insert->$app_user_id))	unset($insert->$app_user_id);


		 	return $this->mongoDB->mongo_insert_update($table, $where, $insert, $type);
		 }
		
		
		
	}
?>