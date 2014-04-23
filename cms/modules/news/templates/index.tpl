<h1>News</h1>

<table class="table">
    <tr>
        <td colspan="4" id="buttons">
            <a href="<?=_cfg('cmssite').'/#news/add'?>"><div class="add-image"></div><?=at('add_new')?> article</a>
        </td>
    </tr>
    <tr>
        <td width="30%"><b>News title</b></td>
        <td width="60%"><b>Image</b></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->news) {
            foreach($module->news as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#strings/edit/'.$v->key?>"><?=$v->key?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#strings/edit/'.$v->key?>"><?=substr(t($v->value),0,100)?></a></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#strings/edit/'.$v->key?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a>
                        <? if ($v->status != 1) { ?> 
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#strings/delete/'.$v->key?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                        <? } else { ?>
                            <img src="<?=_cfg('cmsimg')?>/cancel-disabled.png" class="hint" name="Can't delete system link, only can be removed from links" />
                        <? } ?>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>