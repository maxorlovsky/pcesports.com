$(window).scroll(function() {
    viewport = getViewPort();
    
    if (viewport.top > 24 && !$('#header').hasClass('move')) {
        $('.socicons').animate({marginTop: -20});
        $('#navbar').animate({marginTop: 0});
        $('#navbar .logo').animate({marginTop: 0});
        $('#header').animate({height: 115}).addClass('move');
    }
    else if (viewport.top < 24 && $('#header').hasClass('move')) {
        $('.socicons').animate({marginTop: 2});
        $('#navbar').animate({marginTop: 24});
        $('#navbar .logo').animate({marginTop: -15});
        $('#header').animate({height: 121}).removeClass('move');
    }
    
    $('.globalnav ul li').removeClass('active');
    
    correcTop = viewport.top + 70;
    
    $('.globalnav ul li').each(function(k, v) {
        var element = $(v).find('a').attr('href');
        if ($(element).length != 0 && correcTop >= $(element).offset().top && !$(element+'-url').hasClass('active')) {
            $('.globalnav ul li').removeClass('active');
            $(element+'-url').addClass('active');
        }
    });
    
});

$('.scroll').on('click', this, function(event) {
    event.preventDefault();
    
    //if already scrolling, ignoring clicks
    if ($('html,body').is(':animated')) {
        return false;
    }
    
    if ($('#wrapper').is(':hidden')) {
        //$('.wrapper-single').fadeOut();
        //$('#wrapper').fadeIn();
    }

    //calculate destination place
    var dest=0;
    if($(this.hash).offset().top > $(document).height()-$(window).height()) {
        dest=$(document).height()-$(window).height();
    }
    else {
        dest=$(this.hash).offset().top;
    }
    
    //removing header pixels
    dest = dest - 70;
    
    //go to destination
    $('html,body').animate({scrollTop:dest, queue: false}, 500, 'swing');
});

$('#join-tournament').on('click', function(){
    $(this).slideUp(500, function() {
        $('#join-form').slideDown(1000);
    });
});

$('#participants-content').isotope({
    itemSelector : '.block',
    layoutMode : 'fitRows'
});

$('#participants-content').on('click', '.block', function(e) {
    if(!$(e.target).is('a')){
        $(this).find('.player-list').slideToggle(500, function() {
            $('#participants-content').isotope( 'reLayout' );
        });
    }
});

var formInProgress = 0;
$('#add-team').on('click', function() {
    if (formInProgress == 1) {
        return false;
    }
    
    var errRegistered = 0;
    formInProgress = 1;
    $('#da-form .message').hide();
    $('#da-form .message').removeClass('error success');
    $(this).addClass('alpha');
    
    var query = {
        type: 'POST',
        dataType: 'json',
        data: {
            control: 'registerTeam',
            post: $('#da-form').serialize()
        },
        success: function(answer) {
            $('#add-team').removeClass('alpha');
            formInProgress = 0;
            
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
            formInProgress = 0;
            
            alert('Something\'s got wrong... Contact admin at pentaclickesports@gmail.com');
        }
    }
    ajax(query);
});

function ajax(object) {
    if (!object.url) {
        object.url = 'ajax.php';
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
        object.timeout = 180000;
    }
    if (!object.error) {
        object.error = function(xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            console.log(ajaxOptions);
            console.log(thrownError);
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

function getViewPort() {
    var win = $(window);
    var viewport = {
        top : win.scrollTop(),
        left : win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();
    
    return viewport;
}

$('#header').removeClass('move').addClass('force-move');

$(window).trigger('scroll');