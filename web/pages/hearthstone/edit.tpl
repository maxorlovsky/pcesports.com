<section class="container page lol">

<div class="left-containers">
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('edit_information')?> "<?=$_SESSION['participant']->name?>"</h1>
		</div>
		
		<div class="block-content">
			<p class="team-edit-completed success-add"><?=t('info_edited')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="email" placeholder="Email*" value="<?=$editData->email?>" />
					<div id="email-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <input type="text" name="phone" placeholder="Phone number" value="<?=$editData->contact_info->phone?>" />
					<div id="phone-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <?/*<select class="hero1" name="hero1">
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>" <?=($editData->hero1==$k?'selected':null)?>><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div id="hero1-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <select class="hero2" name="hero2">
                        <option value="0"><?=t('pick_hero')?></option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>" <?=($editData->hero2==$k?'selected':null)?>><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div id="hero2-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <div class="heroes-images">
                        <h6><?=t('your_classes')?></h6>
                        <div id="hero1img" class="hsicons" attr-picked=""></div>
                        <div id="hero2img" class="hsicons" attr-picked=""></div>
                    </div>*/?>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="edit-player-lan"><?=t('edit_information')?></a>
			</div>
		</div>
	</div>
</div>

<script>
$('#edit-player-lan').on('click', function() {
    PC.editPlayerLan($(this));
});

$('.hero1, .hero2').on('change keyup', function() {
    var getClass = $(this).attr('class');
    var name = $(this).find(':selected').text();
    var id = $(this).find(':selected').val();
    
    if (getClass == 'hero1') {
        var picked = $('#hero1img').attr('attr-picked');
        $('#hero1img').removeClass(picked);
        
        if (id == 0) {
            $('#hero1img').removeClass('active');
        }
        else {
            $('#hero1img').addClass(name.toLowerCase());
            $('#hero1img').addClass('active');
            $('#hero1img').attr('attr-picked', name.toLowerCase());
        }
    }
    else {
        var picked = $('#hero2img').attr('attr-picked');
        $('#hero2img').removeClass(picked);
        
        if (id == 0) {
            $('#hero2img').removeClass('active');
        }
        else {
            $('#hero2img').addClass(name.toLowerCase());
            $('#hero2img').addClass('active');
            $('#hero2img').attr('attr-picked', name.toLowerCase());
        }
    }
});

$('.hero1, .hero2').trigger('change');
</script>