<!DOCTYPE html>
<html lang="<?=_cfg('language')?>">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="<?=$this->data->settings['site_description']?>" />
    <meta name="keywords" content="<?=$this->data->settings['site_keywords']?>" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta property="fb:app_id" content="766575306708443"/>
    
    <title><?=$this->title?><?=$this->data->settings['site_name']?></title>
    
    <script src="<?=_cfg('static')?>/js/scripts.js"></script>
    
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="<?=_cfg('site')?>/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/slider.css" />
	<link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/highslide.css" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/style.css" />
    
    <? if (_cfg('env') == 'prod') { ?>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', 'UA-47216717-1', 'pcesports.com');
        ga('require', 'displayfeatures');
        ga('send', 'pageview');
    </script>
    <? } ?>
    
    <script>
    	var site = '<?=_cfg('site')?>';
		var str = [];
		str['days'] = '<?=t('days')?>';
		str['day'] = '<?=t('day')?>';
		
		//Highslide
		hs.graphicsDir = '<?=_cfg('static')?>/images/graphics/';
		hs.wrapperClassName = 'hidden';
		hs.align = 'center';
		hs.transitions = ['expand'];
		hs.outlineType = 'rounded-white';
		hs.fadeInOut = false;
		hs.dimmingOpacity = 0.8;
		//Highslide
    </script>
</head>

<section id="full-site-wrapper" class="default">

<div id="head-line"></div>

<? if (_cfg('env') != 'dev') { ?>
<div id="fb-root"></div>
<script>
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=766575306708443&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
<? } ?>

<header class="container">
    <a href="<?=_cfg('href')?>" class="logo"><img src="<?=_cfg('img')?>/logo.png" /></a>
    <div class="fright">
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
        <div class="languages">
            <a href="javascript:void(0);">
                <img src="<?=_cfg('img')?>/flags/<?=$this->data->langugePicker['picked']->flag?>.png" />
                <?=ucfirst(t($this->data->langugePicker['picked']->title))?>
            </a>
            <div class="language-switcher">
                <div class="title">Choose language</div>
                <?
                unset($this->data->langugePicker['picked']);
                foreach($this->data->langugePicker as $v) {
                ?>
                    <a href="<?=_cfg('site')?>/<?=$v->flag?>/"><img src="<?=_cfg('img')?>/flags/<?=$v->flag?>.png" /><?=ucfirst(t($v->title))?></a>
                <?
                }
                ?>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</header>

<nav class="navbar container">
    <div class="navbar-inner">
        <ul class="nav">
        	<?
        	if ($this->data->links) {
				foreach($this->data->links as $v) {
                    if ($v->logged_in == 1 && $this->logged_in || $v->logged_in == 0) {
                    ?>
                    <li class="" id="<?=$v->link?>">
                        <a href="<?=_cfg('href')?>/<?=$v->link?>"><?=t($v->value)?></a>
                    </li>
                    <?
                    }
				}
            }
            ?>
        </ul>
        <div class="login"><div class="usericon"></div></div>
    </div>
</nav>