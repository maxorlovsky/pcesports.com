<h1><?=at('pages')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#pages">Back</a>
<table class="pages" id="add" name="pages">
	<tr>
        <td width="20%"><b><?=at('page_name')?> <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50"/></td>
    </tr>
    <tr>
        <td width="300" class="b"><label for="logged_in"><?=at('only_for_logged_in')?></label></td>
        <td><input type="checkbox" id="logged_in" value="0" /></td>
    </tr>
    <?
	foreach($module->languages as $v) {
		?>
		<tr>
			<td class="b">
                <?=at('page_name')?> <?=at('text')?> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/><br />
                <small>Required for string</small>
            </td>
			<td><input type="text" id="string_<?=$v->title?>" /></td>
		</tr>
		<?
	}
	?>
	<?
	foreach($module->languages as $v) {
		?>
		<tr>
			<td class="b">
                <?=at('page_text')?> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/><br />
                <small>Page content</small>
            </td>
			<td><textarea id="text_<?=$v->title?>" cols="80"></textarea></td>
		</tr>
		<?
	}
	?>
	<tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> <?=at('text_page')?></button></td></tr>
</table>

<script>
$('#logged_in').on('click', function() {
    if ($(this).is(':checked') === false) {
        $(this).attr('value', 0);
    }
    else {
        $(this).attr('value', 1);
    }
});
</script>