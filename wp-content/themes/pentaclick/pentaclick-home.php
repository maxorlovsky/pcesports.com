<div class="header-holder" id="home"></div>
<article class="content" id="home-content">
    <div class="twitch <?=(cOptions('promo-enabled')==1?'enabled':null)?>">
        <div id="player">
            <object type="application/x-shockwave-flash" height="500" width="620" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=pentaclick_tv2"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" /><param name="flashvars" value="hostname=www.twitch.tv&amp;channel=pentaclick_tv2&amp;auto_play=true&amp;start_volume=25" /></object><a href="http://www.twitch.tv//pentaclick_tv2" class="trk" style="padding:2px 0px 4px; display:block; width:345px; font-weight:normal; font-size:10px; text-decoration:underline; text-align:center;"><?=_e('watch_twitch', 'pentaclick')?> www.twitch.tv</a>
        </div>
        
        <div id="chat">
            <iframe id="chat_embed" src="http://twitch.tv/chat/embed?channel=pentaclick_tv2&amp;amp;popout_chat=true" height="500" width="350"></iframe>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <section class="gamesList" title="League of Legends">
        <a class="game" href="<?=LOLURL.'/'.qtrans_getLanguage()?>">
            <h1>League of Legends</h1>
            <img class="gray" src="<?php bloginfo('template_directory'); ?>/images/design/gamelist-picker-lol-0.png" alt="League of Legends" title="League of Legends" />
            <img class="colored" src="<?php bloginfo('template_directory'); ?>/images/design/gamelist-picker-lol-1.png" alt="League of Legends" title="League of Legends" />
            <p>
                <?=_e('next_tournament', 'pentaclick')?> #<?=cOptions('tournament-lol-number')?><br />
                <span><?=_e('registration_open', 'pentaclick')?></span> 1 <?=getMonth(3)?> 10:00<br />
                <span><?=_e('tournament_start', 'pentaclick')?></span> 29 <?=getMonth(3)?> 14:00<br />
                <span><?=_e('official_timezone', 'pentaclick')?></span> CET / GMT+1
            </p>
        </a>
        
        <a class="game" href="<?=HSURL.'/'.qtrans_getLanguage()?>" title="Hearthstone">
            <h1>Hearthstone</h1>
            <img class="gray" src="<?php bloginfo('template_directory'); ?>/images/design/gamelist-picker-hs-0.png" alt="Hearthstone" title="Hearthstone" />
            <img class="colored" src="<?php bloginfo('template_directory'); ?>/images/design/gamelist-picker-hs-1.png" alt="Hearthstone" title="Hearthstone" />
            <p>
                <?=_e('next_tournament', 'pentaclick')?> #<?=cOptions('tournament-hs-number')?><br />
                <span><?=_e('registration_open', 'pentaclick')?></span> 25 <?=getMonth(2)?> 10:00<br />
                <span><?=_e('tournament_start', 'pentaclick')?></span> 1 <?=getMonth(3)?> 14:00<br />
                <span><?=_e('official_timezone', 'pentaclick')?></span> CET / GMT+1
            </p>
        </a>
    </section>
</article>