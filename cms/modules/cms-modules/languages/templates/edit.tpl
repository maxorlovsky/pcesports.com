<h1><?=at('languages')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#languages">Back</a>
<table class="table" id="edit" name="languages">
	<tr>
        <td width="30%"><?=at('name')?> <span class="red">*</span></td>
        <td><input type="text" id="title" value="<?=$module->editData->title?>" /></td>
    </tr>
	<tr>
        <td>ISO2 language code <span class="red">*</span><br /><small>Example: ru, en, lv, us</small></td>
        <td><input type="text" id="meta" value="<?=$module->editData->flag?>" maxlength="2" /></td>
    </tr>
	<tr>
        <td colspan="2"><button class="submitButton"><?=at('change')?> <?=at('languages_language')?></button></td>
    </tr>
	<input type="hidden" id="lang_old_title" value="<?=$module->editData->title?>" />
	<input type="hidden" id="lang_id" value="<?=$module->editData->id?>" />
</table>