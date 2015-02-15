<h1>Tournaments List</h1>

<a class="back" href="<?=_cfg('cmssite')?>/#tournamentList">Back</a>
<table class="table tournamentList" id="edit" name="tournamentList">
    <tr>
        <td width="20%"><b>Game <span class="red">*</span></b></td>
        <td>
            <select id="game">
                <? foreach($module->availableGames as $k => $v) { ?>
                <option value="<?=$k?>" <?=($module->editData->game==$k?'selected="selected"':null)?>><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Addition</b></td>
        <td>
            <select id="server">
                <option value="">none</option>
                <option value="euw" <?=($module->editData->server=='euw'?'selected="selected"':null)?>>EUW</option>
                <option value="eune" <?=($module->editData->server=='eune'?'selected="selected"':null)?>>EUNE</option>
                <option value="na" <?=($module->editData->server=='na'?'selected="selected"':null)?>>NA</option>
                <option value="eu" <?=($module->editData->server=='eu'?'selected="selected"':null)?>>EU</option>
                <option value="s1" <?=($module->editData->server=='s1'?'selected="selected"':null)?>>Season 1</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Name <span class="red">*</span></b></td>
        <td><input type="text" id="name" size="50" value="<?=$module->editData->name?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (registration) <span class="red">*</span></b></td>
        <td><input type="text" id="datesRegistration" size="50" value="<?=$module->editData->dates_registration?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (start) <span class="red">*</span></b></td>
        <td><input type="text" id="datesStart" size="50" value="<?=$module->editData->dates_start?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Time</b><br />
            <small>Only for informational status</small>
        </td>
        <td><input type="text" id="time" size="50" value="<?=$module->editData->time?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Prize <span class="red">*</span></b></td>
        <td><input type="text" id="prize" size="50" value="<?=$module->editData->prize?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Maximum participants count</b></td>
        <td><input type="text" id="maxNum" size="50" value="<?=$module->editData->max_num?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Status <span class="red">*</span></b></td>
        <td>
            <select id="status">
                <option>Ended</option>
                <option <?=($module->editData->status=='Start'?'selected="selected"':null)?>>Start</option>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> tournament</button></td></tr>
    <input type="hidden" id="id" value="<?=$module->editData->id?>"/>
</table>