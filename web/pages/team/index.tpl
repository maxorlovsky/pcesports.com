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

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('join_us')?></h1>
        </div>

        <div class="block-content about-us">
            <div class="text">
                <?=t('join_us_text')?>
            </div>
        </div>
    </div>

	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('team')?></h1>
        </div>

        <div class="block-content team-list">
            <a class="team-division leagueoflegends" href="<?=_cfg('href')?>/team/leagueoflegends">
                <div class="name">EUNE <?=t('division')?></div>
            </a>
            <a class="team-division hearthstone" href="<?=_cfg('href')?>/team/hearthstone">
                <div class="name"><?=t('division')?></div>
            </a>
            <a class="team-division staff" href="<?=_cfg('href')?>/team/staff">
                <div class="name">Staff</div>
            </a>
            <div class="clear"></div>
        </div>
    </div>
</div>