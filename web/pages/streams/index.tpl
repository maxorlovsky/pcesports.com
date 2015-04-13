<section class="container page streams">

<div class="left-containers">
    <div class="block streamers">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('streams')?></h1>
        </div>
        <?
		if ($this->streams) {
        	foreach($this->streams as $v) {
        ?>
            <div href="http://www.twitch.tv/<?=$v->name?>" class="block-content streamer <?=(isset($v->event)&&$v->event==1?'event':null)?> <?=($v->featured==1?'featured':null)?> <?=($v->onlineStatus==0?'alpha':null)?>" target="_blank" attr-id="<?=$v->id?>">
                <? if ($v->game != 'other') { ?>
                    <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <? } ?>
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?></label> <?=(isset($v->event)&&$v->event==1?'(Tournament stream)':null)?>
                <span class="viewers"><?=($v->onlineStatus==0?0:$v->viewers)?> <?=t('viewers')?></span>
            </div>
            <div class="block twitch <?=($this->pickedStream!=$v->id?'hidden_info':null)?>" id="stream_<?=$v->id?>">
                <div class="player">
                    <object type="application/x-shockwave-flash" height="600" width="790" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=$v->name?>"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" /><param name="flashvars" value="hostname=www.twitch.tv&amp;channel=<?=$v->name?>&amp;auto_play=true&amp;start_volume=25" /></object>
                </div>
            </div>
        <?
        	}
        }
        ?>
    </div>
    
    <div class="block add-streamer">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('add_streamer')?></h1>
        </div>
        
        <div class="block-content">
            <? if ($this->logged_in) { ?>
                <form class="streamer-form contact-form">
                    <div id="error"><p></p></div>
        	
                    <div class="fields">
                        <label for="name"><?=t('name')?></label>
                        <input name="name" id="name" type="text" placeholder="<?=t('name_or_link_on')?> Twitch.tv*" />
                    </div>
                    <div class="fields">
                        <label for="game"><?=t('game')?></label>
                        <select name="game" id="game">
                            <? foreach(_cfg('streamGames') as $k => $v) { ?>
                            <option value="<?=$k?>"><?=t($v)?></option>
                            <? } ?>
                        </select>
                    </div>
                    
                    <a href="javascript:void(0);" class="button" id="submitStreamer"><?=t('send_form')?></a>
                </form>
                
                <div class="success-sent"><p></p></div>
            <? } else { ?>
                <?=t('login_to_add_streamer')?>
            <? } ?>
        </div>
    </div>
    
    <? if ($this->logged_in) { ?>
    <div class="block add-streamer">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('how_to_be_featured')?></h1>
        </div>
        
        <div class="block-content">
            <?=t('become_featured_streamer')?>
        </div>
    </div>
    <? } ?>
    
</div>