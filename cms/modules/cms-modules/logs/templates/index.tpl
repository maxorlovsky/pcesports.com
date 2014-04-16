<h1><?=at('logs')?></h1>

<table class="logs">
    <!-- <tr>
        <td colspan="6" class="search">
            <?=at('find_string')?>: 
            <input id="search-text" type="text" value="<?=$module->searchString;?>" /> 
            <input type="submit" id="search" value="<?=at('search');?>" />
        </td>
    </tr> -->
    <tr>
        <td width="10%"><b><?=at('module')?></b></td>
        <td width="10%"><b><?=at('type')?></b></td>
        <td width="10%"><b><?=at('user')?></b></td>
        <td width="20%"><b><?=at('date')?></b></td>
        <td width="10%"><b><?=at('ip')?></b></td>
        <td width="40%"><b><?=at('info')?></b></td>
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