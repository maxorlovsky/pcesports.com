<h1>Tournaments List</h1>

<a class="back" href="<?=_cfg('cmssite')?>/#tournamentList">Back</a>
<table class="table tournamentList" id="edit" name="tournamentList">
    <tr>
        <td width="20%"><b>Game <span class="red">*</span></b></td>
        <td>
            <select id="game">
                <option value="lol">League of Legends</option>
                <option value="hs" <?=($module->editData->game=='hs'?'selected="selected"':null)?>>Hearthstone</option>
                <option value="hslan" <?=($module->editData->game=='hslan'?'selected="selected"':null)?>>Hearthstone League</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Server<br /><small>Only for LoL</small></td>
        <td>
            <select id="server">
                <option value="">none</option>
                <option value="euw" <?=($module->editData->server=='euw'?'selected="selected"':null)?>>EUW</option>
                <option value="eune" <?=($module->editData->server=='eune'?'selected="selected"':null)?>>EUNE</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Name <span class="red">*</span></b></td>
        <td><input type="text" id="name" size="50" value="<?=$module->editData->name?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates <span class="red">*</span></b></td>
        <td><input type="text" id="dates" size="50" value="<?=$module->editData->dates?>" /></td>
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
        <td width="20%"><b>Status <span class="red">*</span></b></td>
        <td>
            <select id="status">
                <option>Ended</option>
                <option <?=($module->editData->status=='Registration'?'selected="selected"':null)?>>Registration</option>
                <option <?=($module->editData->status=='Start'?'selected="selected"':null)?>>Start</option>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> tournament</button></td></tr>
    <input type="hidden" id="id" value="<?=$module->editData->id?>"/>
</table>