<section class="container page lol <?=$this->server?>">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Special for Riot info page</h1>
        </div>
        <div class="block-content tournament-rules">
            All games were played on this date: <h2 style="display: inline;"><?=$tournamentTime['start']?></h2>
            <p>Check page for simple users: <a href="<?=_cfg('href')?>/leagueoflegends/<?=$this->server?>/<?=$this->pickedTournament?>"><?=_cfg('href')?>/leagueoflegends/<?=$this->server?>/<?=$this->pickedTournament?></a></p>
            <ul>
                <li>Info (this)</li>
                <li>Brackets</li>
                <li>Participants who checked in the tournament</li>
                <li>Participants who registered, but didn't show up</li>
                <li>Battle Log with matches ID</li>
            </ul>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>
        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?> THAT WERE IN THE TOURNAMENT</h1>
        </div>

        <div class="block-content participants not" style="width: 100%; box-sizing: border-box;">
			<?
            $i = 0;
            if ($this->participants) {
                foreach($this->participants as $v) {
                    if ($v['checked_in'] == 1) {
                    ++$this->participantsCount;
                ?>
                    <div class="block" title="<?=$v['name']?> #<?=$this->participantsCount?>" style="float: left; margin: 0 15px 10px 0 !important; cursor: default; width: 225px; min-height: 255px;">
                        <div class="team-name" title="Participants ID in Pentaclick system: <?=$v['id']?>" style="width: 170px;">
                            <?=$v['name']?>
                        </div>
                        <span style="size: 15px; color: #ddd;">Participant ID: <?=$v['id']?></span>
                        <div class="clear"></div>
                        <div class="player-list" style="display: block;">
                            <ul style="color: #fff;">
                                <li><h4>Place in tournament: <span style="color: #0ff; font-size: 15px;<?=($v['place']>=1 && $v['place']<=4?'color: #cc0;':null)?>"><?=$v['place']?></span></h4></li>
                                <?
                                foreach($v as $k2 => $v2) {
                                    if (is_int($k2)) {
                                    ?>
                                    <li title="Player ID in Riot system: <?=$v2['player_id']?>">
                                        <b><?=$v2['player']?></b> (ID: <?=$v2['player_id']?>)
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
            <div class="clear"></div>
        </div>
    </div>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?> THAT REGISTERED FOR TOURNAMENT BUT DIDN'T APPEAR</h1>
        </div>

        <div class="block-content participants">
			<?
            $j = 0;
            if ($this->participants) {
                foreach($this->participants as $v) {
                    if ($v['checked_in'] == 0) {
                    ++$this->participantsCount;
                ?>
                    <div class="block" title="<?=$v['name']?> #<?=$this->participantsCount?>" style="float: left; margin: 0 15px 10px 0 !important; cursor: default; width: 225px; min-height: 205px;">
                        <div class="team-name" title="Participants ID in Pentaclick system: <?=$v['id']?>" style="width: 170px;">
                            <?=$v['name']?>
                        </div>
                        <span style="size: 15px; color: #ddd;">Participant ID: <?=$v['id']?></span>
                        <div class="clear"></div>
                        <div class="player-list" style="display: block;">
                            <ul style="color: #fff;">
                                <?
                                foreach($v as $k2 => $v2) {
                                    if (is_int($k2)) {
                                    ?>
                                    <li title="Player ID in Riot system: <?=$v2['player_id']?>">
                                        <b><?=$v2['player']?></b> (ID: <?=$v2['player_id']?>)
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
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Battle Log</h1>
        </div>

        <div class="block-content">
            <?
            if ($this->battleLog) {
                foreach($this->battleLog as $v) {
                ?>
                    <div class="hint" attr-msg="<?=htmlentities($v->message)?>" style="float: left; width: 235px; color: #ddd; background-color: #32333b; border: 1px solid #fff; padding: 10px; min-height: 80px; margin-right: 15px; margin-bottom: 5px;">
                        <?//=dump($v)?>
                        <p><b><?=$participantsNames[$v->participant_id1]?></b> VS <b><?=$participantsNames[$v->participant_id2]?></b></p>
                        <? if ($v->game_id) { ?>
                            <p><b>Game ID:</b> <?=$v->game_id?></p>
                            <a href="http://matchhistory.euw.leagueoflegends.com/en/#match-details/<?=($this->server=='eune'?'EUN1':'EUW1')?>/<?=$v->game_id?>/0?tab=overview" target="_blank">Match on official website</a>
                        <? } else { ?>
                            <p><i>Game not registered, probably forfeit</i></p>
                        <? } ?>
                    </div>
                <?
                }
            }
            ?>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
	
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>

<script>
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

<div class="clear"></div>

<style>
.left-containers {
    width: 100%;
}
#hint-helper {
    max-width: 300px;
}
</style>