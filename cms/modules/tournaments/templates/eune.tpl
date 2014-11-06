<h1>Tournaments</h1>

<table class="table">
    <tr><td colspan="2"><center><b>EUNE settings</b></center></td></tr>
    <tr>
        <td width="30%"><b>Automatic advancement</b></td>
        <td width="70%"><input type="checkbox" id="auto-advance" <?=($module->config['tournament-auto-lol-eune']==1?'checked':null)?>/></td>
    </tr>
</table>
<br /><br />
<table class="table"><tr><td>
	<?
	if ($module->chats) {
		foreach($module->chats as $v) {
	?>
	<div class="chat" id="<?=$v->match_id?>" attr-file="<?=$v->id1?>_vs_<?=$v->id2?>">
		<h4><b class="player" id="player_<?=$v->id1?>"><?=$v->name1?></b> VS <b class="player" id="player_<?=$v->id2?>"><?=$v->name2?></b></h4>
        <div class="finish-match hint" name="Finish match">[End]</div>
        <div class="close-chat hint" name="Hide chat">[X]</div>
		<div class="chat-content"></div>
		<div class="chat-input">
			<input type="text" class="chat-submit" id="chat-input" attr-id="<?=$v->id1?>_vs_<?=$v->id2?>" />
		</div>
	</div>
	<?
		}
	}
	?>
</td></tr></table>

<div class="finish-popup">
    
</div>

<script>
$('.finish-match').on('click', function() {
    $('.finish-popup').show();
});

$('#auto-advance').on('click', function() {
    showMsg(2,strings['loading']);
    
    var id = $(this).attr('id');
    var checked = 0;
    if ($(this).is(':checked') === true) {
        checked = 1;
    }
    
    var query = {
        type: 'POST',
        timeout: 10000,
        data: {
            control: 'saveSetting',
            param: 'tournament-auto-lol-eune',
            value: checked
        },
        success: function(data) {
            answer = data.split(';');
            cleanMsg();
            showMsg(answer[0],answer[1]);
            messageTimer = setTimeout(cleanMsg,3000);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            showMsg(0,'Error timeout');
            messageTimer = setTimeout(cleanMsg,3000);
        }
    };
    ajax(query);
});

$('.close-chat').on('click', function() {
    $(this).parent().hide('slow');;
});

$('.chat-input').on('click', function() {
   $(this).closest('#chat-input').focus();
});

$('.chat-submit').on('keyup', function(e) {
    if (!e) {
        e = window.event;
	}

    if (e.keyCode == 13 && $.trim($(this).val())) {
		var form = [];
        form[0] = $(this).val();
		form[1] = $(this).closest('.chat').attr('attr-file');
		form[2] = $(this).closest('.chat').attr('id');
        $(this).val('');
        var query = {
            type: 'POST',
            data: {
				control: 'submitForm',
                module: 'tournaments',
				action: 'sendChat',
				form: form
            },
            success: function(answer) {
                answer = answer.split(';');
                if (answer[0] == 0) {
					alert('Error in chat: '+answer[1]);
					if (answer[1] == 'ENDED') {
						$('#'+form[2]).closest('.chat').hide();
					}
                }
            }
        }
        ajax(query);
    }
});

// --------------------------------------------------------------------------------------------------------------------

//Main things
profiler = {
    fetchChat: function() {
		var i = 0;
		var arrayElements = [];
		$.each($('.chat'), function(k, v) {
			arrayElements[i] = [];
			arrayElements[i][0] = $(this).closest('.chat').attr('id');
			arrayElements[i][1] = $(this).closest('.chat').attr('attr-file');
			i++;
		});
		
        var query = {
            type: 'POST',
            data: {
                control: 'submitForm',
                module: 'tournaments',
				action: 'fetchChat',
				form: arrayElements
            },
            timeout: 5000,
            success: function(answer) {
				$.each($.parseJSON(answer), function(k, v) {
					$('#'+k+' .chat-content').html(v);
					
					checkTop = parseInt($('#'+k+' .chat-content').prop('scrollTop')) + parseInt($('#'+k+' .chat-content').height()) + 10;
                    checkHeight = parseInt($('#'+k+' .chat-content').prop('scrollHeight'));
					
					if (checkTop != checkHeight) {
                        $('#'+k+' .chat-content').scrollTop($('#'+k+' .chat-content').prop('scrollHeight'));
                    }
				});
            }
        }
        ajax(query);
    },
    statusCheck: function() {
		var i = 0;
		var arrayElements = [];
		$.each($('.chat'), function(k, v) {
			arrayElements[i] = $(this).closest('.chat').attr('attr-file');;
			i++;
		});
		
		$('.player').removeClass('online offline');
		
        var query = {
            type: 'POST',
            data: {
                control: 'submitForm',
                module: 'tournaments',
				action: 'statusCheck',
				form: arrayElements
            },
            success: function(answer) {
				$.each($.parseJSON(answer), function(k, v) {
					$('#player_'+k).addClass(v);
				});
            }
        }
        ajax(query);
    },
};

//Start
$(document).ready(function() {
    profiler.fetchChat();
	profiler.statusCheck();
    setInterval(function () { profiler.fetchChat(); }, 5000);
	setInterval(function () { profiler.statusCheck(); }, 30000);
});
</script>

<style>
.chat {
    border: 1px solid #333;
    border-radius: 8px;
    float: left;
    width: 49%;
    background-color: #fff;
    margin-left: 10px;
    margin-bottom: 10px;
    position: relative;
}
.chat h4 {
	font-size:15px;
	margin: 0;
	font-weight: normal;
    background-color: #f9f9f9;
	border-radius: 8px 8px 0 0;
    padding: 3px 10px;
}
.chat .chat-content {
    padding: 5px 10px;
    height: 300px;
    border-bottom: 1px solid #000;
    background-color: #eee;
    font-size: 15px;
    overflow-y: auto;
}
.chat .chat-content #notice {
    font-size: 13px;
    color: #999;
}
.chat .close-chat {
    position: absolute;
    top: 1px;
    right: 5px;
    cursor: pointer;
    transition: .3s;
}
.chat .close-chat:hover {
    color: blue;
}
.chat .finish-match {
    position: absolute;
    top: 1px;
    right: 30px;
    cursor: pointer;
    transition: .3s;
}
.chat .finish-match:hover {
    color: blue;
}
.chat .chat-input {
    padding: 0 10px;
}
.chat .chat-input input[type="text"] {
    width: 100%;
    padding: 3px 0;
    border: 0;
	outline: 0;
}
.chat .chat-input input[type="text"]:hover {
	background-color: #fff;
}
.player.online {
	color: #090;
}
.player.offline {
	color: #900;
}
/* Smallest phones */
@media (max-width: 320px) {
    .chat {
        width: 280px;
    }
}
</style>