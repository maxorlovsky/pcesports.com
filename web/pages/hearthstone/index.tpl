<section class="container page tournament hs">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Hearthstone <?=$this->server?> <?=t('tournament_list')?></h1>
        </div>
        
		<? if ($this->tournamentData) {
            foreach($this->tournamentData as $v) { ?>
        <a class="block-content tournament-list <?=(strtolower($v['status'])=='ended'?'ended-tournament':'active-tournament')?>" href="<?=_cfg('href')?>/hearthstone/<?=$this->server?>/<?=$v['name']?>">
            <div class="left-part">
                <div class="title"><?=t('tournament')?> #<?=$v['name']?></div>
                <div class="participant_count"><?=(isset($v['teamsCount'])?$v['teamsCount']:0)?> <?=t('of')?> <span class="<?=($v['teamsCount']>=$v['max_num']?'red':null)?>"><?=$v['max_num']?></span> <?=t('participants')?></div>
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
        <a name="stats"></a>
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
    
    <div class="block">
        <a name="rules"></a>
        <div class="block-header-wrapper">
            <h1 class="bordered">Season legacy</h1>
        </div>

        <div class="block-content tournament-rules">
			<a href="<?=_cfg('href')?>/hearthstone/s1">Season 1 stats</a>
        </div>
    </div>
</div>