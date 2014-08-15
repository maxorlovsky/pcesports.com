<section class="container page lol">

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
        
        <div class="block-content vods">
            <p><?=t('tournament_start_in')?>: <span class="timer" attr-time="<?=intval(1408795200-time())?>" attr-br="0"><img src="<?=_cfg('img')?>/bx_loader.gif" /></span></p>
            <p><?=t('brackets')?>: <a href="http://pentaclick.challonge.com/dreamforge" target="_blank">http://pentaclick.challonge.com/dreamforge</a></p>
            <?=t('lan_participant_information_txt')?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>

        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script>
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
    $('#challonge').challonge('dreamforge', {
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