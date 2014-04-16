<section class="login">
	<header>
		<a href="http://www.themages.net" target="_blank"><img src="<?=_cfg('cmsimg')?>/logo.png" /></a>
	</header>
    
    <div class="body">
        <? if (isset($this->data->login_error) && $this->data->login_error == 1) { ?>
            <div class="errmsg">
                <div class="msgs_text"><?=$this->messages['login_error']?></div>
    		</div>
    	<? } ?>
    
        <form method="post">
            <input type="text" name="login" id="login" placeholder="Login" />
            <input type="password" name="password" id="password" placeholder="Password" />
            
            <input type="submit" value="Enter" name="submit_login" onclick="$(this).attr('readonly', 'readonly');" class="enter" />
		</form>
    </div>
    
    <footer>
        <div class="fleft"><a href="http://www.themages.net/" target="_blank">CMS Version: <?=$this->data->cmsSettings['version']?></a></div>
    	<div class="fright"><a href="http://www.maxorlovsky.net" target="_blank">Max Orlovsky <span>&copy;</span> <?=date('Y')?></a></div>
    	<div class="clear"></div>
    </footer>
    
</section>