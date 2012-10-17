/*
* onClickify - jQuery plugin - Chase Farmer - 11/1/11
*
* This function will take a JSON feed and parse that shit out into some ballin' onclicks.
* Ya mean!
*
* Handy Resource: http://www.cparker15.com/utilities/csv-to-json/
*
*	{
*		"selector": "mainlogo",
*		"name": "SEMAWorthyFacebookApplicaton",
*		"label": "Click - Dodge Logo",
*		"action": "Dodge Logo"
*	}
* 
*   Example Usage:
*
*   $.getJSON('modules/onclicks/clicks.json', function(data) {
*      $('body').onclickify(data);
*   });
* 
*
* @return string of JS
* @param JSON string of data formatted as above
* 
*/

(function( $ ){

$.fn.onclickify = function(clicks) {

   return this.each(function() {

     $.each(clicks, function(key, value){

      $(value.selector).live('click', function(){
        console.log('clicked');
         _gaq.push(['_trackEvent', value.name, value.label, value.action ]);
       });

     });
   });

  };
})( jQuery );