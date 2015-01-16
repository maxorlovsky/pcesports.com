<section class="container page submitBoard">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('submit_post')?></h1>
        </div>
        
        <div class="block-content">
        	<?=t('submit_board_notice')?>
        </div>
        <div class="block-content">
            <div><input type="text" id="title" placeholder="<?=t('title')?>" /></div>
        </div>
        <div class="block-content">
            <h3>Category</h3>
            <div class="categories">
                <div attr-category="general" class="active"></div>
                <? foreach(_cfg('boardGames') as $v) { ?>
                    <div attr-category="<?=$v?>"><img src="<?=_cfg('img')?>/<?=$v?>.png" /></div>
                <? } ?>
            </div>
            <div class="clear"></div>
        </div>
        
        <div class="comments">
            <form class="leave-comment">
                
                <div id="error"><p></p></div>
                
                <div class="formatting">
                    <div class="formbut b" title="Bold"></div>
                    <div class="formbut i" title="Italic"></div>
                    <div class="formbut s" title="Line through"></div>
                    <div class="formbut link" title="Link"></div>
                    <div class="formbut q" title="Quote"></div>
                    <div class="formbut list" title="List"></div>
                    <div class="clear"></div>
                </div>
                <div class="fields">
                    <textarea name="msg" id="msg" placeholder="<?=t('post_text')?>"></textarea>
                </div>
                
                <a href="javascript:void(0);" class="button" id="submitBoard"><?=t('post')?></a>
                
                <input type="hidden" id="module" name="module" value="boards" />
                <input type="hidden" id="category" name="category" value="general" />
                
            </form>
            
        </div>
    </div>
</div>