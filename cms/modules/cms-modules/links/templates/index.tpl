<h1><?=at('links')?></h1>

<table class="table links">
	<tr>
    	<td colspan="6" id="buttons" class="hint" name="Add">
        	<a href="<?=_cfg('cmssite').'/#links/add'?>"><div class="add-image"></div><?=at('add_new')?> <?=strtolower(at('link'))?></a>
    	</td>
	</tr>

	<tr>
        <td width="30%" class="b"><?=at('link')?></td>
        <td width="30%" class="b"><?=at('sublink')?></td>
        <td width="20%" class="centered b"><?=at('only_for_logged_in')?></td>
        <td width="10%" class="b"><?=at('enaldisabl')?></td>
        <td width="10%" class="centered b"><?=at('actions')?></td>
    </tr>

	<tr>
		<td colspan="6" class="sortable">
		<?
		if ($module->links) {
			foreach($module->links as $v) {
				?>
				<ul class="link" attr-id="<?=$v->id?>">
					<li class="col1"><a href="<?=_cfg('cmssite').'/#links/edit/'.$v->id?>"><?=str_replace('web-link-','',$v->value)?></a></li>
					<li class="col2"></li>
                    <li class="col3 centered">
                        <?=($v->logged_in == 1 ? '<img src='._cfg('cmsimg').'/tick-small.png  class="hint" name="Page available only for logged in users"/>' : '')?>
                    </li>
                    <li class="col4 centered">
                        <a href="<?=_cfg('cmssite').'/#links/able/'.$v->id?>">
                            <?=($v->able == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Disable"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Enable"/>')?>
                            
                        </a>
                    </li>
					<li class="col5 centered">
						<a href="<?=_cfg('cmssite').'/#links/edit/'.$v->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
						<a href="javascript:void(0);" class="hint" name="Delete" onclick="TM.deletion('<?=_cfg('cmssite').'/#links/delete/'.$v->id?>'); return false;">
                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
                        </a>
					</li>

	                <?
	                if ($module->sublinks) {
	            	?>
	            	<ul class="sublinks">
	            	<?
		                foreach($module->sublinks as $vs) {
		                    if ($v->id == $vs->main_link) {
		                        ?>
		                        <li attr-id="<?=$vs->id?>">
									<div class="col1 centered"><a href="<?=_cfg('cmssite').'/#links/edit/'.$vs->id?>">=></a></div>
									<div class="col2"><a href="<?=_cfg('cmssite').'/#links/edit/'.$vs->id?>"><?=str_replace('web-link-','',$vs->value)?></a></div>
		                            <div class="col3 centered">
		                                <?=($vs->logged_in == 1 ? '<img src='._cfg('cmsimg').'/tick-small.png  class="hint" name="Page available only for logged in users"/>' : '')?>
		                            </div>
				                    <div class="col4 centered">
				                        <a href="<?=_cfg('cmssite').'/#links/able/'.$vs->id?>">
				                            <?=($vs->able == 1 ? '<img src='._cfg('cmsimg').'/enabled.png  class="hint" name="Disable"/>' : '<img src='._cfg('cmsimg').'/disabled.png  class="hint" name="Enable"/>')?>
				                        </a>
				                    </div>
									<div class="col5 centered">
										<a href="<?=_cfg('cmssite').'/#links/edit/'.$vs->id?>" class="hint" name="Edit"><img src="<?=_cfg('cmsimg')?>/edit.png" /></a> 
										<a href="javascript:void(0);" class="hint" name="Delete" onclick="TM.deletion('<?=_cfg('cmssite').'/#links/delete/'.$vs->id?>'); return false;">
				                            <img src="<?=_cfg('cmsimg')?>/cancel.png" />
				                        </a>
									</div>
								</li>
		                        <?
		                    }
		                }
            		?>
            		</ul>
            		<? 
            		}
            		?>
                </ul>
                <?
			}
		}
		?>
		</td>
	</tr>
</table>

<script>
	$(function() {
		$('.sortable').sortable({
			cancel: '.sublinks',
			placeholder: 'sortable-highlight',
			distance: 10,
			opacity: 0.7,
			item: '> ul.link',
			start: function(e, ui) {
		        ui.placeholder.height(ui.item.height());
		    },
			update: function() {
				var ids = [];
				$(this).find('ul').each(function() {
					ids.push($(this).attr('attr-id'));
				});
				TM.changeOrder('links', ids);
			}
		});

		$('.sublinks').sortable({

			axis: 'y',
			placeholder: 'sortable-highlight',
			distance: 5,
			opacity: 0.7,
			cursorAt: { bottom: 1 },
			item: '> li',
			start: function(e, ui) {
		        ui.placeholder.height(ui.item.height());
		    },
			update: function() {
				var ids = [];
				$(this).find('li').each(function() {
					ids.push($(this).attr('attr-id'));
				});

				TM.changeOrder('links', ids);
			}
		}).disableSelection();
	});
</script>