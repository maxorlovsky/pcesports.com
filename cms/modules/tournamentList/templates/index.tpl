<h1>Tournaments List</h1>

<table class="table">
    <tr>
        <td colspan="7" id="buttons">
            <a href="<?=_cfg('cmssite').'/#tournamentList/add'?>"><div class="add-image"></div><?=at('add_new')?> tournament</a>
        </td>
    </tr>
    <tr>
    	<td width="5%"><b>ID</b></td>
        <td width="10%"><b>Game</b></td>
        <td width="10%"><b>Name</b></td>
        <td width="30%"><b>Dates + Time</b></td>
        <td width="15%"><b>Prize</b></td>
        <td width="15%"><b>Status</b></td>
        <td width="15%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->tournaments) {
            foreach($module->tournaments as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->id?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->game?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->name?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->dates?> <?=($v->time?'- '.$v->time:null)?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->prize?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>"><?=$v->status?></a></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#tournamentList/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#tournamentList/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>