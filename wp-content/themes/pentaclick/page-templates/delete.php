<?php
/**
 * Template Name: Delete template

 * @since v1
 */
if (!$wp_query->query_vars['team_id'] && !$wp_query->query_vars['code']) {
    wp_redirect( get_site_url() );
    exit;
}

$q = mysql_query(
	'SELECT approved FROM `teams` WHERE '.
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
    
    sendMail('pentaclickesports@gmail.com', 'PentaClick eSports team deleted', 'Team: <b>'.$r->name.'</b>');
}

get_header(); ?>


<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <?
        while ( have_posts() ) : the_post();
            get_template_part( 'content', 'page' );
        endwhile;
        ?>
    </div>
</article>

<?php get_footer(); ?>