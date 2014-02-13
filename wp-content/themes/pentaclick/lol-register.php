<div class="holder" id="register"></div>
<article class="register-content-wrapper">
    <div class="content" id="register-content">
        <? if (cOptions('tournament-on-lol') == 0) { ?>
            <p class="reg-closed"><?=_e('tournament_reg_closed', 'pentaclick')?></p>
        <? } else { ?>
            <p class="reg-completed"><?=_e('tournament_reg_finished', 'pentaclick')?></p>
        <div class="hidden" id="join-form">
            <h1><?=_e('join_tournament', 'pentaclick')?> #<?=cOptions('tournament-lol-number')?></h1>
            <form id="da-form" method="post">
                <input type="text" name="team" placeholder="<?=_e('team_name', 'pentaclick')?>" />
                <div id="team-msg" class="message hidden"></div>
                <input type="text" name="email" placeholder="<?=_e('contact_email', 'pentaclick')?>" />
                <div id="email-msg" class="message hidden"></div>
                <input type="text" name="mem1" placeholder="<?=_e('cpt_nickname', 'pentaclick')?> (<?=_e('member', 'pentaclick')?> #1)" />
                <div id="mem1-msg" class="message hidden"></div>
                <? for($i=2;$i<=7;++$i) { ?>
                    <input type="text" name="mem<?=$i?>" placeholder="<?=_e('member', 'pentaclick')?> #<?=$i?>" />
                    <div id="mem<?=$i?>-msg" class="message hidden"></div>
                <? } ?>
                <textarea name="contact" placeholder="<?=_e('contact_info', 'pentaclick')?> <?=_e('contact_info_add', 'pentaclick')?>"></textarea>
                <div id="contact-msg" class="message hidden"></div>
            </form>
            <div class="clear"></div>
            <button id="add-team"><?=_e('join', 'pentaclick')?></button>
        </div>
        <button id="join-tournament"><?=_e('join_tournament', 'pentaclick')?> #<?=cOptions('tournament-lol-number')?></button>
        <? } ?>
    </div>
</article>