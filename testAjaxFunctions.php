<?
	require_once 'testClass.php';
	require_once 'db/mongo_functions.php';
	
	$mongoDB = new MongoFunctions('test', 'localhost'); //KEEP $mongoDB on the AjaxFunctions script because we might update more than just the user.  We can still use $user->update() too
	
	$user = new User();

	//=====================================================
	$action = $_POST['action'];
	$response = array();
	
	$input = array('mongoDB' => $mongoDB, '$_POST' => $_POST, 'user' => $user);

	if($action == 'update_user')
		$response = update_user($input);
	//===================================================== 



	/**
	 * Client side calls to update the user or calls within the AjaxFunctions script should call this for consistency
	 * Pass data as an update array with properties from javascript, so $update would be an array  -  action=update_user&update[first_name]=TJ&update[last_name]=Nevis, action=update_user&update[tiles.85]=5, JSON { action : 'update_user', update : { email : $('#email').val() } }
	 */
	function update_user($input){
		extract($input);
		extract($_POST);
			
		//Add to the $update array as needed
		if(isset($update['email']))
			$update['registered'] = true;


		$user->update($update);
		
		//test($input); //Testing, to show that other functions within the AjaxFunctions page can easily call this function to update the user
		
		return array('Success' => true);
	}
	
	
	
	function test($input){
		extract($input);
		
		$user->update('user', array('email' => 'TJ410@rochester.rr.com'));
		
		//OR
		//$someArray['email'] = 'TJNevis@gmail.com';
		//$user->update('user', $someArray);
		
		return true;
	}
	 

 
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	echo json_encode($response);
?>