<section class="container page lol smite">

<div class="left-containers">
	<? if ($regged == 1) { ?>
		<p class="success-add"><?=t('participation_verified')?></p>
	<? } ?>
    
    <? if (!$this->logged_in) { ?>
        <p class="info-add"><?=t('participant_not_user')?></p>
    <? } else if ($_SESSION['participant']->user_id == 0) { ?>
        <p class="info-add"><?=t('participant_user_not_connected')?></p>
        <div class="connect-team">
            <div class="button" id="connectTeamToAccount"><?=t('connect_team_to_account')?></div>
        </div>
    <? } ?>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content">
            <p><?=t('brackets')?>: <a href="http://pentaclick.challonge.com/smite<?=$this->server?><?=$this->data->settings['smite-current-number-'.$this->server]?>/" target="_blank">http://pentaclick.challonge.com/smite<?=$this->server?><?=$this->data->settings['smite-current-number-'.$this->server]?>/</a></p>
            <?=t('participant_information_txt_smite')?>
        </div>
    </div>
    
    <? if ($this->participantsCount >= 2) { ?>
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

<script>
participantsNumber = <?=$this->participantsCount?>;
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
    $('#challonge').challonge('smite<?=$this->server?><?=$this->currentTournament?>', {
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