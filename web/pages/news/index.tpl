<section class="container page news">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('news')?></h1>
        </div>
        <?
		if ($this->news) {
        	foreach($this->news as $v) {
        ?>
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
                    <p><?=t('no_image')?></p>
                <? } ?>
            </a>
        	<a href="<?=_cfg('href')?>/news/<?=$v->id?>" class="title"><?=$v->title?></a>
        	<div class="text"><?=$v->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				<?=t('added_by')?> <a href="<?=_cfg('href')?>/team/#<?=$v->login?>"><?=$v->login?></a>, 
				<span id="news-like-<?=$v->id?>"><?=$v->likes?></span> <?=t('likes')?>, 
                <span><?=$v->views?></span> <?=t('views')?>,
				<div class="fb-comments-count" data-href="<?=_cfg('site')?>/en/news/<?=$v->id?>">0</div> <?=t('comments')?>
			</div>
        	<a class="button" href="<?=_cfg('href')?>/news/<?=$v->id?>"><?=t('read_more')?></a>
        	<div class="clear"></div>
        </div>
        <?
        	}
        }
        ?>
    </div>
</div>