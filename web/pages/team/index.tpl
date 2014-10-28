<section class="container page team">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('about_us')?></h1>
        </div>

        <div class="block-content about-us">
            <div class="text">
				<?=t('about_us_text')?>
			</div>
        </div>
    </div>
	
	<?/*<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Hearthstone <?=t('division')?></h1>
        </div>

        <div class="block-content team-list">
			<div class="game-logo"><img src="<?=_cfg('img')?>/footer-hs-logo.png" /></div>
            <div class="team-user">
            	<div class="name">Soldecroix</div>
            	<div class="role">Team captain/Player</div>
                <div class="email">&nbsp;</div>
            </div>
            <div class="clear"></div>
        </div>
    </div>*/?>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('organization')?></h1>
        </div>

        <div class="block-content team-list">
            <? foreach($this->team as $k => $v) { ?>
                <div class="team-user">
                    <? if ($v['avatar']) { ?>
                        <div class="icon"><img src="<?=_cfg('avatars')?>/<?=$v['avatar']?>.jpg" /></div>
                    <? } ?>
                    <div class="name"><?=$v['name']?></div>
                    <div class="role"><?=$v['role']?></div>
                    <? if ($v['email']) {?>
                        <div class="email"><?=$v['email']?></div>
                    <? } ?>
                </div>
            <? } ?>
            <div class="clear"></div>
        </div>
    </div>
</div>