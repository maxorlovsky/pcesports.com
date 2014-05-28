<section class="container page lol">

<div class="left-containers">
    <? if (t('hearthstone_tournament_vod_'.$this->pickedTournament) != 'hearthstone_tournament_vod_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('broadcast_vod')?>Broadcast/VOD</h1>
        </div>
        
        <div class="block-content vods">
            <iframe width="750" height="505" src="//www.youtube.com/embed/<?=t('hearthstone_tournament_vod_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>
    <!--2 ql1NdcqTTEk?list=PLhzcprkTx_-ZA0VZ_fqzNFFg4PW3g8JGy-->

    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
            <?=t('hearthstone_tournament_information_'.$this->pickedTournament)?>
            <p>Games starts <strong>1 march 2014</strong>, 10:00 GMT-0</p>
            <p>Prize (1st place) – 15€</p>
            <p>Prize (2nd place) – 10€</p>
            <p>Prize (3rd place) – 5€</p>
            <p>Prize money will be sent via Paypal</p>
            <a href="<?=_cfg('href')?>/hearthstone"><?=t('global_tournament_rules')?></a>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?>Participants</h1>
        </div>

        <div class="block-content participants">
            <?
            if ($this->participants) {
                $i = 1;
                foreach($this->participants as $v) {
            ?>
                <div class="block" title="<?=$v->name?> #<?=$i?>">
                    <div class="team-name" title="<?=$v->name?>"><?=strlen($v->name) > 14?substr($v->name,0,13).'...':$v->name?></div>
                    <span class="team-num">#<?=$i?></span>
                    <div class="clear"></div>
                </div>
            <?
                ++$i;
                }
            }
            else {
                ?><p class="empty-list"><?=t('no_players_registered')?>No players registered</p><?
            }
            ?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?>Brackets</h1>
        </div>

        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
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
            
            alert('Something went wrong... Contact admin at info@pcesports.com');
        }
    }
    ajax(query);
});

//challongeHeight
//550 <24
//950 >24

var participantsNumber = <?=count($this->participants) - 1?>;

if (participantsNumber > 24) {
    challongeHeight = 950;
}
else {
    challongeHeight = 550;
}

if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    $('#challonge').challonge('hs<?=$this->pickedTournament?>', {
        subdomain: 'pentaclick',
        theme: '1',
        multiplier: '1.0',
        match_width_multiplier: '0.7',
        show_final_results: '0',
        show_standings: '0',
        overflow: '0'
    });
}
</script>