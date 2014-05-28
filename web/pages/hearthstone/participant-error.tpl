<section class="container page lol">

<div class="left-containers">
	<p class="error-add"><?=t('error')?>Error</p>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?>Information</h1>
        </div>
        
        <div class="block-content">
            <?=str_replace('%email%', 'info@pcesports.com', t('participant_error_txt'))?>
            <p>Sorry, all tournaments are now closed or link to profilers is incorrect.</p>
			<p>If you think that your link is correct, then contact us at <a href="mailto:%email%">%email%</a></p>
        </div>
    </div>
</div>