<section class="container page lol">

<div class="left-containers">
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('opponent_info')?></h1>
        </div>
        
        <div class="block-content opponent-info">
            <label><?=t('opponent')?></label>: <span id="opponentName"></span><br />
            <label><?=t('status')?></label>: <span id="opponentStatus"></span> (<span id="opponentSec"></span> sec)<br />
            <label><?=t('code')?></label>: <span id="tournamentCode">none</span>
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
                    <input type="text" id="chat-input" />
                    <div id="uploadScreen" class="attach-file" title="<?=t('attach_file')?>"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/profiler.js"></script>