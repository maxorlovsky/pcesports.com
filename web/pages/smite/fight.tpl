<section class="container page lol smite">

<div class="left-containers">
    <? if ($_SESSION['participant']->checked_in != 1) { ?>
    <div class="block check-in">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('check_in')?></h1>
        </div>
        
        <div class="block-content">
            <p><?=t('check_in_will_apear_in')?></p>
            
            <? if ($this->data->settings['tournament-checkin-smite-'.$this->server] == 1) {?>
            <div class="check-in-holder">
                <div class="button" id="checkInSmite"><?=t('check_in')?></div>
            </div>
            <? } ?>
        </div>
    </div>
    <? } ?>
    
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('opponent_info')?></h1>
        </div>
        
        <div class="block-content opponent-info">
            <label><?=t('opponent')?></label>: <span id="opponentName"></span><br />
            <label><?=t('status')?></label>: <span id="opponentStatus"></span> (<span id="opponentSec"></span> sec)<br />
            <label><?=t('enemy_list')?></label>: <textarea readonly="readonly" id="tournamentCode" style="height: 120px">none</textarea>
            <div class="clear"></div>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('battle_chat')?></h1>
        </div>
        
        <div class="block-content">
            <div class="notification"><?=t('battle_chat_notif1')?></div>
			<div class="notification"><?=t('battle_chat_notif2')?></div>
            <div class="chat">
                <div class="chat-content"></div>
                <div class="chat-input">
                    <input type="text" id="chat-input" placeholder="<?=t('enter_text')?>" />
                    <div id="uploadScreen" class="attach-file" attr-msg="<?=t('attach_file')?>"></div>
                    <div id="chatSound" class="chat-sound hint on" attr-msg="<?=t('turn_off_sound')?>"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<audio id="ping" src="<?=_cfg('static')?>/chat.ogg"></audio>

<script src="<?=_cfg('static')?>/js/profiler.js"></script>