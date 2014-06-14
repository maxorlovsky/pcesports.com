<h1>Tournaments List</h1>

<a class="back" href="<?=_cfg('cmssite')?>/#tournamentList">Back</a>
<table class="table tournamentList" id="add" name="tournamentList">
    <tr>
        <td width="20%"><b>Game <span class="red">*</span></b></td>
        <td>
            <select id="game">
                <option value="lol">League of Legends</option>
                <option value="hs">Hearthstone</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Name <span class="red">*</span></b></td>
        <td><input type="text" id="name" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates <span class="red">*</span></b></td>
        <td><input type="text" id="dates" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Time</b><br />
            <small>Only for informational status</small>
        </td>
        <td><input type="text" id="time" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Prize <span class="red">*</span></b></td>
        <td><input type="text" id="prize" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Status <span class="red">*</span></b></td>
        <td>
            <select id="status">
                <option>Ended</option>
                <option>Registration</option>
                <option>Live</option>
                <option>On hold</option>
                <option>Start</option>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> tournament</button></td></tr>
</table>