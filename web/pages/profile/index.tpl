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
                <p class="error-add"><?=t('name_not_set_add')?></p>
            <? } ?>
            <div class="fields">
                <label for="name"><?=t('name')?></label>
                <input name="name" id="name" type="text" value="<?=$this->data->user->name?>" placeholder="<?=t('name')?>*" />
            </div>
            
            <? if (!$this->data->user->email) { ?>
                <p class="error-add"><?=t('email_not_set_add')?></p>
            <? } ?>
            <div class="fields">
                <label for="email">Email</label>
                <input name="email" id="email" type="text" value="<?=$this->data->user->email?>" placeholder="Email" />
            </div>
            
            <div class="fields">
                <label for="avatar"><?=t('avatar')?></label>
                <div class="holder">Pick image (not yet working)</div>
                <input name="avatar" id="avatar" type="hidden" value="<?=$this->data->user->avatar?>" />
            </div>
            
            <a href="javascript:void(0);" class="button" id="updateProfile"><?=t('update_profile')?></a>
            
            <div class="fields">
                <label><?=t('registration_date')?></label>
                <p><?=date('d.m.Y H:i', strtotime($this->data->user->registration_date))?></p>
            </div>
            <div class="fields">
                <label><?=t('team_name')?></label>
                <p><i><?=t('none')?></i></p>
            </div>
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