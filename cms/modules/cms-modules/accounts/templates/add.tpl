<h1><?=at('accounts')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#accounts">Back</a>
<table class="accounts" id="add" name="accounts">
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
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> <?=at('admin')?></button></td></tr>
</table>