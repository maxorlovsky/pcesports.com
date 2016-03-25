</section>

<footer class="container">
    <!--<div class="separator left boards">
        <? include_once _cfg('pages').'/boards/snippet.tpl'; ?>
    </div>-->
    
    <div class="copyrights">
        <p class="rights">© <?=date('Y', time())?> Pentaclick eSports / <a href="http://www.maxorlovsky.net" target="_blank">Max Orlovsky</a>. All rights reserved.</p>
        <div class="devs">
            <a href="<?=_cfg('href')?>/pentaclick">About Pentaclick eSports</a> | 
            <a href="<?=_cfg('href')?>/contacts">Contact us</a> | 
            <a href="<?=_cfg('href')?>/partners">Partners</a>
        </div>
        <div class="clear"></div>
    </div>
</footer>

<? if ($this->logged_in != 1 && $this->page != 'restoration ') { ?>
<div class="hidden popup" id="login-window">
    <div class="close"></div>

    <div class="login-inside" ng-app="pcesports" ng-controller="Login" ng-cloak>
        
        <form class="form" name="loginForm" ng-submit="login();">
            <h1>Pentaclick <?=t('login')?></h1>
            <div id="ngError" ng-show="errorLogin"><p>{{errorLogin}}</p></div>
            
            <div class="fields">
                <label for="email"><?=t('email')?></label>
                <input name="email" id="email" ng-model="emailLogin" type="email" value="" placeholder="<?=t('email')?>*" ng-pattern="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" required />
            </div>
            <div id="ngError" ng-show="loginForm.email.$error.required && loginForm.email.$touched"><p><?=t('email_is_empty')?></p></div>
            <div id="ngError" ng-show="loginForm.email.$error.pattern"><p><?=t('email_invalid')?></p></div>

            <div class="fields">
                <label for="password"><?=t('password')?></label>
                <input name="password" id="password" ng-model="passwordLogin" type="password" value="" placeholder="<?=t('password')?>*" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" required />
            </div>
            <div id="ngError" ng-show="loginForm.password.$error.required && loginForm.password.$touched"><p><?=t('password_empty')?></p></div>

            <button class="button {{buttonLogin}}"><?=t('login')?></button>

            <div class="pass-reg-links">
                <a href="" class="left-part-link" ng-click="showRestore();"><?=t('forgot_password')?>?</a>
                <a href="" class="register-link" ng-click="showRegistration();"><?=t('new_user')?></a>
            </div>
        </form>

        <form class="form" name="registrationForm" ng-submit="register();">
            <h1>Pentaclick <?=t('registration')?></h1>
            <div id="ngError" ng-show="errorRegistration"><p>{{errorRegistration}}</p></div>
            <div class="success-add" ng-show="successRegistration"><p>{{successRegistration}}</p></div>
            
            <div class="fields">
                <label for="email"><?=t('email')?></label>
                <input name="email" id="email" ng-model="emailRegistration" type="email" value="" placeholder="<?=t('email')?>*" ng-pattern="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" required />
            </div>
            <div id="ngError" ng-show="registrationForm.email.$error.pattern"><p><?=t('email_invalid')?></p></div>

            <div class="fields">
                <label for="password"><?=t('password')?></label>
                <input name="password" id="password" ng-model="passwordRegistration" type="password" value="" placeholder="<?=t('password')?>*" ng-minlength="6" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" required />
            </div>
            <div id="ngError" ng-show="registrationForm.password.$error.required && registrationForm.password.$touched"><p><?=t('password_empty')?></p></div>
            <div id="ngError" ng-show="registrationForm.password.$error.minlength"><p><?=t('password_too_small')?></p></div>

            <div class="fields">
                <div class="g-recaptcha" data-sitekey="<?=_cfg('recaptchaSiteKey')?>"></div>
            </div>

            <button class="button {{buttonRegistration}}"><?=t('register')?></button>

            <div class="pass-reg-links">
                <a href="" class="left-part-link" ng-click="backStep();"><?=t('back_to_login')?></a>
            </div>
        </form>

        <form class="form" name="restoreForm" ng-submit="restore();">
            <h1>Pentaclick <?=t('password_restoration')?></h1>
            <div id="ngError" ng-show="errorRestore"><p>{{errorRestore}}</p></div>
            <div class="success-add" ng-show="successRestore"><p>{{successRestore}}</p></div>
            
            <div class="fields">
                <label for="email"><?=t('email')?></label>
                <input name="email" id="email" ng-model="emailRestore" type="email" value="" placeholder="<?=t('email')?>*" ng-pattern="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" required />
            </div>
            <div id="ngError" ng-show="restoreForm.email.$error.pattern"><p><?=t('email_invalid')?></p></div>

            <button class="button {{buttonRestore}}"><?=t('restore_password')?></button>

            <div class="pass-reg-links">
                <a href="" class="left-part-link" ng-click="backStep();"><?=t('back_to_login')?></a>
            </div>
        </form>
        
        <h2><?=t('sign_in_with')?></h2>
        <a href="javascript:void(0);" class="socialLogin" id="tw"><img src="<?=_cfg('img')?>/tw-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="fb"><img src="<?=_cfg('img')?>/fb-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="gp"><img src="<?=_cfg('img')?>/gp-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="vk"><img src="<?=_cfg('img')?>/vk-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="tc"><img src="<?=_cfg('img')?>/tc-login.png" /></a>
        <a href="javascript:void(0);" class="socialLogin" id="bn"><img src="<?=_cfg('img')?>/bn-login.png" /></a>
        <div class="information"><?=t('penta_sign_info')?></div>
    </div>
    <script src='https://www.google.com/recaptcha/api.js'></script>
</div>
<? } ?>

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
<div class="hidden popup" id="message-window"><div class="close"></div><p></p></div>
<div id="hint-helper"><p></p></div>
<div id="toTop"></div>
<div id="fader"></div>

</section>

<? if (_cfg('env') == 'dev') { ?>
<script src="<?=_cfg('static')?>/js/pc.js"></script>
<script src="<?=_cfg('static')?>/js/main.js"></script>
<? } else { ?>
<script src="<?=_cfg('static')?>/js/combined.js?v=1.14"></script>
<? } ?>

<? if (_cfg('env') == 'prod') { ?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-47216717-1', 'pcesports.com');
ga('require', 'displayfeatures');
ga('send', 'pageview');
</script>
<? } ?>

</body>
</html>    