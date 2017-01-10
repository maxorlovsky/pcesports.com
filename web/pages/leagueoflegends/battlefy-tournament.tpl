<div class="legacy-tournament leagueoflegends">
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
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content battlefy-embed">
        	<iframe src="https://battlefy.com/embeds/teams/<?=$tournamentRow->battlefy_id?>" title="Battlefy Tournament Teams" width="100%" height="500" scrolling="yes" frameborder="0"></iframe>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('bracket')?></h1>
        </div>

        <div class="block-content battlefy-embed">
        	<iframe src="https://battlefy.com/embeds/<?=$tournamentRow->battlefy_id?>/stage/<?=$tournamentRow->battlefy_stage?>" title="Battlefy Tournament" width="100%" height="500" scrolling="yes" frameborder="0"></iframe>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Tournament rules</h1>
        </div>

        <div class="block-content">
            <?=t('lol_tournament_rules')?>
        </div>
    </div>

</div>