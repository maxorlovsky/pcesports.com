<?php
/**
 * Template Name: Verify template

 * @since v1
 */
exit('Closed');
if (!$wp_query->query_vars['team_id'] && !$wp_query->query_vars['code']) {
    wp_redirect( get_site_url() );
    exit;
}

if (cOptions('tournament-on-lol') == 1) {
    $q = mysql_query(
    	'SELECT `name`, `approved` FROM `teams` WHERE '.
    	' `tournament_id` = 1 AND '.
    	' `game` = "lol" AND '.
        ' `id` = '.(int)$wp_query->query_vars['team_id'].' AND '.
        ' `link` = "'.mysql_real_escape_string($wp_query->query_vars['code']).'" AND '.
        ' `deleted` = 0'
    );
    $r = mysql_fetch_object($q);
}
get_header(); ?>


<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <? if (cOptions('tournament-on-lol') == 0) { ?>
            <h1><?=_e('tournament_started', 'pentaclick')?></h1>
            <p><?=_e('cant_verify_tournament_started', 'pentaclick')?></p>
        <? } else if (mysql_num_rows($q) == 0) { ?>
            <h1><?=_e('incorrect_link', 'pentaclick')?></h1>
            <p><?=_e('check_incorrect_link', 'pentaclick')?> pentaclickesports@gmail.com</p>
        <? } else if ($r->approved == 1) {
            ?>
            <h1><?=_e('double_click', 'pentaclick')?></h1>
            <p><?=_e('double_click-sub', 'pentaclick')?> <a href="<?=get_site_url()?>#participants"><?=_e('list', 'pentaclick')?></a>!</p>
        <? } else {
            mysql_query('UPDATE `teams` SET approved = 1 WHERE `tournament_id` = 1 AND `game` = "lol" AND `id` = '.(int)$wp_query->query_vars['team_id']);
            mysql_query('UPDATE `players` SET approved = 1 WHERE `tournament_id` = 1 AND `game` = "lol" AND `team_id` = '.(int)$wp_query->query_vars['team_id']);
            
            if (ENV == 'prod') {
                $participant_id = $wp_query->query_vars['team_id'] + 100000;
            }
            /*else if (ENV == 'test') {
                $participant_id = $wp_query->query_vars['team_id'] + 50000;
            }
            else {
                $participant_id = $wp_query->query_vars['team_id'];
            }*/
            
            if (ENV == 'prod') {
                $apiArray = array(
                    'participant_id' => $participant_id,
                    'participant[name]' => $r->name,
                );
                
                //Adding team to Challonge bracket
                runChallongeAPI('tournaments/pentaclick-'.cOptions('brackets-link-lol').'/participants.post', $apiArray);
                
                //Registering ID, becaus Challonge idiots not giving an answer with ID
                $answer = runChallongeAPI('tournaments/pentaclick-'.cOptions('brackets-link-lol').'/participants.json');
                array_reverse($answer, true);
                foreach($answer as $f) {
                    if ($f->participant->name == $r->name) {
                        mysql_query('UPDATE `teams` SET `challonge_id` = '.(int)$f->participant->id.' WHERE `tournament_id` = 1 AND `game` = "lol" AND `id` = '.(int)$wp_query->query_vars['team_id']);
                        break;
                    }
                }
                
                sendMail('pentaclickesports@gmail.com',
                'Team added. PentaClick eSports.',
                'Team was added!!!<br />
                Date: '.date('d/m/Y H:i:s').'<br />
                Team: <b>'.$r->name.'</b><br>
                IP: '.$_SERVER['REMOTE_ADDR']);
            }

            while ( have_posts() ) : the_post();
                get_template_part( 'content', 'page' );
            endwhile;
        }
        ?>
    </div>
</article>

<?php get_footer(); ?>