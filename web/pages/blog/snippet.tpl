<div class="block">
    <div class="block-header-wrapper">
        <h1 class=""><?=t('latest_comments_in_blog')?></h1>
    </div>

    <?
    if ($this->comments) {
        foreach($this->comments as $v) {
    ?>
    <div class="block-content comments" attr-id="<?=$v->blog_id?>">
        <div class="thread">
            <a class="title" href="<?=_cfg('href')?>/blog/<?=$v->blog_id?>"><?=$v->title?></a>
            <div class="text"><?=$v->text?></div>
            <div class="clear"></div>
            <div class="date-user-box">
            	<?=t('submitted')?> <?=$v->interval?>
                <?
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
            </div>
        </div>
        
        <div class="clear"></div>
    </div>
    <?
        }
    }
    ?>
</div>