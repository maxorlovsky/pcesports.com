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
            <?
            if ($this->team) {
                foreach($this->team as $k => $v) {
                    if ($v['avatar']) {
            ?>
                        <div class="team-user">
                            <? if ($v['avatar']) { ?>
                                <div class="icon"><img src="<?=_cfg('avatars')?>/<?=$v['avatar']?>.jpg" /></div>
                            <? } ?>
                            <a href="<?=_cfg('href')?>/member/<?=$v['name']?>" class="name"><?=$v['name']?></a>
                            <div class="role"><?=$v['role']?></div>
                            <? if ($v['socials']) {?>
                                <div class="social">
                                    <? foreach ($v['socials'] as $ks => $vs) { ?>
                                        <a class="<?=$ks?>" href="<?=$vs?>" target="_blank"></a>
                                    <? } ?>
                                </div>
                            <? } ?>
                        </div>
            <?
                    }
                }
            }
            ?>
            <div class="clear"></div>
        </div>
    </div>
</div>