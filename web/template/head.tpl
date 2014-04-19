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
</head>

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
            <li class="" id="news"><a href="<?=_cfg('href')?>/news">news</a></li>
            <li class="" id="lol"><a href="<?=_cfg('lolsite')?>">league of legends</a></li>
            <li class="" id="hs"><a href="<?=_cfg('hssite')?>">hearthstone</a></li>
            <li class="" id="streams"><a href="<?=_cfg('href')?>/streams">streams</a></li>
            <li class="" id="streams"><a href="<?=_cfg('href')?>/shop">shop</a></li>
            <li class="" id="contacts"><a href="<?=_cfg('href')?>/contacts">contacts</a></li>
        </ul>
    </div>
</nav>

<section class="container page">
    
<div class="left-containers">
    <div class="block">
        <ul class="bx-wrapper">
            <li><a href="#"><img src="<?=_cfg('img')?>/poster.png" /></a></li>
            <li><a href="#"><img src="<?=_cfg('img')?>/poster-hs.png" /></a></li>
        </ul>
    </div>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">News</h1>
        </div>
        <div class="block-content news">
            <? for($i=0;$i<=5;++$i) { ?>
            <div class="small-block">
                <div class="image-holder"><p>NO IMAGE</p></div>
                <a href="#" class="title">News title News title Newsz title News titlez</a>
                <div class="info">
                    <div class="dates">5 days ago</div>
                    <a href="#" class="comments"><?=rand(1,99)?></a>
                    <div class="clear"></div>
                </div>
            </div>
            <? } ?>
            <div class="clear"></div>
        </div>
    </div>
</div>

<div class="right-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Next tournaments</h1>
        </div>
        <div class="block-content next-tournaments">
            <h2>League of Legends tournament #3</h2>
            <div class="timer" attr-start="1398420000" attr-time="<?=time()?>"></div>
        </div>
    </div>
</div>

<div class="clear"></div>
</section>

<script src="<?=_cfg('static')?>/js/main.js"></script>

</body>
</html>