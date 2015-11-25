if( window.canRunAds === undefined ){
    $('.survive').show();
}

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

$('.line-bar').each(function() {
    var current = $(this).attr('attr-current');
    var goal = $(this).attr('attr-goal');
    
    percentage = Math.round((current / goal) * 100);

    if (percentage > 100) {
        percentage = 100;
    }

    $(this).find('#gathered').text(percentage+'%');
    
    $(this).find('div span').css('width', 0);
    $(this).find('div span').animate({'width': percentage+'%'}, 500);
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

$('#fader, #close-popup, .popup .close').on('click', function() {
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
$('#updateEmail').on('click', function() {
    PC.updateEmail();
});
$('#updatePassword').on('click', function() {
    PC.updatePassword();
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
    pause: 5000
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

var isotopeBlocks = ['', '-pending', '-verified'];
$.each(isotopeBlocks, function(k, v) {
    if ($('.participants.isotope-participants'+v+' .block').length > 0) {
        $('.participants.isotope-participants'+v).isotope({
            itemSelector : '.block',
            layoutMode : 'fitRows'
        });
    }
});

$('.participants:not(.not)').on('click', '.block', function(e) {
    if(!$(e.target).is('a')){
        $(this).find('.player-list').slideToggle(500, function() {
            $.each(isotopeBlocks, function(k, v) {
                if ($('.participants.isotope-participants'+v+' .block').length > 0) {
                    $('.participants.isotope-participants'+v).isotope( 'reLayout' );
                }
            });
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
	$('#hint-helper').css('display', 'none');
});

$('.achievements').on('click', this, function() {
    $(this).fadeOut('slow');
    return false;
});

if (typeof requireStatus != 'undefined' && requireStatus == 1) {
	PC.statusCheck();
	setInterval(function () { PC.statusCheck(); }, 15000);
}

PC.runTimers();
PC.socketInitiate();

if (g.logged_in == 1) {
    PC.checkAchievements();
}