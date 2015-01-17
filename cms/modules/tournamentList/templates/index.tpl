<h1>Tournaments List</h1>

<table class="table">
    <tr>
        <td colspan="10" id="buttons">
            <a href="<?=_cfg('cmssite').'/#tournamentList/add'?>"><div class="add-image"></div><?=at('add_new')?> tournament</a>
        </td>
    </tr>
    <tr>
    	<td width="5%"><b>ID</b></td>
        <td width="10%"><b>Game</b></td>
        <td width="5%"><b>Server</b></td>
        <td width="10%"><b>Name</b></td>
        <td width="10%"><b>Dates (reg)</b></td>
        <td width="12%"><b>Dates (start)</b></td>
        <td width="8%"><b>Time</b></td>
        <td width="15%"><b>Prize</b></td>
        <td width="10%"><b>Status</b></td>
        <td width="15%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->tournaments) {
            foreach($module->tournaments as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->id?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->game?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->server?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->name?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->dates_registration?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->dates_start?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->time?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->prize?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->status?></a></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="TM.deletion('<?=_cfg('cmssite').'/#tournamentList/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>