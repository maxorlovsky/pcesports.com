<div class="block">
    <div class="block-header-wrapper">
        <h1 class=""><?=t('last_topics_on_boards')?></h1>
    </div>

    <?
    if ($this->boards) {
        foreach($this->boards as $v) {
    ?>
    <div class="block-content board" attr-id="<?=$v->id?>">
        <div class="voting">
            <div class="arrow top <?=($v->direction=='plus'?'voted':null)?>"></div>
            <div class="count" id="board_vote_<?=$v->id?>"><?=$v->votes?></div>
            <div class="arrow bottom <?=($v->direction=='minus'?'voted':null)?>"></div>
        </div>
        <a class="category" href="<?=_cfg('href')?>/boards/<?=$v->id?>">
            <img src="<?=_cfg('img').'/'.$v->category?>.png" />
        </a>
        <div class="thread">
            <a class="title" href="<?=_cfg('href')?>/boards/<?=$v->id?>"><?=$v->title?></a>
            <div class="clear"></div>
            <div class="date-user-box">
            	<? if ($v->comments == 0) { ?>
                	<?=t('submitted')?> 
                <? } else { ?>
                	<?=t('received_reply')?> 
                <? } ?>

                <?=$v->interval?>

                <?
                if ($v->comments == 0) {
	                if ($v->edited==1 && $v->status != 1) {
	                    echo ' <i>('.t('edited').')</i>';
	                }
	                else if ($v->status == 1) {
	                    echo ' <span class="deleted">('.t('deleted').') </span>';
	                }
                ?> 
	                <?=t('by')?> 
	                <a class="comment-user" href="<?=_cfg('href')?>/member/<?=$v->name?>">
	                    <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$v->avatar?>.jpg" /><?=$v->name?>
	                </a>
                <? } ?>
            </div>
            <div class="actions">
                <a class="comments-list" href="<?=_cfg('href')?>/boards/<?=$v->id?>"><?=$v->comments?> <?=t(($v->comments>1?'comments':'comment'))?></a>
                <!--<a class="share" href="#"><?=t('share')?></a>-->
                <? if ($v->user_id == $this->data->user->id && $v->status != 1) { ?>
                    <a class="edit" href="<?=_cfg('href')?>/boards/submit/<?=$v->id?>"><?=t('edit')?></a>
                    <a class="delete" href="#" attr-msg="<?=t('sure_to_delete_message')?>"><?=t('delete')?></a>
                <? } else if ($v->status != 1) { ?>
                    <a class="report" href="#" attr-msg="<?=t('sure_to_report_message')?>"><?=t('report')?></a>
                <? } ?>
            </div>
        </div>
        
        <div class="clear"></div>
    </div>
    <?
        }
    }
    ?>
</div>