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
    	'SELECT `name`, `approved` FROM `teams` WHERE '.
    	' `tournament_id` = '.(int)cOptions('tournament-lol-number').' AND '.
    	' `game` = "lol" AND '.
        ' `id` = '.(int)$wp_query->query_vars['team_id'].' AND '.
        ' `link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
        ' `deleted` = 0'
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
    
    unset($r);
}
else if (cOptions('game') == 'hs') {
    $q = mysql_query(
    	'SELECT `t`.`id`, `t`.`name`, `t`.`approved`, `t`.`contact_info`, `t`.`challonge_id` FROM `teams` AS `t` WHERE '.
    	' `tournament_id` = '.(int)cOptions('tournament-hs-number').' AND '.
    	' `game` = "hs" AND '.
        ' `id` = '.(int)$wp_query->query_vars['team_id'].' AND '.
        ' `link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
        ' `deleted` = 0'
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

<? if (cOptions('game') == 'lol' && $r) { ?>

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
        <a class="disabled"><?=_e('edit_team', 'pentaclick')?></a>
        <a href="javascript:void(0);" id="leave"><?=_e('leave_tournament', 'pentaclick')?></a>
    </div>
    
    <div class="menu inside-content">
        <h4><?=_e('information', 'pentaclick')?></h4>
        <div class="content-info">
            <p><?=_e('official_timezone', 'pentaclick')?> GMT+1</p>
            <p><?=_e('tournament_start', 'pentaclick')?>: 01.03.2014 14:00</p>
            <p><?=_e('current_participants_count', 'pentaclick')?>: <?=$partNum?></p> 
        </div>
    </div> 
    
    <div class="menu chat">
        <h4><?=_e('battle_chat', 'pentaclick')?></h4>
        <div class="chat-content">
            <p id="notice"><?=_e('to_start_chat_text', 'pentaclick')?></p>
            <p></p>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-input" />
            <div class="attach-file" title="<?=_e('attach_file', 'pentaclick')?>"></div>
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

<?php get_footer(); ?>