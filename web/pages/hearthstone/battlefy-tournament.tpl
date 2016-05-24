<div class="hidden popup" id="rules-window">
    <div class="rules-inside">
        <h1>League of legends rules</h1>
        <?=t('lol_tournament_rules')?>
    </div>
</div>

<section class="container page tournament hs <?=$this->server?>">

<div class="left-containers">
    <? if ($tournamentRow->status == 'upcoming') { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
            <div>Tournament registration will be available on <strong><?=$tournamentTime['registration']?></strong> on Battlefy page</div>
        </div>
    </div>
    <? } ?>

	<? if ($tournamentRow->status == 'registration' || $tournamentRow->status == 'check_in') { ?>
	<div class="block registration">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('sign_up')?></h1>
		</div>
		
		<div class="block-content signup battlefy-embed">
			<div id="join-form">
                <a href="https://battlefy.com/pentaclick-esports//<?=$tournamentRow->battlefy_id?>/info" target="_blank" class="button join-battlefy">Sign up on Battlefy page</a>
            </div>

            <div class="tournament-rules">
                <?=str_replace(
                    array('%startTime%', '%registrationTime%', '%checkInTime%', '%prize%'),
                    array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $tournamentRow->prize),
                    t('hs_tournament_information')
                )?>
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
			<?=str_replace(
                array('%startTime%', '%registrationTime%', '%checkInTime%', '%prize%'),
                array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $tournamentRow->prize),
                t('hs_tournament_information')
            )?>
            <div>
                <a href="javascript:;" class="rules"><?=t('global_tournament_rules')?></a>
            </div>
        </div>
    </div>
    <? } ?>
    
    <? if ($tournamentRow->status != 'upcoming') { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content battlefy-embed">
        	<iframe src="https://battlefy.com/embeds/teams/<?=$tournamentRow->battlefy_id?>" title="Battlefy Tournament Teams" width="100%" height="500" scrolling="yes" frameborder="0"></iframe>
        </div>
    </div>
    <? } ?>

    <? if ($tournamentRow->status != 'upcoming' && $tournamentRow->status != 'registration') { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('bracket')?></h1>
        </div>

        <div class="block-content battlefy-embed">
        	<iframe src="https://battlefy.com/embeds/<?=$tournamentRow->battlefy_id?>/stage/<?=$tournamentRow->battlefy_stage?>" title="Battlefy Tournament" width="100%" height="500" scrolling="yes" frameborder="0"></iframe>
        </div>
    </div>
    <? } ?>

</div>