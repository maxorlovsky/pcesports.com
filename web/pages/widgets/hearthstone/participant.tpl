<section class="container page tournament">

<div class="left-containers">
    <? if ($this->participant->checked_in != 1) { ?>

    <? if ($this->paymentNeeded == 1) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('payment_status')?></h1>
        </div>
        
        <div class="block-content">
            <? if ($this->participant->verified != 1) { ?>
                <p class="error-add"><?=t('participation_in_tournament_not_verified')?><br /><br /><?=t('skillz_payment_info')?></p>
            <? } else { ?>
                <p class="success-add"><?=t('participation_in_tournament_verified')?></p>
                <br />
            <? } ?>
        </div>
    </div>
    <? } ?>
    
    <div class="block check-in">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('check_in')?></h1>
        </div>
        
        <div class="block-content">
            <p><?=t('check_in_will_apear_in')?></p>
            
            <? if ($this->data->settings['tournament-checkin-hs-widget'] == 1) {?>
            <div class="check-in-holder">
                <? if ($this->paymentNeeded != 1) { ?>
                    <div class="button tournamentOff">Check in is progress, but you're not verified, sorry</div>
                <? } else { ?>
                    <div class="button checkIn" id="check-in-tournament"><?=t('check_in')?></div>
                <? } ?>
            </div>
            <? } else { ?>
            <div class="check-in-holder">
                <div class="button tournamentOff" id="fightStatus"><img src="<?=_cfg('img')?>/bx_loader.gif" style="width: 12px;"/></div>
            </div>
            <? } ?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('edit_information')?> "<?=$this->participant->name?>"</h1>
        </div>
        
        <div class="block-content">
            <p class="team-edit-completed success-add"><?=t('info_edited')?></p>
            <div id="join-form">
                <form id="da-form" method="post">
                    <div class="form-item" data-label="phone">
                        <input type="text" name="phone" placeholder="Phone number" value="<?=$this->participant->contact_info->phone?>" />
                        <div class="message hidden"></div>
                    </div>
                    
                    <div class="form-item" data-label="country">
                        <select name="country">
                            <option value="es" <?=($this->participant->contact_info->country=='es'?'selected="selected"':null)?>>Estonia</option>
                            <option value="lv" <?=($this->participant->contact_info->country=='lv'?'selected="selected"':null)?>>Latvia</option>
                            <option value="lt" <?=($this->participant->contact_info->country=='lt'?'selected="selected"':null)?>>Lithuania</option>
                        </select>
                        <div class="message hidden"></div>
                    </div>

                    <? for ($i=1;$i<=3;++$i) { ?>
                    <div class="form-item" data-label="hero<?=$i?>">
                        <select class="hero<?=$i?>" name="hero<?=$i?>">
                            <? foreach($this->heroes as $k => $v) {
                                $hero = hero.$i;
                            ?>
                                <option value="<?=$k?>" <?=($this->participant->contact_info->$hero==$k?'selected':null)?>><?=ucfirst($v)?></option>
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

                    <input type="hidden" name="participant" id="participant" value="<?=$_GET['val3']?>" />
                    <input type="hidden" name="link" id="link" value="<?=$_GET['val4']?>" />
                </form>
                <div class="clear"></div>
                <a href="javascript:void(0);" class="button" id="edit-in-tournament"><?=t('edit_information')?></a>
                
                <? if ($this->participant->verified != 1) { ?>
                    <a href="<?=$_SERVER['REQUEST_URI']?>/leave" class="button confirm" id="leave-tournament" attr-msg="<?=t('sure_to_leave')?>"><?=t('leave_tournament')?></a>
                <? } ?>
            </div>
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
                <div class="chat">
                    <div class="chat-content"></div>
                    <div class="chat-input">
                        <input type="text" id="chat-input" placeholder="<?=t('enter_text')?>" />
                        <div id="chatSound" class="chat-sound hint on" attr-msg="<?=t('turn_off_sound')?>"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="participant" id="participant" value="<?=$_GET['val3']?>" />
        <input type="hidden" name="link" id="link" value="<?=$_GET['val4']?>" />
        <audio id="ping" src="<?=_cfg('static')?>/chat.ogg"></audio>

        <div class="block" style="margin-top: 20px;">
            <div class="block-header-wrapper">
                <h1 class="bordered"><?=t('brackets')?></h1>
            </div>

            <div class="block-content participants">
                Available by link: <a href="http://pentaclick.challonge.com/exths1" target="_blank">http://pentaclick.challonge.com/exths1</a>
            </div>
        </div>

        <div style="display: none;" id="hsicons-holder">
            <div class="hsicons-small %hero% hint" attr-msg="%heroName%"></div>
        </div>
    <? } ?>
</div>