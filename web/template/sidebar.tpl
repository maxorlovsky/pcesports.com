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
            <ul class="panel-links <?=$_SESSION['participant']->game?>">
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/"><?=t('information')?></a></li>
				<? if ($this->data->settings['tournament-start-hs'] == 1) {?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/surrender" class="confirm" id="lostBattle" attr-msg="<?=t('sure_to_surrender')?>"><?=t('i_lost')?></a></li>
				<? } else { ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/edit"><?=t('edit_information')?></a></li>
                    <? if ($_SESSION['participant']->verified != 1) { ?>
                        <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/leave" class="confirm" attr-msg="<?=t('sure_to_leave')?>"><?=t('leave_tournament')?></a></li>
                    <? } ?>
				<? } ?>
                <? if (!$this->logged_in) { ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/exit"><?=t('exit_panel')?></a></li>
                <? } ?>
            </ul>
			<? } ?>

            <? if ($_SESSION['participant']->game == 'lol') { ?>
			<ul class="panel-links <?=$_SESSION['participant']->game?>">
                <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/"><?=t('information')?></a></li>
                <? if ($this->data->settings['tournament-start-lol-'.$_SESSION['participant']->server] != 1) {?>
                    <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/team"><?=t('edit_team')?></a></li>
                <? } ?>
				<? if ($this->data->settings['tournament-start-lol-'.$_SESSION['participant']->server] == 1) {?>
                    <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/surrender" class="confirm" id="lostBattle" attr-msg="<?=t('sure_to_surrender')?>"><?=t('i_lost')?></a></li>
				<? } else { ?>
                    <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/leave" class="confirm" attr-msg="<?=t('sure_to_leave')?>"><?=t('leave_tournament')?></a></li>
				<? } ?>
                <? if (!$this->logged_in) { ?>
                <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/exit"><?=t('exit_panel')?></a></li>
                <? } ?>
            </ul>
			<? } ?>
        </div>
    <? } ?>
    
    <? if ($this->page != 'home' && $this->page != 'tournaments') { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('next_tournament')?></h1>
        </div>
        <?
        if ($this->serverTimes) {
            foreach($this->serverTimes as $v) {
                $live = (intval($v['time'] - time())<0?'live':'');
            ?>
            <div class="block-content incoming-tournament hint <?=$v['game']?> <?=$live?>" attr-msg="<?=$this->convertTime($v['time'], 'j M - H:i'.($this->data->user->timestyle!=1?' A':null), 1)?>">
                <div class="tourn-name"><?=$v['name']?> <?=($v['server']?'('.strtoupper($v['server']).')':'')?> #<?=$v['id']?>
                    <br /><?=t($v['status'])?>
                </div>
                
                <div class="timer" attr-time="<?=intval($v['time'] - time())?>"><img src="<?=_cfg('img')?>/bx_loader.gif" /></div>
                <a href="<?=_cfg('href')?>/<?=str_replace(' ', '', strtolower($v['name']))?>/<?=($v['server']?$v['server'].'/':'')?><?=$v['id']?>" class="button">
                    <?=t('join')?>
                </a>
                <div class="clear"></div>
            </div>
            <?
            }
        }
        else {
            ?>
            <div class="block-content">
                No tournaments registered
            </div>
            <?
        }
        ?>
    </div>
    <? } ?>

    <div class="block">
        <a href="<?=_cfg('href')?>/fifa">
            <img style="width: 320px;" src="<?=_cfg('img')?>/fifa-2015.jpg" />
        </a>
    </div>
    
    <div class="block streamers">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('streamers')?></h1>
        </div>
        <?
        if ($this->streams) {
            foreach($this->streams as $k => $v) {
                if (_cfg('https') == 1) {
        ?>
            <a href="http://www.twitch.tv/<?=$v->name?>" class="block-content streamer <?=($v->featured==1?'featured':null)?> <?=(isset($v->event)&&$v->event==1?'event':null)?> <?=(isset($v->onlineStatus)&&$v->onlineStatus==0?'alpha':null)?>">
                <? if ($v->game != 'other') { ?>
                    <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <? } ?>
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?></label>
                <span class="viewers"><?=$v->viewers?> <?=t('viewers')?></span>
            </a>
        <?
                }
                else {
        ?>
            <a href="<?=_cfg('href')?>/streams/<?=$v->id?>" class="block-content streamer <?=($v->featured==1?'featured':null)?> <?=(isset($v->event)&&$v->event==1?'event':null)?> <?=(isset($v->onlineStatus)&&$v->onlineStatus==0?'alpha':null)?>">
                <? if ($v->game != 'other') { ?>
                    <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <? } ?>
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?></label>
                <span class="viewers"><?=$v->viewers?> <?=t('viewers')?></span>
            </a>
        <?
                }
            }
        }
        else {
        ?>
            <div class="block-content">
                <?=t('no_streams_online')?>
            </div>
        <?
        }
        ?>
    </div>

    <div class="block g2a">
        <div class="block-header-wrapper">
            <h1 class="bordered">Buy games with G2A</h1>
        </div>
        
        <div class="ad-holder block-content">
            <a href="https://www.g2a.com/r/pentaclick" target="_blank"><img src="<?=_cfg('img')?>/g2a.jpg" /></a>
        </div>
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

</div>

<div class="clear"></div>