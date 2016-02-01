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
                <li><a href="http://pentaclick.challonge.com/<?=$_SESSION['participant']->game?><?=$_SESSION['participant']->server?><?=$_SESSION['participant']->tournament_id?>" target="_blank"><?=t('brackets')?></a></li>
				<? if ($this->data->settings['tournament-start-hs'] == 1) {?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/surrender" class="confirm" id="lostBattle" attr-msg="<?=t('sure_to_surrender')?>"><?=t('i_lost')?></a></li>
				<? } else { ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/edit"><?=t('edit_information')?></a></li>
                    <? //if ($_SESSION['participant']->verified != 1) { ?>
                        <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/leave" class="confirm" attr-msg="<?=t('sure_to_leave')?>"><?=t('leave_tournament')?></a></li>
                    <? //} ?>
				<? } ?>
                <? if (!$this->logged_in) { ?>
                <li><a href="<?=_cfg('href')?>/hearthstone/<?=$_SESSION['participant']->server?>/participant/exit"><?=t('exit_panel')?></a></li>
                <? } ?>
            </ul>
			<? } ?>

            <? if ($_SESSION['participant']->game == 'lol') { ?>
			<ul class="panel-links <?=$_SESSION['participant']->game?>">
                <li><a href="<?=_cfg('href')?>/leagueoflegends/<?=$_SESSION['participant']->server?>/participant/"><?=t('information')?></a></li>
                <li><a href="http://pentaclick.challonge.com/<?=$_SESSION['participant']->game?><?=$_SESSION['participant']->server?><?=$_SESSION['participant']->tournament_id?>" target="_blank"><?=t('brackets')?></a></li>
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

    <div class="block boards">
        <? include_once _cfg('pages').'/boards/snippet.tpl'; ?>
    </div>

    <div class="block boards">
        <div class="block-header-wrapper">
            <h1 class="bordered">We're in social networks</h1>
        </div>

        <div class="block-content social">
            <a class="fb hint" attr-msg="Facebook" href="https://www.facebook.com/pentaclickesports" target="_blank"></a>
            <a class="tw hint" attr-msg="Twitter" href="https://twitter.com/pentaclick" target="_blank"></a>
            <a class="yt hint" attr-msg="YouTube" href="https://www.youtube.com/c/pentaclickesports" target="_blank"></a>
            <a class="tv hint" attr-msg="Twitch.TV" href="http://www.twitch.tv/pentaclick_tv" target="_blank"></a>
            <a class="sm hint" attr-msg="Steam group" href="http://steamcommunity.com/groups/pentaclickesports" target="_blank"></a>
            <script>
            $('.social a').css('transition', '.5s');
            </script>
        </div>
    </div>
    
    <?
    if (_cfg('env') == 'prod') {
        $g2a = 0;
        if (rand(0,100) <= 30) {
            $g2a = 1;
        }
    ?>
	<div class="block <?=($g2a===1?'g2a':'sense')?>">
		<div class="block-header-wrapper">
            <h1 class="bordered"><?=t('advertising')?></h1>
        </div>
		
        <? if ($g2a === 1) { ?>
            <div class="block-content">
                <a href="https://www.g2a.com/r/pentaclick" target="_blank"><img src="<?=_cfg('img')?>/g2a.jpg" /></a>
            </div>
        <? } else { ?>
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
        <? } ?>

        <div class="survive block-content">
            We detected that you might use AdBlock. You might not know, that Pentaclick eSports is running completely out of our pockets and small advertisement revenue that we get. You would be a project savior and helped us a lot, if you added our website to your adblock whitelist. Thank you.
        </div>
    </div>
    <? } ?>
</div>

<div class="clear"></div>