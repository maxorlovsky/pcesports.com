<h1>News</h1>

<table class="table">
    <tr>
        <td colspan="5" id="buttons">
            <a href="<?=_cfg('cmssite').'/#news/add'?>"><div class="add-image"></div><?=at('add_new')?> article</a>
        </td>
    </tr>
    <tr>
    	<td width="5%"><b>ID</b></td>
        <td width="25%"><b>News title</b></td>
        <td width="60%"><b>Image</b></td>
        <td width="1"><b><?=at('enaldisabl')?></b></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->news) {
            foreach($module->news as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#news/edit/'.$v->id?>"><?=$v->id?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#news/edit/'.$v->id?>"><?=$v->title?></a></td>
                    <td>
                    	<a href="<?=_cfg('cmssite').'/#news/edit/'.$v->id?>">
                    		<? if ($v->extension) { ?>
                    		<img src="<?=_cfg('imgu')?>/news/small-<?=$v->id?>.<?=$v->extension?>" />
                    		<? } ?>
                    	</a>
                    </td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#news/able/'.$v->id?>">
                            <?=($v->able == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Disable"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Enable"/>')?>
                            
                        </a>
                    </td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#news/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="TM.deletion('<?=_cfg('cmssite').'/#news/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>