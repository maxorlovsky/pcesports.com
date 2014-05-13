	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Participants</h1>
        </div>

        <div class="block-content participants">
            <?
            if ($this->participants) {
                $i = 1;
                foreach($this->participants as $v) {
            ?>
                <div class="block" title="<?=$v['name']?> #<?=$i?>">
                    <div class="team-name" title="<?=$v['name']?>"><?=strlen($v['name']) > 14?substr($v['name'],0,13).'...':$v['name']?></div>
                    <span class="team-num">#<?=$i?></span>
                    <div class="clear"></div>
                    <div class="player-list">
                        <ul>
                            <?
                            foreach($v as $k2 => $v2) {
                                if (is_int($k2)) {
                                ?><li><a href="http://www.lolking.net/summoner/euw/<?=$v2['player_id']?>" target="_blank"><?=$v2['player']?></a></li><?
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
            else {
                ?><p class="empty-list">No teams registered</p><?
            }
            ?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Brackets</h1>
        </div>

        <div class="block-content challonge-brackets">
            <div id="challonge"></div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    $('#challonge').challonge('hs<?=$id?>', {
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