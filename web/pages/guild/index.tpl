<section class="container page team">

<div class="left-containers">
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('about_guild')?></h1>
        </div>

        <div class="block-content ">
            <?=t('about_guild_text')?>
            <br />
            <br />
            To join you need to:<br />
            1) Register on website, add email<br />
            2) Add summoner account and verify it<br />
            3) Fill out small form that will appear below<br />
            4) Wait for answer from GM/officers on your email<br />
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('join_guild')?></h1>
        </div>

        <div class="block-content ">
            <? if (!$this->logged_in) { ?>
                <div class="info-add"><?=t('not_logged_in')?></div>
            <? } else { ?>
            <? } ?>
        </div>
    </div>
</div>