<h1><?=at('accounts')?></h1>

<table class="strings">
    <tr>
        <td colspan="5" id="buttons" class="hint" name="Add">
            <a href="<?=_cfg('cmssite').'/#accounts/add'?>"><div class="add-image"></div><?=at('add_new')?> <?=at('account')?></a>
        </td>
    </tr>
    <tr>
        <td width="20%"><b><?=at('login')?></b></td>
        <td width="20%"><b><?=at('email')?></b></td>
        <td width="10%"><b><?=at('access_level')?></b></td>
        <td width="40%"><b><?=at('last_ent_overall_ent')?></b></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->accounts) {
            foreach($module->accounts as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#accounts/edit/'.$v->id?>"><?=$v->login?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#accounts/edit/'.$v->id?>"><?=$v->email?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#accounts/edit/'.$v->id?>"><?=$v->level?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#accounts/edit/'.$v->id?>"><?=$v->last_login?> :: <?=$v->login_count?></a></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#accounts/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a>
                        <? if ($v->level < $this->user->level) { ?>
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#accounts/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                        <? } else { ?>
                            <img src="<?=_cfg('cmsimg')?>/cancel-disabled.png" class="hint" name="Can't delete yourself or account of higher level" />
                        <? } ?>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>