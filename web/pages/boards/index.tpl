<section class="container page boards">

<div class="left-containers">
    <div class="block">
        <? if ($this->logged_in) { ?>
            <a class="button submit" href="<?=_cfg('href')?>/boards/submit"><?=t('submit_post')?></a>
        <? } else { ?>
            <a href="javascript:void(0);" class="button submit must-login"><?=t('login_to_post')?></a>
        <? } ?>
        <div class="block-header-wrapper">
            <h1 class=""><?=t('boards')?></h1>
        </div>
        <?
		if ($this->boards) {
        	foreach($this->boards as $v) {
        ?>
        <div class="block-content board">
            <div class="voting" attr-id="<?=$v->id?>">
                <div class="arrow top <?=($v->direction=='plus'?'voted':null)?>"></div>
                <div class="count" id="board_vote_<?=$v->id?>"><?=$v->votes?></div>
                <div class="arrow bottom <?=($v->direction=='minus'?'voted':null)?>"></div>
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
                    <? if ($v->user_id == $this->data->user->id) { ?>
                        <a class="edit" href="#"><?=t('edit')?></a>
                        <a class="delete" href="#"><?=t('delete')?></a>
                    <? } else { ?>
                        <a class="report" href="#"><?=t('report')?></a>
                    <? } ?>
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
            <a href="javascript:void(0);" class="button submit bottom must-login"><?=t('login_to_post')?></a>
        <? } ?>
    </div>
</div>