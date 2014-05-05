<section class="container page article">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=$row->title?> | News</h1>
        </div>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($row->added))?><br /><?=date('d', strtotime($row->added))?></div>
        		<a class="like" href="javascript:void(0);" attr-news-id="<?=$row->id?>">
        			<div class="placeholder">
        				<div class="like-icon <?=($row->active?'active':null)?>"></div>
					</div>
        		</a>
        	</div>
        	<div class="image-holder">
                <? if ($row->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/news/big-<?=$row->id?>.<?=$row->extension?>" />
                <? } else { ?>
                    <p>NO IMAGE</p>
                <? } ?>
            </div>
        	<p class="text"><?=$row->value?></p>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				by <a href="<?=_cfg('href')?>/team/#<?=$row->login?>"><?=$row->login?></a>, 
				<span id="news-like-<?=$row->id?>"><?=$row->likes?></span> Likes, 
				<span>0</span> Comments
			</div>
        	<div class="clear"></div>
        </div>
        <div class="block-divider"></div>
        <div class="comments">
        	<h2>Leave a comment</h2>
        	<div class="disabled">Disabled</div>
        </div>
    </div>
</div>