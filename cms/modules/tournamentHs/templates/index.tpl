<table class="table"><tr><td>
	<?
	if ($module->chats) {
		foreach($module->chats as $v) {
	?>
	<div class="chat" id="<?=$v->match_id?>" attr-file="<?=$v->id1?>_vs_<?=$v->id2?>" attr-challoge-team1="<?=$v->challongeTeam1?>" attr-challoge-team2="<?=$v->challongeTeam2?>" attr-name-team1="<?=$v->name1?>" attr-name-team2="<?=$v->name2?>">
        <h4><a href="javascript:;" target="_blank" class="player" id="player_<?=$v->id1?>"><?=$v->name1?></a> VS <a href="javascript:;" target="_blank" class="player" id="player_<?=$v->id2?>"><?=$v->name2?></a></h4>
        <? if (strtolower($module->system->user->login) != 'ggfwoofus') { ?><div class="finish-match hint" name="Finish match">[End]</div><? } ?>
        <div class="close-chat hint" name="Hide chat">[X]</div>
		<div class="chat-content"></div>
		<div class="chat-input">
			<input type="text" class="chat-submit" id="chat-input" attr-id="<?=$v->id1?>_vs_<?=$v->id2?>" />
		</div>
	</div>
	<?
		}
	}
    else {
        ?><i>No chats</i><?
    }
	?>
</td></tr></table>

<div class="finish-popup">
    <h4>Select the winner</h4>
    <div class="close">[X]</div>
    <div class="buttons" attr-match-id="">
        <button class="team1" attr-id=""></button>
        <button class="team2" attr-id=""></button>
    </div>
</div>

<script>
$('.finish-match').on('click', function() {
    var teamChallogeIds = [$(this).parent().attr('attr-challoge-team1'), $(this).parent().attr('attr-challoge-team2')];
    var teamNames = [$(this).parent().attr('attr-name-team1'), $(this).parent().attr('attr-name-team2')];
    
    TM.fadeScr();
    $('.finish-popup').show();
    $('.finish-popup').find('button.team1').attr('attr-id', teamChallogeIds[0]).text(teamNames[0]);
    $('.finish-popup').find('button.team2').attr('attr-id', teamChallogeIds[1]).text(teamNames[1]);
    $('.finish-popup').find('.buttons').attr('attr-match-id', $(this).parent().attr('id'));
});

$('.finish-popup button.team1, .finish-popup button.team2').on('click', function() {
    var form = [];
    form[0] = $(this).parent().attr('attr-match-id');
    form[1] = '<?=$module->server?>';
    form[2] = '0-0';
    form[3] = $('button.'+$(this).attr('class')).attr('attr-id');
    
    if ($(this).attr('class') == 'team1') {
        form[2] = '1-0';
        form[4] = $('button.team2').attr('attr-id');
    }
    else if ($(this).attr('class') == 'team2') {
        form[2] = '0-1';
        form[4] = $('button.team1').attr('attr-id');
    }
    else {
        alert('Error');
        return false;
    }
    
    var query = {
        type: 'POST',
        data: {
            control: 'submitForm',
            module: 'tournamentHs',
            action: 'finishMatch',
            form: form
        },
        success: function(answer) {
            answer = answer.split(';');
            if (answer[0] == 1) {
                $('.finish-popup .close').trigger('click');
                $('#'+form[0]+' .close-chat').trigger('click');
                return false;
            }
            
            alert('Error: '+answer[1]);
        }
    }
    TM.ajax(query);
});

$('.finish-popup .close, #fader').on('click', function() {
    $('#fader').hide();
    $('.finish-popup').hide();
});

$('.close-chat').on('click', function() {
    $(this).parent().find('div').css('visibility', 'hidden');
    $(this).parent().find('h4').css({width: 0, height: 22, overflow: 'hidden'});
    $(this).parent().animate({width:0}, 500, function() {
        $(this).remove();
    });
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
                module: 'tournamentHs',
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
        TM.ajax(query);
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
                module: 'tournamentHs',
				action: 'fetchChat',
				form: arrayElements
            },
            timeout: 10000,
            success: function(answer) {
				$.each($.parseJSON(answer), function(k, v) {
                    checkTop = parseInt($('#'+k+' .chat-content').prop('scrollTop')) + parseInt($('#'+k+' .chat-content').height()) + 10;
                    checkHeight = parseInt($('#'+k+' .chat-content').prop('scrollHeight'));
                    
                    currentContent = $('#'+k+' .chat-content').html();
                    if (escape(currentContent) != escape(v)) {
                        $('.chat-content').html(v);
                        $('#'+k+' .chat-content').scrollTop($('#'+k+' .chat-content').prop('scrollHeight'));
                    }
				});
            }
        }
        TM.ajax(query);
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
                module: 'tournamentHs',
				action: 'statusCheck',
				form: arrayElements
            },
            success: function(answer) {
				$.each($.parseJSON(answer), function(k, v) {
					$('#player_'+k).addClass(v);
				});
            }
        }
        TM.ajax(query);
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
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.15);
    border: 1px solid #333;
    border-radius: 8px;
    float: left;
    width: 49%;
    background-color: #fff;
    margin-left: 0;
    margin-bottom: 10px;
    position: relative;
}
.chat:nth-child(2n) {
    margin-left: 10px;
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
    font-size: 14px;
    overflow-y: auto;
    font-family: arial;
}
.chat .chat-content .message {
    box-shadow: 2px 2px 3px rgba(0, 0, 0, 0.5);
    padding: 5px 10px;
    border-radius: 8px;
    word-break: break-all;
}
.chat .chat-content span {
    font-size: 11px;
    margin-left: 5px;
}
.chat .chat-content #notice {
    font-size: 10px;
    color: #777;
    margin: 0;
}
.chat .chat-content .player1 {
    float: left;
    clear: both;
}
.chat .chat-content .player1 .message {
    background-color: #c7edfc;
}
.chat .chat-content .player2 {
    float: right;
    clear: both;
}
.chat .chat-content .player2 .message {
   background-color: #fcc7c7;
}
.chat .chat-content .manager {
    clear: both;
}
.chat .chat-content .manager span {
    color: #0018a9;
}
.chat .chat-content .manager .message {
    background-color: #85ff8b;
    color: #0019b4;
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
.player {
    font-weight: bold;
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

.finish-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    margin-left: -212px;
    margin-top: -40px;
    width: 424px;
    height: 80px;
    background-color: #fff;
    border: 1px solid #000;
    border-radius: 8px;
    z-index: 200;
}
.finish-popup h4 {
    padding: 3px 0 0 10px;
    margin: 0;
}
.finish-popup .close {
    position: absolute;
    top: 3px;
    right: 8px;
    transition: .3s;
    cursor: pointer;
}
.finish-popup .close:hover {
    color: blue;
}
.finish-popup .buttons {
    margin: 10px 0 0 10px;
}
.finish-popup button {
    display: inline-block;
    width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>