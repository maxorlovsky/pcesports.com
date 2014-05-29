<section class="container page lol">

<div class="left-containers">
	<? if (t('lol_tournament_vod_'.$this->pickedTournament) != 'lol_tournament_vod_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('broadcast_vod')?></h1>
        </div>
        
        <div class="block-content vods">
            <iframe width="750" height="505" src="//www.youtube.com/embed/<?=t('lol_tournament_vod_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>
	
	<? if ($this->data->settings['tournament-reg-lol'] == 1 && $this->pickedTournament == $this->currentTournament) { ?>
	<div class="block">
		<div class="block-header-wrapper">
			<h1 class="bordered"><?=t('join_tournament')?> #<?=$this->currentTournament?></h1>
		</div>
		
		<div class="block-content">
			<p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>
			<!--<div class="hidden" id="join-form">
				<form id="da-form" method="post">
					<input type="text" name="battletag" placeholder="<?=t('battle_tag')?>" />
					<div id="battletag-msg" class="message hidden"></div>
					<div class="clear"></div>
					<input type="text" name="email" placeholder="Email" />
					<div id="email-msg" class="message hidden"></div>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="add-player"><?=t('join_tournament')?> #<?=$this->currentTournament?></a>
			</div>-->
		</div>
	</div>
	<? } ?>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
			<?=t('lol_tournament_information_'.$this->pickedTournament)?>
            <a href="<?=_cfg('href')?>/leagueoflegends"><?=t('global_tournament_rules')?></a>
        </div>
    </div>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content participants">
			<?
			$participantsCount = 0;
            if ($this->participants) {
                foreach($this->participants as $v) {
				++$participantsCount;
            ?>
                <div class="block" title="<?=$v['name']?> #<?=$participantsCount?>">
                    <div class="team-name" title="<?=$v['name']?>">
						<?=strlen($v['name']) > 14?substr($v['name'],0,13).'...':$v['name']?>
					</div>
                    <span class="team-num">#<?=$participantsCount?></span>
                    <div class="clear"></div>
					<div class="player-list">
                        <ul>
                            <?
                            foreach($v as $k2 => $v2) {
                                if (is_int($k2)) {
                                ?>
								<li>
									<a href="http://www.lolking.net/summoner/euw/<?=$v2['player_id']?>" target="_blank">
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
                }
            }
            else {
                ?><p class="empty-list"><?=t('no_teams_registered')?></p><?
            }
            ?>
        </div>
    </div>
	
	<? if ($participantsCount >= 2) { ?>
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
//challongeHeight
//550 <24
//950 >24

var participantsNumber = <?=$participantsCount?>;

if (participantsNumber > 24) {
    challongeHeight = 950;
}
else {
    challongeHeight = 550;
}

if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    $('#challonge').challonge('lol<?=$this->pickedTournament?>', {
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