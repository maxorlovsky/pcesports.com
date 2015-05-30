<h1><?=at('logs')?></h1>

<div class="clear"></div>

<?=$module->pages->html?>

<table class="table logs">
    <tr>
        <td width="10%"><b><?=at('module')?></b></td>
        <td width="10%"><b><?=at('type')?></b></td>
        <td width="15%"><b><?=at('user')?></b></td>
        <td width="15%"><b><?=at('date')?></b></td>
        <td width="10%"><b><?=at('ip')?></b></td>
        <td width="40%"><b><?=at('info')?></b></td>
    </tr>
    <tr>
        <td colspan="2">
            <select class="chosen" id="choose_modules">
                <option value="0">(<?=at('choose_module')?>)</option>
                <?
                    foreach($module->modules as $v) {
                        ?><option value="<?=$v?>" <?=($module->pickedModule==$v?'selected="selected"':null)?>><?=ucfirst($v)?></option><?
                    }
                ?>
            </select>
        </td>
        <td colspan="4">
            <select class="chosen" id="choose_types">
                <option value="0">(<?=at('choose_type')?>)</option>
                <?
                    foreach($module->types as $f) {
                        ?><option value="<?=$f?>" <?=($module->pickedType==$f?'selected="selected"':null)?>><?=$f?></option><?
                    }
                ?>
            </select>
        </td>
    </tr>
    <?
        if ($module->logs) {
            foreach($module->logs as $v) {
                ?>
                <tr>
                    <td><?=$v->module?></td>
                    <td><?=$v->type?></td>
                    <td><?=$v->login?> (ID: <?=$v->user_id?>)</td>
                    <td><?=$v->date?></td>
                    <td><?=$v->ip?></td>
                    <td><?=$v->info?></td>
                </tr>
                <?
            }
        }
    ?>
</table>

<?=$module->pages->html?>

<script>
$('#choose_modules').on('change', function() {
    window.location = TM.site+'/admin/#logs/index/page/1/module/'+$(this).val();
});

$('#choose_types').on('change', function() {
    var params = window.location.href.split('/');
    var redirect = TM.site+'/admin/#logs';

    if (params[5] != 'index') {
        redirect += '/index/page/1';
    }
    else {
        redirect += '/index/page/'+params[7];
    }

    if (params[8] != 'module') {
        redirect += '/module/0';
    }
    else {
        redirect += '/module/'+params[9];
    }

    redirect += '/types/'+$(this).val();

    window.location = redirect;
});
</script>