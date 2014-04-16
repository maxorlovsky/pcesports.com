<h1><?=at('accounts')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#accounts">Back</a>
<table class="accounts" id="edit" name="accounts">
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
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> <?=at('admin')?></button></td></tr>
    <input type="hidden" id="admin_id" value="<?=$module->editData->id?>"/>
</table>