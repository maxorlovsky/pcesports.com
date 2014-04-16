<?php
if (ENV == 'dev') {
    $number = 37;
}
elseif (ENV == 'test') {
    $number = 39;
}

$post = get_page($number); 
$content = apply_filters('the_content', $post->post_content); 
?>

<div class="holder" id="about"></div>
<article class="textonpage-content-wrapper">
    <div class="content" id="textonpage-content">
        <?=$content?>
    </div>
</article>