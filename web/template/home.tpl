<section class="container page home">

<div class="left-containers">
    <div class="block promo">
        <ul class="bx-wrapper">
            <li><a href="#"><img src="<?=_cfg('img')?>/poster-lol.jpg" /></a></li>
            <li><a href="#"><img src="<?=_cfg('img')?>/poster-hs.png" /></a></li>
            <li><a href="#"><img src="<?=_cfg('img')?>/poster-teemo-hat.jpg" /></a></li>
        </ul>
    </div>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">News</h1>
        </div>
        <? if ($this->data->news->top) { ?>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($this->data->news->top->added))?><br /><?=date('d', strtotime($this->data->news->top->added))?></div>
        		<a class="like" href="#">
        			<div class="placeholder">
        				<div class="like-icon"></div><span>0</span>
					</div>
        		</a>
        	</div>
        	<a href="#" class="image-holder">
                <? if ($this->data->news->top->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$this->data->news->top->id?>.<?=$this->data->news->top->extension?>" />
                <? } else { ?>
                    <p>NO IMAGE</p>
                <? } ?>
            </a>
        	<a href="#" class="title"><?=$this->data->news->top->title?></a>
        	<p class="text"><?=$this->data->news->top->value?></p>
        </div>
        <? } ?>
        
        <div class="block-content news">
            <?
            if ($this->data->news->others) {
                foreach($this->data->news->others as $v) {
            ?>
            <div class="small-block">
                <div class="image-holder">
                    <? if ($v->extension) { ?>
                        <a href="#"><img src="<?=_cfg('imgu')?>/news/small-<?=$v->id?>.<?=$v->extension?>" /></a>
                    <? } else { ?>
                        <a href="#"><p>NO IMAGE</p></a>
                    <? } ?>
                </div>
                <a href="#" class="title"><?=$v->title?></a>
                <div class="info">
                    <div class="dates">5 days ago</div>
                    <a href="#" class="comments">0</a>
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