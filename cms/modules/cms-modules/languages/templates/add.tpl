<h1><?=at('languages')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#languages">Back</a>
<table class="table" id="add" name="languages">
	<tr>
        <td width="30%"><?=at('name')?> <span class="red">*</span></td>
        <td><input type="text" id="title" /></td>
    </tr>
	<tr>
        <td>ISO2 language code <span class="red">*</span><br /><small>Example: ru, en, lv, us</small></td>
        <td><input type="text" id="meta" maxlength="2" /></td>
    </tr>
	<tr>
        <td colspan="2"><button class="submitButton"><?=at('add_new')?> <?=at('languages_language')?></button></td>
    </tr>
</table>