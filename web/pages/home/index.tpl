<section class="container page home">

<div class="left-containers">
    <div class="block promo">
        <ul class="bx-wrapper">
        	<? foreach($slider as $v) { ?>
            <li><a href="<?=$v[0]?>"><img src="<?=$v[1]?>" /></a></li>
            <? } ?>
        </ul>
    </div>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">News</h1>
        </div>
        <? if ($news->top) { ?>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($news->top->added))?><br /><?=date('d', strtotime($news->top->added))?></div>
        		<a class="like" href="#">
        			<div class="placeholder">
        				<div class="like-icon"></div><span>0</span>
					</div>
        		</a>
        	</div>
        	<a href="<?=_cfg('href')?>/news/<?=$news->top->id?>" class="image-holder">
                <? if ($news->top->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$news->top->id?>.<?=$news->top->extension?>" />
                <? } else { ?>
                    <p>NO IMAGE</p>
                <? } ?>
            </a>
        	<a href="<?=_cfg('href')?>/news/<?=$news->top->id?>" class="title"><?=$news->top->title?></a>
        	<p class="text"><?=$news->top->value?></p>
        </div>
        <div class="block-content news big-block readmore">
        	<a class="button" href="<?=_cfg('href')?>/news/<?=$news->top->id?>">Read more</a>
        	<div class="clear"></div>
        </div>
        <? } ?>
        
        <div class="block-content news">
            <?
            if ($news->others) {
                foreach($news->others as $v) {
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