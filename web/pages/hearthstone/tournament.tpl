<section class="container page lol">

<div class="left-containers">
	<? if ($this->data->settings['tournament-reg-hslan'] == 1 && !isset($_SESSION['participant']) && $_SESSION['participant']->game != 'hslan') { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('signin_league')?></h1>
		</div>
		
		<div class="block-content">
            <p class="info-add"><?=t('this_is_lan_event')?></p>
			<p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="battletag" placeholder="<?=t('battle_tag')?>*" />
					<div id="battletag-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="email" placeholder="Email*" />
					<div id="email-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <input type="text" name="phone" placeholder="<?=t('phone_number')?>" />
					<div id="phone-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <input type="checkbox" name="agree" id="agree" /><label for="agree"><?=t('agree_with_rules_hslan')?></label>
					<div id="agree-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <?/*<select class="hero1" name="hero1">
                        <option value="0"><?=t('pick_hero')?></option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>"><?=ucfirst($v)?></option>
                        <? } ?>
                    </select>
                    <div id="hero1-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <select class="hero2" name="hero2">
                        <option value="0"><?=t('pick_hero')?></option>
                        <? foreach($this->heroes as $k => $v) { ?>
                            <option value="<?=$k?>"><?=ucfirst($v)?></option>
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
				<a href="javascript:void(0);" class="button" id="add-player-lan"><?=t('join_tournament')?></a>
			</div>
		</div>
	</div>
	<? } ?>
    
    <?/*<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('tournament_rules')?></h1>
        </div>
        
        <div class="block-content">
            <?=t('hearthstone_lan_tournament_information')?>
        </div>
    </div>*/?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('schedule')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
            <?=t('hearthstone_lan_schedule_information')?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content groups">
            <?
            $j = 0;
            foreach($this->groups as $k => $v) {
            ?>
                <div class="group">
                    <div class="header">
                        <h3><?=t('group')?> <?=$v?></h3>
                        <span class="place"><?=t('score')?></span>
                        <div class="clear"></div>
                    </div>
                    <div class="group-list">
                        <?
                        if ($this->participants) {
                            $i = 1;
                            foreach($this->participants as $p) {
                                if ($p->seed_number == $k) {
                                ?>
                                <div class="holder">
                                    <span class="player-num"><?=$i?></span>
                                    <span class="player-name"><?=$p->name?></span>
                                    <span class="player-score"><?=(isset($p->contact_info->place)&&$p->contact_info->place?$p->contact_info->place:0)?></span>
                                    <div class="clear"></div>
                                </div>
                                <?
                                ++$i;
                                }
                            }
                            
                            if ($i == 1) {
                                ?><div class="holder empty"><?=t('group_empty')?></div><?
                            }
                        }
                        else {
                            ?><div class="holder empty"><?=t('group_empty')?></div><?
                        }
                        ?>
                    </div>
                </div>
            <?
                if ($j%2 == 1) {
                    echo '<div class="clear"></div>';
                }
                
                ++$j;
            }
            ?>
			<div class="clear"></div>
        </div>
    </div>
</div>

<script>
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

$('#add-player-lan').on('click', function() {
    PC.addLanPlayer();
});
</script>