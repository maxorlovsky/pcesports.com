<div class="header-holder" id="home"></div>
<article class="content" id="home-content">
    <div class="twitch <?=(cOptions('promo-enabled')==1?'enabled':null)?>">
        <div id="player">
            <object type="application/x-shockwave-flash" height="500" width="620" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=cOptions('twitch-link')?>"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" /><param name="flashvars" value="hostname=www.twitch.tv&amp;channel=<?=cOptions('twitch-link')?>&amp;auto_play=true&amp;start_volume=25" /></object><a href="http://www.twitch.tv//<?=cOptions('twitch-link')?>" class="trk" style="padding:2px 0px 4px; display:block; width:345px; font-weight:normal; font-size:10px; text-decoration:underline; text-align:center;"><?=_e('watch_twitch', 'pentaclick')?> www.twitch.tv</a>
        </div>
        
        <div id="chat">
            <iframe id="chat_embed" src="http://twitch.tv/chat/embed?channel=<?=cOptions('twitch-link')?>&amp;amp;popout_chat=true" height="500" width="350"></iframe>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <div class="promo lol">
        <? if (cOptions('tournament-on-lol') == 1) { ?>
        <div class="reg-but-holder"><a href="#register" class="scroll" id="reg-button" title="<?=_e('register_in_tournament', 'pentaclick')?>"><?=_e('register_in_tournament', 'pentaclick')?></a></div>
        <? } ?>
    </div>
</article>