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

	<? if ($this->data->settings['tournament-reg-hs'] == 1 && $this->pickedTournament == $this->currentTournament) { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('join_tournament')?> #<?=$this->currentTournament?></h1>
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
                    <!--<div class="clear"></div>
                    <input type="text" name="twitch" placeholder="Stream channel (full link)" />
					<div id="twitch-msg" class="message hidden"></div>-->
                    
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="add-player"><?=t('join_tournament')?> #<?=$this->currentTournament?></a>
			</div>
		</div>
	</div>
	<? } ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
			<h1><?=t('specific_tournament_rules')?></h1>
            <?=t('hearthstone_tournament_information_'.$this->pickedTournament)?>
            <a href="<?=_cfg('href')?>/hearthstone"><?=t('global_tournament_rules')?></a>
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
                    <div class="team-name" title="<?=$v->name?>"><?=$v->name?></div>
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