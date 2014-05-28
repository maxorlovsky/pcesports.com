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
            
            alert('Something\'s got wrong... Contact admin at pentaclickesports@gmail.com');
        }
    }
    ajax(query);
});

//challongeHeight
//550 <24
//950 >24

var participantsNumber = <?=$this->participants?>;

if (participantsNumber > 24) {
    challongeHeight = 950;
}
else {
    challongeHeight = 550;
}

if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    $('#challonge').challonge('hs<?=$id?>', {
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