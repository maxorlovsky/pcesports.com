$('.bx-wrapper').bxSlider({
	auto: true,
	autoDelay: 8000,
	mode: 'fade',
	speed: 2000,
	pause: 8000
});

var likeInProgress = 0;
$('.like').on('click', this, function() {
	if (likeInProgress == 1) {
		return false;
	}
	
	var likeButton = this;
	var newsId = parseInt($(this).attr('attr-news-id'));
	likeInProgress = 1;
	
	var query = {
        type: 'POST',
        timeout: 10000,
        data: {
        	ajax: 'newsVote',
        	id: parseInt($(this).attr('attr-news-id'))
		},
    	success: function(data) {
    		likeInProgress = 0;
    		answer = data.split(';');
    		
    		if (answer[0] == 1) {
    			if (answer[1] == '+ 1') {
    				$(likeButton).find('.like-icon').addClass('active');
    				$('#news-like-'+newsId).html(parseInt($('#news-like-'+newsId).html()) + 1);
    			}
    			else {
        			$(likeButton).find('.like-icon').removeClass('active');
        			$('#news-like-'+newsId).html(parseInt($('#news-like-'+newsId).html()) - 1);
        		}
    		}
    		
    		return false;
    	}
    };
	ajax(query);
});

function updateTimers() {
	$.each($('.timer'), function(k, v) {
		delta = parseInt($(this).attr('attr-time'));
		originalTimer = delta;
	
		if (delta < 1 || !delta) {
			$(this).html('Live');
			return;
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

$(document).on('mousemove', '.hint', function(event) {
	$('#hint-helper').offset({ top: event.pageY-30, left: event.pageX+10 });
	
	if ($('#hint-helper').is(':visible')) {
		return false;
	}
	
	msg = $(this).attr("attr-msg");
	if ($('#hint-helper p').html != msg) {
		$('#hint-helper p').html(msg);
	}
	if ($('#hint-helper').is(':hidden')) {
		$('#hint-helper').show();
	}
}).on('mouseout', '.hint', function(){
	$('#hint-helper').offset({ top: 0, left: 0 });
	$('#hint-helper').hide();
});

function ajax(object) {
    if (!object.url) {
        object.url = site;
    }
    if (!object.async) {
        object.async = true;
    }
    if (!object.dataType) {
        object.dataType = '';
    }
    if (!object.success) {
        object.success = function(data) {
            alert(data);
        };
    }
    if (!object.data) {
        object.data = {};
    }
    if (!object.type) {
        object.type = 'GET';
    }
    if (!object.xhrFields) {
        object.xhrFields = { withCredentials: true };
    }
    if (!object.crossDomain) {
        object.crossDomain = true;
    }
    if (!object.cache) {
        object.cache = true;
    }
    if (!object.timeout) {
        object.timeout = 60000;
    }
    if (!object.error) {
        object.error = function(xhr, ajaxOptions, thrownError) {
            console.log(object.url);
            console.log(xhr);
            console.log(ajaxOptions);
            console.log(thrownError);
            //ajax(object);
        };
    }
    
    return $.ajax({
    	url: object.url,
        type: object.type,
    	async: object.async,
        data: object.data,
    	dataType: object.dataType,
        xhrFields: object.xhrFields,
        crossDomain: object.crossDomain,
        cache: object.cache,
        timeout: object.timeout,
    	success: object.success,
        error: object.error
    });
}