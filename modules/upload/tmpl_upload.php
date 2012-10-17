<script src="modules/upload/js/jquery.imgareaselect.min.js" type="text/javascript" charset="utf-8"></script>
<script language="javascript" type="text/javascript">
	var image_filename, image_width, image_height, preview_width, preview_height, selected_box, create_thumbnail;
	var uploads_dir = 'uploads/';
	
	var file_uploader = 'File: <input name="upload_picture" type="file" size="30" /><!--<input type="submit" name="submitBtn" value="Upload" />-->';
	
	function preview(img, selection) { 
		var scaleX = preview_width / selection.width; 
		var scaleY = preview_height / selection.height; 
		
		$('#preview_thumbnail').css({ 
			width: Math.round(scaleX * image_width) + 'px', 
			height: Math.round(scaleY * image_height) + 'px',
			marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
			marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
		});
		$('#start_x').val(selection.x1);
		$('#start_y').val(selection.y1);
		$('#crop_width').val(selection.width);
		$('#crop_height').val(selection.height);
	} 
	
	$(document).ready(function () { 
		$('#file_uploader').html(file_uploader);
		
		$('#upload_container').live('change', 'input[name="upload_picture"]', function(){
			$('#upload_form').submit(); //Automatically submit form to start uploading
		});
		
		$('#save_thumb').click(function() {
			// var x = $('#x1').val();
			// var y = $('#y1').val();
			// var w = $('#w').val();
			// var h = $('#h').val();
			// if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
				// alert("You must make a selection first");
				// return false;
			// }else{
				// return true;
			// }
		});
		
		$('.box').click(function(){
			$('#upload_container').show();
			selected_box = this.id;
			$('.box').removeClass('selected');
			$(this).addClass('selected');
		});
	}); 
	
	
	
	
	function startUpload(){
		$('#upload_container').hide();
	    $('#loading').show();
	}
	
	function stopUpload(success){
		  $('#loading').hide();
		 
	      if (success){
	         image_filename = success.image_filename;
	         image_width = success.image_width;
	         image_height = success.image_height;
	         preview_width = success.preview_width;
	         preview_height = success.preview_height;
	         
	         if(image_filename.indexOf('resized_') != -1 ){
	         	$('#thumbnail_container').show();
	         	$('#image_filename').val(image_filename);
	         
	         	$('#create_thumbnail, #preview_thumbnail').attr('src', uploads_dir + success.image_filename);
	         	create_thumbnail = $('#create_thumbnail').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: preview });
	         }else{
	         	create_thumbnail.imgAreaSelect({ remove : true });
	         	$('#thumbnail_container').hide();
	         	$('#' + selected_box).html('<img src="' + uploads_dir + success.image_filename + '" />');
	         	
		     	selected_box = null;
		     	$('.box').removeClass('selected');
		     	$('#file_uploader').html(file_uploader); //Reset the file uploader by overwriting what was there
	         } 
	      }
	}
	
	/*
	 * The basic procedure for this page is first a user has to select one of the 4 boxes.  This will show the uploader.  
	 * They choose a photo and it will upload asynchronously (and saved as resize_[random numbers].jpg) and once completed, will load the resized image and the crop preview DIVs.
	 * The user can choose their desired crop (we are setting it on a 1:1 aspect ratio, or square).  Once they are finished, they choose 'Save Thumbnail'. 
	 * The thumbnail will be saved (as cropped_[random numbers].jpg) and populated in the DIV the user initially selected (1-4).
	 * Once the other form items are on this page, the idea is that once the user has passed validation, the form values and the 4 image URLS (cropped_[random numbers].jpg URLs) will be sent to be saved in the database.
	 * The images will be run through the PHP function RENAME() once we have a style ID and then the images will change to [style_id]-[#].jpg, such as 21-2.jpg.  After all the images are renamed, we can send them as an array along with the other data to save.  If we had to do them one by one for some reason, we can use the Mongo operator $push to add to the images array.
	 */
</script>
<link rel="stylesheet" href="modules/upload/styles/imgareaselect-animated.css" type="text/css" media="screen" title="no title" charset="utf-8"/>
<style type="text/css" media="screen">
	#loading, #thumbnail_container{display:none;}
	.box{height:100px;width:100px;padding:5px;border:1px solid black;}
	.box.selected{background-color:red;}
</style>

<div id="loading">Loading...<br/><img src="modules/upload/horizontal_boxes-t.gif" /></div>

<h2 id="message"></h2>

<div id="upload_container" style="display:none">
    <form id="upload_form" action="modules/upload/upload_function.php" method="post" enctype="multipart/form-data" target="upload_iframe" onsubmit="startUpload();" >
          <div id="file_uploader"></div>
         
         <iframe name="upload_iframe" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
     </form>
</div> 

<div id="thumbnail_container">
	<h2>Create Thumbnail</h2>
	<div align="center">
		<img src="" style="float: left; margin-right: 10px;" id="create_thumbnail" alt="Create Thumbnail" />
		<div style="border:1px #e5e5e5 solid; float:left; position:relative; overflow:hidden; width:100px; height:100px;">
			<img src="" style="position: relative;" alt="Thumbnail Preview" id="preview_thumbnail" />
		</div>
		<br style="clear:both;"/>
		<form name="thumbnail" action="modules/upload/upload_function.php" method="post" target="thumbnail_iframe">
			<input type="hidden" name="image_filename" id="image_filename" />
			<input type="hidden" name="start_x" id="start_x" />
			<input type="hidden" name="start_y" id="start_y" />
			<input type="hidden" name="crop_width" id="crop_width" />
			<input type="hidden" name="crop_height" id="crop_height" />
			<input type="submit" name="upload_thumbnail" value="Save Thumbnail" id="save_thumb" />
			
			<iframe name="thumbnail_iframe" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
		</form>
	</div>
</div>

<div id="one" class="box"></div>
<div id="two" class="box"></div>
<div id="three" class="box"></div>
<div id="four" class="box"></div>
