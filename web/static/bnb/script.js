var BB = {
    //global insides
    site: g.site,
    formInProgress: 0, //used when required to check if form is still in progress
    prevHeight: 0,
    checkTimer: 15,
    chatStart: 0,
    
    //functions
    sound: function(element) {
        if (element.hasClass('on')) {
            element.removeClass('on').addClass('off');
            //element.attr('attr-msg', g.str.turn_on_sound);
            document.getElementById('ping').volume = 0;
        }
        else {
            element.removeClass('off').addClass('on');
            //element.attr('attr-msg', g.str.turn_off_sound);
            document.getElementById('ping').volume = 0.2;
        }
        element.trigger('mouseout');
    },
    stripTags: function(string) {
        return string.replace(/(<([^>]+)>)/ig,'');
    },
    fetchChat: function() {
        var query = {
            type: 'POST',
            data: {
                ajax: 'chatExternal',
                action: 'fetch',
                id: $('#participant').val(),
                link: $('#link').val()
            },
            timeout: 5000,
            success: function(answer) {
                answer = answer.split(';;');
                if (answer[0] == 1) {
                    checkTop = parseInt($('.chat-content').scrollTop()) + parseInt($('.chat-content').height()) + 10;
                    checkHeight = parseInt($('.chat-content').prop('scrollHeight'));
                    
                    currentContent = $('.chat-content').html().replace(/&lt;/g, '&#60;').replace(/&gt;/g, '&#62;').replace(/&amp;/g, '&');
                    if (escape(BB.stripTags(currentContent)) != escape(BB.stripTags(answer[1])) && BB.chatStart == 1) {
                        //$('#ping').play();
                        document.getElementById('ping').play();
                        
                        $('.chat-content').html(answer[1]);
                        
                        if (checkTop == checkHeight) {
                            $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
                        }
                    }

                    if (BB.chatStart != 1) {
                        BB.chatStart = 1;
                        $('.chat-content').html(answer[1]);
                        $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
                    }
                }
            }
        }
        BB.ajax(query);
    },
    checkIn: function(game) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        $(this).addClass('alpha');
        this.formInProgress = 1;
        
        command = 'checkInHsSkillz';
        
        var query = {
            type: 'POST',
            data: {
                ajax: command,
                id: $('#participant').val(),
                link: $('#link').val()
            },
            success: function(data) {
                BB.formInProgress = 0;
                $(this).removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.block.check-in').fadeOut();
                    location.reload();
                }
                else {
                    alert(answer[1]);
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    statusCheck: function() {
        $('#fightStatus').removeClass('online').removeClass('red');

        var query = {
            type: 'POST',
            data: {
                ajax: 'statusCheckExternal',
                id: $('#participant').val(),
                link: $('#link').val()
            },
            success: function(answer) {
                answer = answer.split(';');

                if (answer[0] == 2 || answer[0] == 3) {
                    if (answer[2] == 'online') {
                        $('#fightStatus').addClass('online');
                    }
                    if (answer[0] == 2) {
                        $('#fightStatus').addClass('red');
                    }
                    $('#fightStatus').html(answer[2]);
                }
                else {
                    $('#opponentStatus').removeClass('online offline');
                    $('#opponentName').removeClass('not-none');
                    
                    $('#opponentSec').html(BB.checkTimer);
                    
                    $('#opponentName').addClass((answer[1]!='none'?'not-none':''));
                    $('#opponentStatus').addClass(answer[2]);
                    $('#opponentName').html(answer[1]);
                    $('#opponentStatus').html(answer[2]);
                    
                    //Required for LoL
                    if ($('#tournamentCode').length > 0) {
                        $('#tournamentCode').val(answer[3]);//focus()
                    }
                    
                    //Required for HS
                    if ($('.opponent-info .player-heroes').length > 0) {
                        var html = $('#hsicons-holder').html();
                        var returnHtml = '';
                        var buildUp = '';

                        if (answer[3] != 'none') {
                            var heroes = $.parseJSON(answer[3]);
                            $.each(heroes, function(k,v) {
                                returnHtml = returnHtml+html.replace('%hero%', v).replace('%heroName%', v.charAt(0).toUpperCase() + v.slice(1));
                            });
                        }
                        
                        $('.opponent-info .player-heroes').html(returnHtml);
                    }
                }

            }
        };
        this.ajax(query);
    },
    statusCheckTimer: function() {
        var sec = parseInt($('#opponentSec').html()) - 1;
        if (sec != 0) {
            $('#opponentSec').html(sec);
        }
    },
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
                ajax: 'editInSkillz',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-in-tournament').removeClass('alpha');
                BB.formInProgress = 0;
                
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
                BB.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@skillz.lv');
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
                ajax: 'registerInSkillz',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#register-in-tournament').removeClass('alpha');
                BB.formInProgress = 0;
                
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
                BB.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@skillz.lv');
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

$('.chat-input').on('click', function() {
   $('#chat-input').focus();
});

$('#chatSound').on('click', function() {
    BB.sound($(this));
});

$('#chat-input').on('keyup', function(e) {
    if (!e) {
        e = window.event;
    }

    if (e.keyCode == 13 && $.trim($(this).val())) {
        var text = $(this).val();
        $(this).val('');
        var query = {
            type: 'POST',
            data: {
                ajax: 'chatExternal',
                action: 'send',
                text: text,
                id: $('#participant').val(),
                link: $('#link').val()
            },
            success: function(answer) {
                answer = answer.split(';;');
                if (answer[0] != 0) {
                    checkTop = parseInt($('.chat-content').prop('scrollTop')) + parseInt($('.chat-content').height()) + 10;
                    checkHeight = parseInt($('.chat-content').prop('scrollHeight'));
                    
                    $('.chat-content').html(answer[1]);
                    
                    if (checkTop == checkHeight) {
                        $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
                    }
                }
            }
        }
        BB.ajax(query);
    }
});

$('#register-in-tournament').on('click', function() {
    BB.addParticipant();
});
$('#edit-in-tournament').on('click', function() {
    BB.editParticipant();
});
$('#check-in-tournament').on('click', function() {
    BB.checkIn();
});
$('.confirm').on('click', function() {
    if(confirm($(this).attr('attr-msg'))) {
        location.href = $(this).attr('href');
    }
    return false;
});

setInterval(function(){BB.sendFrameMessage($('body').height());}, 200);

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

if (participant == 1) {
    document.getElementById('ping').volume = 0.2;
    BB.fetchChat();
    BB.statusCheck();
    setInterval(function () { BB.fetchChat(); }, 5000);
    setInterval(function () { BB.statusCheck(); }, 15000);
    setInterval(function () { BB.statusCheckTimer(); }, 1000);
}