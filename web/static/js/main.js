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

if ($('.ad-holder').height() == 0) {
    $('.ad-blocked').show();
}

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
    submitStreamer: function() {
        $('.streamer-form #error').hide();
        var query = {
            type: 'POST',
            data: {
                ajax: 'submitStreamer',
                form: $('.streamer-form').serialize()
            },
            success: function(data) {
                PC.formInProgress = 0;
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
        PC.formInProgress = 1;
        var query = {
            type: 'POST',
            data: {
                ajax: 'connectTeamToAccount'
            },
            success: function(data) {
                PC.formInProgress = 0;
                answer = data.split(';');
                
                if (answer[0] == 1) {
                    $('.info-add').fadeOut();
                    $('.connect-team').fadeOut();
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
    comment: function() {
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
                if (end == text.length && end != 0) {
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
        
        element.html(returnString);
        
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
		$('#fightStatus').removeClass('online');
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
        tc: function() {
            this.get_token('tc');
            
            return false;
        },
        bn: function() {
            this.get_token('bn');
            
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
                    
                    if (data[0] == 0) {
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
    addTeam: function() {
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
                ajax: 'registerInLOL',
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
        }
        this.ajax(query);
    },
    editPlayerLan: function(element) {
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
                ajax: 'editInLanHS',
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-player-lan').removeClass('alpha');
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
                $('#edit-player-lan').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        }
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
        }
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

if (requireStatus == 1) {
	PC.statusCheck();
	setInterval(function () { PC.statusCheck(); }, 15000);
}

PC.runTimers();