<section class="container page lol">

<div class="left-containers">
	<? if ($this->data->settings['tournament-reg-hslan'] == 1 && !isset($_SESSION['participant']) && $_SESSION['participant']->game != 'hslan') { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('signin_league')?></h1>
		</div>
		
		<div class="block-content">
            <p class="info-add"><?=t('this_is_lan_event')?></p>
			<p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="battletag" placeholder="<?=t('battle_tag')?>*" />
					<div id="battletag-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="email" placeholder="Email*" />
					<div id="email-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <input type="text" name="phone" placeholder="<?=t('phone_number')?>" />
					<div id="phone-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <? for ($i=1;$i<=4;++$i) { ?>
                    <select class="hero<?=$i?>" name="hero<?=$i?>">
                        <option value="0"><?=t('pick_hero')?></option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>"><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div id="hero<?=$i?>-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <? } ?>
                    
                    <div class="heroes-images">
                        <h6><?=t('your_classes')?></h6>
                        <? for ($i=1;$i<=4;++$i) { ?>
                        <div id="hero<?=$i?>img" class="hsicons" attr-picked=""></div>
                        <? } ?>
                    </div>
                    <div class="clear"></div>
                    
                    <input type="checkbox" name="agree" id="agree" /><label for="agree"><?=t('agree_with_rules_hslan')?></label>
					<div id="agree-msg" class="message hidden"></div>
                    <div class="clear"></div>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="add-player-lan"><?=t('join_tournament')?></a>
			</div>
		</div>
	</div>
	<? } ?>
    
    <?/*<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('tournament_rules')?></h1>
        </div>
        
        <div class="block-content">
            <?=t('hearthstone_lan_tournament_information')?>
        </div>
    </div>*/?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('schedule')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
            <?=t('hearthstone_lan_schedule_information_'.$this->pickedTournament)?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content groups">
            <?
            $j = 0;
            foreach($this->groups as $k => $v) {
            ?>
                <div class="group">
                    <div class="header">
                        <h3><?=t('group')?> <?=$v?></h3>
                        <span class="place"><?=t('place_in_group')?></span>
                        <div class="clear"></div>
                    </div>
                    <div class="group-list">
                        <?
                        if ($this->participants) {
                            $i = 1;
                            foreach($this->participants as $p) {
                                if ($p->seed_number == $k && $p->approved == 1) {
                                    $wonClass = '';
                                    if ( ($this->pickedTournament == 1 && isset($p->contact_info->place) && $p->contact_info->place == 1) ||
                                         ($this->pickedTournament >= 2 && isset($p->contact_info->place) && $p->contact_info->place >= 2)
                                       ){
                                        $wonClass = 'player-won';
                                    }
                                ?>
                                <div class="holder <?=$wonClass?>">
                                    <span class="player-num"><?=$i?></span>
                                    <span class="player-name"><?=$p->name?></span>
                                    <span class="player-score">
                                        <?=(isset($p->contact_info->place)&&$p->contact_info->place?$p->contact_info->place:0)?>
                                    </span>
                                    <div class="clear"></div>
                                </div>
                                <?
                                ++$i;
                                }
                            }
                            
                            if ($i == 1) {
                                ?><div class="holder empty"><?=t('group_empty')?></div><?
                            }
                        }
                        else {
                            ?><div class="holder empty"><?=t('group_empty')?></div><?
                        }
                        ?>
                    </div>
                </div>
            <?
                if ($j%2 == 1) {
                    echo '<div class="clear"></div>';
                }
                
                ++$j;
            }
            ?>
			<div class="clear"></div>
        </div>
    </div>
    
    <? if ($this->participants) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('pending_participants')?></h1>
        </div>
        
        <div class="block-content participants isotope-participants">
        <?
        $participantsCount = 0;
        foreach($this->participants as $v) {
            if ($v->approved == 0) {
            ++$participantsCount;
    ?>
        <div class="block" title="<?=$v->name?> #<?=$participantsCount?>">
            <div class="team-name" title="<?=$v->name?>"><?=strlen($v->name) > 14?substr($v->name,0,13).'...':$v->name?></div>
            <span class="team-num">#<?=$participantsCount?></span>
            <div class="clear"></div>
        </div>
    <?
            }
        }
    ?>
        </div>
    </div>
    <? } ?>
    
    <? if ($this->participants && isset($participantsCount) && $participantsCount >= 2) { ?>
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>

        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
    </div>
	<? } ?>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
var heroes = {
    1: 0,
    2: 0,
    3: 0,
    4: 0    
};

$('.hero1, .hero2, .hero3, .hero4').on('change keyup', function() {
    var getClass = $(this).attr('class');
    var name = $(this).find(':selected').text();
    var id = $(this).find(':selected').val();
    var num = getClass.substr(-1);
    
    var picked = $('#'+getClass+'img').attr('attr-picked');
    $('#'+getClass+'img').removeClass(picked);
    
    if (id == 0) {
        $('#'+getClass+'img').removeClass('active');
    }
    else {
        $('#'+getClass+'img').addClass(name.toLowerCase());
        $('#'+getClass+'img').addClass('active');
        $('#'+getClass+'img').attr('attr-picked', name.toLowerCase());
    }
    
    heroes[num] = id;
    
    $.each($('#da-form select'), function(k, v) {
        $(this).find('option').attr('disabled', false);
    });
    
    $.each(heroes, function(k, v) {
        if (v != 0) {
            for (i=1;i<=4;++i) {
                if (i != k) {
                    $('.hero'+i).find('option[value="'+v+'"]').attr('disabled', true);
                }
            }
        }
    });
});

$('#add-player-lan').on('click', function() {
    PC.addLanPlayer();
});

challongeHeight = 550;

if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    $('#challonge').challonge('hl1_<?=$this->pickedTournament?>', {
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