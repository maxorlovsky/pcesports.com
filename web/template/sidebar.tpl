<script>
	var requireStatus = 0;
	<? if (isset($_SESSION['participant']) && $_SESSION['participant']->id) { ?>
	var requireStatus = 1;
	<? } ?>
</script>
	
<div class="right-containers">
    <? if (isset($_SESSION['participant']) && $_SESSION['participant']->id) { ?>
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class="bordered"><?=t('panel')?> (<?=(strlen($_SESSION['participant']->name)>10?substr($_SESSION['participant']->name,0,25):$_SESSION['participant']->name)?>)</h1>
            </div>
            
			<? if ($_SESSION['participant']->game == 'hs') { ?>
            <ul class="panel-links">
                <li><a href="<?=_cfg('href')?>/hearthstone/participant/"><?=t('information')?></a></li>
                <li><a href="<?=_cfg('href')?>/hearthstone/participant/fight"><?=t('fight_status')?> (<span id="fightStatus"><img src="<?=_cfg('img')?>/bx_loader.gif" style="width: 12px;"/></span>)</a></li>
				<? if ($this->data->settings['tournament-start-hs'] == 1) {?>
                <li><a href="<?=_cfg('href')?>/hearthstone/participant/surrender" class="confirm" id="lostBattle" attr-msg="<?=t('sure_to_surrender')?>"><?=t('i_lost')?></a></li>
				<? } else { ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/participant/leave" class="confirm" attr-msg="<?=t('sure_to_surrender')?>"><?=t('leave_tournament')?></a></li>
				<? } ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/participant/exit"><?=t('exit_panel')?></a></li>
            </ul>
			<? } else { ?>
			<ul class="panel-links">
                <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/"><?=t('information')?></a></li>
                <? if ($this->data->settings['tournament-start-lol'] != 1) {?>
                    <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/team"><?=t('edit_team')?></a></li>
                <? } ?>
                <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/fight"><?=t('fight_status')?> (<span id="fightStatus"><img src="<?=_cfg('img')?>/bx_loader.gif" style="width: 12px;"/></span>)</a></li>
				<? if ($this->data->settings['tournament-start-lol'] == 1) {?>
                    <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/surrender" class="confirm" id="lostBattle" attr-msg="<?=t('sure_to_surrender')?>"><?=t('i_lost')?></a></li>
				<? } else { ?>
                    <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/leave" class="confirm" attr-msg="<?=t('sure_to_surrender')?>"><?=t('leave_tournament')?></a></li>
				<? } ?>
                <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/exit"><?=t('exit_panel')?></a></li>
            </ul>
			<? } ?>
        </div>
    <? } ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('next_tournament')?></h1>
        </div>
        <?
        $i = 0;
        foreach($this->serverTimes as $v) {
        ?>
        <div class="block-content <?=($i==0?'next-tournaments':'incoming-tournament')?> hint" attr-msg="<?=date('d/m H:i', $v['time'])?>">
            <? if ($i!=0) { ?>
                <div class="tourn-name"><?=$v['name']?> <?=($v['server']?'('.strtoupper($v['server']).')':'')?> #<?=$v['id']?><br /><?=t($v['status'])?></div>
            <? } else { ?>
                <h2><?=$v['name']?> <?=($v['server']?'('.strtoupper($v['server']).')':'')?> #<?=$v['id']?><br /><?=t($v['status'])?></h2>
            <? } ?>
            
            <div class="timer" attr-time="<?=intval($v['time'] - time() + 10800)?>"><img src="<?=_cfg('img')?>/bx_loader.gif" /></div>
            <a href="<?=_cfg('href')?>/<?=str_replace(' ', '', strtolower($v['name']))?>/<?=($v['server']?$v['server'].'/':'')?><?=$v['id']?>" class="button"><?=t('join')?></a>
            
            <? if ($i!=0) { ?>
                <div class="clear"></div>
            <? } ?>
        </div>
        <?
            $i = 1;
        }
        ?>
    </div>
	
    <? if (_cfg('env') == 'prod') { ?>
    
	<div class="block adsense">
		<div class="block-header-wrapper">
            <h1 class="bordered"><?=t('advertising')?></h1>
        </div>
		
		<div class="ad-holder block-content">
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- Right block -->
            <ins class="adsbygoogle"
                 style="display:inline-block;width:300px;height:250px"
                 data-ad-client="ca-pub-5398156195893681"
                 data-ad-slot="6366917450"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
		</div>
    </div>
    <? } ?>
	
	
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
    
    <? if (_cfg('env') != 'dev') { ?>
	
	<? if (_cfg('language') == 'ru') { ?>
		<div class="block vk">
			<div class="block-header-wrapper">
				<h1 class="bordered">Мы Вконтакте!</h1>
			</div>
			<div class="vk-holder block-content">
				<div id="vk_groups"></div>
			</div>
		</div>
	<? } else { ?>
		<div class="block fb">
			<div class="block-header-wrapper">
				<h1 class="bordered">Like us!</h1>
			</div>
			<div class="facebook-holder block-content">
				<div class="fb-like-box" data-href="https://www.facebook.com/pentaclickesports" data-colorscheme="light" data-width="100%" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
			</div>
		</div>
	<? } ?>

    <div class="block fb">
        <div class="block-header-wrapper">
            <h1 class="bordered">@Tweet</h1>
        </div>
        <div class="twitter-holder block-content">
            <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/pentaclick"  data-widget-id="422786485738147841">Tweets by @pentaclick</a>
        </div>
    </div>
    <? } ?>
</div>

<div class="clear"></div>