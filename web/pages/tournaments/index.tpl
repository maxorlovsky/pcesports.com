<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('tournament_list')?></h1>
        </div>
        
		<? if ($this->tournamentData) {
            foreach($this->tournamentData as $v) { ?>
        <a class="block-content <?=(strtolower($v['status'])=='ended'?'ended-tournament':'active-tournament')?>" href="<?=_cfg('href')?>/<?=$v['link']?>">
            <div class="left-part">
                <div class="title"><img src="<?=_cfg('img')?>/<?=$v['game']?>-logo-small.png" /><?=$v['server']?> <?=t('tournament')?> #<?=$v['name']?></div>
                <div class="participant_count">Max: <?=$v['max_num']?> <?=t('participants')?></div>
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
</div>