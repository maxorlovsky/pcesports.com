<section class="container page team">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('staff')?></h1>
        </div>

        <div class="block-content team-list">
            <? foreach($this->team as $k => $v) { ?>
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
            <? } ?>
            <div class="clear"></div>
        </div>
    </div>
</div>