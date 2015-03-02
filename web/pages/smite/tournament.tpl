<section class="container page lol smite <?=$this->server?>">

<div class="left-containers">
    <? if (t('smite_tournament_vod_'.$this->server.'_'.$this->pickedTournament) != 'smite_tournament_vod_'.$this->server.'_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('broadcast_vod')?></h1>
        </div>
        
        <div class="block-content vods">
            <iframe width="750" height="505" src="//www.youtube.com/embed/<?=t('smite_tournament_vod_'.$this->server.'_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>
    
	<? if ($this->data->settings['tournament-reg-smite-'.$this->server] == 1 && $this->pickedTournament == $this->currentTournament) { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('join_tournament')?> #<?=$this->currentTournament?></h1>
		</div>
		
		<div class="block-content">
			<p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="team" placeholder="<?=t('team_name')?>*" />
					<div id="team-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="email" placeholder="Email*" value="<?=($this->data->user->email?$this->data->user->email:null)?>" />
					<div id="email-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="mem1" placeholder="<?=t('cpt_nickname')?> (<?=t('member')?> #1)*" value="<?=$pickedSummoner?>" />
					<div id="mem1-msg" class="message hidden"></div>
					<div class="clear"></div>
					<? for($i=2;$i<=7;++$i) { ?>
						<input type="text" name="mem<?=$i?>" placeholder="<?=t('member')?> #<?=$i?><?=($i<=5?'*':null)?>" />
						<div id="mem<?=$i?>-msg" class="message hidden"></div>
						<div class="clear"></div>
					<? } ?>
                    <input class="hint" attr-msg="<?=t('stream_tournament_hint_smite')?>" type="text" name="stream" placeholder="<?=t('stream_name_or_link_from')?> Twitch.tv" value="" />
					<div id="stream-msg" class="message hidden"></div>
					<div class="clear"></div>
                    <input type="checkbox" name="agree" id="agree" /><label for="agree"><?=t('agree_with_rules_smite')?></label>
					<div id="agree-msg" class="message hidden"></div>
                    <div class="clear"></div>
                    <input type="hidden" name="server" value="<?=$this->server?>" />
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="add-team"><?=t('join_tournament')?> #<?=$this->currentTournament?></a>
			</div>
		</div>
	</div>
	<? } ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
			<h1><?=t('specific_tournament_rules')?></h1>
			<?=str_replace(
                array('%startTime%', '%registrationTime%'),
                array($tournamentTime['start'], $tournamentTime['registration']),
                t('smite_'.$this->server.'_tournament_information')
            )?>
            <a href="<?=_cfg('href')?>/smite/<?=$this->server?>"><?=t('global_tournament_rules')?></a>
            
            <div class="share-tournament">
                <h2><?=t('share_this_tournament')?></h2>
                <div class="addthis_sharing_toolbox"></div>
            </div>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content participants isotope-participants">
			<?
            $i = 0;
            if ($this->participants) {
                foreach($this->participants as $v) {
                    if ($v['checked_in'] == 1) {
                    ++$this->participantsCount;
                ?>
                    <div class="block" title="<?=$v['name']?> #<?=$this->participantsCount?>">
                        <div class="team-name" title="<?=$v['name']?>">
                            <?=$v['name']?>
                        </div>
                        <span class="team-num">#<?=$this->participantsCount?></span>
                        <div class="clear"></div>
                        <div class="player-list">
                            <ul>
                                <?
                                foreach($v as $k2 => $v2) {
                                    if (is_int($k2)) {
                                    ?>
                                    <li>
                                        <a href="https://account.hirezstudios.com/smitegame/stats.aspx?player=<?=preg_replace('/\[.*?\]/','',$v2['player'])?>" target="_blank">
                                            <?=$v2['player']?>
                                        </a>
                                    </li><?
                                    }
                                }
                                ?> 
                            </ul>
                        </div>
                    </div>
                <?
                    ++$i;
                    }
                }
            }
            
            if ($i == 0) {
                ?><p class="empty-list"><?=t('no_checked_in_teams')?></p><?
            }
            ?>
        </div>
    </div>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('pending_participants')?></h1>
        </div>

        <div class="block-content participants isotope-participants-pending">
			<?
            $j = 0;
            if ($this->participants) {
                foreach($this->participants as $v) {
                    if ($v['checked_in'] == 0) {
                    ++$this->participantsCount;
                ?>
                    <div class="block" title="<?=$v['name']?> #<?=$this->participantsCount?>">
                        <div class="team-name" title="<?=$v['name']?>">
                            <?=$v['name']?>
                        </div>
                        <span class="team-num">#<?=$this->participantsCount?></span>
                        <div class="clear"></div>
                        <div class="player-list">
                            <ul>
                                <?
                                foreach($v as $k2 => $v2) {
                                    if (is_int($k2)) {
                                    ?>
                                    <li>
                                        <a href="https://account.hirezstudios.com/smitegame/stats.aspx?player=<?=preg_replace('/\[.*?\]/','',$v2['player'])?>" target="_blank">
                                            <?=$v2['player']?>
                                        </a>
                                    </li><?
                                    }
                                }
                                ?> 
                            </ul>
                        </div>
                    </div>
                <?
                    ++$j;
                    }
                }
            }

            if ($j == 0) {
                ?><p class="empty-list"><?=t('no_teams_registered')?></p><?
            }
            ?>
        </div>
    </div>
	
	<? if ($i >= 2) { ?>
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>

    
        <? if ($this->data->user->https == 1) { ?>
        <div class="block-content participants">
            <?=t('challonge_available_http_only')?> <a href="http://pentaclick.challonge.com/smite<?=$this->server?><?=$this->pickedTournament?>" target="_blank">http://pentaclick.challonge.com/smite<?=$this->server?><?=$this->pickedTournament?></a>
        </div>
        <? } else { ?>
        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
        <? } ?>
    </div>
	<? } ?>
	
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>

<script>
$('#add-team').on('click', function() {
    PC.addTeam('registerInSmite');
});

participantsNumber = <?=$this->participantsCount?>;
if (participantsNumber > 100) {
    challongeHeight = 3500;
}
else if (participantsNumber > 50) {
    challongeHeight = 1800;
}
else if (participantsNumber > 25) {
    challongeHeight = 950;
}
else {
    challongeHeight = 550;
}

if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    $('#challonge').challonge('smite<?=$this->server?><?=$this->pickedTournament?>', {
        subdomain: 'pentaclick',
        theme: '1',
        multiplier: '1.0',
        match_width_multiplier: '0.7',
        show_final_results: '0',
        show_standings: '0',
        overflow: '0'
    });
}
</script>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4dfdc8015d8f785b"></script>