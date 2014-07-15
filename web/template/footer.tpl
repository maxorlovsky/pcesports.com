</section>

<footer class="container">
    <div class="separator">
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class=""><?=t('available_leagues')?></h1>
            </div>
            <div class="block-content games">
                <a href="http://www.leagueoflegends.com" target="_blank"><img src="<?=_cfg('img')?>/footer-lol-logo.png" /></a>
                <a href="http://eu.battle.net/hearthstone/" target="_blank"><img src="<?=_cfg('img')?>/footer-hs-logo.png" /></a>
            </div>
        </div>
    </div>
    
    <div class="separator">
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class="bordered"><?=t('partners')?></h1>
            </div>
            <div class="block-content partners">
                <a class="unicon" href="https://www.unicon.lv" target="_blank"><img src="<?=_cfg('img')?>/partners/unicon.png" /></a>
                <a class="skillz" href="http://www.skillz.lv" target="_blank"><img src="<?=_cfg('img')?>/partners/skillz.png" /></a>
                <div class="clear"></div>
                <a class="aimskillz" href="http://aimskillz.lv/" target="_blank"><img src="<?=_cfg('img')?>/partners/aimskillz.png" /></a>
                <a class="lesf" href="http://lesf.lv/" target="_blank"><img src="<?=_cfg('img')?>/partners/lesf.png" /></a>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    
    <div class="clear"></div>
    
    <div class="copyrights">
        <p class="rights">© <?=date('Y', time())?> Pentaclick eSports. All Rights Reserved.</p>
        <a href="http://www.maxorlovsky.net" target="_blank" class="devs"><?=t('made_by')?> Max & Anya Orlovsky</a>
        <div class="clear"></div>
    </div>
</footer>

<div class="hidden popup" id="login-window">
    <div class="login-inside">
        <h1>Pentaclick Login</h1>
        <h2>Sign in with</h2>
        <a href="javascript:void(0);" class="socialLogin" id="tw"><img src="<?=_cfg('img')?>/tw-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="fb"><img src="<?=_cfg('img')?>/fb-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="gp"><img src="<?=_cfg('img')?>/gp-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="vk"><img src="<?=_cfg('img')?>/vk-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="tc"><img src="<?=_cfg('img')?>/tc-login.png" /></a>
        <div class="information">Pentaclick will never use your social data to "stalk" you or sell it to 3rd parties. Social login/registration required to save you, from your account being stolen and to approve that you're not a robot.</div>
    </div>
</div>

<div id="hint-helper"><p></p></div>
<div id="toTop"></div>
<div id="fader"></div>

<script src="<?=_cfg('static')?>/js/main.js"></script>
<? if (_cfg('language') == 'ru') { ?>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?113"></script>
<? } ?>

<script type="text/javascript">
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");

<? if (_cfg('language') == 'ru') { ?>
VK.Widgets.Group("vk_groups", {mode: 0, width: "310", height: "260", color1: 'ffffff', color2: '888888', color3: '5B7FA6'}, 64250147);
<? } ?>
</script>

</section>

</body>
</html>    