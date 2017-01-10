<div class="hidden popup" id="rules-window">
    <div class="rules-inside">
        <h1>League of legends rules</h1>
        <?=t('lol_tournament_rules')?>
    </div>
</div>

<div class="legacy-tournament leagueoflegends">
	<? if (t('lol_tournament_vod_'.$this->server.'_'.$this->pickedTournament) != 'lol_tournament_vod_'.$this->server.'_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1><?=t('broadcast_vod')?></h1>
        </div>
        
        <div class="block-content vods">
            <iframe src="//www.youtube.com/embed/<?=t('lol_tournament_vod_'.$this->server.'_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>
    
    <? if ($this->winners) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1><?=t('winners')?></h1>
        </div>
        
        <div class="block-content places">
            <div class="third hint" attr-msg="<?=$this->winners[3]?>"><p><?=$this->winners[3]?></p></div>
            <div class="second hint" attr-msg="<?=$this->winners[2]?>"><p><?=$this->winners[2]?></p></div>
            <div class="first hint" attr-msg="<?=$this->winners[1]?>"><p><?=$this->winners[1]?></p></div>
        </div>
    </div>
    <? } ?>
	  
    <div class="block">
        <div class="block-header-wrapper">
            <h1><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
			<h1><?=t('specific_tournament_rules')?></h1>
			<?=str_replace(
                array('%startTime%', '%registrationTime%', '%checkInTime%', '%eventPage%', '%prize%'),
                array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $this->eventPage, $tournamentRow->prize),
                t('lol_'.$this->server.'_tournament_information'.($this->pickedTournament<5?'_'.$this->pickedTournament:null))
            )?>
            
            <? if ($this->eventId) { ?>
                <p><?=t('eventpage_link_text')?>: <a href="http://events.<?=$this->server?>.leagueoflegends.com/en/events/<?=$this->eventId?>" target="_blank">http://events.<?=$this->server?>.leagueoflegends.com/en/events/<?=$this->eventId?></a></p>
            <? } ?>

            <div>
                <a href="javascript:;" class="rules"><?=t('global_tournament_rules')?></a>
            </div>
            
            <div class="share-tournament">
                <h2><?=t('share_this_tournament')?></h2>
                <div class="addthis_sharing_toolbox"></div>
            </div>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1><?=t('participants')?></h1>
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
                                        <a href="http://<?=$this->server?>.op.gg/summoner/?userName=<?=$v2['player']?>" target="_blank">
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
            <h1><?=t('pending_participants')?></h1>
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
                                        <a href="http://<?=$this->server?>.op.gg/summoner/?userName=<?=$v2['player']?>" target="_blank">
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
	<div class="block bracket">
        <div class="block-header-wrapper">
            <h1><?=t('brackets')?></h1>
        </div>

        <div class="block-content participants <? if (_cfg('https') != 1) {?>hidden<?}?>">
            <?=t('challonge_available_http_only')?> <a href="http://pentaclick.challonge.com/lol<?=$this->server?><?=$this->pickedTournament?>" target="_blank">http://pentaclick.challonge.com/lol<?=$this->server?><?=$this->pickedTournament?></a>
        </div>
        <div class="block-content challonge-brackets <? if (_cfg('https') == 1) {?>hidden<?}?>">
            <div id="challonge"></div>
        </div>
    </div>
	<? } ?>
	
</div>

<script src="../../legacy/js/jquery.challonge.js"></script>
<script src="../../legacy/js/jquery.isotope.min.js"></script>

<script>
var participantsNumber = <?=$this->participantsCount?>;

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
    $('#challonge').challonge('lol<?=$this->server?><?=$this->pickedTournament?>', {
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