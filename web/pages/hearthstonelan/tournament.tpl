<section class="container page lol">

<div class="left-containers">
    <? if (t('hearthstone_tournament_vod_'.$this->pickedTournament) != 'hearthstone_tournament_vod_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('broadcast_vod')?></h1>
        </div>
        
        <div class="block-content vods">
            <iframe width="750" height="505" src="//www.youtube.com/embed/<?=t('hearthstone_tournament_vod_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>

	<? if ($this->data->settings['tournament-reg-hslan'] == 1 /*&& $this->pickedTournament == $this->currentTournament*/) { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('join_tournament')?></h1>
		</div>
		
		<div class="block-content">
			<p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="battletag" placeholder="<?=t('battle_tag')?>*" />
					<div id="battletag-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="email" placeholder="Email*" />
					<div id="email-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <input type="text" name="phone" placeholder="Phone number" />
					<div id="phone-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <select class="hero1">
                        <option value="0">Pick hero</option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>"><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div id="hero1-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <select class="hero2">
                        <option value="0">Pick hero</option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>"><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div id="hero2-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <div class="heroes-images">
                        <h6>Your classes</h6>
                        <div id="hero1img" class="hsicons" attr-picked=""></div>
                        <div id="hero2img" class="hsicons" attr-picked=""></div>
                    </div>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="add-player-lan"><?=t('join_tournament')?></a>
			</div>
		</div>
	</div>
	<? } ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('tournament_rules')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
            <?=t('hearthstone_lan_tournament_information')?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content participants">
            <?
			$participantsCount = 0;
            if ($this->participants) {
                foreach($this->participants as $v) {
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
            else {
                ?><p class="empty-list"><?=t('no_players_registered')?></p><?
            }
            ?>
        </div>
    </div>
	
    <? if ($participantsCount >= 2) { ?>
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
$('.hero1, .hero2').on('change keyup', function() {
    var getClass = $(this).attr('class');
    var name = $(this).find(':selected').text();
    var id = $(this).find(':selected').val();
    
    if (getClass == 'hero1') {
        var picked = $('#hero1img').attr('attr-picked');
        $('#hero1img').removeClass(picked);
        
        if (id == 0) {
            $('#hero1img').removeClass('active');
        }
        else {
            $('#hero1img').addClass(name.toLowerCase());
            $('#hero1img').addClass('active');
            $('#hero1img').attr('attr-picked', name.toLowerCase());
        }
    }
    else {
        var picked = $('#hero2img').attr('attr-picked');
        $('#hero2img').removeClass(picked);
        
        if (id == 0) {
            $('#hero2img').removeClass('active');
        }
        else {
            $('#hero2img').addClass(name.toLowerCase());
            $('#hero2img').addClass('active');
            $('#hero2img').attr('attr-picked', name.toLowerCase());
        }
    }
});

$('#add-player').on('click', function() {
    PC.addPlayer();
});

participantsNumber = <?=$participantsCount?>;
if (participantsNumber > 100) {
    challongeHeight = 3500;
}
else if (participantsNumber > 50) {
    challongeHeight = 1800;
}
else if (participantsNumber > 25) {
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