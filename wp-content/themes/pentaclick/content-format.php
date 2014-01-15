<?php
$post = get_page(19); 
$content = apply_filters('the_content', $post->post_content); 
?>

<div class="holder" id="format"></div>
<article class="format-content-wrapper">
    <div class="content" id="format-content">
        <?=$content?>
    </div>
</article>