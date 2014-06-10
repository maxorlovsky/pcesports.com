<section class="container page article">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=$this->news->title?> | <?=t('news')?></h1>
        </div>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($this->news->added))?><br /><?=date('d', strtotime($this->news->added))?></div>
        		<a class="like" href="javascript:void(0);" attr-news-id="<?=$this->news->id?>">
        			<div class="placeholder">
        				<div class="like-icon <?=($this->news->active?'active':null)?>"></div>
					</div>
        		</a>
        	</div>
        	<div class="image-holder">
                <? if ($this->news->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$this->news->id?>.<?=$this->news->extension?>" />
                <? } else { ?>
                    <p><?=t('no_image')?></p>
                <? } ?>
            </div>
        	<div class="text"><?=$this->news->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				<?=t('added_by')?> <a href="<?=_cfg('href')?>/team/#<?=$this->news->login?>"><?=$this->news->login?></a>, 
				<span id="news-like-<?=$this->news->id?>"><?=$this->news->likes?></span> <?=t('likes')?>,
                <span><?=$this->news->views?></span> <?=t('views')?>, 
				<div class="fb-comments-count" data-href="<?=_cfg('site')?>/en/news/<?=$this->news->id?>">0</div> <?=t('comments')?>
			</div>
        	<div class="clear"></div>
        </div>
        <div class="block-divider"></div>
        <div class="comments">
        	<h2><?=t('leave_comment')?></h2>
            <div class="fb-comments" data-href="<?=_cfg('site')?>/en/news/<?=$this->news->id?>" data-width="100%" data-numposts="5" data-colorscheme="light"></div>
        	<!-- <div class="disabled">Disabled</div> -->
        </div>
    </div>
</div>