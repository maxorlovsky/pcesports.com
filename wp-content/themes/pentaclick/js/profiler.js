//Globals
var sixtySecondsQuery;
var formInProgress = 0;


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

$('#update-team').on('click', function() {
    if (formInProgress == 1) {
        return false;
    }
    
    var errRegistered = 0;
    formInProgress = 1;
    $('#register-content-profile .form .message').hide();
    $('#register-content-profile .form .message').removeClass('error success');
    $(this).addClass('alpha');
    
    var query = {
        type: 'POST',
        dataType: 'json',
        data: {
            control: 'updateTeam',
            u: 'tId='+tId+'&code='+code,
            post: $('#register-content-profile .form').serialize()
        },
        success: function(answer) {
            $('#update-team').removeClass('alpha');
            formInProgress = 0;
            
            $.each(answer.message, function(k, v) {
                answ = v.split(';');
                $('#'+k+'-msg').html(answ[1]);
                $('#'+k+'-msg').show();
                if (answ[0] == 1) {
                    $('#'+k+'-msg').addClass('success');
                }
                else {
                    $('#'+k+'-msg').addClass('error');
                }
            });
        },
        error: function() {
            $('#update-team').removeClass('alpha');
            formInProgress = 0;
            
            alert('Something\'s got wrong... Contact admin at pentaclickesports@gmail.com');
        }
    }
    ajax(query);
});

var uploadInProgress = 0;
new AjaxUpload(
    $('#uploadScreen'), {
    	action: '/wp-content/themes/pentaclick/ajax.php?lang='+lang+'&control=uploadScreenshot&tId='+tId+'&code='+code,
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
            data = JSON.parse(data);
            uploadInProgress = 0;
            if (data.ok == 0) {
                alert(data.message);
            }
            $('#uploadScreen').removeClass('alpha');
    	}  
    }
);

$('.menu.links a').on('click', function() {
    if ($(this).hasClass('disabled') || $(this).hasClass('active')) {
        return false;
    }
    
    $('.menu.links a').removeClass('active');
    
    var url = $(this).attr('id').split('-');
    $('.menu.inside-content:visible').not('#'+url[0]).slideUp(500, function() {
        $('#'+url[0]).hide().slideDown(500);
    });
    $(this).addClass('active');
});

$('#receive-tournament-hour-notif').on('click', function() {
    var checked = 0;
    if ($(this).is(':checked') === true) {
        checked = 1;
    }
    
    var query = {
        type: 'POST',
        dataType: 'json',
        data: {
            control: 'notifications',
            action: 'hour',
            post: 'tId='+tId+'&code='+code+'&checked='+checked
        },
        success: function(answer) {},
        error: function() {
            alert('Error');
        }
    }
    ajax(query);
});

$('#receive-tournament-day-notif').on('click', function() {
    var checked = 0;
    if ($(this).is(':checked') === true) {
        checked = 1;
    }
    
    var query = {
        type: 'POST',
        dataType: 'json',
        data: {
            control: 'notifications',
            action: 'day',
            post: 'tId='+tId+'&code='+code+'&checked='+checked
        },
        success: function(answer) {},
        error: function() {
            alert('Error');
        }
    }
    ajax(query);
});

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