<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">League of Legends <?=$this->server?> <?=t('tournament_list')?></h1>
        </div>
		
		<? if ($this->tournamentData) {
            foreach($this->tournamentData as $v) { ?>
        <a class="block-content <?=(strtolower($v['status'])=='ended'?'ended-tournament':'active-tournament')?>" href="<?=_cfg('href')?>/leagueoflegends/<?=$this->server?>/<?=$v['name']?>">
            <div class="left-part">
                <div class="title"><?=t('tournament')?> #<?=$v['name']?></div>
                <div class="participant_count"><?=(isset($v['teamsCount'])?$v['teamsCount']:0)?> <?=t('of')?> 128 <?=t('participants')?></div>
            </div>
            
            <div class="right-part">
                <div class="status"><?=(strtolower($v['status'])=='ended'?t('ended'):t('active'))?></div>
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
            <h1 class="bordered"><?=t('tournament_rules')?></h1>
        </div>

        <div class="block-content tournament-rules">
			<?=t('lol_tournament_rules')?>
        </div>
    </div>
</div>