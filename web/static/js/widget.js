if (window.jQuery) {

$('#pce-widget').ready(function() {
	var widget = $('#pce-widget');
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
	widget.find('iframe').css({
		width: '830px',
		border: '0',
		height: '100%',
		minHeight: '100%'
	});

	widget.find('iframe').attr('src', parentUrl);
});

}
else {
    console.log('jQuery not found, shutting down');
}