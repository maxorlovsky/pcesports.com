<section class="container page home">

<div class="left-containers">
    <? if ($this->slider) { ?>
    <div class="block promo">
        <ul class="bx-wrapper">
        	<? foreach($this->slider as $v) { ?>
            <li><a href="<?=$v[0]?>"><img src="<?=$v[1]?>" /></a></li>
            <? } ?>
        </ul>
    </div>
    <? } ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">News</h1>
        </div>
        <? if ($this->news->top) { ?>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($this->news->top->added))?><br /><?=date('d', strtotime($this->news->top->added))?></div>
        		<a class="like" href="javascript:void(0);" attr-news-id="<?=$this->news->top->id?>">
        			<div class="placeholder">
        				<div class="like-icon <?=($this->news->top->active?'active':null)?>"></div>
					</div>
        		</a>
        	</div>
            <? if ($this->news->top->extension) { ?>
                <a href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>">
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$this->news->top->id?>.<?=$this->news->top->extension?>" />
                </a>
            <? } else { ?>
                <a href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>" class="image-holder">
                    <p>NO IMAGE</p>
                </a>
            <? } ?>
        	<a href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>" class="title"><?=$this->news->top->title?></a>
        	<div class="text"><?=$this->news->top->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				by <a href="<?=_cfg('href')?>/team/#<?=$this->news->top->login?>"><?=$this->news->top->login?></a>, 
				<span id="news-like-<?=$this->news->top->id?>"><?=$this->news->top->likes?></span> Likes,
                <span><?=$this->news->top->views?></span> Views, 
				<span class="fb-comments-count" data-href="http://www.pcesports.com/en/news/<?=$this->news->top->id?>">0</span> Comments
			</div>
        	<a class="button" href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>">Read more</a>
        	<div class="clear"></div>
        </div>
        <? } ?>
        
        <div class="block-content news">
            <?
            if ($this->news->others) {
                foreach($this->news->others as $v) {
            ?>
            <div class="small-block">
                <div class="image-holder">
                	<a href="<?=_cfg('href')?>/news/<?=$v->id?>">
                    <? if ($v->extension) { ?>
                        <img src="<?=_cfg('imgu')?>/news/small-<?=$v->id?>.<?=$v->extension?>" />
                    <? } else { ?>
                        <p>NO IMAGE</p>
                    <? } ?>
                    </a>
                </div>
                <a href="<?=_cfg('href')?>/news/<?=$v->id?>" class="title"><?=$v->title?></a>
                <div class="info">
                    <div class="dates"><?=date('d M Y', strtotime($v->added))?></div>
                    <!-- <a href="<?=_cfg('href')?>/news/<?=$v->id?>#comments" class="comments"><?=$v->comments?></a>-->
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