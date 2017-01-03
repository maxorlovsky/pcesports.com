<section class="container page tournament hs">

<div class="left-containers">

	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Special for Blizzard info page</h1>
        </div>
        <div class="block-content tournament-rules">
            All games were played on this date: <h2 style="display: inline;"><?=$tournamentTime['start']?></h2>
            <p>Check page for simple users: <a href="<?=_cfg('href')?>/hearthstone/<?=$this->server?>/<?=$this->pickedTournament?>"><?=_cfg('href')?>/hearthstone/<?=$this->server?>/<?=$this->pickedTournament?></a></p>
            <ul>
                <li>Info (this)</li>
                <li>Winners (for easy copy/paste)</li>
                <li>Brackets</li>
                <li>Participants who checked in the tournament</li>
            </ul>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('winners')?></h1>
        </div>
        
        <div class="block-content">
        	<?
        	if ($this->winners) {
        		$i = 1;
        		foreach($this->winners as $v) {
        			?>
        				<strong><?=$i?> place</strong>: <input style="padding: 2px; cursor: pointer;" type="text" value="<?=$v?>" onClick="$(this).select();" class="hint" attr-msg="Click to select for copy (ctrl+c)" />
                        <div class="clear"></div>
        			<?
        			++$i;
        		}
    		}
    		?>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>
        <div class="block-content participants">
            Direct link to brackets: <a href="http://pentaclick.challonge.com/hs<?=$this->server?><?=$this->pickedTournament?>" target="_blank">http://pentaclick.challonge.com/hs<?=$this->server?><?=$this->pickedTournament?></a>
        </div>
        <div class="block-content challonge-brackets <? if (_cfg('https') == 1) {?>hidden<?}?>">
            <div id="challonge"></div>
        </div>
    </div>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?> THAT WERE IN THE TOURNAMENT</h1>
        </div>
        
        <div class="block-content participants isotope-participants">
        <?
        $participantsCount = 0;
        $i = 0;
        if ($this->participants) {
            foreach($this->participants as $v) {
                //This must be here
                ++$participantsCount;
                ?>
                <div class="block" title="<?=$v->battletag?>">
                    <div class="team-name"><?=$v->name?></div>
                    <span class="team-num">#<?=$participantsCount?></span>
                    <div class="clear"></div>
                    <div class="player-heroes">
                        <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                        <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                        <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                        <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                        <? } ?>
                    </div>
                    <div class="clear"></div>
                    <span style="size: 15px; color: #ddd;">Participant ID: <?=$v->id?></span>
                </div>
                <?
                ++$i;
            }
        }
    ?>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
var participantsNumber = <?=$participantsCount?>;
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

    $('#challonge').challonge('hs<?=$this->server.$this->pickedTournament?>', {
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