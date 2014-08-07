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
            <a href="http://www.twitch.tv/<?=$v->name?>" class="block-content streamer <?=($v->featured==1?'featured':null)?> <?=($v->onlineStatus==0?'alpha':null)?>" target="_blank">
                <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?></label>
                <span class="viewers"><?=($v->onlineStatus==0?0:$v->viewers)?> <?=t('viewers')?></span>
            </a>
        <?
        	}
        }
        ?>
    </div>
</div>