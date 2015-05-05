$('.burger').on('click', function() {
    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
    }
    else {
        $(this).addClass('active');   
    }
    $('header nav .navbar-inner, header .nav-user .nav-sub').slideToggle();
});

$('.twitch .featured-list').on('click', '.featured-streamer', function() {
    var streamer = $(this).attr('attr-name');
    var oldStreamer = $('#player').attr('attr-current');
    var objectData = $('#player object').attr('data');
    var flashvars = $('#player object param[name="flashvars"]').val();
    
    objectData = objectData.replace(oldStreamer, streamer);
    flashvars = flashvars.replace(oldStreamer, streamer);
    $('#player object').attr('data', objectData);
    $('#player object param[name="flashvars"]').val(flashvars);
});

$('.donate').on('click', '.arrow-down', function() {
    $('.donate').find('.list').slideToggle();
});

$('.donate-bar').ready(function() {
    var current = $('.donate-bar').attr('attr-current');
    var goal = $('.donate-bar').attr('attr-goal');
    
    $('.donate-bar').find('#gathered').html(current);
    $('.donate-bar').find('#goal').html(goal);
    
    percentage = ((current / goal) * 100) * 2;
    
    $('.donate-bar div span').css('width', percentage);
});

$('.user-comments').on('click', '.edit', function() {
    var element = $(this).closest('.actions').find('.edit-text');
    element.stop().slideToggle();
}).on('click', '#closeEditComment', function() {
    var element = $(this).closest('.actions').find('.edit-text');
    element.stop().slideUp();
}).on('click', '.delete', function() {
    if(confirm($(this).attr('attr-msg'))) {
		PC.deleteBoardComment($(this));
	}
    
    return false;
}).on('click', '.report', function() {
    if(confirm($(this).attr('attr-msg'))) {
		
	}
    
    return false;
}).on('click', '#editComment', function() {
    PC.editComment($(this));
});

$('.board').on('click', '.delete', function() {
    var parent = $(this).closest('.board');
    var id = parent.attr('attr-id');
    
    if(confirm($(this).attr('attr-msg'))) {
		PC.deleteBoard(id);
	}
    
    return false;
}).on('click', '.report', function() {
    var parent = $(this).closest('.board');
    var id = parent.attr('attr-id');
    if(confirm($(this).attr('attr-msg'))) {
		//PC.reportBoard(id);
	}
    return false;
});

$('.confirm').on('click', function() {
	if(confirm($(this).attr('attr-msg'))) {
		location.href = $(this).attr('href');
	}
	return false;
});

$('.board .voting').on('click', '.arrow', function() {
    if (g.logged_in === 0) {
        PC.openPopup('login-window');
        return false;
    }
    
    var id = parseInt($(this).closest('.board').attr('attr-id'));
    if ($(this).hasClass('top')) {
        PC.boardVote('plus', id);
    }
    else {
        PC.boardVote('minus', id);
    }
});

$('.summoners').on('click', '.notApproved .status', function() {
    var id = parseInt($(this).closest('.summoner').attr('attr-id'));
    var masteries = $(this).closest('.summoner').attr('attr-masteries');
    $('.how_to_approve').slideUp('fast', function() {
        $('.how_to_approve').slideDown('slow');
        $('.how_to_approve').find('.verification-code').val(masteries);
        $('.how_to_approve').find('#masteries-code').html(masteries);
        $('.how_to_approve').find('#summonerVerifyId').val(id);
    });
});

$('#verifySummoner').on('click', function() {
    PC.verifySummoner($(this));
});

$('.summoners').on('click', '.removeSummoner', function() {
    PC.removeSummoner($(this).closest('.summoner'));
});

$('#addSummoner').on('click', function() {
    PC.addSummoner();
});

$('#checkInHs').on('click', function() {
    PC.checkIn('hs');
});

$('#checkInSmite').on('click', function() {
    PC.checkIn('smite');
});

$('#checkInLol').on('click', function() {
    PC.checkIn('lol');
});

$('.submitBoard .categories').on('click', 'div', function() {
    $('.submitBoard .categories div').removeClass('active');
    $(this).addClass('active');
    $('.submitBoard #category').val($(this).attr('attr-category'));
});
$('.submitBoard #submitBoard').on('click', function() {
    PC.submitBoard();
});
$('#submitBoardComment').on('click', function() {
    PC.submitBoardComment();
});

$('.avatars-list .avatar-block').on('click', function() {
    var oldId = $('#avatar').val();
    
    $('.avatars-list .avatar-block').removeClass('picked');
    $(this).addClass('picked');
    $('#avatar').val($(this).attr('attr-id'));
    
    //Setting avatar in navigation
    var src = $('.nav-avatar a img').attr('src');
    src = src.replace(oldId+'.jpg', $(this).attr('attr-id')+'.jpg');
    $('.nav-avatar a img').attr('src', src);
});

$('.editStreamerAction .change_game, .editStreamerAction .change_languages').on('change', function() {
    PC.editStreamer(this);
});
$('.editStreamerAction #removeStreamer').on('click', function() {
    PC.removeStreamer(this);
});
$('#submitStreamer').on('click', function() {
    PC.submitStreamer();
});

$('#connectTeamToAccount').on('click', function() {
    PC.connectTeamToAccount();
});

$('div.streamer').on('click', function() {
    var id = $(this).attr('attr-id');
    $('#stream_'+id).stop().slideToggle('slow');
});

$('#submitComment').on('click', function() {
    PC.comment();
});

$('.formbut').on('click', function() {
    allowedFormat = ['b','i','s','link','q','list'];
    element = $(this);
    
    $.each(allowedFormat, function(k, v) {
        if (element.hasClass(v)) {
            PC.format[v]();
        }
    });
});

$('.rules').on('click', function() {
    PC.openPopup('rules-window');
});

$('.login, .must-login').on('click', function() {
    PC.openPopup('login-window');
});

$('#fader, #close-popup').on('click', function() {
    PC.closePopup();
});

$('.socialLogin').on('click', function() {
    var id = $(this).attr('id');
    PC.social[id]();
});
$('.socialConnect').on('click', function() {
    var id = $(this).attr('id');
    PC.social.connect(id);
});
$('.socialDisconnect').on('click', function() {
    var id = $(this).attr('id');
    PC.social.disconnect(id);
});

$('#updateProfile').on('click', function() {
    PC.updateProfile();
});

$('.connected').on('mouseover', function() {
    $(this).text(g.str.disconnect);
}).on('mouseout', function() {
    $(this).text(g.str.connected);
});

$('.disconnected').on('mouseover', function() {
    $(this).text(g.str.connect);
}).on('mouseout', function() {
    $(this).text(g.str.disconnected);
});

$('.bx-wrapper').bxSlider({
    auto: true,
    autoHover: true,
    mode: 'fade',
    speed: 2000,
    pause: 4000
});

$('.languages a').on('click', function() {
    $('.languages .language-switcher').stop().slideToggle('fast');
});

$(document).on('scroll', function() {
	if (parseInt($(document).scrollTop()) !== 0 && $('#toTop').is(':hidden')) {
		$('#toTop').fadeIn('slow', function() {
			$('#toTop').css('transition', '.3s');
		});
	}
	else if (parseInt($(document).scrollTop()) === 0) {
		$('#toTop').css('transition', '0');
		$('#toTop').fadeOut('fast');
	}
});

$('#toTop').on('click', function() {
	$('html, body').animate({scrollTop: 0}, 500);
});

if ($('.participants.isotope-participants .block').length > 0) {
	$('.participants.isotope-participants').isotope({
	    itemSelector : '.block',
	    layoutMode : 'fitRows'
	});
}
if ($('.participants.isotope-participants-pending .block').length > 0) {
	$('.participants.isotope-participants-pending').isotope({
	    itemSelector : '.block',
	    layoutMode : 'fitRows'
	});
}

$('.participants:not(.not)').on('click', '.block', function(e) {
    if(!$(e.target).is('a')){
        $(this).find('.player-list').slideToggle(500, function() {
            if ($('.participants.isotope-participants .block').length > 0) {
                $('.participants.isotope-participants').isotope( 'reLayout' );
            }
            if ($('.participants.isotope-participants-pending .block').length > 0) {
                $('.participants.isotope-participants-pending').isotope( 'reLayout' );
            }
        });
    }
});

$('.like').on('click', function() {
    PC.like(this);
});

$('#submitContactForm').on('click', function() {
    PC.submitContactForm(this);
});

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
	$('#hint-helper').offset({ top: 0, left: 0 });
	$('#hint-helper').css('display', 'none');
});

// Functions
var PC = {
    //global insides
    site: g.site,
    formInProgress: 0, //used when required to check if form is still in progress
    
    //functions
    deleteBoardComment: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        
        var type = 'comment';
        if (element.closest('.master').attr('attr-module') == 'newsComment') {
            type = 'newsComment';
        }
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitBoard',
                module: 'delete',
                type: type,
                id: parseInt(element.closest('.master').attr('attr-id'))
            },
            success: function(data) {
                PC.formInProgress = 0;
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    element.closest('.master').find('.body .edited').hide();
                    element.closest('.master').find('.body div').html(answer[1]);
                    element.closest('.master').find('.actions').remove();
                }
            }
        };
        this.ajax(query);
    },
    deleteBoard: function(id) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitBoard',
                module: 'delete',
                type: 'board',
                id: id
            },
            success: function(data) {
                PC.formInProgress = 0;
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    if ($('.board .thread .text').length > 0) {
                        $('.board .thread .text').html(answer[1]);
                    }
                }
                else {
                    alert(answer[1]);
                }
            }
        };
        this.ajax(query);
    },
    boardVote: function(status, id) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'boardVote',
                type: 'board',
                status: status,
                id: id
            },
            success: function(data) {
                PC.formInProgress = 0;
                answer = data.split(';');
                
                count = parseInt($('#board_vote_'+id).html());
                
                if (answer[0] != 3) {
                    $('#board_vote_'+id).parent().find('.arrow.top').removeClass('voted');
                    $('#board_vote_'+id).parent().find('.arrow.bottom').removeClass('voted');
                }
                
                if (answer[0] == 1) {
                    if (status == 'plus') {
                        $('#board_vote_'+id).parent().find('.arrow.top').addClass('voted');
                        $('#board_vote_'+id).html(count + 1);
                    }
                    else {
                        $('#board_vote_'+id).parent().find('.arrow.bottom').addClass('voted');
                        $('#board_vote_'+id).html(count - 1);
                    }
                }
                else if (answer[0] == 2) {
                    if (status == 'plus') {
                        $('#board_vote_'+id).html(count - 1);
                    }
                    else {
                        $('#board_vote_'+id).html(count + 1);
                    }
                }
            }
        };
        this.ajax(query);
    },
    submitBoardComment: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#submitBoardComment').addClass('alpha');
        $('.leave-comment #error').hide();
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitBoard',
                module: $('.leave-comment #module').val(),
                text: $('.leave-comment #msg').val(),
                id: $('.leave-comment #id').val()
            },
            success: function(data) {
                PC.formInProgress = 0;
                $('#submitBoardComment').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.leave-comment #msg').val('');
                    console.log(answer[1]);
                    $('.user-comments').prepend(answer[1]);
                }
                else {
                    $('.leave-comment #error').html('<p>'+answer[1]+'</p>').slideDown('fast');
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    submitBoard: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#submitBoard').addClass('alpha');
        $('.submitBoard #error').hide();
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitBoard',
                module: $('.submitBoard #module').val(),
                title: $('.submitBoard #title').val(),
                text: $('.submitBoard #msg').val(),
                category: $('.submitBoard #category').val(),
                id: $('.submitBoard #boardId').val()
            },
            success: function(data) {
                PC.formInProgress = 0;
                $('#submitBoard').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    window.location.replace(answer[1]);
                }
                else {
                    $('.submitBoard #error').html('<p>'+answer[1]+'</p>').slideDown('fast');
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    verifySummoner: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        $(element).addClass('alpha');
        $('.summoner-verification #error').slideUp('fast');
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'verifySummoner',
                id: parseInt($('#summonerVerifyId').val()),
            },
            success: function(data) {
                PC.formInProgress = 0;
                $(element).removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.summoner-verification #error').slideUp('fast');
                    $('.how_to_approve').slideUp('slow', function() {
                        $('.summoner[attr-id="'+answer[1]+'"]').removeClass('notApproved').addClass('approved');
                        $('.summoner[attr-id="'+answer[1]+'"] .status').removeClass('hint').html(g.str.approved);
                    });
                }
                else {
                    $('.summoner-verification #error p').html(answer[1]);
                    $('.summoner-verification #error').slideDown('fast');
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    removeSummoner: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        $(element).addClass('alpha');
        $('.summoner-form #error').slideUp('fast');
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'removeSummoner',
                id: parseInt(element.attr('attr-id')),
            },
            success: function(data) {
                PC.formInProgress = 0;
                //$(element).removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $(element).slideUp('slow', function() {
                        $(this).remove();
                        if ($('.summoners .block-content.summoner').length <= 1) {
                            $('.summoners').append('<div class="block-content empty">none</div>');
                        }
                    });
                }
                else {
                    alert(answer[1]);
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    addSummoner: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        $('#addSummoner').addClass('alpha');
        $('.summoner-form #error').slideUp('fast');
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'addSummoner',
                name: $('.summoner-form #name').val(),
                region: $('.summoner-form #region').val()
            },
            success: function(data) {
                PC.formInProgress = 0;
                $('#addSummoner').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    data = $.parseJSON(answer[1]);
                    var html = $('.dumpSummoner').html();
                    html = html.replace('%id%', data.id);
                    html = html.replace('%masteries%', data.verificationCode);
                    html = html.replace(/%name%/g, data.name);
                    html = html.replace('%regionName%', data.regionName);
                    html = html.replace('%region%', data.region);
                    
                    if ($('.summoners .block-content').hasClass('empty')) {
                        $('.summoners .block-content').remove();
                    }
                    
                    $('.summoners').append(html);
                    $('.summoners').find('.summoner:hidden').slideDown('slow', function() {
                        $(this).find('.status').trigger('click');
                    });
                }
                else {
                    $('.summoner-form #error').html('<p>'+answer[1]+'</p>').slideDown('fast');
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    checkIn: function(game) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        $(this).addClass('alpha');
        this.formInProgress = 1;
        
        if (game == 'lol') {
            command = 'checkInLOL';
        }
        else if (game == 'smite') {
            command = 'checkInSmite';
        }
        else if (game == 'hs') {
            command = 'checkInHs';
        }
        else {
            alert('No game');
            return false;
        }
        
        var query = {
            type: 'POST',
            data: {
                ajax: command
            },
            success: function(data) {
                PC.formInProgress = 0;
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
    editStreamer: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        streamerId = $(element).closest('.streamer').attr('attr-id');
        valueGame = $(element).closest('.editStreamerAction').find('.change_game').val();
        valueLanguage = $(element).closest('.editStreamerAction').find('.change_languages').val();
        this.formInProgress = 1;
        $(element).closest('.streamer').addClass('alpha');
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'editStreamer',
                id: streamerId,
                game: valueGame,
                language: valueLanguage
            },
            success: function(data) {
                PC.formInProgress = 0;
                $(element).closest('.streamer').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    //$(element).closest('.streamer').find('.game-logo').attr('src', '');
                }
                else {
                    alert(answer[1]);
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    removeStreamer: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        streamerId = $(element).closest('.streamer').attr('attr-id');
        this.formInProgress = 1;
        $(element).closest('.streamer').addClass('alpha');
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'removeStreamer',
                id: streamerId
            },
            success: function(data) {
                PC.formInProgress = 0;
                $(element).closest('.streamer').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $(element).closest('.streamer').remove();
                }
                else {
                    alert(answer[1]);
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    submitStreamer: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#submitStreamer').addClass('alpha');
        
        $('.streamer-form #error').hide();
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitStreamer',
                form: $('.streamer-form').serialize()
            },
            success: function(data) {
                PC.formInProgress = 0;
                $('#submitStreamer').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.streamer-form').slideUp('fast');
                    $('.success-sent').slideDown('fast');
                    $('.success-sent p').html(answer[1]);
                }
                else {
                    $('.streamer-form #error').html('<p>'+answer[1]+'</p>').slideDown('fast');
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    connectTeamToAccount: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'connectTeamToAccount'
            },
            success: function(data) {
                PC.formInProgress = 0;
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.info-add').closest('.block').fadeOut();
                }
                else {
                    alert(answer[1]);
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    getNewsComments: function(id) {
        var query = {
            type: 'POST',
            data: {
                ajax: 'getNewsComments',
                id: parseInt(id)
            },
            success: function(data) {
                $('.user-comments').html(data);
            }
        };
        this.ajax(query);
    },
    editComment: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        element.addClass('alpha');
        element.closest('.master').find('.edit-text #error').hide();
        var module = 'editBoardComment';
        if (element.closest('.master').attr('attr-module') == 'newsComment') {
            module = 'editNewsComment';
        }
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'comment',
                module: module,
                id: parseInt(element.closest('.master').attr('attr-id')),
                text: element.closest('.master').find('.edit-text textarea').val()
            },
            success: function(data) {
                PC.formInProgress = 0;
                element.removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    element.closest('.master').find('#closeEditComment').trigger('click');
                    element.closest('.master').find('.body div').html(answer[1]);
                    element.closest('.master').find('.body .edited').show();
                }
                else {
                    element.closest('.master').find('.edit-text #error p').html(answer[1]);
                    element.closest('.master').find('.edit-text #error').slideDown();
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    comment: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#submitComment').addClass('alpha');
        $('.leave-comment #error').hide();
        var query = {
            type: 'POST',
            data: {
                ajax: 'comment',
                module: $('.leave-comment #module').val(),
                id: parseInt($('.leave-comment #id').val()),
                text: $('.leave-comment #msg').val()
            },
            success: function(data) {
                PC.formInProgress = 0;
                $('#submitComment').removeClass('alpha');
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.leave-comment #msg').val('');
                    PC.getNewsComments($('.leave-comment #id').val());
                    if ($('#comments-count').length > 0) {
                        $('#comments-count').html(parseInt($('#comments-count').html()) + 1);
                    }
                }
                else {
                    $('.leave-comment #error').html('<p>'+answer[1]+'</p>').slideDown('fast');
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    format: {
        b: function() {
            var text = $('#msg').val().split('');
            var start = $('#msg')[0].selectionStart;
            var end = $('#msg')[0].selectionEnd;
            
            if (end != start) {
                text.splice(end, 0, '**');
                text.splice(start, 0, '**');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start;
                $('#msg')[0].selectionEnd = end + 4;
            }
            else {
                text.splice(start, 0, '****');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start + 2;
                $('#msg')[0].selectionEnd = start + 2;
            }
        },
        i: function() {
            var text = $('#msg').val().split('');
            var start = $('#msg')[0].selectionStart;
            var end = $('#msg')[0].selectionEnd;
            
            if (end != start) {
                text.splice(end, 0, '*');
                text.splice(start, 0, '*');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start;
                $('#msg')[0].selectionEnd = end + 2;
            }
            else {
                text.splice(start, 0, '**');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start + 1;
                $('#msg')[0].selectionEnd = start + 1;
            }
        },
        s: function() {
            var text = $('#msg').val().split('');
            var start = $('#msg')[0].selectionStart;
            var end = $('#msg')[0].selectionEnd;
            
            if (end != start) {
                text.splice(end, 0, '~~');
                text.splice(start, 0, '~~');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start;
                $('#msg')[0].selectionEnd = end + 4;
            }
            else {
                text.splice(start, 0, '~~~~');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start + 2;
                $('#msg')[0].selectionEnd = start + 2;
            }
        },
        link: function() {
            var link = prompt(g.str.enter_url);
            if (!link) {
                return false;
            }
            if (link.substring(0,7) != 'http://') {
                link = 'http://'+link;
            }
            var text = $('#msg').val().split('');
            var start = $('#msg')[0].selectionStart;
            var end = $('#msg')[0].selectionEnd + 1;
            
            if (end != start) {
                text.splice(start, 0, '[');
                text.splice(end, 0, ']('+link+')');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = end + 3 + link.length;
                $('#msg')[0].selectionEnd = end + 3 + link.length;
            }
            else {
                text.splice($('#msg')[0].selectionStart, 0, '[]('+link+')');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start + 1;
                $('#msg')[0].selectionEnd = start + 1;
            }
        },
        q: function() {
            var text = $('#msg').val().split('');
            var start = $('#msg')[0].selectionStart;
            var end = $('#msg')[0].selectionEnd;
            
            if (end != start) {
                text.splice(end, 0, '[/q]');
                text.splice(start, 0, '[q]');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start;
                $('#msg')[0].selectionEnd = end + 7;
            }
            else {
                addBreak = '';
                plusNum = 3;
                console.log(end);
                console.log(text.length);
                if (end == text.length && end !== 0) {
                    addBreak = '\r';
                    plusNum = 4;
                }
                text.splice(start, 0, addBreak+'[q][/q]');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start + plusNum;
                $('#msg')[0].selectionEnd = start + plusNum;
            }
        },
        list: function() {
            var text = $('#msg').val().split('');
            var start = $('#msg')[0].selectionStart;
            var end = $('#msg')[0].selectionEnd;
            
            if (end != start) {
                text.splice(end, 0, '[/l]');
                text.splice(start, 0, '[l]');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start;
                $('#msg')[0].selectionEnd = end + 4;
            }
            else {
                text.splice(start, 0, '\r[l][/l]');
                returnText = text.join('');
                $('#msg').val(returnText).focus();
                $('#msg')[0].selectionStart = start + 4;
                $('#msg')[0].selectionEnd = start + 4;
            }
        },
    },
    openPopup: function(name) {
        if ($('#'+name).height() > $(window).height()) {
            var minus = $(window).height() * 0.1;
            $('#'+name).height(parseInt($(window).height()) - minus);
            $('#'+name).css('overflow-y', 'scroll');
        }
        $('html, body').css('overflow', 'hidden');
        $('#fader').fadeIn('fast');
        $('#'+name).css('top', -$('#'+name).height());
        
        getLeft = ($(window).width()/2) - ($('#'+name).width()/2);
        getTop = ($(window).height()/2) - ($('#'+name).height()/2);
        $('#'+name).css({left: getLeft});
        $('#'+name).show();
        $('#'+name).stop().animate({top: getTop+20}, 500, function() {
            $(this).stop().animate({top: getTop});
        });
    },
    closePopup: function() {
        $('html, body').css('overflow', '');
        $('#fader').fadeOut('fast');
        $('.popup:visible').stop().animate({top: -$('.popup:visible').height()});
    },
    timers: [],
    secs: 0,
    runTimers: function() {
        PC.secs++;
        clearTimeout(PC.timers[0]);
        PC.timers[0] = setTimeout(function(){PC.runTimers();}, 1000);
        $.each($('.timer'), function(k, v) {
            //PC.timers[k] = setTimeout(function(){PC.updateTimer(k, v);}, 1000);
            PC.updateTimer(k, v);
        });
    },
    updateTimer: function(key, element) {
        element = $(element);
        delta = parseInt(element.attr('attr-time'));
        nobr = 0;
        if (element.attr('attr-br')) {
            nobr = 1;
        }
        
        if (delta < 1 || !delta) {
            element.html('Live');
            return;
        }
        
        element.attr('attr-time', delta-1);
        
        var days = Math.floor(delta / 86400);
        delta -= days * 86400;
        var hours = Math.floor(delta / 3600) % 24;
        delta -= hours * 3600;
        var minutes = Math.floor(delta / 60) % 60;
        //delta -= minutes * 60;
        //var seconds = delta % 60;
        dayStr = g.str.days;
        if (days == 1) {
            dayStr = g.str.day;
        }
        
        var returnString = '';
        if (days > 0) {
            returnString += days+' '+dayStr;
            if (nobr != 1) {
                returnString += '<br />';
            }
            else {
                returnString += ' ';
            }
        }
        if (hours   < 10) {hours   = '0'+hours;}
        if (minutes < 10) {minutes = '0'+minutes;}
        //if (seconds < 10) {seconds = '0'+seconds;}
        
        returnString += hours+':'+minutes;//+':'+seconds;
        
        if (element.html() != returnString) {
            element.html(returnString);
        }
        
        //PC.timers[key] = setTimeout(function(){PC.updateTimer(key, element);}, 1000);
    },
    likeInProgress: 0, //local var for bottom function
    like: function(element) {
        if (this.likeInProgress == 1) {
            return false;
        }

        var newsId = parseInt($(element).attr('attr-news-id'));
        this.likeInProgress = 1;
	
        var query = {
            type: 'POST',
            timeout: 10000,
            data: {
                ajax: 'newsVote',
                id: parseInt($(element).attr('attr-news-id'))
            },
            success: function(data) {
                PC.likeInProgress = 0;
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    if (answer[1] == '+ 1') {
                        $(element).find('.like-icon').addClass('active');
                        $('#news-like-'+newsId).html(parseInt($('#news-like-'+newsId).html()) + 1);
                    }
                    else {
                        $(element).find('.like-icon').removeClass('active');
                        $('#news-like-'+newsId).html(parseInt($('#news-like-'+newsId).html()) - 1);
                    }
                }
                
                return false;
            }
        };
        this.ajax(query);
    },
    submitContactForm: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $(element).addClass('loading');
        $('.contact-form #error').hide();
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitContactForm',
                form: $('.contact-form').serialize()
            },
            success: function(data) {
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.contact-form').slideUp('fast');
                    $('.success-sent').slideDown('fast');
                    $('.success-sent p').html(answer[1]);
                }
                else {
                    PC.formInProgress = 0;
                    $(element).removeClass('loading');
                    $('.contact-form #error p').html(answer[1]);
                    $('.contact-form #error').slideDown('fast');
                }
                
                return false;
            },
            error: function(data) {
                PC.formInProgress = 0;
                $(element).removeClass('loading');
            }
        };
        this.ajax(query);
    },
    statusCheck: function() {
		$('#fightStatus').removeClass('online').removeClass('red');
		var query = {
			type: 'POST',
			data: {
				ajax: 'statusCheck',
			},
			success: function(answer) {
				answer = answer.split(';');
				if (answer[2] == 'online') {
					$('#fightStatus').addClass('online');
				}
                if (answer[0] == 2) {
                    $('#fightStatus').addClass('red');
                }
				$('#fightStatus').html(answer[2]);
			}
		};
		this.ajax(query);
	},
    social: {
        vk: function() {
            this.get_token('vk');
            return false;
        },
        tw: function() {
            this.get_token('tw');
            
            return false;
        }, 
        gp: function() {
            this.get_token('gp');
            
            return false;
        }, 
        fb: function() {
            this.get_token('fb');
            
            return false;
        },
        tc: function() {
            this.get_token('tc');
            
            return false;
        },
        bn: function() {
            this.get_token('bn');
            
            return false;
        },
        st: function() {
            this.get_token('st');
            
            return false;
        },
        get_token: function(provider) {
            var query = {
                type: 'POST',
                data: {
                    ajax: 'socialLogin',
                    provider: provider
                },
                success: function(data) {
                    PC.social.auth_redirect(data);
                }
            };
            PC.ajax(query);
        },
        auth_redirect: function(answ) {
            data = answ.split(';');
            
            if(data[0] != '0') {
                window.location.href = answ;
            }
            else {
                alert(data[1]);
            }
        },
        windowSocial: '',
        connect: function(network) {
            PC.social.windowSocial = window.open('', "connectSocial", "width=800, height=600, scrollbars=no");
            
            var query = {
                type: 'POST',
                data: {
                    ajax: 'socialLogin',
                    provider: network
                },
                success: function(answer) {
                    data = answer.split(';');
                    
                    if (data[0] === 0) {
                        alert(data[1]);
                        PC.social.windowSocial.close();
                        return false;
                    }
                    
                    PC.social.windowSocial.location = data[0];
                }
            };
            PC.ajax(query);
        },
        disconnect: function(network) {
            var query = {
                type: 'POST',
                data: {
                    ajax: 'socialDisconnect',
                    provider: network
                },
                success: function(answer) {
                    data = answer.split(';');

                    if (data[0] != 1) {
                        alert(data[1]);
                        return false;
                    }
                    
                    location.reload();
                }
            };
            PC.ajax(query);
        },
    },
    addTeam: function(game) {
        if (!game) {
            game = 'registerInLoL';
        }
        
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .message').hide();
        $('#da-form .message').removeClass('error success');
        $('#add-team').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: game,
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#add-team').removeClass('alpha');
                PC.formInProgress = 0;
                
                if (answer.ok == 1) {
                    $('#register-url a').trigger('click');
                    $('#join-form').slideUp(1000, function() {
                        $('.reg-completed').slideDown(1000);
                    });
                }
                else {
                    $.each(answer.err, function(k, v) {
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
                }
            },
            error: function() {
                $('#add-team').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    editSmiteTeam: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .message').hide();
        $('#da-form .message').removeClass('error success');
        $('.team-edit-completed').hide();
        element.addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'editInSmite',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-team').removeClass('alpha');
                PC.formInProgress = 0;
                
                $.each(answer.err, function(k, v) {
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
                
                if (answer.ok == 1) {
                    $('.team-edit-completed').slideDown(1000);
                }
            },
            error: function() {
                $('#edit-team').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    editPlayer: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('.team-edit-completed').hide();
        $('#da-form .message').hide();
        $('#da-form .message').removeClass('error success');
        element.addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'editInHS',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-player').removeClass('alpha');
                PC.formInProgress = 0;
                
                $.each(answer.err, function(k, v) {
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
                
                if (answer.ok == 1) {
                    $('.team-edit-completed').slideDown(1000);
                }
            },
            error: function() {
                $('#edit-player').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    editTeam: function(element) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .message').hide();
        $('#da-form .message').removeClass('error success');
        $('.reg-completed').hide();
        element.addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'editInLOL',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-team').removeClass('alpha');
                PC.formInProgress = 0;
                
                $.each(answer.err, function(k, v) {
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
                
                if (answer.ok == 1) {
                    $('.reg-completed').slideDown(1000);
                }
            },
            error: function() {
                $('#edit-team').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    addLanPlayer: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .message').hide();
        $('#da-form .message').removeClass('error success');
        $('#add-player-lan').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'registerInLanHS',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#add-player-lan').removeClass('alpha');
                PC.formInProgress = 0;
                
                if (answer.ok == 1) {
                    $('#register-url a').trigger('click');
                    $('#join-form').slideUp(1000, function() {
                        $('.reg-completed').slideDown(1000);
                    });
                }
                else {
                    $.each(answer.err, function(k, v) {
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
                }
            },
            error: function() {
                $('#add-player-lan').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    addPlayer: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .message').hide();
        $('#da-form .message').removeClass('error success');
        $('#add-player').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'registerInHS',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#add-player').removeClass('alpha');
                PC.formInProgress = 0;
                
                if (answer.ok == 1) {
                    $('#register-url a').trigger('click');
                    $('#join-form').slideUp(1000, function() {
                        $('.reg-completed').slideDown(1000);
                    });
                }
                else {
                    $.each(answer.err, function(k, v) {
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
                }
            },
            error: function() {
                $('#add-player').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    updateProfile: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('.profile #error').hide();
        $('.profile #success').hide();
        $('#updateProfile').addClass('alpha');
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'updateProfile',
                form: $('.profile').serialize()
            },
            success: function(answer) {
                $('#updateProfile').removeClass('alpha');
                PC.formInProgress = 0;
                data = answer.split(';');
                
                if (data[0] != 1) {
                    $('.profile #error p').text(data[1]);
                    $('.profile #error').slideDown(1000);
                }
                else {
                    $('.profile #success p').text(data[1]);
                    $('.profile #success').slideDown(1000);
                    
                    if ($('.profile #name').val()) {
                        $('#name_not_set').slideUp('fast');
                    }
                    if ($('.profile #email').val()) {
                        $('#email_not_set').slideUp('fast');
                    }
                }
            },
            error: function() {
                alert('Something went wrong... Contact admin at info@pcesports.com');
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

if (typeof requireStatus != 'undefined' && requireStatus == 1) {
	PC.statusCheck();
	setInterval(function () { PC.statusCheck(); }, 15000);
}

PC.runTimers();