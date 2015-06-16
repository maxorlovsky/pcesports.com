<!DOCTYPE html>
<html lang="<?=_cfg('language')?>">
<head>
    <meta charset="UTF-8" />
    
    <title>Widget | <?=$this->data->settings['site_name']?></title>
    
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    
    <link rel="stylesheet" type="text/css" href="<?=_cfg('static')?>/css/widget.css" />
</head>
<body>

<script src="<?=_cfg('static')?>/js/scripts.js"></script>

<script>
var g = {
    site: '<?=_cfg('site')?>'
};
</script>


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