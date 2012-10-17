// SAMPLE ONCLICKS
$('.enter-button p a').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click to Enter','Enter Sweepstakes to Win']);   
});
    
$('.closepopup').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-close warning','Close']);
});
    
$('.termslink').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-Terms and Conditions','Terms and Conditions']);
});

$('.sharetwitter').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-Share','Twitter']);
});
    
$('.shareemail').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-Share','E-mail']);
});
    
$('.goback').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-Learn More','Winner Circle']);
});
    
//take a closer look onclicks
$('.closer-laramie').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-take a closer look','The Luxury Pickup of Texas - Ram Laramie']);
});
    
$('.closer-wagon').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-take a closer look','The Heavy Duty Pickup Truck of Texas - Power Wagon']);
});
    
$('.closer-2500').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-take a closer look','The Heavy Duty Pickup Truck of Texas - 2500']);
});
    
$('.closer-outdoorsman').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-take a closer look','The Full Size Pickup Truck of Texas - Outdorrsman']);
});
    
$('.closer-1500').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-take a closer look','The Full Size Pickup Truck of Texas - 1500']);
});
    
$('.closer-lone').live('click', function() {
    _gaq.push(['_trackEvent','RAM Truck of Texas','Click-take a closer look','The Full Size Pickup Truck of Texas - Lone Star']);
});