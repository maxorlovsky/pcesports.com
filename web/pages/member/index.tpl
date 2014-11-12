<section class="container page contacts">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=$this->member->name?> | <?=t('profile')?></h1>
        </div>
        <div class="block-content member">
            <div class="avatar">
                <img src="<?=_cfg('img')?>/avatar/<?=$this->member->avatar?>.jpg" />
            </div>
            <div class="information">
                <p><label><?=t('name')?></label> <?=$this->member->name?></p>
                <p><label><?=t('registration_date')?></label> <?=date('d.m.Y', strtotime($this->member->registration_date))?></p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper member-summoners">
            <h1 class="bordered"><?=t('summoners_accounts')?></h1>
        </div>
        <div class="block-content member">
            <?=dump($this->member)?>
        </div>
    </div>
    
    <?/*<div class="block connections">
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
    </div>*/?>
</div>