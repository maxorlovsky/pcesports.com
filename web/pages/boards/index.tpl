<section class="container page boards">

<div class="left-containers">
    <div class="block">
        <? if ($this->logged_in) { ?>
            <a class="button submit" href="<?=_cfg('href')?>/boards/submit"><?=t('submit_post')?></a>
        <? } else { ?>
            <a class="button submit must-login" href="javascipt:void(0);"><?=t('login_to_post')?></a>
        <? } ?>
        <div class="block-header-wrapper">
            <h1 class=""><?=t('boards')?></h1>
        </div>
        <?
		if ($this->boards) {
        	foreach($this->boards as $v) {
        ?>
        <div class="block-content board">
            <div class="voting">
                <div class="arrow top"></div>
                <div class="count"><?=$v->votes?></div>
                <div class="arrow bottom"></div>
            </div>
            <a class="category" href="<?=_cfg('href')?>/boards/<?=$v->id?>">
                <? if ($v->category != 'general') { ?>
                <img src="<?=_cfg('img').'/'.$v->category?>.png" />
                <? } ?>
            </a>
            <div class="thread">
                <a class="title" href="<?=_cfg('href')?>/boards/<?=$v->id?>"><?=$v->title?></a>
                <div class="clear"></div>
                <div class="date-user-box">
                    <?=t('submitted')?> <?=$v->interval?> <?=t('by')?> 
                    <a class="comment-user" href="<?=_cfg('href')?>/member/<?=$v->name?>">
                        <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$v->avatar?>.jpg" /><?=$v->name?>
                    </a>
                </div>
                <div class="actions">
                    <a class="comments-list" href="<?=_cfg('href')?>/boards/<?=$v->id?>"><?=$v->comments?> <?=t('comments')?></a>
                    <!--<a class="share" href="#"><?=t('share')?></a>-->
                    <!--<a class="report" href="#"><?=t('report')?></a>-->
                </div>
            </div>
            
            <div class="clear"></div>
        </div>
        <?
        	}
        }
        ?>
        
        <?=$this->pages->html?>
        <? if ($this->logged_in) { ?>
            <a class="button submit bottom" href="<?=_cfg('href')?>/boards/submit"><?=t('submit_post')?></a>
        <? } else { ?>
            <a class="button submit bottom must-login" href="javascipt:void(0);"><?=t('login_to_post')?></a>
        <? } ?>
    </div>
</div>