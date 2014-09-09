<section id="main" class="wrapper">
	<header>
		<a class="logo" href="<?=_cfg('site')?>/admin/#<?=$this->defaultPage?>"><img src="<?=_cfg('cmsimg')?>/logo.png" /></a>
        <p>v <?=$this->data->cmsSettings['version']?></p>
        <div class="copyrights">
            <a href="http://www.maxorlovsky.net" target="_blank"><span>&copy;</span> TheMages CMS 2011-<?=date('Y')?><br />
            by Max &amp; Anya Orlovsky</a>
        </div>
	</header>

	<nav class="menu">
		<?
		foreach($this->data->pages as $v) {
			if ($this->user->level >= $v[2] ||
               ($this->user->level == 0 && ($this->user->custom_access->setting->$v[0] == 1 || $this->user->custom_access->module->$v[0] == 1))) {
				?>
					<a class="<?=($this->page==$v[0]?'active':null)?>" id="link_<?=$v[0]?>" href="<?=_cfg('site').'/admin/#'.$v[0]?>"><?=$v[1]?></a>
				<?
			}
		}
		?>
   
        <? if ($this->data->modules) { ?>
  		    <div id="menusub" class="cursor <?=($this->subPageOpen==1?'active':null)?>">
                <a id="site_name_val" href="javascript:void(0);"><?=at('modules')?></a> 
                <div id="arrows"></div>
                <div id="submenu">
                <?
                foreach($this->data->modules as $v) {
                    $var = $v->name;
                ?>
                    <? if ($this->user->level >= $v->level ||
                          ($this->user->level == 0 && ($this->user->custom_access->setting->$var == 1 || $this->user->custom_access->module->$var == 1))) {
                    ?>
                    <a id="cpage-<?=$v->name?>" class="pointer settings_div" href="<?=_cfg('site').'/admin/#'.$v->name?>"><span><?=$v->displayName?></span></a>
                    <? } ?>
                <? } ?>
                </div>
            </div>
        <?}?>
            
		<a class="fright" href="<?=_cfg('site').'/admin/#exit'?>"><?=at('exit')?></a>
		<div class="clear"></div>
	</nav>
	
	<div class="content">
	</div>