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
        <td width="20%">
            <b>Addition</b><br />
            <small>Required for LoL tournaments</small>
        </td>
        <td>
            <select id="server">
                <option value="">none</option>
                <? foreach($module->availableServers as $k => $v) { ?>
                <option value="<?=$k?>"><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Name <span class="red">*</span></b></td>
        <td><input type="text" id="name" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (registration) <span class="red">*</span></b></td>
        <td><input type="text" id="datesRegistration" size="50" value="" readonly="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (start) <span class="red">*</span></b></td>
        <td><input type="text" id="datesStart" size="50" value="" readonly="" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Time (UTC-0)</b>
        </td>
        <td>
            <select id="time">
                <? for($i=0;$i<=23;++$i) { ?>
                <option value="<?=($i<10?'0':null)?><?=$i?>:00"><?=($i<10?'0':null)?><?=$i?>:00</option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%">
            <b>Ext. Event ID</b><br />
            <small>Required for Riot ID</small>
        </td>
        <td><input type="text" id="eventId" size="20" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Prize <span class="red">*</span></b></td>
        <td><input type="text" id="prize" size="50" value="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Maximum participants count</b></td>
        <td>
            <select id="maxNum">
                <? foreach(array(8,16,32,64,128,256) as $v) { ?>
                <option value="<?=$v?>" <?=($v==64?'selected=""':null)?>><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> tournament</button></td></tr>
</table>

<script>
$('#datesRegistration, #datesStart').datepicker({
    dateFormat: 'dd.mm.yy',
    minDate: 0
});
</script>