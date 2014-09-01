<h1><?=at('dashboard')?></h1>

<div class="dashboard">

<div class="column">
    <div class="changelog box">
        <h4><?=at('cms_latest_changes')?></h4>
        <?=$module->line['changeLog']?>
        
        <a href="http://cms.themages.net/admin/updates/CHANGELOG.txt" target="_blank"><?=at('view_full')?> <?=at('changelog')?></a>
    </div>
	
	<div class="clear"></div>
</div>

<div class="column">
	<div class="logged_in_menu box">
		<div class="account_info"><?=at('logged_in_as')?> <span class="b"><?=$this->user->login?></span></div>
		<div class="clear"></div>
        <div class="centered b"><?=at('admin_pers_settings')?></div>
		<table class="table account_settings">
			<tr>
				<td width="50%" class="b"><?=at('language')?></td>
				<td width="50%">
					<select id="change_language" class="hint chosen" name="<?=at('hint_admin_language')?>" style="min-width: 100px">
						<? foreach(_cfg('allowedLanguages') as $v) { ?>
							<option value="<?=$v?>" <?=($v==$this->user->language?'selected="selected"':null)?> >
                                <?=at($v)?>
                            </option>
						<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="50%" class="b"><?=at('email')?></td>
				<td width="50%">
					<input type="text" id="adminEmail" value="<?=$this->user->email?>"/>
				</td>
			</tr>
            <tr>
				<td width="50%" class="b"><?=at('new_password')?></td>
				<td width="50%">
					<input type="password" id="adminPassword" value=""/>
				</td>
			</tr>
            <tr>
				<td width="50%" class="b"><?=at('current_password')?></td>
				<td width="50%">
					<input type="password" id="currentPassword" value=""/>
				</td>
			</tr>
            <tr><td colspan="2"><button class="updateProfile"><?=at('update_profile')?></button></td></tr>
		</table>
	</div>
	
	<div class="clear"></div>
</div>

<div class="column">
	<div class="version box">
		<h4><?=at('cms_information')?></h4>
		<table>
			<tr>
				<td width="80%"><?=at('you_ver_of_cms')?></td>
				<td width="20%" class="b">
					<?
					if ($module->line['version'] == $this->data->cmsSettings['version']) {
						echo $this->data->cmsSettings['version'];
					}
					else {
						echo '<font color="red">'.$this->data->cmsSettings['version'].'</font>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td width="80%"><?=at('latest_ver_cms')?></td>
				<td width="20%" class="b"><?=$module->line['version']?></td>
			</tr>
		</table>
		<div class="verdict">
			<? if ($module->line['version'] == $this->data->cmsSettings['version']) {?>
				<?=at('cms_up_to_date')?>
			<? } else {?>
                <?=at('cms_outdated')?><br ><br />
				<a href="javascript:void(0);" onclick="alert('Currently impossible to update'); return false;"><?=at('update')?></a>
			<? } ?>
		</div>
	</div>

	<div class="credits_block box">
		<h4>TheMages CMS</h4>
		<?=at('cms_detailed_info')?><br />
		Web: <a href="http://www.maxorlovsky.net" target="_blank">www.maxorlovsky.net</a><br />
		CMS Web: <a href="http://www.themages.net" target="_blank">www.themages.net</a><br />
		Email: <a href="mailto:max.orlovsky@gmail.com">max.orlovsky@gmail.com</a>
	</div>

	<div class="clear"></div>
</div>

<div class="clear"></div>

</div>

<script>

$('.updateProfile').on('click', function(){
	showMsg(2,strings['loading']);
	
	var query = {
        type: 'POST',
        data: {
        	control: 'updateProfile',
    		lang: $('#change_language').val(),
            email: $('#adminEmail').val(),
            password: $('#adminPassword').val(),
            currentPassword: $('#currentPassword').val()
		},
    	success: function(data) {
    		answer = data.split(';');
    		cleanMsg();
            msgAnswer = answer[0];
            if (msgAnswer > 0) {
                msgAnswer = 1;
            }
			showMsg(msgAnswer,answer[1]);
			messageTimer = setTimeout(cleanMsg, 3000);
            
            if (answer[0] >= 1) {
                $('#adminPassword').val('');
                $('#currentPassword').val('');
            }
            
            if (answer[0] == 2) {
				goDelay('', 3000);
			}
    	}
    }
	ajax(query);
});

</script>