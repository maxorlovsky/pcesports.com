<h1><?=at('links')?></h1>

<table class="links">
    <td colspan="5" id="buttons" class="hint" name="Add">
        <a href="<?=_cfg('cmssite').'/#links/add'?>"><div class="add-image"></div><?=at('add_new')?> <?=strtolower(at('link'))?></a>
    </td>
	<tr>
        <td width="40%"><b><?=at('link')?></b></td>
        <td width="50%"><b><?=at('sublink')?></b></td>
        <td width="1"><b><?=at('updown')?></b></td>
        <td width="1"><b><?=at('enaldisabl')?></b></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>
	<?
		if ($module->links) {
			foreach($module->links as $v) {
				/*if (is_array($module->positions) && $module->positions) { ?>
				    <tr>
				        <td width="300" class="b" colspan="5"><?=dump($module->positions[0])?></td>
				    </tr>
				<?
				}*/
				?>
                <tr>
					<td><a href="<?=_cfg('cmssite').'/#links/edit/'.$v->id?>"><?=str_replace('web-link-','',$v->value)?></a></td>
					<td></td>
                    <td class="centered">
                        <span class="big">
                            <a href="<?=_cfg('cmssite').'/#links/moveup/'.$v->id?>" class="hint" name="Move UP"><img src="<?=_cfg('cmsimg')?>/arrow_up.png" /></a>
                        </span>
                        <span class="big">
                            <a href="<?=_cfg('cmssite').'/#links/movedown/'.$v->id?>" class="hint" name="Move DOWN"><img src="<?=_cfg('cmsimg')?>/arrow_down.png" /></a>
                        </span>
                    </td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#links/able/'.$v->id?>">
                            <?=($v->able == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Disable"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Enable"/>')?>
                            
                        </a>
                    </td>
					<td class="centered">
						<a href="<?=_cfg('cmssite').'/#links/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
						<a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#links/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
					</td>
				</tr>
                <?
                if ($module->sublinks) {
                foreach($module->sublinks as $vs) {
                    if ($v->id == $vs->main_link) {
                        ?>
                        <tr>
							<td class="centered"><a href="<?=_cfg('cmssite').'/#links/edit/'.$vs->id?>">=></a></td>
							<td><a href="<?=_cfg('cmssite').'/#links/edit/'.$vs->id?>"><?=str_replace('web-link-','',$vs->value)?></a></td>
		                    <td class="centered">
		                        <span class="big">
		                            <a href="<?=_cfg('cmssite').'/#links/moveup/'.$vs->id?>">
		                                <img src="<?=_cfg('cmsimg')?>/arrow_up.png" />
		                            </a>
		                        </span>
		                        <span class="big">
		                            <a href="<?=_cfg('cmssite').'/#links/movedown/'.$vs->id?>">
		                                <img src="<?=_cfg('cmsimg')?>/arrow_down.png" />
		                            </a>
		                        </span>
		                    </td>
		                    <td class="centered">
		                        <a href="<?=_cfg('cmssite').'/#links/able/'.$vs->id?>">
		                            <?=($vs->able == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Disable"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Enable"/>')?>
		                        </a>
		                    </td>
							<td class="centered">
								<a href="<?=_cfg('cmssite').'/#links/edit/'.$vs->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
								<a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#links/delete/'.$vs->id?>'); return false;">
		                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
		                        </a>
							</td>
						</tr>
                        <?
                    }
                }
                }
			}
		}
	?>
</table>