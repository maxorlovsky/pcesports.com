<section class="container page lol">

<div class="left-containers">
    <? if ($_SESSION['participant']->checked_in != 1) { ?>
    <div class="block check-in">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('check_in')?></h1>
        </div>
        
        <div class="block-content">
            <p><?=t('check_in_will_apear_in')?></p>
            
            <? if ($this->data->settings['tournament-checkin-lol-'.$this->server] == 1) {?>
            <div class="check-in-holder">
                <div class="button" id="checkInLol"><?=t('check_in')?></div>
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
            <label><?=t('code')?></label>: <textarea readonly="readonly" id="tournamentCode">none</textarea>
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
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/profiler.js"></script>