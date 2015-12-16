<h1>HS Participants</h1>

<table class="table">
    <tr>
    	<td width="45%"><b>Name</b></td>
        <td width="45%"><b>Email</b></td>
        <td width="10%"><b>Verified</b></td>
    </tr>
    <?
        if ($module->participants) {
            foreach($module->participants as $v) {
                ?>
                <tr id="<?=$v->id?>">
                    <td><?=$v->name?></td>
                    <td><?=$v->email?></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#participantsHs/able/'.$v->id?>">
                            <?=($v->verified == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Verified"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Not verified"/>')?>
                            
                        </a>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>