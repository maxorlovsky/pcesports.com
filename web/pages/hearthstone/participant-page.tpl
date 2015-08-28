<section class="container page tournament">

<div class="left-containers">
    <? if ($_SESSION['participant']->checked_in != 1) { ?>
        <? if ($_GET['val4'] == 'verify') { ?>
            <p class="success-add"><?=t('hs_verify_in_progress')?></p>
            <br />
        <? } ?>

        <? if ($regged == 1) { ?>
            <p class="success-add"><?=t('registered_successfully')?></p>
            <br />
        <? } ?>
        
        <? if ($paymentVerified != 1) { ?>
            <p class="error-add"><?=t('participation_hsleague_not_verified')?></p>
        <? } else { ?>
            <p class="success-add"><?=t('participation_hsleague_verified')?></p>
            <br />
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
                
                <? if ($this->data->settings['tournament-checkin-hs'] == 1) {?>
                <div class="check-in-holder">
                    <? if ($paymentVerified != 1) { ?>
                        <div class="button tournamentOff">Check in is progress, but you're not verified, sorry</div>
                    <? } else { ?>
                        <div class="button checkIn" id="checkInHs"><?=t('check_in')?></div>
                    <? } ?>
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
                <label><?=t('enemy_heroes')?></label>
                <div class="pick-ban red hidden"><?=t('pick_your_ban')?></div>
                <div class="player-heroes bans"></div>
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
        
        <audio id="ping" src="<?=_cfg('static')?>/chat.ogg"></audio>
	<? } ?>
    
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content">
            <?=t('hs_participant_information_txt')?>
        </div>
    </div>
    
    <? if ($_SESSION['participant']->checked_in != 1) { ?>
    <div class="block">
        <a name="verification"></a>
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('verification')?></h1>
        </div>
        
        <div class="block-content verification-pay">
            <?=t('hs_verification_txt')?>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="max.orlovsky@gmail.com"> <!--max.orlovsky@gmail.com-->
                <input type="hidden" name="lc" value="LV">
                <input type="hidden" name="item_name" value="Pentaclick HS Participation - <?=$_SESSION['participant']->name?>">
                <input type="hidden" name="amount" value="3.50">
                <input type="hidden" name="currency_code" value="EUR">
                <input type="hidden" name="button_subtype" value="services">
                <input type="hidden" name="no_note" value="1">
                <input type="hidden" name="no_shipping" value="1">
                <input type="hidden" name="rm" value="1">
                <input type="hidden" name="return" value="<?=_cfg('site')?>/en/hearthstone/<?=$_SESSION['participant']->server?>/participant/verify/">
                <input type="hidden" name="cancel_return" value="<?=_cfg('site')?>/en/hearthstone/<?=$_SESSION['participant']->server?>/participant/">
                <input type="hidden" name="battletag" value="<?=$_SESSION['participant']->name?>">
                <button class="button">Pay for participation</button>
            </form>
        </div>
    </div>
    <? } ?>
</div>

<div style="display: none;" id="hsicons-holder">
    <div class="hsicons-small %hero% hint" attr-msg="%heroName%"></div>
</div>

<script src="<?=_cfg('static')?>/js/profiler.js"></script>