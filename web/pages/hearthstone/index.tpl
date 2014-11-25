<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Hearthstone League <?=$this->server?> <?=t('tournament_list')?></h1>
        </div>
        
		<? if ($this->tournamentData) {
            foreach($this->tournamentData as $v) { ?>
        <a class="block-content <?=(strtolower($v['status'])=='ended'?'ended-tournament':'active-tournament')?>" href="<?=_cfg('href')?>/hearthstone/<?=$v['name']?>">
            <div class="left-part">
                <div class="title"><img src="<?=_cfg('img')?>/hs-logo-small.png" />Season 1 - <?=t('tournament')?> #<?=$v['name']?></div>
                <div class="participant_count"><?=(isset($v['teamsCount'])?$v['teamsCount']:0)?> <?=t('of')?> <?=$v['max_num']?> <?=t('participants')?></div>
            </div>
            
            <div class="right-part">
                <div class="status"><?=$v['status']?></div>
                <div class="event-date"><?=t('event_date')?>: <?=$v['dates_start']?></div>
                <div class="event-date"><?=t('prize_pool')?>: <?=$v['prize']?></div>
            </div>
            
            <div class="mid-part">
                <div class="clear"></div>
            </div>
        </a>
        <?
            }
        }
        else {
            ?>
            <div class="block-content">
                <?=t('no_tournaments_registered')?>
            </div><?
        }
        ?>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('season_stats')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
            <?=t('hl_stats_1')?>
        </div>
    </div>
    
    <div class="block">
        <a name="rules"></a>
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('global_tournament_rules')?></h1>
        </div>

        <div class="block-content tournament-rules">
			<?=t('hearthstone_tournament_rules')?>
        </div>
    </div>
</div>