<section class="container page lol">

<div class="left-containers">
	<? if ($regged == 1) { ?>
		<p class="success-add">Your participation is now verified. Congratulations!</p>
	<? } ?>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Information</h1>
        </div>
        
        <div class="block-content vods">
            <p>Tournament start in: <span class="timer" attr-time="<?=intval(1400914800-time())?>" attr-br="0"><img src="<?=_cfg('img')?>/bx_loader.gif" /></span></p>
            <p>Brackets: <a href="http://pentaclick.challonge.com/hs4/" target="_blank">http://pentaclick.challonge.com/hs4/</a></p>
            <p>Brackerts will be reshufled after registration for tournament will be closed</p>
            <p>You will be notified about your participation in the tournament by email, 24h before start</p>
            <p>Current page is work in progress</p>
        </div>
    </div>
</div>