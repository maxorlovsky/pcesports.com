<section class="container page tournament">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('tournament_list')?></h1>
        </div>
        
		<? if ($this->tournamentData) {
            foreach($this->tournamentData as $v) { ?>
        <a class="block-content tournament-list <?=(strtolower($v['status'])=='ended'?'ended-tournament':'active-tournament')?> <?=$v['game']?>" href="<?=_cfg('widget')?>/<?=$this->project?>/<?=$v['link']?>/<?=$v['id']?>">
            <div class="left-part">
                <div class="title"><?=$v['server']?> <?=t('tournament')?> #<?=$v['name']?></div>
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