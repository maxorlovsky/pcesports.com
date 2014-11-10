<section class="container page contacts">

<div class="left-containers">
    <div class="block summoners">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('added_summoners')?></h1>
        </div>
        <?
		if ($this->additional->summoners) {
        	foreach($this->additional->summoners as $v) {
        ?>
            <div class="block-content summoner <?=($v->approved==1?'approved':'notApproved')?>" target="_blank" attr-id="<?=$v->id?>">
                <img class="game-logo" src="http://avatar.leagueoflegends.com/<?=$v->region?>/<?=$v->name?>.png" />
                <label class="summoner-name"><?=$v->name?></label>
                <a href="javascript:void(0);" id="removeSummoner" class="right"><?=t('remove')?></a>
                <a href="javascript:void(0);" class="status <?=($v->approved==1?null:'hint')?>" attr-msg="<?=t('create_masteries_page')?>: <b><?=$v->masteries?></b>"><?=($v->approved==1?'Approved':'Approval required')?></a>
                <div class="clear"></div>
            </div>
        <?
        	}
        }
        else {
        ?>
            <div class="block-content">
                <?=t('none')?>
            </div>
        <?
        }
        ?>
    </div>
    
    <div class="block add-streamer">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('add_summoner')?></h1>
        </div>
        
        <div class="block-content">
            <form class="summoner-form contact-form">
                <div id="error"><p></p></div>
        
                <div class="fields">
                    <label for="name"><?=t('name')?></label>
                    <input name="name" id="name" type="text" placeholder="" />
                </div>
                <div class="fields">
                    <label for="region"><?=t('region')?></label>
                    <select name="region" id="region">
                        <? foreach(_cfg('lolRegions') as $k => $v) { ?>
                        <option value="<?=$k?>"><?=$v?></option>
                        <? } ?>
                    </select>
                </div>
                <a href="javascript:void(0);" class="button" id="addSummoner"><?=t('add_summoner')?></a>
            </form>
            
            <div class="success-sent"><p></p></div>
        </div>
    </div>
</div>