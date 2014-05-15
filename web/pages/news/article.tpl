<section class="container page article">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=$this->news->title?> | News</h1>
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
                    <p>NO IMAGE</p>
                <? } ?>
            </div>
        	<div class="text"><?=$this->news->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				by <a href="<?=_cfg('href')?>/team/#<?=$this->news->login?>"><?=$this->news->login?></a>, 
				<span id="news-like-<?=$this->news->id?>"><?=$this->news->likes?></span> Likes,
                <span><?=$this->news->views?></span> Views, 
				<span>0</span> Comments
			</div>
        	<div class="clear"></div>
        </div>
        <div class="block-divider"></div>
        <div class="comments">
        	<h2>Leave a comment</h2>
            <div class="fb-comments" data-href="http://www.pcesports.com/en/news/<?=$this->news->id?>" data-width="100%" data-numposts="5" data-colorscheme="light"></div>
        	<!-- <div class="disabled">Disabled</div> -->
        </div>
    </div>
</div>