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
            <div><input type="text" id="title" placeholder="<?=t('title')?>" value="<?=($row->title?$row->title:null)?>" /></div>
        </div>
        <div class="block-content">
            <h3>Category</h3>
            <div class="categories">
                <div attr-category="general" <?=($row->category=='general'?'class="active"':null)?>"></div>
                <? foreach(_cfg('boardGames') as $v) { ?>
                    <div attr-category="<?=$v?>" <?=($row->category==$v?'class="active"':null)?>><img src="<?=_cfg('img')?>/<?=$v?>.png" /></div>
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
                    <textarea name="msg" id="msg" placeholder="<?=t('post_text')?>"><?=($row->text?$row->text:null)?></textarea>
                </div>
                
                <a href="javascript:void(0);" class="button" id="submitBoard"><?=($row->id?t('edit'):t('post'))?></a>
                <? if ($row->id) { ?>
                    <a href="<?=_cfg('href')?>/boards/<?=$row->id?>"><?=t('cancel')?></a>
                <? } ?>
                
                <input type="hidden" id="boardId" name="boardId" value="<?=($row->id?$row->id:'0')?>" />
                <input type="hidden" id="module" name="module" value="<?=($row->id?'editBoard':'boards')?>" />
                <input type="hidden" id="category" name="category" value="<?=($row->category?$row->category:'general')?>" />
                
            </form>
            
        </div>
    </div>
</div>