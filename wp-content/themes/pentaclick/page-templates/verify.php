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
    ' `link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
    ' `deleted` = 0'
);
$r = mysql_fetch_object($q);
get_header(); ?>


<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <? if (mysql_num_rows($q) == 0) { ?>
            <h1><?=_e('incorrect_link', 'pentaclick')?></h1>
            <p><?=_e('check_incorrect_link', 'pentaclick')?> pentaclickesports@gmail.com</p>
        <? }
        else if ($r->approved == 1) {
            ?>
            <h1><?=_e('double_click', 'pentaclick')?></h1>
            <p><?=_e('double_click-sub', 'pentaclick')?> <a href="<?=get_site_url()?>#participants"><?=_e('list', 'pentaclick')?></a>!</p>
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