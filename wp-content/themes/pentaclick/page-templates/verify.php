<?php
/**
 * Template Name: Verify template

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
    ' `link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'"'
);
$r = mysql_fetch_object($q);
get_header(); ?>


<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <? if (mysql_num_rows($q) == 0) { ?>
            <h1>Incorrect link!</h1>
            <p>Check it one more time, or if you think that it is correct, write us an email at pentaclickesports@gmail.com</p>
        <? }
        else if ($r->approved == 1) {
            ?>
            <h1>Double click</h1>
            <p>Don't worry, your team is already approved and must be in the <a href="<?=get_site_url()?>#participants">list</a>!</p>
            <?
        }
        else {
            mysql_query('UPDATE `teams` SET approved = 1 WHERE `tournament_id` = 1 AND `game` = "lol" AND `id` = '.(int)$wp_query->query_vars['team_id']);
            mysql_query('UPDATE `players` SET approved = 1 WHERE `tournament_id` = 1 AND `game` = "lol" AND `team_id` = '.(int)$wp_query->query_vars['team_id']);
            while ( have_posts() ) : the_post();
                get_template_part( 'content', 'page' );
            endwhile;
        }
        ?>
    </div>
</article>

<?php get_footer(); ?>