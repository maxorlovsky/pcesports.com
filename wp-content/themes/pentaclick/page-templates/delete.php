<?php
/**
 * Template Name: Delete template

 * @since v1
 */
if (!$wp_query->query_vars['team_id'] && !$wp_query->query_vars['code']) {
    wp_redirect( get_site_url() );
    exit;
}

if (cOptions('tournament-on-lol') == 1) {
    $q = mysql_query(
    	'SELECT `name`, `challonge_id` FROM `teams` WHERE '.
    	' `tournament_id` = 1 AND '.
    	' `game` = "lol" AND '.
        ' `id` = '.(int)$wp_query->query_vars['team_id'].' AND '.
        ' `link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
        ' `deleted` = 0'
    );
    if (mysql_num_rows($q) == 0) {
        wp_redirect( get_site_url() );
        exit;
    }
    else {
        $r = mysql_fetch_object($q);
        
        mysql_query('UPDATE `teams` SET `deleted` = 1 WHERE `tournament_id` = 1 AND `game` = "lol" AND `id` = '.(int)$wp_query->query_vars['team_id']);
        mysql_query('UPDATE `players` SET `deleted` = 1 WHERE `tournament_id` = 1 AND `game` = "lol" AND `team_id` = '.(int)$wp_query->query_vars['team_id']);
        
        if (ENV == 'prod') {
            $apiArray = array(
                '_method' => 'delete',
            );
            runChallongeAPI('tournaments/pentaclick-'.cOptions('brackets-link-lol').'/participants/'.$r->challonge_id.'.post', $apiArray);
        }
        
        sendMail('pentaclickesports@gmail.com',
        'Deleted team. PentaClick eSports.',
        'Team was deleted.<br />
        Date: '.date('d/m/Y H:i:s').'<br />
        Team: <b>'.$r->name.'</b><br>
        IP: '.$_SERVER['REMOTE_ADDR']);
    }
}

get_header(); ?>


<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <? if (cOptions('tournament-on-lol') == 0) { ?>
            <h1><?=_e('tournament_started', 'pentaclick')?></h1>
            <p><?=_e('cant_delete_tournament_started', 'pentaclick')?></p>
        <? } else {
            while ( have_posts() ) : the_post();
                get_template_part( 'content', 'page' );
            endwhile;
           }
        ?>
    </div>
</article>

<?php get_footer(); ?>