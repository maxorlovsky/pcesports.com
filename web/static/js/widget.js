$('#pce-widget').ready(function() {
	var widget = $('#pce-widget');

	if (widget.length < 0) {
		console.log('Widget frame not found, shutting down');
		return false;
	}

	//widget-css
	widget.css({
		width: '830px',
		height: '100%',
		minHeight: '100%',
		margin: '0 auto'
	});

	widget.html('<iframe src="http://test.pcesports.com/widget/uniconhs" style="width: 830px; border: 0; height: 2155px; margin: 0 auto;" />');
});

$('#register-in-tournament').on('click', function() {
    PC.addParticipantWidget('HS');
});