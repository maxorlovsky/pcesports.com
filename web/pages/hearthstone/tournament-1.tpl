<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Information</h1>
        </div>
        
        <div class="block-content tournament-rules">
            <p>Games starts <strong>1 march 2014</strong>, 10:00 GMT-0</p>
            <p>Prize (1st place) – 15€</p>
            <p>Prize (2nd place) – 10€</p>
            <p>Prize (3rd place) – 5€</p>
            <p>Prize money will be sent via Paypal</p>
        </div>
    </div>
    
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
                <div class="block" title="<?=$v->name?> #<?=$i?>">
                    <div class="team-name" title="<?=$v->name?>"><?=strlen($v->name) > 14?substr($v->name,0,13).'...':$v->name?></div>
                    <span class="team-num">#<?=$i?></span>
                    <div class="clear"></div>
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
            <div id="challonge-lol"></div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
if ($('#challonge-lol').length) {
    $('#challonge-lol').height(950);
    $('#challonge-lol').challonge('hs<?=$id?>', {
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