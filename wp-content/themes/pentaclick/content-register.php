<div class="holder" id="register"></div>
<article class="register-content-wrapper">
    <div class="content" id="register-content">
        <? if (cOptions('tournament-on') == 0) { ?>
            <p class="reg-closed"><?=_e('tournament_reg_closed', 'pentaclick')?></p>
        <? } else { ?>
        <div class="hidden" id="join-form">
            <h1><?=_e('join_tournament', 'pentaclick')?> #<?=cOptions('tournament-lol-number')?></h1>
            <form id="da-form" action="" method="post">
                <input type="text" name="team" placeholder="<?=_e('team_name', 'pentaclick')?>" />
                <div id="team-msg" class="message hidden"></div>
                <input type="text" name="email" placeholder="<?=_e('contact_email', 'pentaclick')?>" />
                <div id="email-msg" class="message hidden"></div>
                <input type="text" name="mem1" placeholder="<?=_e('cpt_nickname', 'pentaclick')?> (<?=_e('member', 'pentaclick')?> #1)" />
                <div id="mem1-msg" class="message hidden"></div>
                <input type="text" name="mem2" placeholder="<?=_e('member', 'pentaclick')?> #2" />
                <div id="mem2-msg" class="message hidden"></div>
                <input type="text" name="mem3" placeholder="<?=_e('member', 'pentaclick')?> #3" />
                <div id="mem3-msg" class="message hidden"></div>
                <input type="text" name="mem4" placeholder="<?=_e('member', 'pentaclick')?> #4" />
                <div id="mem4-msg" class="message hidden"></div>
                <input type="text" name="mem5" placeholder="<?=_e('member', 'pentaclick')?> #5" />
                <div id="mem5-msg" class="message hidden"></div>
                <input type="text" name="mem6" placeholder="<?=_e('member', 'pentaclick')?> #6" />
                <div id="mem6-msg" class="message hidden"></div>
                <input type="text" name="mem7" placeholder="<?=_e('member', 'pentaclick')?> #7" />
                <div id="mem7-msg" class="message hidden"></div>
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