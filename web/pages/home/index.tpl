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
            <h1 class=""><?=t('news')?></h1>
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
                    <p><?=t('no_image')?></p>
                </a>
            <? } ?>
        	<a href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>" class="title"><?=$this->news->top->title?></a>
        	<div class="text"><?=$this->news->top->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				<?=t('added_by')?> <a href="<?=_cfg('href')?>/team/#<?=$this->news->top->login?>"><?=$this->news->top->login?></a>, 
				<span id="news-like-<?=$this->news->top->id?>"><?=$this->news->top->likes?></span> <?=t('likes')?>,
                <span><?=$this->news->top->views?></span> <?=t('views')?>, 
				<div class="fb-comments-count" data-href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>">0</div> <?=t('comments')?>
			</div>
        	<a class="button" href="<?=_cfg('href')?>/news/<?=$this->news->top->id?>"><?=t('read_more')?></a>
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
                        <p><?=t('no_image')?></p>
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