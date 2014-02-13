<?php
$post = get_page(37); 
$content = apply_filters('the_content', $post->post_content); 
?>

<div class="holder" id="about"></div>
<article class="textonpage-content-wrapper">
    <div class="content" id="textonpage-content">
        <?=$content?>
    </div>
</article>