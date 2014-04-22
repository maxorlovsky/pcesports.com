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
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date">Jun<br />17</div>
        		<a class="like" href="#">
        			<div class="placeholder">
        				<div class="like-icon"></div><span>99</span>
					</div>
        		</a>
        	</div>
        	<a href="#" class="image-holder"><p>NO IMAGE</p></a>
        	<a href="#" class="title">News title News title Newsz title News titlez</a>
        	<p class="text">orem ipsum dolor sit amet, consectetur adipiscing elit. Nam sodales egestas condimentum. Suspendisse posuere enim accumsan massa vehicula cursus. Vivamus vehicula nec mauris quis varius. Duis scelerisque ornare consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut vitae feugiat nisl, quis pretium velit. Suspendisse pellentesque mi in lacus suscipit, non tempus augue tristique...</p>
        </div>
        <div class="block-content news">
            <? for($i=0;$i<=5;++$i) { ?>
            <div class="small-block">
                <div class="image-holder"><a href="#"><p>NO IMAGE</p></a></div>
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