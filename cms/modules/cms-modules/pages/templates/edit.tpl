<h1><?=at('pages')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#pages">Back</a>
<table class="pages" id="edit" name="pages">
	<tr>
        <td width="20%"><b><?=at('link')?> <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50" value="<?=$module->editData->link?>"/></td>
    </tr>
	<tr>
        <td width="20%"><b><?=at('page_name')?> <span class="red">*</span></b> <small>(<?=at('page_name_warn')?>)</small></td>
        <td><input type="text" id="strings" size="50" value="<?=$module->editData->value?>"/></td>
    </tr>
	<?
	foreach($module->languages as $v) {
		?>
		<tr>
			<td class="b"><?=at('page_text')?> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/></td>
			<td><textarea id="string_<?=$v->title?>" cols="80"><?=$module->editData->{$v->title}?></textarea></td>
		</tr>
		<?
	}
	?>
	<tr><td colspan="2"><button class="submitButton"><?=at('change')?> <?=at('text_page')?></button></td></tr>
    <input type="hidden" id="page_id" value="<?=$module->editData->id?>"/>
</table>