<section class="container page lol">

<div class="left-containers">
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('edit_team')?> "<?=$_SESSION['participant']->name?>"</h1>
		</div>
		
		<div class="block-content">
			<p class="reg-completed success-add"><?=t('team_edited')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
                    <? for($i=1;$i<=7;++$i) { ?>
					<input type="text" name="mem<?=$i?>" placeholder="<?=t('member')?> #<?=$i?>" value="<?=$players[$i]?>" />
					<div id="mem<?=$i?>-msg" class="message hidden"></div>
					<div class="clear"></div>
                    <? } ?>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="edit-team"><?=t('edit_team')?></a>
			</div>
		</div>
	</div>
</div>

<script>
$('#edit-team').on('click', function() {
    if (formInProgress == 1) {
        return false;
    }
    
    formInProgress = 1;
    $('#da-form .message').hide();
    $('#da-form .message').removeClass('error success');
    $('.reg-completed').hide();
    $(this).addClass('alpha');
    
    var query = {
        type: 'POST',
        dataType: 'json',
        data: {
            ajax: 'editInLOL',
            form: $('#da-form').serialize()
        },
        success: function(answer) {
            $('#edit-team').removeClass('alpha');
            formInProgress = 0;
            
            $.each(answer.err, function(k, v) {
                answ = v.split(';');
                $('#'+k+'-msg').html(answ[1]);
                $('#'+k+'-msg').show();
                if (answ[0] == 1) {
                    $('#'+k+'-msg').addClass('success');
                }
                else {
                    $('#'+k+'-msg').addClass('error');
                }
            });
            
            if (answer.ok == 1) {
                $('.reg-completed').slideDown(1000);
            }
        },
        error: function() {
            $('#edit-team').removeClass('alpha');
            formInProgress = 0;
            
            alert('Something went wrong... Contact admin at info@pcesports.com');
        }
    }
    ajax(query);
});
</script>