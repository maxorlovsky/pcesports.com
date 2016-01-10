<section class="container page teams">

<div class="left-containers">
    <div class="block teams-list">
        <div class="block-header-wrapper">
            <h1><?=t('teams_list')?></h1>
        </div>
        <div class="headers">
            <label class="cell1"><?=t('name')?></label>
            <label class="cell2"><?=t('members_count')?></label>
            <label class="cell3"><?=t('registration_date')?></label>
        </div>
        <?
        if ($this->teams) {
            foreach ($this->teams as $v) {
        ?>
            <a href="<?=_cfg('href')?>/team/<?=strtolower(urlencode($v->name))?>" class="block-content" title="<?=$v->name?>">
    	        <!-- <div class="avatar">
                    <img src="<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg" />
                </div> -->
                <label class="team-name cell1"><?=$v->name?></label>
                <label class="team-members-count cell2"><?=$v->members?> / 7</label>
                <span href="javascript:void(0);" class="date cell3"><?=date('d.m.Y', strtotime($v->registration_date))?></span>
                <div class="clear"></div>
            </a>
        <?
            }
        }
        ?>
        
        <?=$this->pages->html?>
    </div>
</div>