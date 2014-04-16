<h1><?=at('strings')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#strings">Back</a>
<table class="strings" id="edit" name="strings">
    <tr>
        <td width="20%"><b><?=at('name')?> <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50" value="<?=$module->editData->key?>" /></td>
    </tr>
    <?
    foreach($module->languages as $v) {
        ?>
        <tr>
            <td class="b"><?=at('text')?> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/></td>
            <td><textarea id="string_<?=$v->title?>" cols="80"><?=$module->editData->{$v->title}?></textarea></td>
        </tr>
        <?
    }
    ?>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> <?=at('string')?></button></td></tr>
    <input type="hidden" id="string_old_key" value="<?=$module->editData->key?>"/>
</table>