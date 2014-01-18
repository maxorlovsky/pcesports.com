<?php
/**
 * @package Pentaclick
 * @since v1
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="description" content="Pentaclick eSports is a monthly based video games tournaments, held up in Europe. Currently we marked only 1 game - League of Legends." />
	<meta name="keywords" content="esports, league of legends tournament, pentaclick" />	
	<meta name="author" content="MaxOrlovsky.net" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    
    <script>
        var site = 'http://<?=substr(esc_url(home_url()),7)?>';
    </script>
    
    <script type="text/javascript" src="//vk.com/js/api/openapi.js?105"></script>
    
    <? if (ENV == 'prod') { ?>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    
      ga('create', 'UA-47216717-1', 'pcesports.com');
      ga('send', 'pageview');
    </script>
    <? } ?>
    
    <?php wp_head(); ?>
</head>

<body>
<!--[if lt IE 10]>
<div style="clear:both;text-align:center;position:relative;">
<a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." /></a></div>
<![endif]-->

<div id="fb-root"></div>
<script>

(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=410840362357950";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

<header id="header" class="move">
    <?/*<article class="languages">
		<a href="<?=qtrans_convertURL(get_permalink(), 'en')?>" <?=(qtrans_getLanguage()=='en'?'class="active"':null)?>><div class="english"></div></a>
        <a href="<?=qtrans_convertURL(get_permalink(), 'ru')?>" <?=(qtrans_getLanguage()=='ru'?'class="active"':null)?>><div class="russian"></div></a>
	</article>*/?>
    <article class="socicons">
        <a href="https://www.facebook.com/pages/Pentaclick-eSports/521490341298749" target="_blank" class="facebook"></a>
        <a href="https://www.twitter.com/pentaclick" target="_blank" class="twitter"></a>
        <a href="http://www.twitch.tv/pentaclick_tv" target="_blank" class="twitch"></a>
        <a href="https://vk.com/pentaclickesports" target="_blank" class="vk"></a>
        <a href="skype:?chat&amp;blob=5OQXjmArliuzMMSBcbTbp_5AS12FBOMCQHsik7Tty_-acWKgxKsd8sabSWKtWCGseh4mTzrw6NLaVRnYrqyrSQk8RlPU77I" class="skype"></a>
        <a href="http://www.youtube.com/pentaclickesports" target="_blank" class="youtube"></a>
    </article>
    <section id="navbar">
        <article class="logo"><a href="#home" title="Home" class="scroll"><img src="<?php bloginfo('template_directory'); ?>/images//logo.png" alt="Home" /></a></article>
        <nav class="globalnav">
            <ul>
                <li id="home-url"><a href="#home" class="scroll" title="<?=_e('home', 'pentaclick')?>"><?=_e('home', 'pentaclick')?><br /><small><?=_e('home-sub', 'pentaclick')?></small></a></li>
                <li id="connect-url"><a href="#connect" class="scroll" title="<?=_e('connect', 'pentaclick')?>"><?=_e('connect', 'pentaclick')?><br /><small><?=_e('connect-sub', 'pentaclick')?></small></a></li>
                <li id="participants-url"><a href="#participants" class="scroll" title="<?=_e('participants', 'pentaclick')?>"><?=_e('participants', 'pentaclick')?><br /><small><?=_e('participants-sub', 'pentaclick')?></small></a></li>
                <li id="register-url"><a href="#register" class="scroll" title="<?=_e('register', 'pentaclick')?>"><?=_e('register', 'pentaclick')?><br /><small><?=_e('register-sub', 'pentaclick')?></small></a></li>
                <li id="format-url"><a href="#format" class="scroll" title="<?=_e('format', 'pentaclick')?>"><?=_e('format', 'pentaclick')?><br /><small><?=_e('format-sub', 'pentaclick')?></small></a></li>
            </ul>
        </nav>
    </section>
</header>

<section id="wrapper" class="clearfix">