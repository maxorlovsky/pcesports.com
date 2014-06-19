<h1><?=at('links')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#links">Back</a>
<table class="links" id="edit" name="links">
    <tr>
        <td width="20%"><b><?=at('name')?> <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50" value="<?=$module->editData->value?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b><?=at('link')?></b> 
            <small>http://,  <?=at('or_leave_blank')?>(#)</small>
        </td>
        <td><input type="text" id="href" size="50" value="<?=$module->editData->link?>" /></td>
    </tr>
    <tr>
        <td width="300" class="b"><?=at('made_sublink')?></td>
        <td>
            <select id="main_link" class="chosen">
                <option value="0">(<?=at('no')?>)</option>
                <?
                    foreach($module->links as $v) {
                        ?><option value="<?=$v->id?>" <?=($v->id==$module->editData->main_link?'selected="selected"':null)?>><?=str_replace('web-link-','',$v->value)?></option><?
                    }
                ?>
            </select>
        </td>
    </tr>
    <? if (is_array($module->positions) && $module->positions) { ?>
    <tr>
        <td width="300" class="b"><?=at('link_place')?></td>
        <td>
            <select id="link_block" class="chosen">
                <? foreach($module->positions as $k => $v) { ?>
                    <option value="<?=$k?>" <?=($k==$module->editData->block?'selected="selected"':null)?>><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <? } ?>
    <tr>
        <td width="300" class="b"><label for="logged_in"><?=at('only_for_logged_in')?></label></td>
        <td><input type="checkbox" id="logged_in" <?=($k==$module->editData->logged_in?'checked="checked" value="1"':'value="0"')?> /></td>
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
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> <?=at('link')?></button></td></tr>
    <input type="hidden" id="link_id" value="<?=$module->editData->id?>"/>
	<input type="hidden" id="link_value" value="<?=$module->editData->value?>"/>
</table>

<script>
$('#logged_in').on('click', function() {
    if ($(this).is(':checked') === false) {
        $(this).attr('value', 0);
    }
    else {
        $(this).attr('value', 1);
    }
});
</script>