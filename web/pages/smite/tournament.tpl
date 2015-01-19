<section class="container page lol">

<div class="left-containers">
	<? if (t('dota_tournament_vod_'.$this->pickedTournament) != 'dota_tournament_vod_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('broadcast_vod')?></h1>
        </div>
        
        <div class="block-content vods">
            <iframe width="750" height="505" src="//www.youtube.com/embed/<?=t('dota_tournament_vod_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>
	
	<? if ($this->data->settings['tournament-reg-dota'] == 1 && $this->pickedTournament == $this->currentTournament) { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('join_tournament')?> #<?=$this->currentTournament?></h1>
		</div>
		
		<div class="block-content">
			<p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>
			<div id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="team" placeholder="<?=t('team_name')?>" />
					<div id="team-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="email" placeholder="Email*" value="<?=($this->data->user->email?$this->data->user->email:null)?>" />
					<div id="email-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="mem1" placeholder="<?=t('cpt_nickname')?> (<?=t('member')?> #1)" value="<?=$pickedSummoner?>" />
					<div id="mem1-msg" class="message hidden"></div>
					<div class="clear"></div>
					<? for($i=2;$i<=7;++$i) { ?>
						<input type="text" name="mem<?=$i?>" placeholder="<?=t('member')?> #<?=$i?>" />
						<div id="mem<?=$i?>-msg" class="message hidden"></div>
						<div class="clear"></div>
					<? } ?>
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
                t('dota_tournament_information')
            )?>
            <a href="<?=_cfg('href')?>/dota"><?=t('global_tournament_rules')?></a>
            
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
            if ($this->participants) {
                $i = 0;
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
                                        <a href="http://www.lolking.net/summoner/<?=$this->server?>/<?=$v2['player_id']?>" target="_blank">
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
            if ($this->participants) {
                $j = 0;
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
                                        <a href="http://www.lolking.net/summoner/<?=$this->server?>/<?=$v2['player_id']?>" target="_blank">
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
	
	<? if ($this->participantsCount >= 2 //&& 
          //($this->pickedTournament != $this->currentTournament && $this->data->settings['tournament-start-lol-'.$this->server] != 1)
          ) { ?>
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>

        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
    </div>
	<? } ?>
	
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>

<script>
$('#add-team').on('click', function() {
    PC.addTeam('registerInDota');
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
    $('#challonge').challonge('dota<?=$this->pickedTournament?>', {
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