<section id="navbar">
    <article class="logo"><a href="<?=get_site_url()?>#home" title="Home" <?=is_home()?'class="scroll"':null?>><img src="<?php bloginfo('template_directory'); ?>/images//logo.png" alt="Home" /></a></article>
    <nav class="globalnav">
        <ul>
            <li id="home-url"><a href="<?=get_site_url()?>#home" <?=is_home()?'class="scroll"':null?> title="<?=_e('home', 'pentaclick')?>"><?=_e('home', 'pentaclick')?><br /><small><?=_e('home-sub', 'pentaclick')?></small></a></li>
            <li id="participants-url"><a href="<?=get_site_url()?>#participants" <?=is_home()?'class="scroll"':null?> title="<?=_e('participants', 'pentaclick')?>"><?=_e('participants', 'pentaclick')?><br /><small><?=_e('participants-sub', 'pentaclick')?></small></a></li>
            <li id="register-url"><a href="<?=get_site_url()?>#register" <?=is_home()?'class="scroll"':null?> title="<?=_e('register', 'pentaclick')?>"><?=_e('register', 'pentaclick')?><br /><small><?=_e('register-sub', 'pentaclick')?></small></a></li>
            <li id="format-url"><a href="<?=get_site_url()?>#format" <?=is_home()?'class="scroll"':null?> title="<?=_e('format', 'pentaclick')?>"><?=_e('format', 'pentaclick')?><br /><small><?=_e('format-sub', 'pentaclick')?></small></a></li>
        </ul>
    </nav>
</section>