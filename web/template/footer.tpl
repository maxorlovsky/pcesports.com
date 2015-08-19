</section>

<footer class="container">
    <div class="separator left">
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class=""><?=t('available_disciplines')?></h1>
            </div>
            <div class="block-content games">
                <a href="http://www.leagueoflegends.com" target="_blank"><img src="<?=_cfg('img')?>/footer-lol-logo.png" /></a>
                <a href="http://eu.battle.net/hearthstone/" target="_blank"><img src="<?=_cfg('img')?>/footer-hs-logo.png" /></a>
            </div>
        </div>
    </div>
    
    <div class="separator left">
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class=""><?=t('follow_us')?></h1>
            </div>
            <div class="block-content social">
                <a class="fb" href="https://www.facebook.com/pentaclickesports" target="_blank"></a>
                <a class="tw" href="https://twitter.com/pentaclick" target="_blank"></a>
                <a class="yt" href="https://www.youtube.com/user/pentaclickesports" target="_blank"></a>
                <a class="tv" href="http://www.twitch.tv/pentaclick_tv" target="_blank"></a>
                <a class="sm" href="http://steamcommunity.com/groups/pentaclickesports" target="_blank"></a>
                <script>
                $('.social a').css('transition', '.5s');
                </script>
            </div>
            <div class="block-content subscribe">
            </div>
        </div>
    </div>
    
    
    
    <div class="separator right">
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class="bordered"><?=t('partners')?></h1>
            </div>
            <div class="block-content partners">
                <a class="unicon" href="http://www.unicon.lv" target="_blank"><img src="<?=_cfg('img')?>/partners/unicon.png" /></a>
                <a class="lesf" href="http://lesf.lv/" target="_blank"><img src="<?=_cfg('img')?>/partners/lesf.png" /></a>
                <a class="skillz" href="http://www.skillz.lv" target="_blank"><img src="<?=_cfg('img')?>/partners/skillz.png" /></a>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    
    <div class="clear"></div>
    
    <div class="copyrights">
        <p class="rights">© <?=date('Y', time())?> Pentaclick eSports.</p>
        <a href="http://www.maxorlovsky.net" target="_blank" class="devs"><?=t('made_by')?> Max & Anya Orlovsky</a>
        <div class="clear"></div>
    </div>
</footer>

<div class="hidden popup" id="login-window">
    <div class="login-inside">
        <h1>Pentaclick <?=t('login')?></h1>
        <h2><?=t('sign_in_with')?></h2>
        <a href="javascript:void(0);" class="socialLogin" id="tw"><img src="<?=_cfg('img')?>/tw-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="fb"><img src="<?=_cfg('img')?>/fb-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="gp"><img src="<?=_cfg('img')?>/gp-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="vk"><img src="<?=_cfg('img')?>/vk-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="tc"><img src="<?=_cfg('img')?>/tc-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="bn"><img src="<?=_cfg('img')?>/bn-login.png" /></a>
        <div class="information"><?=t('penta_sign_info')?></div>
    </div>
</div>

<? if ($this->logged_in == 1) { ?>
<a class="achievements" href="">
    <div class="image">No image</div>
    <div class="points"></div>
    <div class="title">Achievement unlocked</div>
    <div class="name"></div>
    <div class="text"></div>
    <audio id="achievement-ping" src="<?=_cfg('static')?>/achievement.ogg"></audio>
</a>
<? } ?>
<div id="hint-helper"><p></p></div>
<div id="toTop"></div>
<div id="fader"></div>

<? if (_cfg('env') == 'dev') { ?>
<script src="<?=_cfg('static')?>/js/pc.js"></script>
<script src="<?=_cfg('static')?>/js/main.js"></script>
<? } else { ?>
<script src="<?=_cfg('static')?>/js/combined.js?v=1.3"></script>
<? } ?>

</section>

</body>
</html>    