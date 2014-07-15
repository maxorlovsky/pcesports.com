<section class="container page contacts">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Profile</h1>
        </div>
        <form class="block-content contact-form profile">
            <div id="error"><p></p></div>
            
            <? if (!$this->data->user->name) { ?>
                <p class="error-add">Your name is not set, please add it!</p>
            <? } ?>
            <div class="fields">
                <label for="name"><?=t('name')?></label>
                <input name="name" id="name" type="text" value="<?=$this->data->user->name?>" placeholder="<?=t('name')?>*" />
            </div>
            
            <? if (!$this->data->user->email) { ?>
                <p class="error-add">Your email is not set, please add it!</p>
            <? } ?>
            <div class="fields">
                <label for="email">Email</label>
                <input name="email" id="email" type="text" value="<?=$this->data->user->email?>" placeholder="Email" />
            </div>
            
            <a href="javascript:void(0);" class="button" id="updateProfile">Update profile</a>
            
            <div class="fields">
                <label>Registration date</label>
                <p><?=date('d.m.Y H:i', strtotime($this->data->user->registration_date))?></p>
            </div>
            <div class="fields">
                <label>Team name</label>
                <p><i>None</i></p>
            </div>
        </form>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Connections</h1>
        </div>
        <div class="block-content">
            <? foreach($this->data->user->socials as $v) { ?>
                <div class="">
                    <a href="javascript:void(0);" class="socialLogin" id="<?=$v->social?>"><img src="<?=_cfg('img')?>/<?=$v->social?>-login.png" /></a>
                </div>
            <? } ?>
        </div>
    </div>
</div>