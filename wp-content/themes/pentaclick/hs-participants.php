<div class="holder" id="participants"></div>
<article class="participants-content-wrapper">
    <div class="content" id="participants-content">
        <?
        $q = mysql_query(
    		'SELECT id, name FROM `teams` WHERE '.
    		' `tournament_id` = '.(int)cOptions('tournament-hs-number').' AND '.
    		' `game` = "hs" AND '.
            ' `approved` = 1 AND '.
            ' `deleted` = 0'
        );
        if (mysql_num_rows($q) == 0) {
            ?><p class="empty-list"><?=_e('no_players_registered', 'pentaclick')?></p><?
        }
        else {
            $i = 1;
            while($r = mysql_fetch_object($q)) { ?>
                <div class="block" title="<?=$r->name?> #<?=$i?>">
                    <h3 title="<?=$r->name?>">
                        <?=strlen($r->name) > 14?substr($r->name,0,13).'...':$r->name?>
                    </h3>
                    <span class="team-num">#<?=$i?></span>
                    <div class="clear"></div>
                </div>
                <?
                ++$i;
            }
        } ?>
        <div class="clear"></div>
    </div>
</article>