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
                <div class="block-content summoner <?=($v->approved==1?'approved':'notApproved')?>" <?=($v->approved==1?'attr-id="'.$v->id.'"':'attr-masteries="'.$v->masteries.'"')?>>
                    <? if ($v->league && $v->approved == 1) { ?>
                        <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/<?=strtolower($v->league)?>_<?=$this->convertDivision($v->division)?>.png" />
                    <? } else { ?>
                        <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/unranked.png" />
                    <? } ?>
                    <label class="summoner-name"><?=$v->name?></label>
                    <a href="javascript:void(0);" class="removeSummoner right"><?=t('remove')?></a>
                    <? if ($v->approved == 1) { ?>
                        <a href="javascript:void(0);" class="status"><?=t('approved')?></a>
                    <? } else { ?>
                        <a href="javascript:void(0);" class="status hint" attr-msg="<?=t('click_to_see_instructions')?>"><?=t('approval_required')?></a>
                    <? } ?>
                    <span href="javascript:void(0);" class="region right"><?=$v->regionName?></span>
                    <div class="clear"></div>
                </div>
                <?
        	}
        }
        else {
        ?>
            <div class="block-content empty">
                <?=t('none')?>
            </div>
        <?
        }
        ?>
    </div>
    
    <div class="block how_to_approve hidden">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('how_to_approve_summoner')?></h1>
        </div>
        
        <div class="block-content summoner-verification">
            <p><?=str_replace('%code%', '<b id="masteries-code"></b>', t('create_masteries_page'))?></p>
            <div class="summoner-verification-image">
                <input type="text" class="verification-code" value="<?=$v->masteries?>" readonly="readonly" />
                <img src="<?=_cfg('img')?>/summoner-verification.jpg" />
            </div>
            <input type="hidden" id="summonerVerifyId" value="" />
            <div id="error"><p></p></div>
            <a href="javascript:void(0);" class="button" id="verifySummoner"><?=t('verify_summoner')?></a>
        </div>
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

<div class="dumpSummoner hidden">
    <div class="block-content summoner notApproved" attr-id="%id%" attr-masteries="%masteries%" style="display: none">
        <img class="game-logo" src="http://avatar.leagueoflegends.com/%region%/%name%.png" />
        <label class="summoner-name">%name%</label>
        <a href="javascript:void(0);" class="removeSummoner right"><?=t('remove')?></a>
        <a href="javascript:void(0);" class="status hint" attr-msg="<?=t('click_to_see_instructions')?>"><?=t('approval_required')?></a>
        <span href="javascript:void(0);" class="region right">%regionName%</span>
        <div class="clear"></div>
    </div>
</div>