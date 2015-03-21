<section class="container page news">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('blog')?></h1>
        </div>
        <?
		if ($this->news) {
        	foreach($this->news as $v) {
        ?>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($v->added)+$this->data->user->timezone)?><br /><?=date('d', strtotime($v->added)+$this->data->user->timezone)?></div>
        		<a class="like" href="javascript:void(0);" attr-news-id="<?=$v->id?>">
        			<div class="placeholder">
        				<div class="like-icon <?=($v->active?'active':null)?>"></div>
					</div>
        		</a>
        	</div>
        	<a href="<?=_cfg('href')?>/news/<?=$v->id?>" class="image-holder">
                <? if ($v->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$v->id?>.<?=$v->extension?>" />
                <? } ?>
            </a>
        	<a href="<?=_cfg('href')?>/news/<?=$v->id?>" class="title"><?=$v->title?></a>
        	<div class="text"><?=$v->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				<?=t('added_by')?> <a href="<?=_cfg('href')?>/member/<?=$v->login?>"><?=$v->login?></a>, 
				<span id="news-like-<?=$v->id?>"><?=$v->likes?></span> <?=t('likes')?>, 
                <span><?=$v->views?></span> <?=t('views')?>,
                <span><?=$v->comments?></span> <?=t('comments')?>
			</div>
        	<a class="button" href="<?=_cfg('href')?>/news/<?=$v->id?>"><?=t('read_more')?></a>
        	<div class="clear"></div>
        </div>
        <?
        	}
        }
        ?>
        
        <?=$this->pages->html?>
    </div>
</div>