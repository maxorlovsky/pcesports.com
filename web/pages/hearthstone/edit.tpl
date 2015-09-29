<section class="container page tournament">

<div class="left-containers">
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('edit_information')?> "<?=$_SESSION['participant']->name?>"</h1>
		</div>
		
		<div class="block-content">
			<p class="team-edit-completed success-add"><?=t('info_edited')?></p>
			<div id="join-form">
				<form id="da-form" method="post">

                    <? if (!$this->logged_in) { ?>
                        <div class="form-item" data-label="email">
                            <input type="text" name="email" placeholder="Email*" value="<?=$editData->email?>" />
                            <div class="message hidden"></div>
                        </div>
                    <? } ?>
                    
                    <? for ($i=1;$i<=3;++$i) { ?>
                    <div class="form-item" data-label="hero<?=$i?>">
                        <select class="hero<?=$i?>" name="hero<?=$i?>">
                            <? foreach($this->heroes as $k => $v) {
                                $hero = hero.$i;
                            ?>
                                <option value="<?=$k?>" <?=($editData->contact_info->$hero==$k?'selected':null)?>><?=ucfirst($v)?></option>
                            <? } ?>
                        </select>
                        <div class="message hidden"></div>
                    </div>
                    <? } ?>
                    
                    <div class="heroes-images">
                        <h6><?=t('your_classes')?></h6>
                        <? for ($i=1;$i<=3;++$i) { ?>
                        <div id="hero<?=$i?>img" class="hsicons" attr-picked=""></div>
                        <? } ?>
                    </div>
                    <div class="clear"></div>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="edit-in-tournament"><?=t('edit_information')?></a>
			</div>
		</div>
	</div>
</div>

<script>
$('#edit-in-tournament').on('click', function() {
    PC.editParticipant('HS');
});

$('.hero1, .hero2, .hero3').on('change keyup', function() {
    var getClass = $(this).attr('class');
    var name = $(this).find(':selected').text();
    var id = $(this).find(':selected').val();
    
    var picked = $('#'+getClass+'img').attr('attr-picked');
    $('#'+getClass+'img').removeClass(picked);
    
    if (id == 0) {
        $('#'+getClass+'img').removeClass('active');
    }
    else {
        $('#'+getClass+'img').addClass(name.toLowerCase());
        $('#'+getClass+'img').addClass('active');
        $('#'+getClass+'img').attr('attr-picked', name.toLowerCase());
    }
});

$('.hero1, .hero2, .hero3').trigger('change');
</script>