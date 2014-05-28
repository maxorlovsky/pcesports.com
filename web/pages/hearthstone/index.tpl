<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Hearthstone <?=t('tournament_list')?></h1>
        </div>
        
        <? foreach($this->tournamentData as $k => $v) { ?>
        <a class="block-content <?=(strtolower($v['status'])!='ended'?'active-tournament':'ended-tournament')?>" href="<?=_cfg('href')?>/hearthstone/<?=$k?>">
            <div class="left-part">
                <div class="title"><?=t('tournament')?> #<?=$k?></div>
                <div class="participant_count"><?=(isset($v['teamsCount'])?$v['teamsCount']:0)?> <?=t('of')?> 512 <?=t('participants')?></div>
            </div>
            
            <div class="right-part">
                <div class="status"><?=t(str_replace(' ','_',$v['status']))?></div>
                <div class="event-date"><?=t('event_date')?>: <?=$v['dates']?></div>
                <div class="event-date"><?=t('prize_pool')?>: <?=$v['prize']?></div>
            </div>
            
            <div class="mid-part">
                <? if (strtolower($v['status'])=='ended') { ?>
                    <div><img src="<?=_cfg('img')?>/gold-cup.png" /> <span class="first-place"><?=(isset($v['places'][1])?$v['places'][1]:null)?></span></div>
                    <div><img src="<?=_cfg('img')?>/silver-cup.png" /> <span class="second-place"><?=(isset($v['places'][2])?$v['places'][2]:null)?></span></div>
                    <div><img src="<?=_cfg('img')?>/bronze-cup.png" /> <span class="third-place"><?=(isset($v['places'][3])?$v['places'][3]:null)?></span></div>
                <? } else { ?>
                    <div class="clear"></div>
                <? } ?>
            </div>
        </a>
        <? } ?>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('tournament_rules')?></h1>
        </div>

        <div class="block-content tournament-rules">
            <?=t('hearthstone_tournament_rules')?>
        </div>
    </div>
</div>