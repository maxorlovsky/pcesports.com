<section class="container page contacts team-page team" ng-app="pcesports" ng-controller="Team" attr-id="<?=$this->team->id?>">

<div class="left-containers">
	<? if ($_GET['val4'] == 'success') { ?>
        <div class="success-add registration-complete"><?=t('team_registration_success')?></div>
    <? } ?>

    <a class="button submit navigation" href="<?=_cfg('href')?>/team/<?=strtolower(urlencode($this->team->name))?>"><?=t('back_to_team_page')?></a>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('edit_team')?> - <?=$this->team->name?> [<?=$this->team->tag?>]</h1>
        </div>
        <form class="block-content contact-form" name="form" ng-submit="editTeam()" ng-cloak>
            <div id="ngSuccess" class="pre" ng-show="success"><p>{{success}}</p></div>
            <div id="ngError" class="pre" ng-show="error"><p>{{error}}</p></div>

            <div class="fields">
                <label for="logo"><?=t('team_logo')?></label>
                <input name="logo" id="logo" type="file" />
            </div>

            <div class="fields">
                <label for="description"><?=t('team_description')?></label>
                <input name="description" id="description" ng-model="description" type="text" value="<?=$this->team->description?>" placeholder="<?=t('team_description')?>" ng-maxlength="500" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" initial-value />
            </div>
            <div id="ngError" ng-show="form.description.$error.maxlength"><p><?=t('team_description_is_too_big')?></p></div>

            <div class="fields">
                <label for="website"><?=t('team_website')?></label>
                <input name="website" id="website" ng-model="website" type="url" value="<?=$this->team->website?>" placeholder="Example: http://www.pcesports.com" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" initial-value />
            </div>
            <div id="ngError" ng-show="!form.website.$pristine && form.website.$error.url"><p><?=t('website_pattern_incorrect')?></p></div>

            <div class="fields">
                <label for="facebook"><?=t('team_facebook')?></label>
                <input name="facebook" id="facebook" ng-model="facebook" type="url" value="<?=$this->team->facebook?>" placeholder="Example: http://www.facebook.com/pentaclickesports" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" initial-value/>
            </div>
            <div id="ngError" ng-show="!form.website.$pristine && form.facebook.$error.url"><p><?=t('website_pattern_incorrect')?></p></div>

            <div class="fields">
                <label for="twitter"><?=t('team_twitter')?></label>
                <input name="twitter" id="twitter" ng-model="twitter" type="url" value="<?=$this->team->twitter?>" placeholder="Example: http://www.twitter.com/pentaclick" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" initial-value />
            </div>
            <div id="ngError" ng-show="!form.website.$pristine && form.twitter.$error.url"><p><?=t('website_pattern_incorrect')?></p></div>

            <div class="fields">
                <label for="twitch_tv"><?=t('team_twitch_tv')?></label>
                <input name="twitch_tv" id="twitch_tv" ng-model="twitch_tv" type="url" value="<?=$this->team->twitch_tv?>" placeholder="Example: http://www.twitch.tv/pentaclick_tv" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" initial-value />
            </div>
            <div id="ngError" ng-show="!form.website.$pristine && form.twitch_tv.$error.url"><p><?=t('website_pattern_incorrect')?></p></div>

            <input name="team_id" id="team_id" ng-model="team_id" type="hidden" value="<?=$this->team->id?>" initial-value />

            <button class="button {{button}}" id="editTeam"><?=t('edit_team')?></button>
        </form>
    </div>
    
    <div class="block streamers edit-streamers member-list">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('team_members')?></h1>
        </div>
        <?
		if ($this->team->members) {
        	foreach($this->team->members as $v) {
        ?>
            <div class="block-content streamer" target="_blank" attr-id="<?=$v->id?>">
                <img class="game-logo" src="<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg" />
                <a href="<?=_cfg('href')?>/member/<?=urlencode($v->name)?>" class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?> <?=($v->title?'('.$v->title.')':null)?></a>
                
                <? if ($this->team->user_id_captain != $v->id) { ?>
                <span class="viewers editStreamerAction">
                	<a href="javascript:void(0);" id="changeTeamCaptain" attr-msg="<?=t('sure_to_change_captain')?>"><?=t('make_team_captain')?></a> |
                    <a href="javascript:void(0);" id="removeTeamMember" attr-msg="<?=t('sure_to_kick_member')?>"><?=t('kick_from_team')?></a>
                </span>
                <? } ?>
            </div>
        <?
        	}
        }
        ?>
    </div>

    <div class="block streamers edit-streamers request-list">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('requests_to_join_team')?></h1>
        </div>
        <?
        if ($requestsRow) {
            foreach($requestsRow as $v) {
        ?>
            <div class="block-content streamer" target="_blank" attr-id="<?=$v->id?>">
                <img class="game-logo" src="<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg" />
                <a href="<?=_cfg('href')?>/member/<?=urlencode($v->name)?>" class="streamer-name"><?=$v->name?></a><label class="date"> - <?=$this->convertTime($v->added_date)?></label>
                
                <span class="viewers editStreamerAction">
                    <a href="javascript:void(0);" id="acceptToTeam"><?=t('accept')?></a> |
                    <a href="javascript:void(0);" id="rejectFromTeam"><?=t('reject')?></a>
                </span>
            </div>
        <?
            }
        }
        ?>
    </div>
</div>