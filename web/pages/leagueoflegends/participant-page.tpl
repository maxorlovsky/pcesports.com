<section class="container page tournament">

<div class="left-containers">
    <? if ($_SESSION['participant']->checked_in != 1) { ?>
        <? if ($regged == 1) { ?>
            <p class="success-add"><?=t('participation_verified')?></p>
        <? } ?>
        
        <? if (!$this->logged_in) { ?>
            <p class="info-add"><?=t('participant_not_user')?></p>
        <? } else if ($_SESSION['participant']->user_id == 0) { ?>
            <div class="block check-in">
                <div class="block-header-wrapper">
                    <h1 class="bordered"><?=t('make_life_easier')?></h1>
                </div>
                
                <div class="block-content">
                    <p class="info-add"><?=t('participant_user_not_connected')?></p>
                    <h2 class="centered"><?=t('or')?></h2>
                    <div class="connect-team">
                        <div class="button" id="connectTeamToAccount"><?=t('connect_team_to_account')?></div>
                    </div>
                </div>
            </div>
        <? } ?>
        
        <div class="block check-in">
            <div class="block-header-wrapper">
                <h1 class="bordered"><?=t('check_in')?></h1>
            </div>
            
            <div class="block-content">
                <p><?=t('check_in_will_apear_in')?></p>
                
                <? if ($this->data->settings['tournament-checkin-lol-'.$this->server] == 1) {?>
                <div class="check-in-holder">
                    <div class="button checkIn" id="checkInLol"><?=t('check_in')?></div>
                </div>
                <? } else { ?>
                <div class="check-in-holder">
                    <div class="button tournamentOff" id="fightStatus"><img src="<?=_cfg('img')?>/bx_loader.gif" style="width: 12px;"/></div>
                </div>
                <? } ?>
            </div>
        </div>
    <? } else { ?>
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
                        <div id="chatSound" class="chat-sound hint on" attr-msg="<?=t('turn_off_sound')?>"></div>
                    </div>
                </div>
            </div>
        </div>
    <? } ?>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content">
            <p><?=t('brackets')?>: <a href="http://pentaclick.challonge.com/lol<?=$this->server?><?=$this->data->settings['lol-current-number-'.$this->server]?>/" target="_blank">http://pentaclick.challonge.com/lol<?=$this->server?><?=$this->data->settings['lol-current-number-'.$this->server]?>/</a></p>
            <?=t('participant_information_txt')?>
        </div>
    </div>
</div>

<audio id="ping" src="<?=_cfg('static')?>/chat.ogg"></audio>

<script src="<?=_cfg('static')?>/js/profiler.js"></script>