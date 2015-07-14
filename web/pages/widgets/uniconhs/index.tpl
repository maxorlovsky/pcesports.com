<div class="hidden popup" id="rules-window">
    <div class="rules-inside">
        <h1>Hearthstone rules</h1>
        <?=t('unicon_hearthstone_tournament_rules')?>
    </div>
</div>

<section class="container tournament hs">

<div class="block registration">
	<div class="block-header-wrapper">
		<h1>UniCon Latvia 2015 Tournament</h1>
	</div>
	
	<div class="block-content signup">
		<div id="join-form">
            <p class="reg-completed success-add"><?=t('unicon_join_tournament_complete')?></p>

			<form id="da-form" method="post">
                <div class="form-item" data-label="battletag">
                    <input type="text" name="battletag" placeholder="<?=t('battle_tag')?>*" value="" />
                    <div class="message hidden"></div>
                </div>

                <div class="form-item" data-label="email">
                    <input type="text" name="email" placeholder="Email*" value="" />
                    <div class="message hidden"></div>
                </div>

                <div class="form-item" data-label="phone">
                    <input type="text" name="phone" placeholder="Phone number" value="" />
                    <div class="message hidden"></div>
                </div>
                
                <? for ($i=1;$i<=3;++$i) { ?>
                <div class="form-item" data-label="hero<?=$i?>">
                    <select class="hero<?=$i?>" name="hero<?=$i?>">
                        <option value="0"><?=t('pick_hero')?></option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>"><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div class="message hidden"></div>
                </div>
                <? } ?>

                <div class="form-item" data-label="agree">
                    <input type="checkbox" name="agree" id="agree" /><label for="agree"><?=t('agree_with_rules_hs_unicon')?></label>
                    <div class="message hidden"></div>
                </div>
                
                <div class="heroes-images">
                    <h6><?=t('your_classes')?></h6>
                    <? for ($i=1;$i<=4;++$i) { ?>
                    <div id="hero<?=$i?>img" class="hsicons" attr-picked=""></div>
                    <? } ?>
                </div>
                <div class="clear"></div>
			</form>
			<div class="clear"></div>
			<a href="javascript:void(0);" class="button" id="register-in-tournament"><?=t('join_tournament')?></a>
		</div>
    </div>
</div>

<? if ($this->participants) { ?>
<div class="block participants">
    <div class="block-header-wrapper">
        <h1><?=t('participants')?></h1>
    </div>
    
    <div class="block-content participants isotope-participants">
    <?
    $i = 1;
    if ($this->participants) {
        foreach($this->participants as $v) {
            ?>
            <div class="block" title="<?=$v->battletag?>">
                <div class="team-name"><?=$v->battletag?></div>
                <span class="team-num">#<?=$i?></span>
                <div class="clear"></div>
                <div class="player-heroes">
                    <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                    <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                    <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                </div>
            </div>
            <?
            
            ++$i;
        }
    }
?>
    </div>
</div>
<? } ?>

</section>

<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
var heroes = {
    1: 0,
    2: 0,
    3: 0 
};

$('.hero1, .hero2, .hero3').on('change keyup', function() {
    var getClass = $(this).attr('class');
    var name = $(this).find(':selected').text();
    var id = $(this).find(':selected').val();
    var num = getClass.substr(-1);
    
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
    
    heroes[num] = id;
    
    $.each($('#da-form select'), function(k, v) {
        $(this).find('option').attr('disabled', false);
    });
    
    $.each(heroes, function(k, v) {
        if (v != 0) {
            for (i=1;i<=3;++i) {
                if (i != k) {
                    $('.hero'+i).find('option[value="'+v+'"]').attr('disabled', true);
                }
            }
        }
    });
});
</script>