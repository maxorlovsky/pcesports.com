<?php
/**
 * Template Name: Profiler template

 * @since v1
 */
if (!$wp_query->query_vars['team_id'] && !$wp_query->query_vars['code']) {
    wp_redirect( get_site_url() );
    exit;
}

if (cOptions('game') == 'lol') {
    $q = mysql_query(
    	'SELECT `t`.`id`, `t`.`name`, `t`.`approved`, `t`.`challonge_id` '.
        'FROM `teams` AS `t` '.
        'WHERE '.
    	' `t`.`tournament_id` = '.(int)cOptions('tournament-lol-number').' AND '.
    	' `t`.`game` = "lol" AND '.
        ' `t`.`id` = '.(int)$wp_query->query_vars['team_id'].' AND '.
        ' `t`.`link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
        ' `t`.`deleted` = 0'
    );
    $r = mysql_fetch_object($q);
    
    if (cOptions('tournament-on-lol') == 0 && $r && $r->approved == 0) {
        //Registration closed and not approved, showing error msg on page
        unset($r);
    }
    else if (cOptions('tournament-on-lol') == 1 && $r && $r->approved == 0) {
        //Not approved, registration open, approving and adding to brackets
        $r->challonge_id = approveRegisterTeam('lol', $r);
            
        $verified = 1;
    }
}
else if (cOptions('game') == 'hs') {
    $q = mysql_query(
    	'SELECT `t`.`id`, `t`.`name`, `t`.`approved`, `t`.`challonge_id` '.
        'FROM `teams` AS `t` '.
        'WHERE '.
    	'`t`.`tournament_id` = '.(int)cOptions('tournament-hs-number').' AND '.
    	'`t`.`game` = "hs" AND '.
        '`t`.`id` = '.(int)$wp_query->query_vars['team_id'].' AND '.
        '`t`.`link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
        '`t`.`deleted` = 0'
    );
    $r = mysql_fetch_object($q);
    
    if (cOptions('tournament-on-hs') == 0 && $r && $r->approved == 0) {
        //Registration closed and not approved, showing error msg on page
        unset($r);
    }
    else if (cOptions('tournament-on-hs') == 1 && $r && $r->approved == 0) {
        //Not approved, registration open, approving and adding to brackets
        $r->challonge_id = approveRegisterTeam('hs', $r);
            
        $verified = 1;
    }
}

get_header(); ?>

<script>
    var tId = <?=$wp_query->query_vars['team_id']?>;
    var code = '<?=$wp_query->query_vars['code']?>';
</script>

<article class="text-content-wrapper">
    <div class="content padding-up" id="text-content">

<? if (cOptions('game') == 'lol' && $r) {
$q = mysql_query(
'SELECT `id` FROM `players` WHERE'.
' `tournament_id` = '.(int)cOptions('tournament-lol-number').' AND'.
' `game` = "lol" AND'.
' `approved` = 1 AND'.
' `deleted` = 0'
);
$partNum = mysql_num_rows($q);

$q = mysql_query(
'SELECT `id`, `player_num`, `name` FROM `players` WHERE '.
' `tournament_id` = '.(int)cOptions('tournament-lol-number').' AND '.
' `game` = "lol" AND '.
' `approved` = 1 AND '.
' `deleted` = 0 AND '.
' `team_id` = '.(int)$r->id.
' ORDER BY `player_num`'
);
while($ro = mysql_fetch_object($q)) {
    $playersList[$ro->player_num]['name'] = $ro->name;
    $playersList[$ro->player_num]['id'] = $ro->id;
}

$q = mysql_query(
'SELECT `hour`, `day` FROM `notifications` WHERE `team_id` = '.(int)$r->id);
$ro = mysql_fetch_object($q);
foreach($ro as $k=>$v) {
    $notifications[$k] = $v;
}

$timeBlock = 0;
if (time() - 86400 >= strtotime(cOptions('tourn-start-date-lol'))) {
    $timeBlock = 1;
}
?>
    <div class="menu links">
        <h4>Menu: <strong><?=$r->name?></strong></h4>
        <a href="javascript:void(0);" id="information-url" class="active"><?=_e('information', 'pentaclick')?></a>
        <a href="javascript:void(0);" id="notifications-url"><?=_e('notifications', 'pentaclick')?></a>
        <a href="javascript:void(0);" <?if($timeBlock==1){?>class="disabled"<?}?> id="team-url"><?=_e('edit_team', 'pentaclick')?></a>
        <a href="javascript:void(0);" class="disabled"><?=_e('profile_protection', 'pentaclick')?></a>
        <a href="javascript:void(0);" id="leave"><?=_e('leave_tournament', 'pentaclick')?></a>
    </div>
    
    <div class="menu inside-content hidden" id="notifications">
        <h4><?=_e('notifications', 'pentaclick')?></h4>
        <div class="content-info">
            <div><input type="checkbox" id="receive-tournament-hour-notif" <?if ($notifications['hour']==1){?>checked="checked"<?}?> /> <label for="receive-tournament-hour-notif"><?=_e('receive_hour_notif', 'pentaclick')?></label></div>
            <div><input type="checkbox" id="receive-tournament-day-notif" <?if ($notifications['day']==1){?>checked="checked"<?}?> /> <label for="receive-tournament-day-notif"><?=_e('receive_day_notif', 'pentaclick')?></label></div>
        </div>
    </div>
    
    <div class="menu inside-content hidden" id="team">
        <h4><?=_e('edit_team', 'pentaclick')?></h4>
        <div class="content-info" id="register-content-profile">
            <form method="post" class="form">
                <input type="text" name="mem1" placeholder="<?=_e('cpt_nickname', 'pentaclick')?> (<?=_e('member', 'pentaclick')?> #1)" value="<?=$playersList[1]['name']?>" />
                <input type="hidden" name="mem1-id" value="<?=$playersList[1]['id']?>" />
                <div id="mem1-msg" class="message hidden"></div>
                <? for($i=2;$i<=7;++$i) { ?>
                    <input type="text" name="mem<?=$i?>" placeholder="<?=_e('member', 'pentaclick')?> #<?=$i?>" value="<?=$playersList[$i]['name']?>" />
                    <input type="hidden" name="mem<?=$i?>-id" value="<?=$playersList[$i]['id']?>" />
                    <div id="mem<?=$i?>-msg" class="message hidden"></div>
                <? } ?>
            </form>
            <div class="clear"></div>
            <button id="update-team"><?=_e('save', 'pentaclick')?></button>
        </div>
    </div>
    
    <div class="menu inside-content" id="information">
        <h4><?=_e('information', 'pentaclick')?></h4>
        <div class="content-info">
            <?=_e('opponent_name', 'pentaclick')?>: <span id="opponentName"></span>
            <br />
            <?=_e('opponent_status', 'pentaclick')?>: <span id="opponentStatus"></span> (<span id="opponentSec"></span> sec)
            <br /><br />
            <p><?=_e('official_timezone', 'pentaclick')?> GMT+1</p>
            <p><?=_e('tournament_start', 'pentaclick')?>: <?=cOptions('tourn-start-date-lol')?></p>
            <p><?=_e('current_participants_count', 'pentaclick')?>: <?=$partNum?></p>
            <p><?=_e('brackets_will_be_available', 'pentaclick')?>: ~24h before start</p>
            <p><?=_e('link_to_brackets', 'pentaclick')?>: <a href="http://pentaclick.challonge.com/lol2/" target="_blank">http://pentaclick.challonge.com/lol2/</a></p>
            <p><?=_e('brackets_reshufled', 'pentaclick')?></p>
        </div>
    </div> 
    
    <div class="menu chat">
        <h4><?=_e('battle_chat', 'pentaclick')?></h4>
        <div class="chat-content">
            <p id="notice"><?=_e('to_start_chat_text', 'pentaclick')?></p>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-input" />
            <div id="uploadScreen" class="attach-file" title="<?=_e('attach_file', 'pentaclick')?>"></div>
        </div>
    </div>
    
    
    <div class="clear"></div>
<?
}
else if (cOptions('game') == 'hs' && $r) {
$q = mysql_query(
'SELECT `id` FROM `players` WHERE'.
' `tournament_id` = '.(int)cOptions('tournament-hs-number').' AND'.
' `game` = "hs" AND'.
' `approved` = 1 AND'.
' `deleted` = 0'
);
$partNum = mysql_num_rows($q);
?>
    <div class="menu links">
        <h4>Menu: <strong><?=$r->name?></strong></h4>
        <a href="javascript:void(0);" id="information-url" class="active"><?=_e('information', 'pentaclick')?></a>
        <a href="javascript:void(0);" id="notifications-url"><?=_e('notifications', 'pentaclick')?></a>
        <a href="javascript:void(0);" class="disabled"><?=_e('profile_protection', 'pentaclick')?></a>
        <a href="javascript:void(0);" id="leave"><?=_e('leave_tournament', 'pentaclick')?></a>
    </div>
    
    <div class="menu inside-content hidden" id="notifications">
        <h4><?=_e('notifications', 'pentaclick')?></h4>
        <div class="content-info">
            <div><input type="checkbox" id="receive-tournament-hour-notif" <?if ($notifications['hour']==1){?>checked="checked"<?}?> /> <label for="receive-tournament-hour-notif"><?=_e('receive_hour_notif', 'pentaclick')?></label></div>
            <div><input type="checkbox" id="receive-tournament-day-notif" <?if ($notifications['day']==1){?>checked="checked"<?}?> /> <label for="receive-tournament-day-notif"><?=_e('receive_day_notif', 'pentaclick')?></label></div>
        </div>
    </div>
     
    <div class="menu inside-content" id="information">
        <h4><?=_e('information', 'pentaclick')?></h4>
        <div class="content-info">
            <?=_e('opponent_name', 'pentaclick')?>: <span id="opponentName"></span>
            <br />
            <?=_e('opponent_status', 'pentaclick')?>: <span id="opponentStatus"></span> (<span id="opponentSec"></span> sec)
            <br /><br />
            <p><?=_e('official_timezone', 'pentaclick')?> GMT+1</p>
            <p><?=_e('tournament_start', 'pentaclick')?>: <?=cOptions('tourn-start-date-hs')?></p>
            <p><?=_e('current_participants_count', 'pentaclick')?>: <?=$partNum?></p>
            <p><?=_e('brackets_will_be_available', 'pentaclick')?>: ~24h before start</p>
            <p><?=_e('link_to_brackets', 'pentaclick')?>: <a href="http://pentaclick.challonge.com/hs2/" target="_blank">http://pentaclick.challonge.com/hs2/</a></p>
            <p><?=_e('brackets_reshufled', 'pentaclick')?></p>
        </div>
    </div>
    
    <div class="menu chat">
        <h4><?=_e('battle_chat', 'pentaclick')?></h4>
        <div class="chat-content">
            <p id="notice"><?=_e('to_start_chat_text', 'pentaclick')?></p>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-input" />
            <div id="uploadScreen" class="attach-file" title="<?=_e('attach_file', 'pentaclick')?>"></div>
        </div>
    </div>
    
    
    <div class="clear"></div>
<?
}
else {
    while ( have_posts() ) : the_post();
        get_template_part( 'content', 'page' );
    endwhile;
}
?>
    </div>
</article>

<?php

wp_enqueue_script( 'ajaxupload', get_template_directory_uri() . '/js/ajaxupload.js', array(), '1', true);
wp_enqueue_script( 'profiler', get_template_directory_uri() . '/js/profiler.js', array(), '1', true);

get_footer();

?>