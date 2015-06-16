if (window.jQuery) {

$('#pce-widget').ready(function() {
	var widget = $('#pce-widget');
    var iframe;
	var parentUrl = 'https://www.pcesports.com/widget/uniconhs?system='+window.location.hostname;

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
        iframe.height(data[1]);
    }
};

}
else {
    console.log('jQuery not found, shutting down');
}