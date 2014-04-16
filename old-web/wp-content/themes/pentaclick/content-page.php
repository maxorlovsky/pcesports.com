<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
    
?>

<article class="text-content-wrapper">
    <div class="content" id="text-content">
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
    </div>
</article>