<!DOCTYPE html>
<html lang="<?=_cfg('language')?>">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="<?=$this->data->settings['site_description']?>" />
    <meta name="keywords" content="<?=$this->data->settings['site_keywords']?>" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta property="fb:app_id" content="766575306708443"/>
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?=$this->title?><?=$this->data->settings['site_name']?>" />
    <meta property="og:description" content="<?=(isset($this->seoData->ogDesc)?$this->seoData->ogDesc:null)?>" />
    <meta property="og:image" content="<?=$this->seoData->ogImg?>" />
    <meta property="og:url" content="<?=_cfg('site').$_SERVER['REQUEST_URI']?>" />
    
    <title><?=$this->title?><?=$this->data->settings['site_name']?></title>
    
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="<?=_cfg('site')?>/favicon.ico" />
    
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/combined.css?v=1.23" />
</head>
<body>

<script src="<?=_cfg('static')?>/js/ads.js"></script>

<script src="<?=_cfg('static')?>/js/angular.js"></script>
<script src="<?=_cfg('static')?>/js/angular-resource.min.js"></script>
<script src="<?=_cfg('static')?>/js/angular-combined.js?v=1.1"></script>
<script src="<?=_cfg('static')?>/js/scripts.js"></script>

<section id="full-site-wrapper">

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
var g = {
    site: '<?=_cfg('site')?>',
    siteSecure: '<?=str_replace("http://", "https://", _cfg('site'))?>',
    env: '<?=_cfg('env')?>',
    logged_in: <?=($this->logged_in?1:0)?>,
    str: {
        days: '<?=t('days')?>',
        day: '<?=t('day')?>',
        connect: '<?=t('connect')?>',
        disconnect: '<?=t('disconnect')?>',
        connected: '<?=t('connected')?>',
        disconnected: '<?=t('disconnected')?>',
        enter_url: '<?=t('enter_url')?>',
        turn_off_sound: '<?=t('turn_off_sound')?>',
        turn_on_sound: '<?=t('turn_on_sound')?>',
        approved: '<?=t('approved')?>',
    }
};

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

<header class="container">
    <div class="burger"></div>
    <a href="<?=_cfg('href')?>" class="logo"><img src="<?=_cfg('img')?>/logo.png" /></a>
    
    <? if ($this->logged_in == 1) { ?>
        <ul class="nav-user">
            <?
            if ($this->data->links) {
                foreach($this->data->links as $v) {
                    if ($v->logged_in == 1 && $v->main_link == 0 && $v->block == 1) {
                    ?>
                    <li class="nav-link" id="<?=$v->link?>">
                        <div class="nav-avatar"><a href="<?=_cfg('href')?>/<?=str_replace('%user%', $this->data->user->name, $v->link)?>"><img src="<?=_cfg('avatars')?>/<?=$this->data->user->avatar?>.jpg" /></a></div>
                        <a href="<?=_cfg('href')?>/<?=str_replace('%user%', $this->data->user->name, $v->link)?>">
                            <?=$this->data->user->name?><?//=t($v->value)?>
                            <p><?=t('points')?>: <span class="achievementsPoints"><?=$this->data->user->experience?></span></p>
                        </a>
                        <ul class="nav-sub">
                        <?
                        if ($v->sublinks) {
                            foreach($v->sublinks as $v2) {
                                ?>
                                <li class="nav-sublink" id="<?=$v2->link?>">
                                    <a href="<?=_cfg('href')?>/<?=$v2->link?>"><?=t($v2->value)?></a>
                                </li>
                                <?
                            }
                        }
                        ?>
                            <li class="nav-sublink" id="logout">
                                <a href="<?=_cfg('site')?>/run/logout"><?=t('logout')?></a>
                            </li>
                        </ul>
                    </li>
                    <?
                    }
                }
            }
            ?>
        </ul>
        
    <? } else { ?>
        <div class="login">
            <span><?=t('login_register')?></span>
            <span class="mobile"><?=t('login')?></span>
            <div class="usericon"></div>
        </div>
    <? } ?>
    
    <nav>
        <div class="navbar-inner">
            <ul class="nav">
                <?
                if ($this->data->links) {
                    foreach($this->data->links as $v) {
                        if ((($v->logged_in == 1 && $this->logged_in) || $v->logged_in == 0) && $v->main_link == 0 && $v->block != 1) {
                        ?>
                        <li class="nav-link <?=$v->value?>" id="<?=$v->link?>">
                            <a href="<?=_cfg('href')?>/<?=$v->link?>"><?=t($v->value)?></a>
                            <?
                            if ($v->sublinks) {
                                ?><ul class="nav-sub"><?
                                foreach($v->sublinks as $v2) {
                                    if ($v2->logged_in == 1 && $this->logged_in || $v2->logged_in == 0) {
                                    ?>
                                    <li class="nav-sublink" id="<?=$v2->link?>">
                                        <a href="<?=_cfg('href')?>/<?=$v2->link?>"><?=t($v2->value)?></a>
                                    </li>
                                    <?
                                    }
                                }
                                ?></ul><?
                            }
                            ?>
                        </li>
                        <?
                        }
                    }
                }
                ?>
            </ul>
            
            <div class="clear"></div>
        </div>
    </nav>
    
    <!--<div class="header-middle"><a href="http://www.1a.lv" target="_blank"><img src="<?=_cfg('img')?>/partners/1a-top.jpg" /></a></div>-->
    
    
    <div class="clear"></div>
</header>