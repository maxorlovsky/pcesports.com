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
            <div href="https://www.twitch.tv/<?=$v->name?>" class="block-content streamer <?=(isset($v->event)&&$v->event==1?'event':null)?> <?=($v->featured==1?'featured':null)?> <?=($v->onlineStatus==0?'alpha':null)?>" target="_blank" attr-id="<?=$v->id?>">
                <? if ($v->game != 'other') { ?>
                    <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <? } ?>
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?></label> <?=(isset($v->event)&&$v->event==1?'(Tournament stream)':null)?>
                <span class="viewers"><?=($v->onlineStatus==0?0:$v->viewers)?> <?=t('viewers')?></span>
            </div>
            <div class="block twitch <?=($this->pickedStream!=$v->id?'hidden_info':null)?>" id="stream_<?=$v->id?>">
                <div class="player">
                    <object type="application/x-shockwave-flash" height="600" width="790" id="live_embed_player_flash" data="https://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=$v->name?>"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="movie" value="https://www.twitch.tv/widgets/live_embed_player.swf" /><param name="flashvars" value="hostname=www.twitch.tv&amp;channel=<?=$v->name?>&amp;auto_play=true&amp;start_volume=25" /></object>
                </div>
            </div>
        <?
        	}
        }
        ?>
    </div>
</div>