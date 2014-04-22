$('.bx-wrapper').bxSlider({
	auto: true,
	autoDelay: 8000,
	mode: 'fade',
	speed: 2000,
	pause: 8000
});

function updateTimers() {
	$.each($('.timer'), function(k, v) {
		delta = parseInt($(this).attr('attr-time'));
		originalTimer = delta;
	
		if (delta < 1 || !delta) {
			$(this).html('Live');
			return false;
		}
		
		var days = Math.floor(delta / 86400);
		delta -= days * 86400;
		var hours = Math.floor(delta / 3600) % 24;
		delta -= hours * 3600;
		var minutes = Math.floor(delta / 60) % 60;
		delta -= minutes * 60;
		var seconds = delta % 60;
		
		returnString = '';
		if (days > 0) {
			returnString += days+' days<br />';
		}
		if (hours   < 10) {hours   = '0'+hours;}
	    if (minutes < 10) {minutes = '0'+minutes;}
	    if (seconds < 10) {seconds = '0'+seconds;}
		
		returnString += hours+':'+minutes+':'+seconds;
		
		originalTimer--;
		
		$(this).attr('attr-time', originalTimer);
		$(this).html(returnString);
	});
}

setInterval(updateTimers, 1000);