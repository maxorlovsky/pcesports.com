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

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    parse_str($_POST['post'], $post);
    foreach($post as $k => $v) {
        $post[$k] = trim($v);
    }
    
    if ($_POST['control'] == 'chat') {
        $fileName = $_SERVER['DOCUMENT_ROOT'].'/chats/'.$post['id'].'.txt';
        
        if ($_POST['action'] == 'send') {
            $file = fopen($fileName, 'a');
            $content = '<p><span id="notice">('.date('H:i:s', time()).')</span> &#60;<u>Pentaclick Admin</u>&#62; - '.$post['text'].'</p>';
            fwrite($file, htmlspecialchars($content));
            fclose($file);
        }

        $answer['ok'] = 1;
        $answer['html'] = stripslashes(html_entity_decode(file_get_contents($fileName)));
    }
    else {
        $answer['ok'] = 0;
        $answer['html'] = 'Error';
    }
    
	exit(json_encode($answer));
}

$title = __('PentaClick Tournament Chats');

wp_enqueue_style( 'pentaclickstyle', '/wp-admin/css/pentaclick.css', 'array', 1 );

include('./admin-header.php');

$activeGames = mysql_query('SELECT `f`.`id`, `f`.`screenshots`, `t1`.`id` AS `id1`, `t1`.`name` AS `name1`, `t2`.`id` AS `id2`, `t2`.`name` AS `name2`
FROM `hs_fights` AS `f`
LEFT JOIN `teams` AS `t1` ON `f`.`player1_id` = `t1`.`challonge_id`
LEFT JOIN `teams` AS `t2` ON `f`.`player2_id` = `t2`.`challonge_id`
WHERE `f`.`done` = 0');
?>

<style>
h4 { font-size:15px; margin: 0; }
h4:first-letter { color:#5c66e9; }
.chat {
    border: 2px solid #333;
    border-radius: 15px;
    float: left;
    width: 400px;
    background-color: #fff;
    margin-left: 10px;
    margin-bottom: 10px;
}
.chat h4 {
    background-color: #eee;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 3px 10px;
    margin-bottom: 0;
}
.chat .chat-content {
    padding: 5px 10px;
    height: 300px;
    border-bottom: 1px solid #000;
    background-color: #ddd;
    font-size: 15px;
    overflow-y: auto;
}
.chat .chat-content #notice {
    font-size: 13px;
    color: #999;
}
.chat .chat-input {
    padding: 0 10px;
}
.chat .chat-input input[type="text"] {
    width: 345px;
    padding: 3px 0;
    border: 0;
}
.chat .attach-file {
    background-image: url('images/design/attach-file.png');
    background-repeat: no-repeat;
    background-color: #ddd;
    background-position: center center;
    height: 25px;
    width: 25px;
    float: right;
    margin-top: 1px;
    border: 1px solid #777;
    cursor: pointer;
}
.chat .attach-file:hover {
    background-color: #eee;
}
</style>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<?
if (mysql_num_rows($activeGames)) {
    while($r = mysql_fetch_object($activeGames)) {
    ?>
        <div class="chat">
            <h4>Chat (<?=$r->name1?> vs <?=$r->name2?>) [<?=$r->screenshots?>] [#<?=$r->id?>]</h4>
            <div class="chat-content" id="<?=$r->id1?>_vs_<?=$r->id2?>">
            </div>
            <div class="chat-input">
                <input type="text" class="chat-submit" id="chat-input" attr-id="<?=$r->id1?>_vs_<?=$r->id2?>" />
            </div>
        </div>
    <?
    }
} 
?>
<script>
jQuery('.chat-content').scrollTop(jQuery('.chat-content').prop('scrollHeight'));

jQuery('.chat-submit').on('keyup', function(e) {
    if (!e) {
        e = window.event;
	}

    if (e.keyCode == 13 && jQuery.trim(jQuery(this).val())) {
        var text = jQuery(this).val();
        var id = jQuery(this).attr('attr-id');
        jQuery(this).val('');
        jQuery.ajax({
            url: '',
            type: 'POST',
            dataType: 'json',
            data: {
                control: 'chat',
                action: 'send',
                post: 'id='+id+'&text='+text
            },
            success: function(answer) {
                console.log(answer);
                jQuery('.chat-content#'+id).html(answer.html);
                jQuery('.chat-content#'+id).scrollTop(jQuery('.chat-content#'+id).prop('scrollHeight'));
            }
        });
    }
});

profiler = {
    fetchChat: function() {
        jQuery.each(jQuery('.chat-content'), function(k, v) {
            var id = jQuery(v).attr('id');
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                data: {
                    control: 'chat',
                    action: 'fetch',
                    post: 'id='+id
                },
                success: function(answer) {
                    checkTop = parseInt(jQuery('.chat-content#'+id).prop('scrollTop')) + parseInt(jQuery('.chat-content#'+id).height()) + 10;
                    checkHeight = parseInt(jQuery('.chat-content#'+id).prop('scrollHeight'));
                    
                    jQuery('.chat-content#'+id).html(answer.html);
                    
                    if (checkTop == checkHeight) {
                        jQuery('.chat-content#'+id).scrollTop(jQuery('.chat-content#'+id).prop('scrollHeight'));
                    }
                }
            });
        });
    }
};

profiler.fetchChat();
setInterval(function () { profiler.fetchChat(); }, 5000);
</script>
</div>

<?php include('./admin-footer.php') ?>