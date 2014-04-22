<!DOCTYPE html>
<html lang="<?=_cfg('language')?>">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="<?=$this->data->settings['site_description']?>" />
    <meta name="keywords" content="<?=$this->data->settings['site_keywords']?>" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <title><?=$this->data->settings['site_name']?> - page</title>
    
    <script src="<?=_cfg('static')?>/js/scripts.js"></script>
    
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="<?=_cfg('site')?>/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/slider.css" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/style.css" />
    
    <? if (_cfg('env') == 'prod') { ?>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    
      ga('create', 'UA-47216717-1', 'pcesports.com');
      ga('send', 'pageview');
    </script>
    <? } ?>
</head>

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

<body class="default">

<header class="container">
    <a href="<?=_cfg('site')?>" class="logo"><img src="<?=_cfg('img')?>/logo.png" /></a>
    <div class="social">
        <a class="fb" href="https://www.facebook.com/pentaclickesports" target="_blank"></a>
        <a class="tw" href="https://twitter.com/pentaclick" target="_blank"></a>
        <a class="gp" href="https://plus.google.com/u/0/communities/106917438189046033786" target="_blank"></a>
        <a class="yt" href="https://www.youtube.com/user/pentaclickesports" target="_blank"></a>
        <a class="vk" href="https://vk.com/pentaclickesports" target="_blank"></a>
        <a class="tv" href="http://www.twitch.tv/pentaclick_tv" target="_blank"></a>
        <a class="sm" href="http://steamcommunity.com/groups/pentaclickesports" target="_blank"></a>
    </div>
    <script>
    $('.social a').css('transition', '.5s');
    </script>
    <div class="clear"></div>
</header>

<nav class="navbar container">
    <div class="navbar-inner">
        <ul class="nav">
        	<?
        	if ($this->data->links) {
				foreach($this->data->links as $v) {
			?>
            <li class="" id="<?=$v->link?>">
            	<a href="<?=_cfg('href')?>/<?=$v->link?>"><?=t($v->value)?></a>
           	</li>
            <?
				}
            }
            ?>
        </ul>
    </div>
</nav>