<section class="container page team">

<div class="left-containers">
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">League of Legends <?=t('division')?></h1>
        </div>

        <div class="block-content team-list">
            <? foreach($this->team as $k => $v) { ?>
                <div class="team-user hearthstone">
                    <? if ($v['summonerName']) { ?>
                        <a href="http://eune.op.gg/summoner/?userName=<?=$v['summonerName']?>" class="summoner-icon hint" attr-msg="Summoner: <?=$v['summonerName']?>">
                            <img class="game-logo" src="<?=_cfg('img')?>/leagues_big/<?=strtolower($v['league'])?>_<?=$this->convertDivision($v['division'])?>.png" />
                        </a>
                    <? } ?>
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