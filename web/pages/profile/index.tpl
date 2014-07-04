<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Profile</h1>
        </div>

        <div class="block-content">
            <? if (!$this->data->user->name) { ?>
                <p class="error-add">Your name is not set, please <a href="<?=_cfg('href')?>/profile/edit">add it</a>!</p>
            <? } ?>
            <? if (!$this->data->user->email) { ?>
                <p class="error-add">Your email is not set, please <a href="<?=_cfg('href')?>/profile/edit">add it</a>!</p>
            <? } ?>
            <div>Registration date: <?=$this->data->user->registration_date?></div>
            <div>Skype: <?=$this->data->user->skype?></div>
            <div>Steam account: <?=$this->data->user->skype?></div>
            <div>Website: <?=$this->data->user->skype?></div>
            <div>Tournament participations: <?=$this->data->user->skype?></div>
            <div>Achievements</div>
        </div>
    </div>
</div>