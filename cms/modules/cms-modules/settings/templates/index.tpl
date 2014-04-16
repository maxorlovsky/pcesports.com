<table class="settings">
    <tr><td colspan="2"><center><b><?=at('settings')?></b></center></td></tr>
    <?
        $i = 0;

        if ($module->siteSettings) {
	        foreach($module->siteSettings as $v) {
	            ?><tr>
	                <td width="30%"><b><?=$v['value']?></b></td>
	                <td width="70%"><?=$v['html']?></td>
	              </tr><?
	            ++$i;
	        }
        }
    ?>
</table>
<br /><br />
<table class="settings">
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
        $i = 0;
        if ($module->mainPages) {
	        foreach($module->mainPages as $f) {
	            ?><tr>
	                <td><?=$f['value']?></td>
	                <td class="centered"><?=$f['html']?></td>
	              </tr><?
	            ++$i;
	        }
        }
    ?>
</table>
<br />
<table class="settings">
    <colgroup>
        <col width="35%"></col>
        <col width="35%"></col>
        <col width="30%"></col>
    </colgroup>
    <tr><td colspan="3" class="centered b"><?=at('installed_modules')?></td></tr>
    <tr>
        <td class="centered b"><?=at('module_name')?></td>
        <td class="centered b"><?=at('level_required')?></td>
        <td class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        $i = 0;
        if ($this->data->modules) {
	        foreach($this->data->modules as $v) {
	            ?><tr>
	                <td><?=$v->name?></td>
	                <td align="center"><?=$v->level?></td>
	                <td align="center"><a href="#<?=$v->name?>">Edit</a> / <a href="#">Uninstall</a></td>
	              </tr><?
	            ++$i;
	        }
        }
    ?>
</table>