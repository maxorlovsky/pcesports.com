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
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/leave" class="confirm" attr-msg="<?=t('sure_to_leave')?>"><?=t('leave_tournament')?></a></li>
				<? } ?>
                <? if (!$this->logged_in) { ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/exit"><?=t('exit_panel')?></a></li>
                <? } ?>
            </ul>
			<? } else if ($_SESSION['participant']->game == 'smite') { ?>
            <ul class="panel-links">
                <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/"><?=t('information')?></a></li>
                <li><a href="<?=_cfg('href')?>/smite/<?=$_SESSION['participant']->server?>/participant/fight"><?=t('fight_status')?> (<span id="fightStatus"><img src="<?=_cfg('img')?>/bx_loader.gif" style="width: 12px;"/></span>)</a></li>
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
			<? } else { ?>
			<ul class="panel-links">
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
        <div class="block-content incoming-tournament hint <?=$v['game']?> <?=$live?>" attr-msg="<?=$this->convertTime($v['time'], 'j M - H:i')?>">
            <div class="tourn-name"><?=$v['name']?> <?=($v['server']?'('.strtoupper($v['server']).')':'')?> #<?=$v['id']?><br /><?=t($v['status'])?></div>
            
            <div class="timer" attr-time="<?=intval($v['time'] - time() + _cfg('timeDifference'))?>"><img src="<?=_cfg('img')?>/bx_loader.gif" /></div>
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
    
    <div class="block donate">
        <div class="block-header-wrapper">
            <h1 class="bordered">Donations</h1>
        </div>
		<div class="block-content">
            <p><?=t('donate_text')?></p>
            <div class="donate-bar" attr-goal="900" attr-current="22.10">
                <p><span id="gathered"></span>€ out of <span id="goal"></span>€</p>
                <div><span></span></div>
            </div>
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=C8PATMT2V6LJW" target="_blank" class="button"><?=t('donate')?></a>
		</div>
        <div class="separator"></div>
        <div class="arrow-down hint" attr-msg="Show donators"></div>
        <div class="list">
            <ul>
                <li><span class="person">Hearthstone League 5</span><span class="price">6€</span></li>
                <li><span class="person annon">Annonimous</span><span class="price">16.10€</span></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
    
    <div class="block streamers">
        <div class="block-header-wrapper">
            <h1 class="bordered">Streamers</h1>
        </div>
        <?
        if ($this->streams) {
            foreach($this->streams as $k => $v) {
        ?>
            <a href="<?=_cfg('href')?>/streams/<?=$v->id?>" class="block-content streamer <?=($v->featured==1?'featured':null)?> <?=(isset($v->event)&&$v->event==1?'event':null)?>">
                <? if ($v->game != 'other') { ?>
                    <img class="game-logo" src="<?=_cfg('img')?>/<?=$v->game?>.png" />
                <? } ?>
                <label class="streamer-name"><?=$v->display_name?></label>
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
	
	<? if (_cfg('env') != 'dev') { ?>    
    <div class="block fb">
        <div class="block-header-wrapper">
            <h1 class="bordered">Like us!</h1>
        </div>
        <div class="facebook-holder block-content">
            <div class="fb-like-box" data-href="https://www.facebook.com/pentaclickesports" data-colorscheme="light" data-width="100%" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
        </div>
    </div>
    <? } ?>
    
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