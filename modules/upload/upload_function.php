<?php
	
	//=========================================================
	
	$upload_path = realpath('../../uploads/') . '/';
	$style_id = 1; //DEFAULT - Generate?  What if it's already been created?
	$picture_number = $_POST['picture_number'];
	$max_filesize = 3; //in MB
	$max_large_file_width = 500;
	$thumb_width = $thumb_height = 100;
	$allowed_filetypes = array('image/jpeg', 'image/gif', 'image/png', 'image/pjpeg', 'image/x-png'); //image/pjpeg, image/x-png are IE8
	$copy = array(
		'filesize' => 'File is too large.  Please reduce the filesize to ' . $max_filesize . 'MB or less',
		'filetype' => 'Please only upload a jpg, gif or png file type',
		'copyingError' => 'There was an error saving the file, please try again'
	);
	
	//=========================================================
	
	if(in_array($_FILES['upload_picture']['type'], $allowed_filetypes)):
		if($_FILES['upload_picture']['size'] > $max_filesize * 1048576): //1048576 is 1MB in bytes
			echo json_encode(array('Error' => $copy['filesize']));
			exit();
		endif;
		
		if(!move_uploaded_file($_FILES['upload_picture']['tmp_name'], $upload_path . $_FILES['upload_picture']['name'])):
			echo json_encode(array('Error' => $copy['copyingError']));
			exit();
		endif;
		
		$return = resizeImage($upload_path . $_FILES['upload_picture']['name'], $upload_path, $max_large_file_width, $thumb_width, $thumb_height);		
	else:
		//echo json_encode(array('Error' => $copy['filetype']));
		//Will fire even when creating the thumbnail (with the if statement like it is)
	endif;


	
	if(isset($_POST['upload_thumbnail'])):
		extract($_POST);
		$return = resizeImage($upload_path . $image_filename, $upload_path, $max_large_file_width, $thumb_width, $thumb_height, $crop_width, $crop_height, $start_x, $start_y);
	endif;


	
	/**
	 * resizeImage does 2 operations:
	 * It will take a full sized image and shrink it to the set $max_large_file_width, resizing the height to the respective scale/ratio.  It can also crop an image, taking a portion of the larger picture.
	 * 
	 * If $thumb_image is not set, then we assume we are resizing a full sized image, shrinking it to the $max_large_file_width and respective height
	 * If $thumb_image is set, we assume we are cropping a portion of the full sized image - will need $thumb_width, $thumb_height, $crop_width, $crop_height, $start_x, $start_y.  $thumb_height and $thumb_width are passed with the resize photo operations, not for the calculations, but to pass to the front end - used for the cropping window.
	 * 
	 * When this function is run as an upload/resize, the file is saved as resize_[random numbers].jpg.  When this function is run to crop a photo, teh new photo is saved as cropped_[random_numbers].jpg and the resize_ .jpg is deleted.
	 * 
	 * @return saved image location
	 * @param string location of image
	 * @param string path of uploads folder, ending with a slash
	 * @param integer max width of image
	 * @param integer thumb width
	 * @param integer thumb height
	 * @param integer crop width
	 * @param integer crop height
	 * @param optional integer start X (for crop)
	 * @param optional integer stary Y (for crop)
	 */
	function resizeImage($image, $upload_path, $max_large_file_width, $thumb_width, $thumb_height, $crop_width, $crop_height, $start_x = 0, $start_y = 0){
		//width and height should be cropped_width and cropped_height
		//we can get the width and height of the fullsized picture with list($full_sized_width, $full_sized_height) = getimagesize($image);
		list($full_sized_width, $full_sized_height, $image_type) = getimagesize($image);
		
		//===============================================================
		//If the $crop_width variable is set, we want to overwrite the $full_sized_width variable with this value.  When cropping, 
		//we want the width and height to be that which the user chose to crop.  If we are resizing, then we want the full height and width of the original image.
		//===============================================================
		$full_sized_width = (isset($crop_width)) ? $crop_width : $full_sized_width; 
		$full_sized_height = (isset($crop_height)) ? $crop_height : $full_sized_height;
		
		$image_type = image_type_to_mime_type($image_type); //Overwrite the integer value returned from getimagesize into a string image_type, image/jpeg, for example
		
		$scale = ($full_sized_width > $max_large_file_width) ? $max_large_file_width / $full_sized_width : 1; //If the width of the image is larger than the $max_large_file_width, get the ratio of the difference, otherwise, keep the scale at 1
		
		//===============================================================
		//If $thumb_width is set, then we want to set the $new_image_width variable to that value because we are cropping the image, otherwise we are scaling the image, 
		//we'll calculate the new width once it has been scaled.  
		//We are taking the $full_sized_width and $full_sized_height and reducing it to the $new_image_width and $new_image_height
		//We pass $thumb_width and $thumb_height always, so we'll have to check and see if the $crop_width and $crop_height
		//===============================================================		
		$new_image_width = (isset($crop_width)) ? $thumb_width : ceil($full_sized_width * $scale); 
		$new_image_height = (isset($crop_height)) ? $thumb_height : ceil($full_sized_height * $scale);
		
		$new_image = imagecreatetruecolor($new_image_width, $new_image_height);
		
		switch($image_type){
			case "image/gif":
				$source_image = imagecreatefromgif($image); 
				break;
		    case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$source_image = imagecreatefromjpeg($image); 
				break;
		    case "image/png":
			case "image/x-png":
				$source_image = imagecreatefrompng($image); 
				break;
	  	}
		
		imagecopyresampled($new_image, $source_image, 0, 0, $start_x, $start_y, $new_image_width, $new_image_height, $full_sized_width, $full_sized_height); //Draw the scale or crop on the canvas ($new_image)
		
		$new_name = (!isset($crop_width)) ? uniqid('resized_') . '.jpg' : uniqid('cropped_') . '.jpg'; //Create a unique ID starting with resized_ or cropped_

		imagejpeg($new_image, $upload_path . $new_name, 100); //Save the new image 
		
		//Destroy the images from memory
		imagedestroy($image);
		imagedestroy($new_image);	
		
		unlink($image); //Delete old image from server		

		return array('image_filename' => $new_name, 'image_width' => $new_image_width, 'image_height' => $new_image_height, 'preview_width' => $thumb_width, 'preview_height' => $thumb_height);
	}

	


?>

<script language="javascript" type="text/javascript">window.top.window.stopUpload(<?php echo json_encode($return); ?>);</script>   
