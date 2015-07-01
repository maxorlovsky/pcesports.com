<h1>Tournaments List</h1>

<a class="back" href="<?=_cfg('cmssite')?>/#tournamentList">Back</a>
<table class="table tournamentList" id="add" name="tournamentList">
    <tr>
        <td width="20%"><b>Game <span class="red">*</span></b></td>
        <td>
            <select id="game">
                <? foreach($module->availableGames as $k => $v) { ?>
                <option value="<?=$k?>"><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Addition</b></td>
        <td>
            <select id="server">
                <option value="">none</option>
                <option value="euw">EUW</option>
                <option value="eune">EUNE</option>
                <option value="na">NA</option>
                <option value="eu">EU</option>
                <option value="s1">Season 1</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Name <span class="red">*</span></b></td>
        <td><input type="text" id="name" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (registration) <span class="red">*</span></b></td>
        <td><input type="text" id="datesRegistration" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (start) <span class="red">*</span></b></td>
        <td><input type="text" id="datesStart" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Time</b><br />
            <small>Only for informational status</small>
        </td>
        <td><input type="text" id="time" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Ext. Event ID</b><br />
        </td>
        <td><input type="text" id="eventId" size="20" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Prize <span class="red">*</span></b></td>
        <td><input type="text" id="prize" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Maximum participants count</b></td>
        <td><input type="text" id="maxNum" size="50" value="64" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Status <span class="red">*</span></b></td>
        <td>
            <select id="status">
                <option>Ended</option>
                <option>Start</option>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> tournament</button></td></tr>
</table>