<section class="container page home">

<div class="left-containers">
    <div class="block promo">
        <ul class="bx-wrapper">
            <li><a href="#"><img src="<?=_cfg('img')?>/poster.png" /></a></li>
            <li><a href="#"><img src="<?=_cfg('img')?>/poster-hs.png" /></a></li>
            <li><a href="#"><img src="<?=_cfg('img')?>/poster-teemo-hat.jpg" /></a></li>
        </ul>
    </div>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">News</h1>
        </div>
        <div class="block-content news">
            <? for($i=0;$i<=5;++$i) { ?>
            <div class="small-block">
                <div class="image-holder"><p>NO IMAGE</p></div>
                <a href="#" class="title">News title News title Newsz title News titlez</a>
                <div class="info">
                    <div class="dates">5 days ago</div>
                    <a href="#" class="comments"><?=rand(1,99)?></a>
                    <div class="clear"></div>
                </div>
            </div>
            <? } ?>
            <div class="clear"></div>
        </div>
    </div>
</div>