<section class="container page members">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=$this->member->name?> | <?=t('profile')?></h1>
        </div>
        <div class="block-content member">
            <div class="avatar">
                <img src="<?=_cfg('img')?>/avatar/<?=$this->member->avatar?>.jpg" />
            </div>
            <div class="information">
                <p><label><?=t('name')?></label> <?=$this->member->name?></p>
                <? if ($this->member->battletag) { ?>
                <p><label>Battle Tag</label> <?=$this->member->battletag?></p>
                <? } ?>
                <p><label><?=t('registration_date')?></label> <?=date('d.m.Y', strtotime($this->member->registration_date))?></p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    
    <? if ($this->member->summoners) { ?>
    <div class="block summoners member-summoners">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('summoners_accounts')?></h1>
        </div>
        <? foreach ($this->member->summoners as $v) { ?>
        <a href="http://<?=$v->region?>.op.gg/summoner/?userName=<?=$v->name?>" target="_blank" class="block-content summoner">
            <? if ($v->league) { ?>
                <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/<?=strtolower($v->league)?>_<?=$this->convertDivision($v->division)?>.png" />
            <? } else { ?>
                <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/unranked.png" />
            <? } ?>
            <label class="summoner-name"><?=$v->name?></label>
            <span href="javascript:void(0);" class="region right"><?=$v->regionName?></span>
            <div class="clear"></div>
        </a>
        <? } ?>
    </div>
    <? } ?>
    
    <? if ($this->member->tournaments) { ?>
    <div class="block member-tournaments">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participated_in_tournaments')?></h1>
        </div>
        <? foreach ($this->member->tournaments as $v) { ?>
        <a href="<?=_cfg('href')?>/<?=$this->fullGameName[$v->game]?>/<?=$v->server?>/<?=$v->tournament_id?>" class="block-content tournament-info place-<?=$v->place?>">
            <img class="game-logo" src="<?=_cfg('img')?>/<?=str_replace('lan', '', $v->game)?>-logo-small.png">
            <label class="tournament-name">
                <?=t($this->fullGameName[$v->game])?> 
                <? if ($v->server) { ?>(<?=strtoupper($v->server)?>)<?}?> 
                #<?=$v->tournament_id?>
            </label> - <?=$v->name?>
            <span class="right place">
                <? if ($v->place>=1 && $v->place<=3) { ?>
                    <img src="<?=_cfg('img')?>/<?=$this->places[$v->place]?>-cup.png" />
                <? } ?>
                <?=$v->place?> place
            </span>
            <div class="clear"></div>
        </a>
        <?//=dump($v)?>
        <? } ?>
    </div>
    <? } ?>
</div>