<h1><?=at('accounts')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#accounts">Back</a>
<table class="table accounts" id="edit" name="accounts">
    <tr>
        <td width="20%"><b><?=at('login')?> <span class="red">*</span></b></td>
        <td><input type="text" id="login" size="50" value="<?=$module->editData->login?>" readonly="readonly" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b><?=at('password')?></b><br />
            <small>leave blank, if no change required</small>
        </td>
        <td><input type="password" id="password" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b><?=at('email')?></b></td>
        <td><input type="text" id="email" size="50" value="<?=$module->editData->email?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b><?=at('access_level')?> <span class="red">*</span></b></td>
        <td>
            <select id="level" class="chosen">
                <? for($i=1;$i<=_cfg('maxLevel');++$i) { ?>
                    <option value="<?=$i?>" <?=($module->editData->level==$i?'selected="selected"':null)?>><?=$i?></option>
                <? } ?>
                <option value="0" <?=($module->editData->level==0?'selected="selected"':null)?>><?=at('custom')?></option>
            </select>
        </td>
    </tr>
    <tr class="hidden customAccess">
        <td width="20%"><b><?=at('pages')?></b></td>
        <td>
            <?
            if ($module->siteSettings) {
                foreach($module->siteSettings as $v) {
                    $var = $v->setting;
                    if ($v->setting == "dashboard") {
                    ?>
                        <div class="siteSetting">
                            <input type="checkbox" id="setting-<?=$v->setting?>" name="setting-<?=$v->setting?>" disabled="disabled" checked="checked" value="1" />
                            <label for="setting-<?=$v->setting?>"><?=ucfirst($v->setting)?></label>
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="siteSetting">
                            <input type="checkbox" id="setting-<?=$v->setting?>" name="setting-<?=$v->setting?>" <?=($module->editData->custom_access&&isset($module->editData->custom_access->setting->$var)&&$module->editData->custom_access->setting->$var==1?'value="1" checked="checked"':'value="0"')?> />
                            <label for="setting-<?=$v->setting?>"><?=ucfirst($v->setting)?></label>
                        </div>
                    <?
                    }
                }
            }
            ?>
        </td>
    </tr>
    <tr class="hidden customAccess">
        <td width="20%"><b><?=at('installed_modules')?></b></td>
        <td>
            <?
            if ($this->data->modules) {
	        foreach($this->data->modules as $v) {
                $var = $v->name;
                ?>
                    <div class="siteSetting">
                        <input type="checkbox" id="module-<?=$v->name?>" name="module-<?=$v->name?>" <?=($module->editData->custom_access&&isset($module->editData->custom_access->module->$var)&&$module->editData->custom_access->module->$var==1?'value="1" checked="checked"':'value="0"')?> />
                        <label for="module-<?=$v->name?>"><?=$v->displayName?></label>
                    </div>
                <?
                }
            }
            ?>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> <?=at('admin')?></button></td></tr>
    <input type="hidden" id="admin_id" value="<?=$module->editData->id?>"/>
</table>

<style>

</style>

<script>
$(document).ready(function() {
    TM.checkCustomAccess();
});
$('#level').change(function() {
    TM.checkCustomAccess();
});
$('.siteSetting input[type="checkbox"]').on('click', function() {
    console.log($(this).is(':checked'));
    if ($(this).is(':checked') === true) {
        $(this).val(1);
    }
    else {
        $(this).val(0);
    }
});
</script>