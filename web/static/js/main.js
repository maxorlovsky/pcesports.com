$('.bx-wrapper').bxSlider({
	auto: true,
	autoDelay: 8000,
	mode: 'fade',
	speed: 2000,
	pause: 8000
});

var startTime = $('.timer').attr('attr-start');
var serverTime = $('.timer').attr('attr-time');
var nextCupTime = Math.abs(startTime - serverTime);

function getNextCupTime() {
	var delta = nextCupTime;
	
	if (delta < 1) {
		$('.timer').html('Live');
	}
	var days = Math.floor(delta / 86400);
	delta -= days * 86400;

	// calculate (and subtract) whole hours
	var hours = Math.floor(delta / 3600) % 24;
	delta -= hours * 3600;

	// calculate (and subtract) whole minutes
	var minutes = Math.floor(delta / 60) % 60;
	delta -= minutes * 60;

	// what's left is seconds
	var seconds = delta % 60;  // in theory the modulus is not required
	
	returnString = '';
	
	if (days > 0) {
		returnString += days+' days<br />';
	}
	
	if (hours   < 10) {hours   = '0'+hours;}
    if (minutes < 10) {minutes = '0'+minutes;}
    if (seconds < 10) {seconds = '0'+seconds;}
	
	returnString += hours+':'+minutes+':'+seconds;
	
	nextCupTime--;

	$('.timer').html(returnString);
}
setInterval(getNextCupTime, 1000);