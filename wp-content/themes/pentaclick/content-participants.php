<div class="holder" id="participants"></div>
<article class="participants-content-wrapper">
    <div class="content" id="participants-content">
        <? if (cOptions('tournament-on') == 0) { ?>
            <p class="empty-list"><?=_e('no_teams_registered', 'pentaclick')?></p>
        <? } else { ?>
            <? for($i=1;$i<=20;++$i) { ?>
            <div class="block" title="<?=_e('team_name', 'pentaclick')?> #<?=$i?>">
                <h3><?=_e('team_name', 'pentaclick')?></h3>
                <span class="team-num">#<?=$i?></span>
                <div class="clear"></div>
                <div class="hidden player-list">
                    <h4><?=_e('players', 'pentaclick')?>:</h4>
                    <ul>
                        <? for($j=1;$j<=7;++$j) { ?>
                            <li><a href="http://www.lolking.net/" target="_blank"><?=_e('member', 'pentaclick')?> #<?=$j?></a></li>
                        <? } ?>
                    </ul>
                </div>
            </div>
            <? } ?>
        <? } ?>
        <div class="clear"></div>
    </div>
</article>