<?php 
	//For small projects, this works very nice.  We can show different content based on values in the user's session, still calling the same page (tmpl_content.php).

	if(isset($user->registered) && $user->registered):
		include 'templates/tmpl_board.php';
	else:
?>

		HOMEPAGE
		<br /><br />
		<a href="https://www.facebook.com/dialog/oauth?client_id=<? echo FACEBOOK_APP_ID ?>&redirect_uri=<? echo FACEBOOK_REDIRECT_URI ?>" target="_parent">LOGIN</a>
		<br /><br />
		<div id="submit_email">Click me</div>
		<input type="text" value="TJNevis@gmail.com" id="email" />

<? endif; ?>