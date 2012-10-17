$(document).ready(function() {

	$('body').on('click', '.analytics', function(){
		analytics.push($(this));
	});
	
	$('#content').on('click', '#submit_email', function(){
		ajax('testAjaxFunctions.php', { action : 'update_user', update : { email : $('#email').val() } }, function(response){
			//loadTab('homepage');
			window.location = '#/view2';
		});
	});
	
	$('#boxItem .close').click(function(){
		closeBoxItem();
	});

});



function loadTab(page, responseCallback){
	closeBoxItem(); //Make sure the loading filter is gone

	analytics.sendToGoogle(page, 'load');
	
	$.get('templates/tmpl_' + page + '.php', function(template_html){ //path.substring(2) gets rid of #/ in the beginning of the path
		$('#content').html(template_html);
		$('html,body').animate({ scrollTop : 0}, 500);

		if(responseCallback) //If a callback function is set
			responseCallback(true); //To let the function who called this template load know that it is loaded and script that needs to be executed with this new template can be excecuted
	}, 'html');
}



function boxItem(content){
	$('#boxItem .content').hide().removeClass('on');
	$('#loadingFilter, #boxItem, #boxItem .' + content).fadeIn();
	$('#boxItem .' + content).addClass('on');
}



function closeBoxItem(){
	$('#boxItem, #loadingFilter').fadeOut();
}



function ajax(url, data, responseCallback){
	$.ajax({
		type : 'post',
		url : url,
		data : data,
		success : function(response){
			responseCallback(response);
		}
	});
}



function openWindow(url, width, height) {
    if(!width) width = 700;
    if(!height) height = 400;	
    
    var left = Math.floor((screen.availWidth - width) / 2);
    var top = Math.floor((screen.availHeight - height) / 2);
    var windowFeatures = "width=" + width + ",height=" + height + ",menubar=yes,toolbar=yes,scrollbars=yes,resizable=yes," + "left=" + left + ",top=" + top + "screenX=" + left + ",screenY=" + top;
    return window.open(url, "_blank", windowFeatures);
}



/**
 * Set up with analytics
 * -------------------------
 * Basic setup - Anything on the page with a class of analytics will trigger a Google Analytics push
 * The item clicked MINIMALLY needs an attribute of AnalyticsLocation="", which would be like 'Header', 'Footer', 'StylePage', etc
 * 
 * If it is an image that is clicked, you need an additional attribute of AnalyticsLabel="", which would be like 'CopyPermalink', 'SubmitForm', 'Signin', etc
 * 
 * You can also manually send a Google Analytics push by calling analytics.sendToGoogle(location, label);  Sometimes this is needed, such as after a Facebook share or Tweet
 */
var analytics = {
	campaign : 'SamsungAngryBirds',
	getLabel : function(clicked){
					//No Label attribute defined, use the text of the link
					label = $(clicked).text();
					label = label.split(' '); //Split up the words
					for(i in label){
						label[i] = label[i][0].toUpperCase() + label[i].slice(1); //Proper caps each word in the label
					}
					label = label.join(''); //Combine the label back together, without spaces
					
					return label;
				},
	sendToGoogle : function(location, label){
						_gaq.push(['_trackEvent', this.campaign, location, label]);
					},
	push : function(clicked){
				var location = $(clicked).attr('AnalyticLocation');
				var label = $(clicked).attr('AnalyticLabel');
		
				if(label == undefined)
					label = this.getLabel(clicked);
					
				this.sendToGoogle(location, label);
				
				return true;
			}
};



//Placeholders for all browsers
$(function() {
    if(!$.support.placeholder){ 
        $(':text').focus(function (){
            if ($(this).attr('placeholder') != '' && $(this).val() == $(this).attr('placeholder')) {
                $(this).val('').removeClass('hasPlaceholder');
            }
        }).blur(function () {
            if ($(this).attr('placeholder') != '' && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder'))) {
                $(this).val($(this).attr('placeholder')).addClass('hasPlaceholder');
            }
        });
        
        $(':text').blur();
        
        $('form').submit(function () {
            $(this).find('.hasPlaceholder').each(function() { $(this).val(''); });
        });
    }
});