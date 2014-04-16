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
	
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />
    
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    
    <script>
        var site = 'http://<?=substr(esc_url(home_url()),7)?>';
        var lang = '<?=qtrans_getLanguage()?>';
        var challongeLoLLinkName = '<?=cOptions('brackets-link-lol')?>';
        var challongeLoLHeight = <?=cOptions('brackets-height-lol')?>;
        var challongeHsLinkName = '<?=cOptions('brackets-link-hs')?>';
        var challongeHsHeight = <?=cOptions('brackets-height-hs')?>;
        var str = [];
        str['sure_to_quit'] = '<?=_e('sure_to_quit', 'pentaclick')?>';
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
    <article class="socicons">
        <a href="https://www.facebook.com/pentaclickesports" target="_blank" class="facebook"></a>
        <a href="https://www.twitter.com/pentaclick" target="_blank" class="twitter"></a>
        <a href="http://www.twitch.tv/pentaclick_tv" target="_blank" class="twitch"></a>
        <a href="https://vk.com/pentaclickesports" target="_blank" class="vk"></a>
        <a href="skype:?chat&amp;blob=5OQXjmArliuzMMSBcbTbp_5AS12FBOMCQHsik7Tty_-acWKgxKsd8sabSWKtWCGseh4mTzrw6NLaVRnYrqyrSQk8RlPU77I" class="skype"></a>
        <a href="http://www.youtube.com/pentaclickesports" target="_blank" class="youtube"></a>
        <article class="languages">
            <div class="current-lang"><?=qtrans_getLanguageName()?></div>
            <div class="popup-langs">
                <?
                foreach(qtrans_getSortedLanguages() as $f) {
                    if ($f != qtrans_getLanguage()) {
                        echo '<a href="'.qtrans_convertURL(get_site_url(), $f).'">'.qtrans_getLanguageName($f).'</a>';
                    }
                }
                ?>
            </div>
    	</article>
    </article>
    <?
    if (cOptions('game')) {
        get_template_part( 'nav', cOptions('game') );
    }
    else {
        get_template_part( 'nav', 'original' );
    }
    ?>
</header>

<section id="wrapper" class="clearfix">