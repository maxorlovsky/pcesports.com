<h1>Tournaments</h1>

<table class="table"><tr><td>
	<?
	if ($module->chats) {
		foreach($module->chats as $v) {
	?>
	<div class="chat" id="<?=$v->match_id?>" attr-file="<?=$v->id1?>_vs_<?=$v->id2?>">
		<h4><b><?=$v->name1?></b> VS <b><?=$v->name2?></b> [<?=$v->match_id?>]</h4>
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

<script>
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
            success: function(answer) {
				$.each($.parseJSON(answer), function(k, v) {
					$('#'+k+' .chat-content').html(v);
					
					checkTop = parseInt($('#'+k+' .chat-content').prop('scrollTop')) + parseInt($('.chat-content').height()) + 10;
                    checkHeight = parseInt($('#'+k+' .chat-content').prop('scrollHeight'));
					
					if (checkTop == checkHeight) {
                        $('#'+k+' .chat-content').scrollTop($('#'+k+' .chat-content').prop('scrollHeight'));
                    }
				});
            }
        }
        ajax(query);
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
                
                $('#opponentSec').html(checkTimer);
                
                $('#opponentName').addClass((answer[1]!='none'?'not-none':''));
                $('#opponentStatus').addClass(answer[2]);
                $('#opponentName').html(answer[1]);
                $('#opponentStatus').html(answer[2]);
            }
        }
        ajax(query);
    },
};

//Start
$(document).ready(function() {
    profiler.fetchChat();
    setInterval(function () { profiler.fetchChat(); }, 5000);
	//setInterval(function () { profiler.statusCheck(); }, 30000);
});
</script>

<style>
.chat {
    border: 1px solid #333;
    border-radius: 8px;
    float: left;
    width: 590px;
    background-color: #fff;
    margin-left: 10px;
    margin-bottom: 10px;
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
</style>