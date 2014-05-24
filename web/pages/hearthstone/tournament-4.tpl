<section class="container page lol">

<div class="left-containers">

	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Broadcast/VOD</h1>
        </div>
        
        <div class="block-content vods">
            Not yet available
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Information</h1>
        </div>
        
        <div class="block-content tournament-rules">
        	<p>Registration opens <strong>15 may 2014</strong>, 10:00 GMT-0</p>
            <p>Games starts <strong>24 may 2014</strong>, 10:00 GMT-0</p>
            <p>Prize (1st place) – 15€</p>
            <p>Prize (2nd place) – 10€</p>
            <p>Prize (3rd place) – 5€</p>
            <p>Prize money will be sent via Paypal</p>
            <a href="<?=_cfg('href')?>/hearthstone">Global tournament format and rules</a>
        </div>
    </div>
    
<script>

challongeHeight = 950;

$('#add-player').on('click', function() {
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
            ajax: 'registerInHS',
            form: $('#da-form').serialize()
        },
        success: function(answer) {
            $('#add-player').removeClass('alpha');
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
            $('#add-player').removeClass('alpha');
            formInProgress = 0;
            
            alert('Something\'s got wrong... Contact admin at pentaclickesports@gmail.com');
        }
    }
    ajax(query);
});
</script>