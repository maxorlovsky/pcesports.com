<h1>Streamers List</h1>

<table class="table">
    <tr>
    	<td width="3%" class="b">ID</td>
        <td width="27%" class="b">Name (Display name)</td>
        <td width="10%" class="b">Added by</td>
        <td width="20%" class="b">Game / Languages</td>
        <td width="10%" class="b">Last online</td>
        <td width="10%" class="centered b">Featured</td>
        <td width="10%" class="centered b">Approved</td>
        <td width="10%" class="centered b">Online</td>
    </tr>
    <?
        if ($module->streams) {
            foreach($module->streams as $v) {
                ?>
                <tr>
                    <td><?=$v->id?></td>
                    <td><?=$v->name?> <?=($v->display_name?'('.$v->display_name.')':null)?></td>
                    <td><?=$v->added_by?></td>
                    <td><?=$v->game?> / <?=$v->languages?></td>
                    <td><?=($v->online!=0?$v->online:null)?></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#streamers/featured/'.$v->id?>">
                            <?=($v->featured == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Remove from Featured"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Add to Featured"/>')?>
                            
                        </a>
                    </td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#streamers/able/'.$v->id?>">
                            <?=($v->approved == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Disable"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Enable"/>')?>
                            
                        </a>
                    </td>
                    <td class="centered">
                        <? if ($v->onlineStatus == 1) { ?>
                            <span class="online">Online</span>
                        <? } else { ?>
                            <span class="offline">Offline</span>
                        <? } ?>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>

<style>
.online {
    color: #0b0;
}
.offline {
    color: #b00;
}
</style>