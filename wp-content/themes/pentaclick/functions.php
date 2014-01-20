<?php
/**
 * @package Pentaclick
 * @since v1
 */

/**
 * @since v1
 */
function pentaclick_setup() {
	/*
	 * Makes Twenty Twelve available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Twelve, use a find and replace
	 * to change 'pentaclick' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'pentaclick', get_template_directory() . '/languages' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'pentaclick' ) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
}
add_action( 'after_setup_theme', 'pentaclick_setup' );

/**
 * Return the Google font stylesheet URL if available.
 * 
 * @since v1
 *
 * @return string Font stylesheet
 */
function pentaclick_get_font_url() {
	$font_url = '';
    
	$subsets = 'latin,cyrillic-ext,latin-ext,cyrillic';

	$protocol = is_ssl() ? 'https' : 'http';
	$query_args = array(
		'family' => 'PT+Sans:400,700',
		'subset' => $subsets,
	);
	$font_url = add_query_arg( $query_args, $protocol.'://fonts.googleapis.com/css' );

	return $font_url;
}

/**
 * Enqueue scripts and styles for front-end.
 *
 * @since Twenty Twelve 1.0
 *
 * @return void
 */
function pentaclick_scripts_styles() {
	global $wp_styles;
    
    //Loading JS top
    wp_enqueue_script( 'pc-jquery', get_template_directory_uri() . '/js/jquery.min.js', array(), '1');
    wp_enqueue_script( 'pre-js', get_template_directory_uri() . '/js/pre-js.js', array(), '1');
    
    //Loading JS bottom
    wp_enqueue_script( 'isotope', get_template_directory_uri() . '/js/jquery.isotope.min.js', array(), '1', true);
    wp_enqueue_script( 'post-js', get_template_directory_uri() . '/js/post-js.js', array(), '1', true);

    //Loading Google fonts
	$font_url = pentaclick_get_font_url();
	if ( ! empty( $font_url ) )
		wp_enqueue_style( 'pentaclick-fonts', esc_url_raw( $font_url ), array(), null );

	//Loading CSS
    wp_enqueue_style( 'style', get_stylesheet_uri(), 'array', 1 );
    wp_enqueue_style( 'isotope', get_template_directory_uri() . '/css/isotope.css', 'array', 1 );
    wp_enqueue_style( 'fonts', get_template_directory_uri() . '/css/fonts.css', 'array', 1 );
	
}
add_action( 'wp_enqueue_scripts', 'pentaclick_scripts_styles' );

/**
 * @since v1
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function pentaclick_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'pentaclick' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'pentaclick_wp_title', 10, 2 );

if ( ! function_exists( 'pentaclick_entry_meta' ) ) :
/**
 * @since v1
 *
 * @return void
 */
function pentaclick_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'pentaclick' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'pentaclick' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'pentaclick' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'pentaclick' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'pentaclick' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'pentaclick' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Twenty Twelve 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 * @return void
 */
function pentaclick_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'pentaclick_customize_register' );

/**
 * Admin functions 
*/
function pentaclick_admin_menus() {
     add_menu_page('PentaClick settings', 'PC eSports', 'edit_posts', 'options-pentaclick.php', '', '', 50);   
}
add_action("admin_menu", "pentaclick_admin_menus");

//Fixing qTranslate TinyMCE height
function content_textarea_height() {
    echo'
    <style type="text/css">
        #qtrans_textarea_content{ height:600px; }
    </style>
    ';
}
add_action('admin_head', 'content_textarea_height');

function getRewriteRules() {
    global $wp_rewrite; // Global WP_Rewrite class object
    return $wp_rewrite->rewrite_rules(); 
}
function addPentaClickRewrite() {
    global $wp_rewrite; // Global WP_Rewrite class object
    
    add_rewrite_tag('%team_id%', '([^/]*)');
    add_rewrite_tag('%code%', '([^/]*)');
    add_rewrite_rule('^verify/([^/]*)/([^/]*)/?','index.php?pagename=verify&team_id=$matches[1]&code=$matches[2]','top');
    add_rewrite_rule('^delete/([^/]*)/([^/]*)/?','index.php?pagename=delete&team_id=$matches[1]&code=$matches[2]','top');
    
    $wp_rewrite->flush_rules(1);
}
addPentaClickRewrite();
//dump(getRewriteRules());

/**
 * Getting options for PentaClick
 *
 * @since v1
 */
$q = mysql_query('SELECT * FROM options');
$siteData = array();
while ($r = mysql_fetch_object($q)) {
    $siteData[$r->name] = $r->value;
}

function cOptions($key) {
    global $siteData;
    
    return $siteData[$key];
}

function dump($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function ddump($array) {
    dump($array);
    die;
}