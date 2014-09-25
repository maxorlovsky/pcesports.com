<section class="container page contacts">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('profile')?></h1>
        </div>
        <form class="block-content contact-form profile">
            <? if ($regComplete == 1) { ?>
                <div class="success-add registration-complete"><?=t('registration_success')?></div>
            <? } ?>
            
            <div id="success"><p></p></div>
            <div id="error"><p></p></div>
            
            <? if (!$this->data->user->name) { ?>
                <p class="error-add" id="name_not_set"><?=t('name_not_set_add')?></p>
            <? } ?>
            <div class="fields">
                <label for="name"><?=t('name')?></label>
                <input name="name" id="name" type="text" value="<?=$this->data->user->name?>" placeholder="<?=t('name')?>*" />
            </div>
            
            <? if (!$this->data->user->email) { ?>
                <p class="error-add" id="email_not_set"><?=t('email_not_set_add')?></p>
            <? } ?>
            <div class="fields">
                <label for="email">Email</label>
                <input name="email" id="email" type="text" value="<?=$this->data->user->email?>" placeholder="Email" />
            </div>
            
            <div class="fields">
                <label for="avatar"><?=t('avatar')?></label>
                <div class="holder">
                    <div class="avatars-list">
                        <? foreach($avatars as $v) { ?>
                        <div class="avatar-block <?=($this->data->user->avatar==str_replace('.jpg','',$v)?'picked':null)?>" attr-id="<?=str_replace('.jpg','',$v)?>">
                            <img src="<?=_cfg('avatars')?>/<?=$v?>" style="height: 50px;"/>
                        </div>
                        <? } ?>
                        <div class="clear"></div>
                    </div>
                    <?//=?>
                </div>
                <input name="avatar" id="avatar" type="hidden" value="<?=$this->data->user->avatar?>" />
            </div>
            
            <div class="fields">
                <label for="timezone"><?=t('timezone')?></label>
                <select name="timezone" id="timezone">
                    <? foreach($timezoneSelector as $k => $v) { ?>
                        <option value="<?=$k?>" <?=($pickedTimezone==$k?'selected':null)?>><?=$v?></option>
                    <? } ?>
                </select>
            </div>
            
            <div class="fields">
                <label for="subscribe"><?=t('receive_emails')?></label>
                <select name="subscribe" id="subscribe">
                    <option value="all"><?=t('all')?></option>
                    <option value="lol"><?=t('only_about_lol')?></option>
                    <option value="hs"><?=t('only_about_hs')?></option>
                    <option value="none"><?=t('turn_off')?></option>
                </select>
            </div>
            
            <a href="javascript:void(0);" class="button" id="updateProfile"><?=t('update_profile')?></a>
            
            <div class="fields">
                <label><?=t('registration_date')?></label>
                <p><?=date('d.m.Y H:i', strtotime($this->data->user->registration_date) + $this->data->user->timezone)?></p>
            </div>
            <!--<div class="fields">
                <label><?=t('team_name')?></label>
                <p><i><?=t('none')?></i></p>
            </div>-->
        </form>
    </div>
    
    <div class="block connections">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('connections')?></h1>
        </div>
        <div class="block-content">
            <? foreach(_cfg('social') as $k => $v) { ?>
                <div class="status-holder">
                    <img src="<?=_cfg('img')?>/<?=$k?>-login.png" />
                    <? if (in_array($k, $this->data->user->socials->connected)) { ?>
                        <div class="connect-line"></div>
                        <div class="status">
                            <a href="javascript:void(0);" class="connected socialDisconnect" id="<?=$k?>"><?=t('connected')?></a>
                        </div>
                    <? } else {?>
                        <div class="connect-line dashed"></div>
                        <div class="status">
                            <a href="javascript:void(0);" class="disconnected socialConnect" id="<?=$k?>"><?=t('disconnected')?></a>
                        </div>
                    <? } ?>
                </div>
            <? } ?>
        </div>
    </div>
</div>