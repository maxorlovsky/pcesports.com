<h1><?=at('accounts')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#accounts">Back</a>
<table class="table accounts" id="add" name="accounts">
    <tr>
        <td width="20%"><b><?=at('login')?> <span class="red">*</span></b></td>
        <td><input type="text" id="login" size="50"/></td>
    </tr>
    <tr>
        <td width="20%"><b><?=at('password')?> <span class="red">*</span></b></td>
        <td><input type="password" id="password" size="50"/></td>
    </tr>
    <tr>
        <td width="20%"><b><?=at('email')?></b></td>
        <td><input type="text" id="email" size="50"/></td>
    </tr>
    <tr>
        <td width="20%"><b><?=at('access_level')?> <span class="red">*</span></b></td>
        <td>
            <select id="level" class="chosen">
            	<? for($i=1;$i<=_cfg('maxLevel');++$i) { ?>
                    <option value="<?=$i?>"><?=$i?></option>
                <? } ?>
                <option value="0"><?=at('custom')?></option>
            </select>
        </td>
    </tr>
    <tr class="hidden customAccess">
        <td width="20%"><b><?=at('pages')?></b></td>
        <td>
            <?
            if ($module->siteSettings) {
                foreach($module->siteSettings as $v) {
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
                            <input type="checkbox" id="setting-<?=$v->setting?>" name="setting-<?=$v->setting?>" value="0" />
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
                ?>
                    <div class="siteSetting">
                        <input type="checkbox" id="module-<?=$v->name?>" name="module-<?=$v->name?>" value="0" />
                        <label for="module-<?=$v->name?>"><?=$v->displayName?></label>
                    </div>
                <?
                }
            }
            ?>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> <?=at('admin')?></button></td></tr>
</table>

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