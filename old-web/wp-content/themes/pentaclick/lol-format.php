<?php
if (ENV == 'dev') {
    $number = 19;
}
elseif (ENV == 'test') {
    $number = 19;
}
else {
    $number = 19;
}

$post = get_page($number); 
$content = apply_filters('the_content', $post->post_content); 
?>

<div class="holder" id="format"></div>
<article class="format-content-wrapper">
    <div class="content" id="format-content">
        <?=$content?>
    </div>
</article>