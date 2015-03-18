//Visual things

$('#tournamentCode').on('click', function() {
    $(this).select();
});

$('.chat-input').on('click', function() {
   $('#chat-input').focus();
});

$('#chatSound').on('click', function() {
    profiler.sound($(this));
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
                ajax: 'chat',
                action: 'send',
                text: text
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
        PC.ajax(query);
    }
});

var uploadInProgress = 0;
new AjaxUpload(
    $('#uploadScreen'), {
    	action: g.site+'?ajax=uploadScreenshot',
    	//Name of the file input box  
    	name: 'upload',
    	onSubmit: function(file, ext) {
            if (uploadInProgress == 1) {
                alert('Upload in progress, wait!');
            }
    		if (! (ext && /^(jpg|png|jpeg)$/.test(ext))) {  
    			alert('Only JPG, PNG files are allowed');  
    			return false; 
    		}
            
            uploadInProgress = 1;
            $('#uploadScreen').addClass('alpha');
    	},  
    	onComplete: function(file, data) {
            data = data.split(';');
            uploadInProgress = 0;
            if (data[0] != 1) {
                alert(data[1]);
            }
            $('#uploadScreen').removeClass('alpha');
    	}  
    }
);
$('input[name="upload"]').addClass('hint').attr('attr-msg', $('#uploadScreen').attr('attr-msg'));

document.getElementById('ping').volume = 0.2;

// --------------------------------------------------------------------------------------------------------------------


//Main things
var profiler = {
    checkTimer: 15,
    chatStart: 0,
    
    sound: function(element) {
        if (element.hasClass('on')) {
            element.removeClass('on').addClass('off');
            element.attr('attr-msg', g.str.turn_on_sound);
            document.getElementById('ping').volume = 0;
        }
        else {
            element.removeClass('off').addClass('on');
            element.attr('attr-msg', g.str.turn_off_sound);
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
                ajax: 'chat',
                action: 'fetch',
            },
            timeout: 5000,
            success: function(answer) {
                answer = answer.split(';;');
                if (answer[0] == 1) {
                    checkTop = parseInt($('.chat-content').scrollTop()) + parseInt($('.chat-content').height()) + 10;
                    checkHeight = parseInt($('.chat-content').prop('scrollHeight'));
                    
                    currentContent = $('.chat-content').html().replace(/&lt;/g, '&#60;').replace(/&gt;/g, '&#62;');
                    if (escape(profiler.stripTags(currentContent)) != escape(profiler.stripTags(answer[1])) && profiler.chatStart == 1) {
                        //$('#ping').play();
                        document.getElementById('ping').play();
                        
                        $('.chat-content').html(answer[1]);
                        
                        if (checkTop == checkHeight) {
                            $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
                        }
                    }

                    if (profiler.chatStart != 1) {
                        profiler.chatStart = 1;
                        $('.chat-content').html(answer[1]);
                        $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
                    }
                }
            }
        }
        PC.ajax(query);
    },
    statusCheck: function() {
        var query = {
            type: 'POST',
            data: {
                ajax: 'statusCheck',
            },
            success: function(answer) {
                answer = answer.split(';');
                $('#opponentStatus').removeClass('online offline');
                $('#opponentName').removeClass('not-none');
                
                $('#opponentSec').html(profiler.checkTimer);
                
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
                    var heroes = $.parseJSON(answer[3]);
                    var returnHtml = '';
                    var buildUp = '';
                    $.each(heroes, function(k,v) {
                        returnHtml = returnHtml+html.replace('%hero%', v).replace('%heroName%', v.charAt(0).toUpperCase() + v.slice(1));
                    });
                    
                    $('.opponent-info .player-heroes').html(returnHtml);
                }
            }
        }
        PC.ajax(query);
    },
    statusCheckTimer: function() {
        var sec = parseInt($('#opponentSec').html()) - 1;
        if (sec != 0) {
            $('#opponentSec').html(sec);
        }
    }
};

//Start
$(document).ready(function() {
    profiler.fetchChat();
    profiler.statusCheck();
    setInterval(function () { profiler.fetchChat(); }, 5000);
    setInterval(function () { profiler.statusCheckTimer(); }, 1000);
    setInterval(function () { profiler.statusCheck(); }, profiler.checkTimer*1000);
});