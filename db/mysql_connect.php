<?php

function PDOConnect(){
    try{
        $db = new PDO('mysql:host=localhost;dbname=tc_d4k', 'ignite', '!gn!t3', array(
            PDO::ATTR_PERSISTENT => true
        ));
		
		return $db;      
    }catch (PDOException $e) {
        return "Error!: " . $e->getMessage() . "<br/>";
    }
}

function connect(){
	// Connect to the Database //
	$dbConnection = mysql_connect('localhost', 'ignite', '!gn!t3');

	if (!$dbConnection){ die('Could not connect: ' . mysql_error()); }
	if(!mysql_select_db('tc_d4k', $dbConnection)){ die('DB ERROR: ' . mysql_error()); }
}

?>
