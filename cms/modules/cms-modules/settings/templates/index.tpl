<table class="table">
    <tr><td colspan="2"><center><b><?=at('settings')?></b></center></td></tr>
    <?
    if ($module->siteSettings) {
        $currentPosition = 0;
        foreach($module->siteSettings as $v) {
            if ($currentPosition != $v['position']) {
                $currentPosition = $v['position'];
                ?><tr><td colspan="2"><hr /></td></tr><?
            }
            if ($v['type'] == 'text' || $v['type'] == 'checkbox') {
                ?>
                <tr>
                    <td width="30%"><b><?=$v['value']?></b></td>
                    <td width="70%"><?=$v['html']?></td>
                </tr>
                <?
            }
        }
    }
    ?>
</table>
<br /><br />
<table class="table">
    <colgroup>
        <col width="70%"></col>
        <col width="30%"></col>
    </colgroup>
    <tr>
        <td colspan="2" class="centered b"><?=at('pages')?></td>
    </tr>
    <tr>
        <td class="centered b"><?=at('page_name')?></td>
        <td class="centered b"><?=at('level_required')?></td>
    </tr>
    <?
    if ($module->siteSettings) {
        foreach($module->siteSettings as $v) {
            if ($v['type'] == 'level') {
            ?>
            <tr>
                <td><?=$v['value']?></td>
                <td class="centered"><?=$v['html']?></td>
            </tr>
            <?
            }
        }
    }
    ?>
</table>
<br />
<table class="table settings">
    <colgroup>
        <col width="70%"></col>
        <col width="30%"></col>
    </colgroup>
    <tr><td colspan="2" class="centered b"><?=at('installed_modules')?></td></tr>
    <tr>
        <td class="centered b"><?=at('module_name')?></td>
        <td class="centered b"><?=at('level_required')?></td>
    </tr>
    <?
        $i = 0;
        if ($this->data->modules) {
	        foreach($this->data->modules as $v) {
	            ?><tr>
	                <td><?=$v->displayName?></td>
	                <td align="center"><div id="module-<?=$v->name?>" class="pointer settings_div" onclick="TM.addInput('module-<?=$v->name?>', 1);"><?=$v->level?></div></td>
	              </tr><?
	            ++$i;
	        }
        }
    ?>
</table>


<script>
$('.save_setting_checkbox').on('click', function() {
    TM.showMsg(2,strings['loading']);
    
    var id = $(this).attr('id');
    var checked = 0;
    if ($(this).is(':checked') === true) {
        checked = 1;
    }
    
    var query = {
        type: 'POST',
        timeout: 10000,
        data: {
            control: 'saveSetting',
            param: id,
            value: checked
        },
        success: function(data) {
            answer = data.split(';');
            TM.cleanMsg();
            TM.showMsg(answer[0],answer[1]);
            messageTimer = setTimeout(TM.cleanMsg,3000);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            TM.showMsg(0,'Error timeout');
            messageTimer = setTimeout(TM.cleanMsg,3000);
        }
    };
    TM.ajax(query);
});
</script>