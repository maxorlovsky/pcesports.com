<section class="container page members">

<div class="left-containers">
    <!--<div class="block-content member">
            <? if ($this->member->battletag) { ?>
            <p><label>Battle Tag</label> <?=$this->member->battletag?></p>
            <? } ?>
            <p><label><?=t('registration_date')?></label> <?=date('d.m.Y', strtotime($this->member->registration_date))?></p>
            <div class="clear"></div>
        </div>-->
    
    <div class="block users-list">
        <div class="block-header-wrapper">
            <h1><?=t('users_list')?></h1>
        </div>
        <div class="headers">
            <label class="cell1"><?=t('name')?></label>
            <label class="cell2"><?=t('summoner_account')?></label>
            <label class="cell3"><?=t('battle_tag')?></label>
            <!--<label class="cell4"><?=t('cups_won')?></label>-->
            <label class="cell5"><?=t('registration_date')?></label>
        </div>
        <?
        if ($this->users) {
            foreach ($this->users as $v) {
        ?>
            <a href="<?=_cfg('href')?>/member/<?=$v->name?>" class="block-content" title="<?=$v->name?>">
                <div class="avatar">
                    <img src="<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg" />
                </div>
                <label class="user-name cell1"><?=$v->name?></label>
                <label class="user-summoner cell2"><?=$v->summoner?></label>
                <label class="user-battle-tag cell3"><?=$v->battletag?></label>
                <!--<label class="user-tournaments cell4"><?=$v->name?></label>-->
                <span href="javascript:void(0);" class="date cell5"><?=date('d.m.Y', strtotime($v->registration_date))?></span>
                <div class="clear"></div>
            </a>
        <?
            }
        }
        ?>
        
        <?=$this->pages->html?>
    </div>
</div>