var UC = {
    //global insides
    site: g.site,
    formInProgress: 0, //used when required to check if form is still in progress
    prevHeight: 0,
    
    //functions
    sendFrameMessage: function(height) {
        if ($('body').height() != this.prevHeight) {
            this.prevHeight = height;
            window.parent.postMessage('height='+height, "*");
        }
    },
    editParticipant: function(game) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('.team-edit-completed').hide();
        $('#da-form .form-item').removeClass('error success');
        $('#da-form .form-item .message').hide();
        $('#edit-in-tournament').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'editInUnicon',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-in-tournament').removeClass('alpha');
                UC.formInProgress = 0;
                
                $.each(answer.err, function(k, v) {
                    answ = v.split(';');
                    $('[data-label="'+k+'"] .message').html(answ[1]);
                    $('[data-label="'+k+'"] .message').show();
                    if (answ[0] == 1) {
                        $('[data-label="'+k+'"]').addClass('success');
                    }
                    else {
                        $('[data-label="'+k+'"]').addClass('error');
                    }
                });
                
                if (answer.ok == 1) {
                    $('.team-edit-completed').slideDown(1000);
                }
            },
            error: function() {
                $('#edit-in-tournament').removeClass('alpha');
                UC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@unicon.lv');
            }
        };
        this.ajax(query);
    },
    addParticipant: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .form-item').removeClass('error success');
        $('#da-form .form-item .message').hide();
        $('#register-in-tournament').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'registerInUnicon',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#register-in-tournament').removeClass('alpha');
                UC.formInProgress = 0;
                
                if (answer.ok == 1) {
                    $('#register-url a').trigger('click');
                    $('#register-in-tournament').fadeOut();
                    $('#da-form').slideUp(1000, function() {
                        $('.reg-completed').slideDown(1000);
                    });
                }
                else {
                    $.each(answer.err, function(k, v) {
                        answ = v.split(';');
                        $('[data-label="'+k+'"] .message').html(answ[1]);
                        $('[data-label="'+k+'"] .message').show();
                        if (answ[0] == 1) {
                            $('[data-label="'+k+'"]').addClass('success');
                        }
                        else {
                            $('[data-label="'+k+'"]').addClass('error');
                        }
                    });
                }
            },
            error: function() {
                $('#register-in-tournament').removeClass('alpha');
                UC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@unicon.lv');
            }
        };
        this.ajax(query);
    },
    ajax: function(object) {
        if (!object.url) {
            object.url = this.site;
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
};

$('#register-in-tournament').on('click', function() {
    UC.addParticipant();
});
$('#edit-in-tournament').on('click', function() {
    UC.editParticipant();
});
$('.confirm').on('click', function() {
    if(confirm($(this).attr('attr-msg'))) {
        location.href = $(this).attr('href');
    }
    return false;
});

setInterval(function(){UC.sendFrameMessage($('body').height());}, 200);

if ($('.participants.isotope-participants .block').length > 0) {
    $('.participants.isotope-participants').isotope({
        itemSelector : '.block',
        layoutMode : 'fitRows'
    });
}

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
		$('#hint-helper').css('display', 'inline-block');
	}
}).on('mouseout', '.hint', function(){
	$('#hint-helper').css('display', 'none');
});