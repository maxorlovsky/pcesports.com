<h1><?=$module->moduleName?></h1>

<table class="table">
    <tr>
        <td colspan="7" id="buttons">
            <a href="<?=_cfg('cmssite').'/#faq/add'?>"><div class="add-image"></div><?=at('add_new')?> Q&A</a>
        </td>
    </tr>
    <tr>
    	<td width="5%"><b>ID</b></td>
        <td width="25%"><b>Question</b></td>
        <td width="45%"><b>Answer</b></td>
        <td width="15%"><b>Order number</b></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->faq) {
            foreach($module->faq as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#faq/edit/'.$v->id?>"><?=$v->id?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#faq/edit/'.$v->id?>"><?=$v->question_english?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#faq/edit/'.$v->id?>"><?=$v->answer_english?></a></td>
                    <td class="centered"><a href="<?=_cfg('cmssite').'/#faq/edit/'.$v->id?>"><?=$v->weight?></a></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#faq/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#faq/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>