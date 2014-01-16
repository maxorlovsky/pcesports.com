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

$title = __('PentaClick Settings');
$parent_file = 'options-pentaclick.php';
/* translators: date and time format for exact current time, mainly about timezones, see http://php.net/date */
$timezone_format = _x('Y-m-d G:i:s', 'timezone date format');

if (isset($_POST) && $_POST['action'] == 'update') {
    $exclude = array('option_page', 'action', '_wpnonce', '_wp_http_referer', 'submit');
    foreach($_POST as $k => $v) {
        if (!in_array($k, $exclude)) {
            mysql_query('UPDATE options SET `value` = "'.mysql_real_escape_string($v).'" WHERE `name` = "'.mysql_real_escape_string($k).'"');
        }
    }
}

include('./admin-header.php');

//Getting options for pentaclick
$q = mysql_query('SELECT * FROM options');
$options = array();
$i = 0;
while ($r = mysql_fetch_object($q)) {
    $options[$i]['name'] = $r->name;
    $options[$i]['value'] = $r->value;
    $options[$i]['field'] = $r->field;
    $options[$i]['type'] = $r->type;
    ++$i;
}
?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<? if (isset($_POST) && $_POST) { ?>
    <div class="updated below-h2" id="message"><p>Данные обновлены</p></div>
<? } ?>

<form method="post" action="<?=$parent_file?>">
<?php settings_fields('options'); ?>

<table class="form-table">
<? foreach($options as $f) { ?>
    <tr valign="top">
        <th scope="row" style="width: 350px"><label for="<?=$f['name']?>"><?=$f['field']?></label></th>
        <td>
            <? if ($f['type'] == 'checkbox') { ?>
                <input name="<?=$f['name']?>" type="hidden" id="<?=$f['name']?>" value="0" />
                <input name="<?=$f['name']?>" type="checkbox" id="<?=$f['name']?>" value="1" <?=($f['value']?'checked="checked"':null)?> />
            <? } else if ($f['type'] == 'text') { ?>
                <input name="<?=$f['name']?>" type="text" id="<?=$f['name']?>" value="<?=$f['value']?>" class="regular-text" />
            <? } else { ?>
                For <?=$f['name']?> type is not defined.
            <? } ?>
        </td>
    </tr>
<? } ?>

<?php do_settings_fields('options', 'default'); ?>

</table>

<?php do_settings_sections('options'); ?>

<?php submit_button(); ?>
</form>

</div>

<?php include('./admin-footer.php') ?>
