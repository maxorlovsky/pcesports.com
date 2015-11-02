<h1>Tournaments List</h1>

<a class="back" href="<?=_cfg('cmssite')?>/#tournamentList">Back</a>
<table class="table tournamentList" id="edit" name="tournamentList">
    <tr>
        <td width="20%"><b>Game <span class="red">*</span></b></td>
        <td>
            <select id="game" disabled="disabled">
                <? foreach($module->availableGames as $k => $v) { ?>
                <option value="<?=$k?>" <?=($module->editData->game==$k?'selected="selected"':null)?>><?=$v?></option>
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
            <select id="server" disabled="disabled">
                <option value="">none</option>
                <? foreach($module->availableServers as $k => $v) { ?>
                <option value="<?=$k?>" <?=($module->editData->server==$k?'selected="selected"':null)?>><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%"><b>Name <span class="red">*</span></b></td>
        <td><input type="text" id="name" size="50" value="<?=$module->editData->name?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (registration) <span class="red">*</span></b></td>
        <td><input type="text" id="datesRegistration" size="50" value="<?=$module->editData->dates_registration?>" readonly="" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Dates (start) <span class="red">*</span></b></td>
        <td><input type="text" id="datesStart" size="50" value="<?=$module->editData->dates_start?>" readonly="" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Time (UTC-0)</b>
        </td>
        <td>
            <select id="time">
                <?
                for($i=0;$i<=23;++$i) {
                    $time = ($i<10?'0':null).$i.':00';
                ?>
                <option value="<?=($i<10?'0':null)?><?=$i?>:00" <?=($module->editData->time==$time?'selected=""':null)?>><?=($i<10?'0':null)?><?=$i?>:00</option>
                <? } ?>
            </select>
        </td>
    </tr>
    <? if ($module->editData->game == 'lol') {?>
    <tr>
        <td width="20%">
            <b>Ext. Event ID</b><br />
            <small>Required for Riot ID</small>
        </td>
        <td><input type="text" id="eventId" size="20" value="<?=$module->editData->event_id?>" /></td>
    </tr>
    <? } ?>
    <tr>
        <td width="20%"><b>Prize <span class="red">*</span></b></td>
        <td><input type="text" id="prize" size="50" value="<?=$module->editData->prize?>" /></td>
    </tr>
    <tr>
        <td width="20%"><b>Maximum participants count</b></td>
        <td>
            <select id="maxNum">
                <? foreach(array(8,16,32,64,128,256) as $v) { ?>
                <option value="<?=$v?>" <?=($v==$module->editData->max_num?'selected=""':null)?>><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="20%">
            <b>Status <span class="red">*</span></b><br />
            <small style="color: red">Do not change if you don't know what you're doing</small>
        </td>
        <td>
            <select id="status">
                <option>Upcoming</option>
                <option <?=($module->editData->status=='registration'?'selected="selected"':null)?>>Registration</option>
                <option <?=($module->editData->status=='check_in'?'selected="selected"':null)?>>Check In</option>
                <option <?=($module->editData->status=='live'?'selected="selected"':null)?>>Live</option>
                <option <?=($module->editData->status=='ended'?'selected="selected"':null)?>>Ended</option>
                <option <?=($module->editData->status=='Start'?'selected="selected"':null)?>>Start (not for project)</option>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> tournament</button></td></tr>
    <input type="hidden" id="id" value="<?=$module->editData->id?>"/>
</table>

<script>
$('#datesRegistration, #datesStart').datepicker({
    dateFormat: 'dd.mm.yy',
    minDate: 0
});

$('#game').on('change', function() {
    eventId();
});
</script>