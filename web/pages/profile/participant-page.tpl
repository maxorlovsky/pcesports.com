<section class="container page lol">

<div class="left-containers">
	<? if ($regged == 1) { ?>
		<p class="success-add"><?=t('participation_verified')?></p>
	<? } ?>
	
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content vods">
            <p><?=t('tournament_start_in')?>: <span class="timer" attr-time="<?=intval(1402729200-time() + 10800)?>" attr-br="0"><img src="<?=_cfg('img')?>/bx_loader.gif" /></span></p>
            <p><?=t('brackets')?>: <a href="http://pentaclick.challonge.com/lol<?=$this->server?><?=$this->data->settings['lol-current-number-'.$this->server]?>/" target="_blank">http://pentaclick.challonge.com/lol<?=$this->server?><?=$this->data->settings['lol-current-number-'.$this->server]?>/</a></p>
            <?=t('participant_information_txt')?>
        </div>
    </div>
</div>