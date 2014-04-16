<div class="holder" id="participants"></div>
<article class="participants-content-wrapper">
    <div class="content" id="participants-content">
        <?
        $q = mysql_query(
    		'SELECT id, name, contact_info FROM `teams` WHERE '.
    		' `tournament_id` = '.(int)cOptions('tournament-hs-number').' AND '.
    		' `game` = "hs" AND '.
            ' `approved` = 1 AND '.
            ' `deleted` = 0'
        );
        if (mysql_num_rows($q) == 0) {
            ?><p class="empty-list"><?=_e('no_players_registered', 'pentaclick')?></p><?
        }
        else {
            while($r = mysql_fetch_object($q)) { ?>
                <div class="block" title="<?=$r->contact_info?>">
                    <h3 title="<?=$r->contact_info?>">
                        <?=strlen($r->contact_info) > 17?substr($r->contact_info,0,16).'...':$r->contact_info?>
                    </h3>
                    <div class="clear"></div>
                </div>
                <?
            }
        } ?>
        <div class="clear"></div>
    </div>
</article>