<section class="container page lol">

<div class="left-containers">
    <? if ($verified != 1) { ?>
		<p class="error-add"><?=t('participation_hsleague_not_verified')?></p>
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
            <?=t('lan_participant_information_txt')?>
        </div>
    </div>
    
    <div class="block">
        <a name="verification"></a>
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('verification')?></h1>
        </div>
        
        <div class="block-content">
            <?=t('lan_verification_txt')?>
        </div>
    </div>
</div>