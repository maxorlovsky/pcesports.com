<section id="navbar">
    <article class="logo"><a href="<?=qtrans_convertURL(get_site_url(), qtrans_getLanguage())?>#home" title="Home" <?=is_home()?'class="scroll"':null?>><img src="<?php bloginfo('template_directory'); ?>/images/logo.png" alt="Home" /></a></article>
    <nav class="globalnav">
        <ul>
            <li id="home-url"><a href="<?=qtrans_convertURL(get_site_url(), qtrans_getLanguage())?>#home" <?=is_home()?'class="scroll"':null?> title="<?=_e('home', 'pentaclick')?>"><?=_e('home', 'pentaclick')?><br /><small><?=_e('home-sub2', 'pentaclick')?></small></a></li>
            <li id="about-url"><a href="<?=qtrans_convertURL(get_site_url(), qtrans_getLanguage())?>#about" <?=is_home()?'class="scroll"':null?> title="<?=_e('about', 'pentaclick')?>"><?=_e('about', 'pentaclick')?><br /><small><?=_e('about-sub', 'pentaclick')?></small></a></li>
            <li id="connect-url"><a href="<?=qtrans_convertURL(get_site_url(), qtrans_getLanguage())?>#connect" <?=is_home()?'class="scroll"':null?> title="<?=_e('connect', 'pentaclick')?>"><?=_e('connect', 'pentaclick')?><br /><small><?=_e('connect-sub', 'pentaclick')?></small></a></li>
            <li class="external"><a href="<?=LOLURL.'/'.qtrans_getLanguage()?>" title="<?=_e('lol', 'pentaclick')?>"><?=_e('lol', 'pentaclick')?><br /><small><?=_e('next-tourn-nr', 'pentaclick')?> #<?=cOptions('tournament-lol-number')?></small></a></li>
            <li class="external"><a href="<?=HSURL.'/'.qtrans_getLanguage()?>" title="<?=_e('hearthstone', 'pentaclick')?>"><?=_e('hearthstone', 'pentaclick')?><br /><small><?=_e('next-tourn-nr', 'pentaclick')?> #<?=cOptions('tournament-hs-number')?></small></a></li>
        </ul>
    </nav>
</section>