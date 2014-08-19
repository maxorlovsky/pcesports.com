//Visual things

$('#tournamentCode').on('click', function() {
    $(this).select();
});

$('.chat-input').on('click', function() {
   $('#chat-input').focus();
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
                answer = answer.split(';');
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

// --------------------------------------------------------------------------------------------------------------------


//Main things
var profiler = {
    checkTimer: 15,
    fetchChat: function() {
        var query = {
            type: 'POST',
            data: {
                ajax: 'chat',
                action: 'fetch',
            },
            success: function(answer) {
                answer = answer.split(';');
                if (answer[0] == 1) {
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
                
                if ($('#tournamentCode').length > 0) {
                    $('#tournamentCode').focus().val(answer[3]);
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