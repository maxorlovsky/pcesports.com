if (window.jQuery) {

$('#pce-widget').ready(function() {
	var widget = $('#pce-widget');
    var iframe;
    var platform;
    var parentUrl;
    
    if (window.location.href.indexOf('test') != -1) {
        platform = 'http://test.';
    }
    else {
        platform = 'https://www.';
    }
    
    if (window.location.hostname.indexOf('skillz') != -1) {
        parentUrl = platform+'pcesports.com/widget/skillzhs';
    }
    else if (window.location.hostname.indexOf('unicon') != -1) {
        parentUrl = platform+'pcesports.com/widget/uniconhs';
    }
    else {
        console.log('Your website is not allowed to use Pentaclick eSports widget');
		return false;
    }

	if (widget.length <= 0) {
		console.log('Widget frame not found, shutting down');
		return false;
	}

	//widget-css
	widget.css({
		width: '100%',
		margin: '0 auto'
	});

	widget.html('<iframe />');
    
    iframe = widget.find('iframe');
	iframe.css({
		width: '100%',
		border: '0',
	});
	iframe.attr('src', parentUrl);
    
    if (window.addEventListener) {
        window.addEventListener("message", fetchFrameMessage, false);
    } else {
        window.attachEvent("onmessage", fetchFrameMessage);
    }
});

function fetchFrameMessage(event) {
    data = event.data.split('=');
    if (data[0] == 'height') {
        $('#pce-widget').find('iframe').height(data[1]);
    }
};

}
else {
    console.log('jQuery not found, shutting down');
}