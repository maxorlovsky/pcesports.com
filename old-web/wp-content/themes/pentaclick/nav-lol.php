<section id="navbar" class="nav-lol">
    <article class="logo"><a href="<?=qtrans_convertURL(get_site_url(), qtrans_getLanguage())?>" title="Home"><img src="<?php bloginfo('template_directory'); ?>/images/logo.png" alt="Home" /></a></article>
    <nav class="globalnav">
        <ul>
            <li id="home-url"><a href="<?=qtrans_convertURL(LOLURL, qtrans_getLanguage())?>#home" <?=is_home()?'class="scroll"':null?> title="<?=_e('home', 'pentaclick')?>"><?=_e('home', 'pentaclick')?><br /><small><?=_e('home-sub', 'pentaclick')?></small></a></li>
            <li id="participants-url"><a href="<?=qtrans_convertURL(LOLURL, qtrans_getLanguage())?>#participants" <?=is_home()?'class="scroll"':null?> title="<?=_e('participants', 'pentaclick')?>"><?=_e('participants', 'pentaclick')?><br /><small><?=_e('participants-sub', 'pentaclick')?></small></a></li>
            <li id="format-url"><a href="<?=qtrans_convertURL(LOLURL, qtrans_getLanguage())?>#format" <?=is_home()?'class="scroll"':null?> title="<?=_e('format', 'pentaclick')?>"><?=_e('format', 'pentaclick')?><br /><small><?=_e('format-sub', 'pentaclick')?></small></a></li>
        </ul>
    </nav>
</section>