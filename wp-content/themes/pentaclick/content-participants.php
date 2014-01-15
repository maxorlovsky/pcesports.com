<div class="holder" id="participants"></div>
<article class="participants-content-wrapper">
    <div class="content" id="participants-content">
        <!--<p class="empty-list">No teams registered</p>-->
        <? for($i=1;$i<=20;++$i) { ?>
        <div class="block" title="Team name #<?=$i?>">
            <h3>Team name</h3>
            <span class="team-num">#<?=$i?></span>
            <div class="clear"></div>
            <div class="hidden player-list">
                <h4>Players:</h4>
                <ul>
                    <? for($j=1;$j<=7;++$j) { ?>
                        <li><a href="http://www.lolking.net/" target="_blank">Member #<?=$j?></a></li>
                    <? } ?>
                </ul>
            </div>
        </div>
        <? } ?>
        <div class="clear"></div>
    </div>
</article>