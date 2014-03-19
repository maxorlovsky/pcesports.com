<?php
/**
 * General settings administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once('./admin.php');

if ( ! current_user_can( 'edit_posts' ) )
	wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );

$title = __('PentaClick Email Sender');

if (isset($_POST) && $_POST['action'] == 'update' && $_POST['secret'] == '123890') {
    $emails = array();
    $where = '`tournament_id` = 1 AND ';
    if ($_POST['game'] != 'both') {
        $where .= '`game` = "'.mysql_real_escape_string($_POST['game']).'"';
    }
    
    $q = mysql_query('SELECT `name`, `email` FROM `teams` WHERE '.$where);
    $alreadyInMail = array();
    $i = 0;
    while($r = mysql_fetch_object($q)) {
        if (!in_array($r->email, $alreadyInMail)) {
            if ($_POST['lang'] == 'ru' && substr($r->email, -2) == 'ru' ||
                $_POST['lang'] == 'en' && substr($r->email, -2) != 'ru' ||
                $_POST['lang'] == 'both'
               ) {
                $alreadyInMail[] = $r->email;
                $emails[$i]['name'] = $r->name;
                $emails[$i]['email'] = $r->email;
                ++$i;
            }
        }
    }
    
    foreach($emails as $k => $v) {
        $msg = str_replace(
            array('%name%', '%url%', '%lolurl%', '%hsurl%'),
            array($v['name'], get_site_url(), LOLURL, HSURL),
            nl2br($_POST['message'])
        );
        $msg = stripslashes($msg);
        echo $v['email'];
        echo '<br>';
        echo $_POST['email-header'];
        echo '<br>';
        echo $msg;
        echo '<br>';
        echo '<br>';
        echo '<br>';
        echo '<br>';
        //sendMail($v['email'], $_POST['email-header'], $msg);
        //sleep(1);
    }
    exit();

    $done = 1;
}

$emailDefaultText = 'Hello <b>%name%</b>

...

Best regard.
PentaClick eSports';

include('./admin-header.php');
?>

<style>
.regular-textarea {
    width: 500px;
    height: 300px;
}
</style>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<? if (isset($done) && $done) { ?>
    <div class="updated below-h2" id="message"><p>Данные обновлены</p></div>
<? } ?>

<form method="post">
<?php settings_fields('options'); ?>

<table class="form-table">
    <tr>
        <th scope="row" style="width: 350px"><label for="email-header">Email Header</label></th>
        <td><input name="email-header" type="text" id="email-header" value="<?=$_POST['email-header']?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th scope="row" style="width: 350px"><label>Game</label></th>
        <td>
            <input name="game" id="lol" type="radio" class="regular-radio" value="lol" /> <label for="lol">League of Legends</label>
            <input name="game" id="hs" type="radio" class="regular-radio" value="hs" /> <label for="hs">Hearthstone</label>
            <input name="game" id="both" type="radio" class="regular-radio" value="both" checked="checked" /> <label for="both">Both</label>
        </td>
    </tr>
    <tr>
        <th scope="row" style="width: 350px"><label>Languages</label></th>
        <td>
            <input name="lang" id="lang_en" type="radio" class="regular-radio" value="en" /> <label for="lang_en">English</label>
            <input name="lang" id="lang_ru" type="radio" class="regular-radio" value="ru" /> <label for="lang_ru">Русский</label>
            <input name="lang" id="lang_both" type="radio" class="regular-radio" value="both" checked="checked" /> <label for="lang_both">Both</label>
        </td>
    </tr>
    <tr>
        <th scope="row" style="width: 350px"><label>Message</label></th>
        <td>
            <textarea class="regular-textarea" name="message"><?=($_POST['message']?$_POST['message']:$emailDefaultText)?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row" style="width: 350px"><label for="secret">Secret</label></th>
        <td><input name="secret" type="text" id="secret" value="" class="regular-text" /></td>
    </tr>
</table>

<?php submit_button(); ?>

</form>

</div>

<?php include('./admin-footer.php') ?>