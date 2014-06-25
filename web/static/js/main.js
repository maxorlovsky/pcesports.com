$('.login').on('click', function() {
    PC.openPopup('login-window');
});

$('#fader, #close-popup').on('click', function() {
    PC.closePopup();
});

$('.socialLogin').on('click', function() {
    var id = $(this).attr('id');
    PC.social[id]();
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

if (requireStatus == 1) {
	PC.statusCheck();
	setInterval(function () { PC.statusCheck(); }, 15000);
}

$(document).on('scroll', function() {
	if (parseInt($(document).scrollTop()) != 0 && $('#toTop').is(':hidden')) {
		$('#toTop').fadeIn('slow', function() {
			$('#toTop').css('transition', '.3s');
		});
	}
	else if (parseInt($(document).scrollTop()) == 0) {
		$('#toTop').css('transition', '0');
		$('#toTop').fadeOut('fast');
	}
});

$('#toTop').on('click', function() {
	$('html, body').animate({scrollTop: 0}, 500);
});

$('.confirm').on('click', function() {
	if(confirm($(this).attr('attr-msg'))) {
		location.href = $(this).attr('href');
	}
	return false;
});

if ($('.participants .block').length > 0) {
	$('.participants').isotope({
	    itemSelector : '.block',
	    layoutMode : 'fitRows'
	});
}

$('.participants').on('click', '.block', function(e) {
    if(!$(e.target).is('a')){
        $(this).find('.player-list').slideToggle(500, function() {
            $('.participants').isotope( 'reLayout' );
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
		$('#hint-helper').show();
	}
}).on('mouseout', '.hint', function(){
	$('#hint-helper').offset({ top: 0, left: 0 });
	$('#hint-helper').hide();
});

// Functions
var PC = {
    //global insides
    site: g.site,
    formInProgress: 0, //used when required to check if form is still in progress
    
    //functions
    openPopup: function(name) {
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
    updateTimers: function() {
        $.each($('.timer'), function(k, v) {
            delta = parseInt($(this).attr('attr-time'));
            nobr = 0;
            if ($(this).attr('attr-br')) {
                nobr = 1;
            }
            originalTimer = delta;
        
            if (delta < 1 || !delta) {
                $(this).html('Live');
                return;
            }
            
            var days = Math.floor(delta / 86400);
            delta -= days * 86400;
            var hours = Math.floor(delta / 3600) % 24;
            delta -= hours * 3600;
            var minutes = Math.floor(delta / 60) % 60;
            delta -= minutes * 60;
            var seconds = delta % 60;
            dayStr = g.str.days;
            if (days == 1) {
                dayStr = g.str.day;
            }
            
            var returnString = '';
            if (days > 0) {
                returnString += days+' '+dayStr+' ';
                if (nobr != 1) {
                    returnString += '<br />';
                }
            }
            if (hours   < 10) {hours   = '0'+hours;}
            if (minutes < 10) {minutes = '0'+minutes;}
            if (seconds < 10) {seconds = '0'+seconds;}
            
            returnString += hours+':'+minutes+':'+seconds;
            
            originalTimer--;
            
            $(this).attr('attr-time', originalTimer);
            $(this).html(returnString);
        });
    },
    likeInProgress: 0, //local var for bottom function
    like: function(element) {
        if (this.likeInProgress == 1) {
            return false;
        }

        var newsId = parseInt($(this).attr('attr-news-id'));
        this.likeInProgress = 1;
	
        var query = {
            type: 'POST',
            timeout: 10000,
            data: {
                ajax: 'newsVote',
                id: parseInt($(this).attr('attr-news-id'))
            },
            success: function(data) {
                likeInProgress = 0;
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
                    this.formInProgress = 0;
                    $(element).removeClass('loading');
                    $('.contact-form #error p').html(answer[1]);
                    $('.contact-form #error').slideDown('fast');
                }
                
                return false;
            },
            error: function(data) {
                this.formInProgress = 0;
                $(element).removeClass('loading');
            }
        };
        this.ajax(query);
    },
    statusCheck: function() {
		$('#fightStatus').removeClass('online');
		$('#lostBattle').addClass('inactive');
		var query = {
			type: 'POST',
			data: {
				ajax: 'statusCheck',
			},
			success: function(answer) {
				answer = answer.split(';');
				if (answer[2] == 'online') {
					$('#fightStatus').addClass('online');
					$('#lostBattle').removeClass('inactive');
				}
				$('#fightStatus').html(answer[2]);
			}
		}
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
        }
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

setInterval(PC.updateTimers, 1000);