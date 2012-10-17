(function($) {  
//Pull a profanityList before calling this and pass it in, such as:
//var profanity = [];
//$.get('dirtywords.txt', function( data ) {
//profanity= data.split(",");						
//});
//Pass in an error div, such as '.error'
//Pass in a maxTextLength, such as '75'
//Example
// -- Anything marked with a class validateMe will be validated.
// -- If a textbox has a class validateMe AND ignoreLinks, it will not be validated for links in the text
//$('#submit').click(function(){
//      $('.validateMe').validate({
//	      emailField : '.email',
//	      errorDiv : '.error',
//	      maxTextLength : 75,
//	      profanityList : profanity
//      }, function( success ){
//	      if(success){
//		      console.log('this worked!');
//	      }else{
//		      console.log('this didn\'t work!');
//	      }
//      });
//});



//Find a way to get the script to wait for this list before continuing (can use $.ajax), BUT if the user sends list, don't wait
//Putting the profanity list here allows us to load the words when the javascript file is called - when the page is loading.  It would usually be long enough for the words to load by the time the page loads to when the user submits their text.  This isn't called with every .validate() function call, just when the page is loaded.
var profanity = [];
// $.get('dirtywords.txt', function( data ) {
	// profanity = data.split(",");						
// });



  $.fn.validate = function( options, validationCallback ) {
      //Pass arguments as an object.  Set defaults and the defaults will be overwritten by the user input
      
      //If the user passes in their own profanity list, turn it into an array
      if(options.profanityList){
	  options.profanityList = options.profanityList.split(',');
	  if(!options.clearProfanityList || options.clearProfanityList === undefined){
	    //If the user hasn't set the clearProfanityList parameter or it is set to false, add the default profanity words we loaded from dirtywords.txt
	    options.profanityList = options.profanityList.concat(profanity);
	  }
      }
      
    //Set defaults
    var defaults = {
      //User validation defaults
      clearProfanityList : false, //If the user passes in their profanity list, amend it to the dirtywords list by default.  If this is true, only use the words the user has passed in
      emailField : '#email',  
      errorDiv : '#error',
      maxTextLength : 100,
      profanityList : profanity,
      radioButtonGroup : '',
      zipField : '#zip',
      //Copy defaults
      blankFieldCopy : '* One of your fields is empty. Don\'t forget to fill it in!',
      invalidEmailCopy : '* Please enter a valid email address',
      invalidZipCopy : '* Please enter a valid zip code',
      profanityCopy : '* Remember to keep it clean! No profanity, please.',
      tooLongCopy : '', //Have to set in the call to displayError if the text is too long, that's the only way I could figure out how to access maxTextLength (this.maxTextLength does not work here - it could be because of $.extend)
      URLCopy: '* Please remove the links in the text',
    };

     //$.extend merges the defaults with and the options the user has entered
     //If no options are set, use the defaults
    (options) ? settings = $.extend( defaults, options ) : settings = defaults;
    
    //Refresh errors
    $(this).removeClass('errorHighlight');
    $(settings.errorDiv).text('');

    this.each(function() {
      //The variable isValid should be refreshed for each call, so it is put here
      //Each item in the .validate() call will go through here...at the end of all the calls, the callback will send whatever the value is...if everything is good, the code that called this function initially will continue processing, else..it will show an error message
      isValid = false;
				 
      //Compare the profanityList against the user's input, return false if there is bad language (hard stop), true if it's clean
      var userInput = $(this)[0];
      
      //Split user's text input into an array
      var userInputArray = userInput.value.split(" ");
  
      if(userInput.value.length > settings.maxTextLength){
	//If the user has input a message that is too long
	displayError(userInput, '* Please reduce to ' + settings.maxTextLength + ' characters or less');
	return false;
      }
      
      //----HTML object specific
      
      //Checkbox
      if(userInput.type === "checkbox" && !userInput.checked){
	      displayError(userInput, settings.blankFieldCopy);
	      return false;
      }
      
      //Dropdown
      //HTML page must have a blank entry for the first option...ex) states, <option></option><option>AL</option>...
      if(userInput.type === "select-one" && userInput.value === ""){
	      displayError(userInput, settings.blankFieldCopy);
	      return false;
      }
      
      //Radio buttons
      if(userInput.type === "radio"){
	if($('input[name="' + settings.radioButtonGroup + '"]:checked').length === 0){
	  displayError(userInput, settings.blankFieldCopy);
	  isValid = false;
	  return false;
	}
      }
      
      //Textbox and TextArea
      //Check if user input is empty - there will always be at least one entry in the array and it will be blank if the user didn't type anything
      if((userInputArray[0] == "" && userInputArray.length === 1) || userInput.value == $(this).attr('placeholder')){
	      displayError(userInput, settings.blankFieldCopy);
	      return false;
      }
      
      //----END HTML object specific
      
   
   
      //----Special fields
      
      //Email
      if(userInput.id === settings.emailField.split('#')[1] || $(userInput).hasClass(settings.emailField.split('.')[1])){
	if(!isValidEmailAddress($(settings.emailField).val())){
	  displayError(userInput, settings.invalidEmailCopy);
	  return false;
	}
      }
      
      //Zip
      if(userInput.id === settings.zipField.split('#')[1] || $(userInput).hasClass(settings.zipField.split('.')[1])){
	if(!validateZipCode($(settings.zipField).val())){
	    displayError(userInput, settings.invalidZipCopy);
	    return false;
	}
      }
      
      //----END special fields
      
      
      
      //If not empty, go through each word and look for 1) Profanity, 2) Website links
      for(var i=0; i < userInputArray.length; i++){
	var userWord = userInputArray[i].toLowerCase();
	//Remove punctuation
	userWord = userWord.replace(/[\!\"\'\:\;\.\,\-\?\(\)\<\>\{\}\|\\\/\~\#\$\%\^\*\_]/g, '');
	if($.inArray(userWord, settings.profanityList) != -1){
		//If there is bad language, show error message and asterisk
		displayError(userInput, settings.profanityCopy);
		//Return false if there is bad language (hard stop)
		return false;
	}
	
	if(!$(userInput).hasClass('ignoreLinks')){
	  //Check for URLs in text
	  if(userWord.indexOf("http") != -1 || userWord.indexOf("://") != -1 || userWord.indexOf("www") != -1 || userWord.indexOf(".com") != -1 || userWord.indexOf(".net") != -1){
	      displayError(userInput, settings.URLCopy);
	      return false;
	  }
	}
      }//End for
      
      //Everything is valid!
      isValid = true;
    });
    
    //Pass the value (true/false) to the javascript callback function for the page that called the validation script to read  
    validationCallback(isValid);
	
    //Return the object for chaining in jQuery
    return this;
  };

})(jQuery);



function displayError(userInput, copy){
  $(userInput).addClass('errorHighlight');
  $(settings.errorDiv).text(copy);
}



function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}



function validateZipCode(elementValue){
    var zipCodePattern = /^\d{5}$|^\d{5}-\d{4}$/;
     return zipCodePattern.test(elementValue);
}
