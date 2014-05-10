<div class="right-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Next tournaments</h1>
        </div>
        <?
        $i = 0;
        foreach($this->serverTimes as $v) {
        ?>
        <div class="block-content <?=($i==0?'next-tournaments':'incoming-tournament')?> hint" attr-msg="<?=date('d/m H:i', $v['time'])?>">
            <? if ($i!=0) { ?>
                <div class="tourn-name"><?=$v['name']?> #<?=$v['id']?><br /><?=$v['status']?></div>
            <? } else { ?>
                <h2><?=$v['name']?> #<?=$v['id']?><br /><?=$v['status']?></h2>
            <? } ?>
            
            <div class="timer" attr-time="<?=intval($v['time'] - time())?>"><img src="<?=_cfg('img')?>/bx_loader.gif" /></div>
            <a href="#" class="button">Join</a>
            
            <? if ($i!=0) { ?>
                <div class="clear"></div>
            <? } ?>
        </div>
        <?
            $i = 1;
        }
        ?>
    </div>
    <?/*
    <div class="block streamers">
        <div class="block-header-wrapper">
            <h1 class="bordered">Streamers</h1>
        </div>
        <a href="http://www.twitch.tv/pentaclick_tv" class="block-content streamer featured">
            <img class="game-logo" src="http://clgaming.net/interface/img/game/lol.png" />
            <label class="streamer-name">Pentaclick TV</label>
            <span class="viewers">200 viewers</span>
        </a>
        <a href="#" class="block-content streamer">
            <img class="game-logo" src="http://clgaming.net/interface/img/game/d3.png" />
            <label class="streamer-name">Aven</label>
            <span class="viewers">9 viewers</span>
        </a>
        <a href="#" class="block-content streamer">
            <img class="game-logo" src="http://clgaming.net/interface/img/game/hs.png" />
            <label class="streamer-name">Soldecroix</label>
            <span class="viewers">3 viewers</span>
        </a>
        <a href="#" class="block-content streamer">
            <img class="game-logo" src="http://clgaming.net/interface/img/game/wow.png" />
            <label class="streamer-name">Maxtream</label>
            <span class="viewers">2 viewers</span>
        </a>
        <a href="#" class="block-content streamer">
            <img class="game-logo" src="http://clgaming.net/interface/img/game/sc2.png" />
            <label class="streamer-name">Demo</label>
            <span class="viewers">0 viewers</span>
        </a>
    </div>
    */?>
    <div class="block fb">
        <div class="block-header-wrapper">
            <h1 class="bordered">Like us!</h1>
        </div>
        <div class="facebook-holder block-content">
            <div class="fb-like-box" data-href="https://www.facebook.com/pentaclickesports" data-colorscheme="light" data-width="100%" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
        </div>
    </div>

    <div class="block fb">
        <div class="block-header-wrapper">
            <h1 class="bordered">@Tweet</h1>
        </div>
        <div class="twitter-holder block-content">
            <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/pentaclick"  data-widget-id="422786485738147841">Tweets by @pentaclick</a>
        </div>
    </div>
</div>

<div class="clear"></div>