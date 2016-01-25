<div class="block">
    <div class="block-header-wrapper">
        <h1 class=""><a href="<?=_cfg('href')?>/boards"><?=t('last_topics_on_boards')?></a></h1>
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
            	<? /*if ($v->comments == 0) { ?>
                	<?=t('submitted')?> 
                <? } else { ?>
                	<?=t('last_reply')?> 
                <? }*/ ?>

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
            </div>
        </div>
        
        <div class="clear"></div>
    </div>
    <?
        }
    }
    ?>
</div>