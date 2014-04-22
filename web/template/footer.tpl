</section>

<footer class="container">
    <div class="separator">
        <div class="block">
            <div class="block-header-wrapper">
                <h1 class="">Available leagues</h1>
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
                <h1 class="bordered">Partners</h1>
            </div>
            <div class="block-content partners">
                <a href="https://www.facebook.com/uniconlv" target="_blank"><img src="<?=_cfg('img')?>/partners/unicon.png" /></a>
                <a class="aimskillz" href="http://aimskillz.lv/" target="_blank"><img src="<?=_cfg('img')?>/partners/aimskillz.png" /></a>
            </div>
        </div>
    </div>
    
    <div class="clear"></div>
    
    <div class="copyrights">
        <p class="rights">© <?=date('Y', time())?> Pentaclick eSports. All Rights Reserved.</p>
        <a href="http://www.maxorlovsky.net" target="_blank" class="devs">Made by Max & Anya Orlovsky</a>
        <div class="clear"></div>
    </div>
</footer>

<div id="hint-helper"><p></p></div>

<script src="<?=_cfg('static')?>/js/main.js"></script>

<script type="text/javascript">
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
</script>
</body>
</html>    