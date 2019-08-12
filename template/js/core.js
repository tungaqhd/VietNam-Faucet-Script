function activeclaim() {
	$('#button-claim').attr('disabled', false);
}
$(document).mousemove(function() {
	$('#button-claim').attr('name', 'submit');
	$(document).off('mousemove');
});
$("#CountDownTimer").TimeCircles({ time: { Days: { show: false }, Hours: { show: false } }});
$("#CountDownTimer").TimeCircles({count_past_zero: false}); 
$("#CountDownTimer").TimeCircles({fg_width: 0.05}); 
$("#CountDownTimer").TimeCircles({bg_width: 0.5}); 
$("#CountDownTimer").TimeCircles(); 
var time_left = $("#CountDownTimer").TimeCircles().getTime();  
setTimeout(function(){
	window.location.href = url;
}, time_left*1000);
$('#button-claim').attr('disabled', true);
function captchachange() {
	var captcha = $('#captcha-select').val();
	if (captcha == 'recaptcha') {
		$('#solvemedia').attr('style', 'display: none;');
		$('#recaptcha').attr('style', 'display: true;');
	} else {
		$('#button-claim').attr('disabled', false);
		$('#recaptcha').attr('style', 'display: none;');
		$('#solvemedia').attr('style', 'display: true;');
	}
}