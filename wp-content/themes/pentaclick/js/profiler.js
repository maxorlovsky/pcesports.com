//Globals
var sixtySecondsQuery;


// --------------------------------------------------------------------------------------------------------------------


//Visual things

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
            dataType: 'json',
            data: {
                control: 'chat',
                action: 'send',
                post: 'tId='+tId+'&code='+code+'&text='+text
            },
            success: function(answer) {
                $('.chat-content').html(answer.html);
                $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
            }
        }
        ajax(query);
    }
});

$('#leave').on('click', function() {
    if (!confirm(_('sure_to_quit'))) {
        return false;
    }
    
    var query = {
        type: 'POST',
        dataType: 'json',
        data: {
            control: 'leave',
            post: 'tId='+tId+'&code='+code
        },
        success: function(answer) {
            if (answer.ok == 1) {
                alert(answer.message);
                window.location = site;
            }
            else {
                alert('Error');
                console.log(answer);
            }
        }
    }
    ajax(query);
});

new AjaxUpload(
    $('#uploadScreen'), {
    	action: '/wp-content/themes/pentaclick/ajax.php?lang='+lang+'&control=uploadScreenshot&tId='+tId+'&code='+code,
    	//Name of the file input box  
    	name: 'upload',
    	onSubmit: function(file, ext){  
    		if (! (ext && /^(jpg|png|jpeg)$/.test(ext))) {  
    			alert('Only JPG, PNG files are allowed');  
    			return false; 
    		}
    		//$('#uploadStatus').html('Upload...');  
    	},  
    	onComplete: function(file, data) {
            data = JSON.parse(data);
            if (data.ok == 0) {
                alert(data.message);
            }
    	}  
    }
);

// --------------------------------------------------------------------------------------------------------------------


//Main things
profiler = {
    fetchChat: function() {
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                control: 'chat',
                action: 'fetch',
                post: 'tId='+tId+'&code='+code
            },
            success: function(answer) {
                if (answer.ok != 2) {
                    checkTop = parseInt($('.chat-content').prop('scrollTop')) + parseInt($('.chat-content').height()) + 10;
                    checkHeight = parseInt($('.chat-content').prop('scrollHeight'));
                    
                    $('.chat-content').html(answer.html);
                    
                    if (checkTop == checkHeight) {
                        $('.chat-content').scrollTop($('.chat-content').prop('scrollHeight'));
                    }
                }
            }
        }
        ajax(query);
    },
    statusCheck: function() {
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                control: 'statusCheck',
                post: 'tId='+tId+'&code='+code
            },
            success: function(answer) {
                $('#opponentStatus').removeClass('online offline');
                $('#opponentName').removeClass('not-none');
                
                $('#opponentSec').html('30');
                
                $('#opponentName').addClass((answer.opponentName!='none'?'not-none':''));
                $('#opponentStatus').addClass((answer.opponentStatus==true?'online':'offline'));
                $('#opponentName').html(answer.opponentName);
                $('#opponentStatus').html((answer.opponentStatus==true?'online':'offline'));
            }
        }
        ajax(query);
    },
    statusCheckTimer: function() {
        var sec = parseInt($('#opponentSec').html()) - 1;
        if (sec != 0) {
            $('#opponentSec').html(sec);
        }
    }
};

//Start
profiler.fetchChat();
profiler.statusCheck();
setInterval(function () { profiler.statusCheckTimer(); }, 1000);
setInterval(function () { profiler.fetchChat(); }, 5000);
setInterval(function () { profiler.statusCheck(); }, 30000);