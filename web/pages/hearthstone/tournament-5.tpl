<section class="container page lol">

<div class="left-containers">

<?/*<div class="block">
    <div class="block-header-wrapper">
        <h1 class="bordered">Broadcast/VOD</h1>
    </div>
    
    <div class="block-content vods">
        Not yet available
        <?=t('not_available')?>
    </div>
</div>*/?>

<?/*<div class="block">
    <div class="block-header-wrapper">
        <h1 class="bordered">Join tournament #<?=$id?></h1>
    </div>
    
    <div class="block-content">
        (<p class="reg-completed success-add">Registration is almost complete, in a few moment you will receive an email with a link. Please use it to verify your participation.</p>
        <div class="hidden" id="join-form">
            <form id="da-form" method="post">
                <input type="text" name="battletag" placeholder="Battle Tag" />
                <div id="battletag-msg" class="message hidden"></div>
                <div class="clear"></div>
                <input type="text" name="email" placeholder="Contact email" />
                <div id="email-msg" class="message hidden"></div>
            </form>
            <div class="clear"></div>
            <a href="javascript:void(0);" class="button" id="add-player">Join tournament #<?=$id?></a>
        </div>
        <p>Registration closed</p>
    </div>
</div>*/?>

<div class="block">
    <div class="block-header-wrapper">
        <h1 class="bordered"><?=t('information')?></h1>
    </div>
    
    <div class="block-content tournament-rules">
        <?=t('hearthstone_tournament_information_5')?>
        <?/*<p>Registration opens <strong>15 may 2014</strong>, 10:00 GMT-0</p>
        <p>Games starts <strong>24 may 2014</strong>, 10:00 GMT-0</p>
        <p>Prize (1st place) – 15€</p>
        <p>Prize (2nd place) – 10€</p>
        <p>Prize (3rd place) – 5€</p>
        <p>Prize money will be sent via Paypal</p>*/?>
        <a href="<?=_cfg('href')?>/hearthstone"><?=t('global_tournament_rules')?>Global tournament format and rules</a>
    </div>
</div>