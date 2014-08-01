<!DOCTYPE html>
<html lang="<?=_cfg('language')?>">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="<?=$this->data->settings['site_description']?>" />
    <meta name="keywords" content="<?=$this->data->settings['site_keywords']?>" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?=$this->data->settings['site_name']?> - CMS</title>
    
    <script src="<?=_cfg('cmsstatic')?>/js/scripts.js"></script>
    <? if ($this->logged_in) { ?>
    <script src="<?=_cfg('cmsstatic')?>/js/pre-js.js"></script>
    <? } ?>
    
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="<?=_cfg('cmsstatic')?>/css/jqueryUI.css" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('cmsstatic')?>/css/chosen.css" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('cmsstatic')?>/css/fonts.css" />
    <link rel="stylesheet" type="text/css" href="<?=_cfg('cmsstatic')?>/css/style.css" />
    
    <script>
    	var site = "<?=_cfg('site')?>";
    	var img = "<?=_cfg('cmsimg')?>";
        var strings = <?=at('cms_json_strings')?>;
        var lang = 'en';
        var logged_in = <?=$this->logged_in?>;
    </script>
</head>
<body>