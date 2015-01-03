<h1><?=at('strings')?></h1>

<table class="table strings">
    <tr>
        <td colspan="4" id="buttons" class="hint" name="Add">
            <a href="<?=_cfg('cmssite').'/#strings/add'?>"><div class="add-image"></div><?=at('add_new')?> <?=at('string')?></a>
        </td>
    </tr>
    <tr>
		<td colspan="3" class="search">
			<?=at('find_string')?>: 
			<input id="search-text" type="text" value="<?=$module->searchString;?>" /> 
			<input type="submit" id="search" value="<?=at('search');?>" />
		</td>
	</tr>
    <tr>
        <td width="20%"><b><?=at('string_name')?></b></td>
        <td width="70%"><b><?=at('string_output')?></b></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>
    <?
        if ($module->strings) {
            foreach($module->strings as $v) {
                ?>
                <tr>
                    <td><a href="<?=_cfg('cmssite').'/#strings/edit/'.$v->key?>"><?=$v->key?></a></td>
                    <td><a href="<?=_cfg('cmssite').'/#strings/edit/'.$v->key?>"><?=substr(strip_tags($v->value),0,100)?></a></td>
                    <td class="centered">
                        <a href="<?=_cfg('cmssite').'/#strings/edit/'.$v->key?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a>
                        <? if ($v->status != 1) { ?> 
                        <a href="javascript:void(0);" class="hint" name="Delete" onclick="TM.deletion('<?=_cfg('cmssite').'/#strings/delete/'.$v->key?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
                        <? } else { ?>
                            <img src="<?=_cfg('cmsimg')?>/cancel-disabled.png" class="hint" name="Can't delete system link, only can be removed from links" />
                        <? } ?>
                    </td>
                </tr>
                <?
            }
        }
    ?>
</table>

<script>
$('.search #search').on('click', function() {
	TM.go(site+'/admin/#strings/index/'+encodeURIComponent($('.search #search-text').val()));
});
$('.search #search-text').on('keypress', function(event) {
	if (event.which == 13) {
		$('.search #search').trigger('click');
	}
});
if ($('.search #search-text').val()) {
	$('.search #search-text').focus();
	length = $('.search #search-text').val().length;
	$('.search #search-text')[0].setSelectionRange(length, length);
}
</script>