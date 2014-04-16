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
			if ($this->user->level >= $v[2]) {
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
                <? foreach($this->data->modules as $v) { ?>
                    <a id="cpage-<?=$v->name?>" class="pointer settings_div" onclick="do_cpinput('cpage-<?=$v->name?>');"><span><?=ucfirst($v->name)?></span></a>
                <? } ?>
                </div>
            </div>
        <?}?>
            
		<a class="fright" href="<?=_cfg('site').'/admin/#exit'?>"><?=at('exit')?></a>
		<div class="clear"></div>
	</nav>
	<? if ($this->data->subpages) { ?>
	<ul class="sublinks_menu" id="submenu" style="display: none;">
	<?
		foreach($this->data->subpages as $v) {
			if ($this->data->user->level >= $v[3]) {
				?><li><a class="<?=($page == $v[0] ? 'active' : null)?>" id="link_<?=$v[0]?>" href="<?=_A.'#'.$v[0]?>"><?=$v[1]?></a></li><?
			}
		}
	?>
	</ul>
	<?}?>
	
	<div class="content">
	</div>