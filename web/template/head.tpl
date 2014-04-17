<!DOCTYPE html>
<html lang="<?=_cfg('language')?>">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="<?=$this->data->settings['site_description']?>" />
    <meta name="keywords" content="<?=$this->data->settings['site_keywords']?>" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <title><?=$this->data->settings['site_name']?> - page</title>
    
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="<?=_cfg('site')?>/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/style.css" />
</head>

<body class="default">

<header class="container">
    <a href="<?=_cfg('site')?>" class="logo"><img src="<?=_cfg('img')?>/logo.png" /></a>
    <div class="social">
        <div class="fb"></div>
        <div class="tw"></div>
        <div class="gp"></div>
        <div class="yt"><i class="ytc"></i></div>
        <div class="vk"></div>
        <div class="tw"></div>
        <div class="sm"></div>
    </div>
</header>

<nav class="navbar container">
    <div class="navbar-inner">
        <ul class="nav">
            <li class="" id="news"><a href="<?=_cfg('href')?>/news">news</a></li>
            <li class="" id="lol"><a href="<?=_cfg('lolsite')?>">league of legends</a></li>
            <li class="" id="hs"><a href="<?=_cfg('hssite')?>">hearthstone</a></li>
            <li class="" id="streams"><a href="<?=_cfg('href')?>/streams">streams</a></li>
            <li class="" id="contacts"><a href="<?=_cfg('href')?>/contacts">contacts</a></li>
        </ul>
    </div>
</nav>

<section class="container page">
    
<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="">Home</h1>
        </div>
        <div class="block-content">Lorem ipsum</div>
    </div>
</div>

<div class="right-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Like us!</h1>
        </div>
        <div class="block-content">rawr</div>
    </div>
</div>

<div class="clear"></div>
</section>

</body>
</html>