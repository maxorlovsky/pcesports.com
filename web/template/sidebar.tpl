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
				<? if ($this->data->settings['tournament-start-hs-s1'] == 1) {?>
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


            <? if ($_SESSION['participant']->game == 'smite') { ?>
            <ul class="panel-links <?=$_SESSION['participant']->game?>">
                <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/"><?=t('information')?></a></li>
                <? if ($this->data->settings['tournament-start-smite-'.$_SESSION['participant']->server] != 1) {?>
                    <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/team"><?=t('edit_team')?></a></li>
                <? } ?>
				<? if ($this->data->settings['tournament-start-smite-'.$_SESSION['participant']->server] == 1) {?>
                    <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/surrender" class="confirm" id="lostBattle" attr-msg="<?=t('sure_to_surrender')?>"><?=t('i_lost')?></a></li>
				<? } else { ?>
                    <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/leave" class="confirm" attr-msg="<?=t('sure_to_leave')?>"><?=t('leave_tournament')?></a></li>
				<? } ?>
                <? if (!$this->logged_in) { ?>
                <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/exit"><?=t('exit_panel')?></a></li>
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
        foreach($this->serverTimes as $v) {
            $live = (intval($v['time'] - time() + _cfg('timeDifference'))<0?'live':'');
        ?>
        <div class="block-content incoming-tournament hint <?=$v['game']?> <?=$live?>" attr-msg="<?=$this->convertTime($v['time'] + _cfg('timeDifference'), 'j M - H:i'.($this->data->user->timestyle!=1?' A':null))?>">
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
        ?>
    </div>
    <? } ?>
    
    <div class="block streamers">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('streamers')?></h1>
        </div>
        <?
        if ($this->streams) {
            foreach($this->streams as $k => $v) {
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
        else {
        ?>
            <div class="block-content">
                <?=t('no_streams_online')?>
            </div>
        <?
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

    <div class="block donate">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('donations_goal')?></h1>
        </div>
        <div class="block-content">
            <p><?=t('donate_text')?></p>
            <div class="donate-bar" attr-goal="50" attr-current="0">
                <p><span id="gathered"></span>€ <?=t('out_of')?> <span id="goal"></span>€</p>
                <div><span></span></div>
            </div>
            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=C8PATMT2V6LJW" target="_blank" class="button"><?=t('donate')?></a>
        </div>
        <div class="separator"></div>
        <div class="arrow-down hint" attr-msg="Show donators"></div>
        <div class="list">
            <ul>
                <li><span class="person">Martin</span><span class="price">2€</span></li>
                <li><span class="person">Michael S.</span><span class="price">14.43€</span></li>
                <li><span class="person">Hearthstone League 7</span><span class="price">3€</span></li>
                <li><span class="person annon">Anonymous</span><span class="price">7.08€</span></li>
                <li><span class="person">Hearthstone League 6</span><span class="price">2€</span></li>
                <li><span class="person">Hearthstone League 5</span><span class="price">6€</span></li>
                <li><span class="person annon">Anonymous</span><span class="price">16.10€</span></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>

<div class="clear"></div>