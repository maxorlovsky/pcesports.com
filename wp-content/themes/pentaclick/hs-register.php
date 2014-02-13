<div class="holder" id="register"></div>
<article class="register-content-wrapper">
    <div class="content" id="register-content">
        <? if (cOptions('tournament-on-hs') == 0) { ?>
            <p class="reg-closed"><?=_e('tournament_reg_closed', 'pentaclick')?></p>
        <? } else { ?>
            <p class="reg-completed"><?=_e('tournament_reg_finished', 'pentaclick')?></p>
        <div class="hidden" id="join-form">
            <h1><?=_e('join_tournament', 'pentaclick')?> #<?=cOptions('tournament-hs-number')?></h1>
            <form id="da-form" method="post">
                <input type="text" name="battletag" placeholder="<?=_e('battle_tag', 'pentaclick')?>" />
                <div id="battletag-msg" class="message hidden"></div>
                <input type="text" name="email" placeholder="<?=_e('contact_email', 'pentaclick')?>" />
                <div id="email-msg" class="message hidden"></div>
            </form>
            <div class="clear"></div>
            <button id="add-player"><?=_e('join', 'pentaclick')?></button>
        </div>
        <button id="join-tournament"><?=_e('join_tournament', 'pentaclick')?> #<?=cOptions('tournament-hs-number')?></button>
        <? } ?>
    </div>
</article>