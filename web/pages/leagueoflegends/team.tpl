<section class="container page lol">

<div class="left-containers">
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('edit_team')?> "<?=$_SESSION['participant']->name?>"</h1>
		</div>
		
		<div class="block-content">
			<p class="team-edit-completed success-add"><?=t('team_edited')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
                    <? for($i=1;$i<=7;++$i) { ?>
					<input type="text" name="mem<?=$i?>" placeholder="<?=t('member')?> #<?=$i?><?=($i<=5?'*':null)?>" value="<?=$players[$i]?>" />
					<div id="mem<?=$i?>-msg" class="message hidden"></div>
					<div class="clear"></div>
                    <? } ?>
                    <input type="hidden" name="server" value="<?=$this->server?>" />
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="edit-team"><?=t('edit_team')?></a>
			</div>
		</div>
	</div>
</div>

<script>
$('#edit-team').on('click', function() {
    PC.editTeam($(this));
});
</script>