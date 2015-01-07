<h1>League Participants</h1>

<table class="table">
    <tr>
    	<td width="20%"><b>Name</b></td>
        <td width="25%"><b>Email</b></td>
        <td width="25%"><b>Phone</b></td>
        <td width="10%"><b>Group</b></td>
        <td width="10%"><b>Group Place</b></td>
        <td width="10%"><b>Verified</b></td>
    </tr>
    <?
        if ($module->participants) {
            foreach($module->participants as $v) {
                ?>
                <tr id="<?=$v->id?>">
                    <td><?=$v->name?></td>
                    <td><?=$v->email?></td>
                    <td><?=$v->contact_info->phone?></td>
                    <td class="centered">
                        <select class="chosen groups" style="min-width: 100px;">
                            <option value="0">none</option>
                            <? foreach($module->groups as $gk => $gv) { ?>
                            <option value="<?=$gk?>" <?=($v->seed_number==$gk?'selected="selected"':null)?>>Group <?=$gv?></option>
                            <? } ?>
                        </select>
                    </td>
                    <td class="centered">
                        <select class="chosen place" style="min-width: 50px;">
                            <? for($i=0;$i<=4;++$i) { ?>
                            <option value="<?=$i?>" <?=($v->contact_info->place==$i?'selected="selected"':null)?>><?=$i?></option>
                            <? } ?>
                        </select>
                    </td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#leagueParticipants/able/'.$v->id?>">
                            <?=($v->approved == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Verified"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Not verified"/>')?>
                            
                        </a>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>

<script>
$('.groups').on('change', function() {
    var id = $(this).closest('tr').attr('id');
    var value = $(this).val();
    var query = {
        type: 'POST',
        timeout: 10000,
        data: {
            control: 'submitForm',
            module: 'leagueParticipants',
            action: 'groups',
            id: id,
            value: value
        },
        success: function(answer) {}
    };
    TM.ajax(query);
});

$('.place').on('change', function() {
    var id = $(this).closest('tr').attr('id');
    var value = $(this).val();
    var query = {
        type: 'POST',
        timeout: 10000,
        data: {
            control: 'submitForm',
            module: 'leagueParticipants',
            action: 'place',
            id: id,
            value: value
        },
        success: function(answer) {
            
        }
    };
    TM.ajax(query);
});
</script>