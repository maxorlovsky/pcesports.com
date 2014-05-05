<section class="container page news">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">Pentaclick eSports Team</h1>
        </div>

        <div class="block-content news big-block">
            <div class="add-box">
                <div class="date"><?=date('M', strtotime($v->added))?><br /><?=date('d', strtotime($v->added))?></div>
                <a class="like" href="javascript:void(0);" attr-news-id="<?=$v->id?>">
                    <div class="placeholder">
                        <div class="like-icon <?=($v->active?'active':null)?>"></div>
                    </div>
                </a>
            </div>
            <a href="<?=_cfg('href')?>/news/<?=$v->id?>" class="image-holder">
                <? if ($v->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$v->id?>.<?=$v->extension?>" />
                <? } else { ?>
                    <p>NO IMAGE</p>
                <? } ?>
            </a>
            <a href="<?=_cfg('href')?>/news/<?=$v->id?>" class="title"><?=$v->title?></a>
            <div class="text"><?=$v->value?></div>
        </div>
        
        <div class="block-content news big-block readmore">
            <div class="news-info">
                by <a href="<?=_cfg('href')?>/team/#<?=$v->login?>"><?=$v->login?></a>, 
                <span id="news-like-<?=$v->id?>"><?=$v->likes?></span> Likes, 
                <span>0</span> Comments
            </div>
            <a class="button" href="<?=_cfg('href')?>/news/<?=$v->id?>">Read more</a>
            <div class="clear"></div>
        </div>
    </div>
</div>