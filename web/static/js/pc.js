var PC = {
    //global insides
    site: g.site,
    formInProgress: 0, //used when required to check if form is still in progress
    //socket: io(g.site+':3000'),
    
    //functions
    socketInitiate: function() {
        if (g.participant === undefined) {
            return false;
        }

        /*var authData = {
            id: g.participant.id,
            link: g.participant.link
        };

        PC.socket.emit('handshake', authData);

        PC.socket.on('fightStatus', function(answer){
            console.log(answer);
            $('#fightStatus').removeClass('online').removeClass('red');
            answer = answer.split(';');
            if (answer[2] == 'online') {
                $('#fightStatus').addClass('online');
            }
            if (answer[0] == 2) {
                $('#fightStatus').addClass('red');
            }
            $('#fightStatus').html(answer[2]);
        });*/
    },
    checkAchievements: function() {
        document.getElementById('achievement-ping').volume = 0.2;
        var query = {
            type: 'POST',
            data: {
                ajax: 'checkAchievements'
            },
            success: function(data) {
                if (data) {
                    data = $.parseJSON(data);
                    if (data.image) {
                        $('.achievements').find('.image').html('<img src="'+data.image+'" />');
                    }
                    $('.achievements').find('.points').html(data.points);
                    $('.achievements').find('.name').html(data.name);
                    $('.achievements').find('.text').html(data.description);
                    $('.achievements').show();
                    $('.achievements').css('opacity', 1);

                    document.getElementById('achievement-ping').play();

                    $('.achievementsPoints').text(parseInt($('.achievementsPoints').text())+parseInt(data.points));

                    setTimeout(function () { $('.achievements').trigger('click'); }, 20000); //hide in 20 sec
                }
            },
            //Empty error message required
            error: function() {}
        };
        this.ajax(query);
    },
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
                ajax: 'boardSubmit',
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
                ajax: 'boardSubmit',
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
                ajax: 'boardSubmit',
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
                ajax: 'boardSubmit',
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
                ajax: 'summonerVerify',
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
                ajax: 'summonerRemove',
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
                ajax: 'summonerAdd',
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
        this.formInProgress = 1;
        $(element).closest('.streamer').addClass('alpha');
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'streamerEdit',
                id: streamerId
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
                ajax: 'streamerRemove',
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
                ajax: 'streamerSubmit',
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
        //If content block is too big, we lowering it height to normal window view and add scrollbar
        if ($('#'+name).height() > $(window).height()) {
            var minus = $(window).height() * 0.1;
            $('#'+name).height(parseInt($(window).height()) - minus);
            $('#'+name).css('overflow-y', 'scroll');
        }
        else {
            $('#'+name).height('auto');
            $('#'+name).css('overflow-y', 'auto');
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
        $('.popup:visible').stop().animate({top: -$('.popup:visible').height()}, function() {
            $('.popup:visible').attr('style', null);
        });
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
    addParticipant: function(game) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('#da-form .form-item').removeClass('error success');
        $('#da-form .form-item .message').hide();
        $('#register-in-tournament').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'registerIn'+game,
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#register-in-tournament').removeClass('alpha');
                PC.formInProgress = 0;
                
                if (answer.ok == 1) {
                    $('#register-url a').trigger('click');
                    $('#register-in-tournament').fadeOut();
                    $('#da-form').slideUp(1000, function() {
                        $('.reg-completed').slideDown(1000);
                    });
                }
                else if (answer.ok == 2) {
                    var link = '';
                    if (game == 'HS') {
                        link = 'hearthstone/s2/participant/';
                    }
                    location.href = g.site+'/en/'+link;
                }
                else {
                    $.each(answer.err, function(k, v) {
                        answ = v.split(';');
                        $('[data-label="'+k+'"] .message').html(answ[1]);
                        $('[data-label="'+k+'"] .message').show();
                        if (answ[0] == 1) {
                            $('[data-label="'+k+'"]').addClass('success');
                        }
                        else {
                            $('[data-label="'+k+'"]').addClass('error');
                        }
                    });
                }
            },
            error: function() {
                $('#register-in-tournament').removeClass('alpha');
                PC.formInProgress = 0;
                
                alert('Something went wrong... Contact admin at info@pcesports.com');
            }
        };
        this.ajax(query);
    },
    editParticipant: function(game) {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('.team-edit-completed').hide();
        $('#da-form .form-item').removeClass('error success');
        $('#da-form .form-item .message').hide();
        $('#edit-in-tournament').addClass('alpha');
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: 'editIn'+game,
                form: $('#da-form').serialize()
            },
            success: function(answer) {
                $('#edit-in-tournament').removeClass('alpha');
                PC.formInProgress = 0;
                
                $.each(answer.err, function(k, v) {
                    answ = v.split(';');
                    $('[data-label="'+k+'"] .message').html(answ[1]);
                    $('[data-label="'+k+'"] .message').show();
                    if (answ[0] == 1) {
                        $('[data-label="'+k+'"]').addClass('success');
                    }
                    else {
                        $('[data-label="'+k+'"]').addClass('error');
                    }
                });
                
                if (answer.ok == 1) {
                    $('.team-edit-completed').slideDown(1000);
                }
            },
            error: function() {
                $('#edit-in-tournament').removeClass('alpha');
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
            }
        };
        this.ajax(query);
    },
    updateEmail: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('.update-email #error').hide();
        $('.update-email #success').hide();
        $('#updateEmail').addClass('alpha');
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'updateEmail',
                form: $('.update-email').serialize()
            },
            success: function(answer) {
                $('#updateEmail').removeClass('alpha');
                PC.formInProgress = 0;
                data = answer.split(';');
                
                if (data[0] != 1) {
                    $('.update-email #error p').text(data[1]);
                    $('.update-email #error').slideDown(1000);
                }
                else {
                    $('.update-email #success p').text(data[1]);
                    $('.update-email #success').slideDown(1000);
                    $('.update-email input[type="password"]').val();
                }
            }
        };
        this.ajax(query);
    },
    updatePassword: function() {
        if (this.formInProgress == 1) {
            return false;
        }
        
        this.formInProgress = 1;
        $('.update-password #error').hide();
        $('.update-password #success').hide();
        $('#updatePassword').addClass('alpha');
        
        var query = {
            type: 'POST',
            data: {
                ajax: 'updatePassword',
                form: $('.update-password').serialize()
            },
            success: function(answer) {
                $('#updatePassword').removeClass('alpha');
                PC.formInProgress = 0;
                data = answer.split(';');
                
                if (data[0] != 1) {
                    $('.update-password #error p').text(data[1]);
                    $('.update-password #error').slideDown(1000);
                }
                else {
                    $('.update-password #success p').text(data[1]);
                    $('.update-password #success').slideDown(1000);
                    $('.update-password input[type="password"]').val();
                }
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
                alert('Something went wrong... Contact admin at info@pcesports.com');
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
    },
    //prototype for cookie reading
    cookie: function(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
};