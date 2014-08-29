<h1><?=at('languages')?></h1>

<table class="table">
    <td colspan="4" id="buttons" class="hint" name="Add">
        <a href="<?=_cfg('cmssite').'/#languages/add'?>"><div class="add-image"></div><?=at('add_new')?> <?=at('languages_language')?></a>
    </td>
	<tr>
        <td><b><?=at('language')?></b></td>
        <td><b><?=at('abbriviature')?></b></td>
        <td><b><?=at('flag')?></b></td>
        <td class="centered b"><?=at('actions')?></td>
    </tr>
	<?
		if ($module->languages) {
			foreach($module->languages as $v) {
				?><tr>
					<td><a href="<?=_cfg('cmssite').'/#languages/edit/'.$v->id?>"><?=ucfirst($v->title)?></a></td>
					<td><a href="<?=_cfg('cmssite').'/#languages/edit/'.$v->id?>"><?=$v->flag?></a></td>
					<td><a href="<?=_cfg('cmssite').'/#languages/edit/'.$v->id?>"><img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/></a></td>
					<td class="centered">
						<a href="<?=_cfg('cmssite').'/#languages/edit/'.$v->id?>" class="hint" name="Edit">
							<img src="<?=_cfg('cmsimg')?>/edit.png" />
						</a> 
						<a href="javascript:void(0);" class="hint" name="Delete" onclick="deletion('<?=_cfg('cmssite').'/#languages/delete/'.$v->id?>'); return false;">
							<img src="<?=_cfg('cmsimg')?>/cancel.png" />
						</a>
					</td>
				  </tr><?
			}
		}
	?>
</table>