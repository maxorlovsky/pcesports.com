<section class="container page contacts">

<div class="left-containers">
    <div class="block streamers edit-streamers">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('added_channels')?></h1>
        </div>
        <?
		if ($this->additional->streams) {
        	foreach($this->additional->streams as $v) {
        ?>
            <div class="block-content streamer" target="_blank" attr-id="<?=$v->id?>">
                <? if ($v->game != 'other') { ?>
                    <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <? } ?>
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?></label>
                
                <span class="viewers editStreamerAction">
                    <select class="change_game">
                        <? foreach(_cfg('streamGames') as $k2 => $v2) { ?>
                        <option value="<?=$k2?>" <?=($v->game==$k2?'selected="selected"':null)?>><?=t($v2)?></option>
                        <? } ?>
                    </select>
                    <a href="javascript:void(0);" id="removeStreamer"><?=t('remove')?></a>
                </span>
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
        </div>
    </div>
    
    <div class="block add-streamer">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('how_to_be_featured')?></h1>
        </div>
        
        <div class="block-content">
            <?=t('become_featured_streamer')?>
        </div>
    </div>
</div>