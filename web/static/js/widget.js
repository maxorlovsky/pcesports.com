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
    
    iframe.ready(function() {
        console.log(iframe.contentWindow.document.body.scrollHeight);
    });
    iframe.on('resize', function() {
        console.log(iframe.contentWindow.document.body.scrollHeight);
    });
});

}
else {
    console.log('jQuery not found, shutting down');
}