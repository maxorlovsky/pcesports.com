<section class="container page home lol error">

<div class="left-containers">
    <div class="maintenance" style="margin-bottom: 40px;">
        Upgrade in progress...
        <img src="<?=_cfg('img')?>/maintenance.png" />
    </div>
    
    <div class="block separate">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('latest_blogs')?></h1>
        </div>

        <div class="block-content news">
        <?
        if ($this->blog) {
            foreach($this->blog as $v) {
        ?>
            <div class="small-block">
                <div class="image-holder">
                    <a href="<?=_cfg('href')?>/blog/<?=$v->id?>">
                    <? if ($v->extension) { ?>
                        <? if (_cfg('env') != 'prod') { ?>
                            <img src="http://www.pcesports.com/web/uploads/news/small-<?=$v->id?>.<?=$v->extension?>" />
                        <? } else { ?>
                            <img src="<?=_cfg('imgu')?>/news/small-<?=$v->id?>.<?=$v->extension?>" />
                        <? } ?>
                    <? } else { ?>
                        <p><?=t('no_image')?></p>
                    <? } ?>
                    </a>
                </div>
                <a href="<?=_cfg('href')?>/blog/<?=$v->id?>" class="title"><?=$v->title?></a>
                <div class="info">
                    <div class="dates"><?=date('d M Y', strtotime($v->added))?></div>
                    <a href="<?=_cfg('href')?>/blog/<?=$v->id?>#comments" class="comments hint" attr-msg="<?=t(($v->comments>1?'comments':'comment'))?>"><?=$v->comments?></a>
                    <a href="<?=_cfg('href')?>/blog/<?=$v->id?>" class="views hint" attr-msg="<?=t('views')?>"><?=$v->views?></a>
                    <a href="javascript:void(0);" attr-news-id="<?=$v->id?>" class="like like-icon hint <?=($v->active?'active':null)?>" attr-msg="Like"><?=$v->likes?></a>
                    <div class="clear"></div>
                </div>
            </div>
        <?
            }
        }
        ?>
            <div class="clear"></div>
        </div>
    </div>
</div>