<div class="hidden popup" id="rules-window">
    <div class="rules-inside">
        <h1>League of legends rules</h1>
        <?=t('lol_tournament_rules')?>
    </div>
</div>

<section class="container page tournament lol <?=$this->server?>">

<div class="left-containers">
	<? if ($this->data->settings['tournament-reg-lol-'.$this->server] == 1 && $this->pickedTournament == $this->currentTournament) { ?>
	<div class="block registration">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('sign_up')?></h1>
		</div>
		
		<div class="block-content signup battlefy-embed">
			<iframe src="https://battlefy.com/embeds/join/<?=$tournamentRow->battlefyId?>" title="Pentaclick test tournament" width="186" height="50" scrolling="no" frameborder="0"></iframe>

            <div class="tournament-rules">
                <h1><?=t('specific_tournament_rules')?></h1>
                <?=str_replace(
                    array('%startTime%', '%registrationTime%', '%checkInTime%', '%prize%'),
                    array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $tournamentRow->prize),
                    t('lol_'.$this->server.'_tournament_information')
                )?>

                <? if ($this->eventId) { ?>
                    <p><?=t('eventpage_link_text')?>: <a href="http://events.<?=$this->server?>.leagueoflegends.com/en/events/<?=$this->eventId?>" target="_blank">http://events.<?=$this->server?>.leagueoflegends.com/en/events/<?=$this->eventId?></a></p>
                <? } ?>
                    
                <div>
                    <a href="javascript:;" class="rules"><?=t('global_tournament_rules')?></a>
                </div>
            </div>

            <div class="clear"></div>

		</div>
	</div>

	<? } else { ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
			<h1><?=t('specific_tournament_rules')?></h1>
			<?=str_replace(
                array('%startTime%', '%registrationTime%', '%checkInTime%', '%eventPage%', '%prize%'),
                array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $this->eventPage, $tournamentRow->prize),
                t('lol_'.$this->server.'_tournament_information')
            )?>
            
            <? if ($this->eventId) { ?>
                <p><?=t('eventpage_link_text')?>: <a href="http://events.<?=$this->server?>.leagueoflegends.com/en/events/<?=$this->eventId?>" target="_blank">http://events.<?=$this->server?>.leagueoflegends.com/en/events/<?=$this->eventId?></a></p>
            <? } ?>

            <div>
                <a href="javascript:;" class="rules"><?=t('global_tournament_rules')?></a>
            </div>
        </div>
    </div>
    <? } ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content battlefy-embed">
        	<iframe src="https://battlefy.com/embeds/teams/<?=$tournamentRow->battlefyId?>" title="Battlefy Tournament Teams" width="100%" height="500" scrolling="yes" frameborder="0"></iframe>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('bracket')?></h1>
        </div>

        <div class="block-content battlefy-embed">
        	<iframe src="https://battlefy.com/embeds/<?=$tournamentRow->battlefyId?>/stage/<?=$tournamentRow->battlefyStage?>" title="Battlefy Tournament" width="100%" height="500" scrolling="yes" frameborder="0"></iframe>
        </div>
    </div>
</div>