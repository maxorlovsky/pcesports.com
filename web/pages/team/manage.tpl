<section class="container page contacts team-page" ng-app="pcesports" ng-controller="Team">

<div class="left-containers">
	<? if ($_GET['val4'] == 'success') { ?>
        <div class="success-add registration-complete"><?=t('team_registration_success')?></div>
    <? } ?>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('edit_team')?> - <?=$this->team->name?> [<?=$this->team->tag?>]</h1>
        </div>
        <form class="block-content contact-form profile" ng-cloak>

            <div id="ngError" class="pre" ng-show="error"><p>{{error}}</p></div>

            <div class="fields">
                <label for="logo"><?=t('team_logo')?></label>
                <input name="logo" id="logo" type="file" />
            </div>

            <div class="fields">
                <label for="description"><?=t('team_description')?></label>
                <input name="description" id="description" ng-model="description" type="text" value="" placeholder="<?=t('team_description')?>" ng-maxlength="500" ng-model-options="{ updateOn: 'keyup blur', debounce: { keyup: 500, blur: 0 } }" value="<?=$this->team->description?>" initial-value />
            </div>
            <div id="ngError" ng-show="form.description.$error.maxlength"><p><?=t('team_description_is_too_big')?></p></div>

            <div class="fields">
                <label for="website"><?=t('team_website')?></label>
                <input name="website" id="website" type="text" value="<?=$this->team->website?>" placeholder="Example: http://www.pcesports.com" />
            </div>

            <div class="fields">
                <label for="facebook"><?=t('team_facebook')?></label>
                <input name="facebook" id="facebook" type="text" value="<?=$this->team->facebook?>" placeholder="Example: http://www.facebook.com/pentaclickesports" />
            </div>

            <div class="fields">
                <label for="twitter"><?=t('team_twitter')?></label>
                <input name="twitter" id="twitter" type="text" value="<?=$this->team->twitter?>" placeholder="Example: http://www.twitter.com/pentaclick" />
            </div>

            <div class="fields">
                <label for="twitch_tv"><?=t('team_twitch_tv')?></label>
                <input name="twitch_tv" id="twitch_tv" type="text" value="<?=$this->team->twitch_tv?>" placeholder="Example: http://www.twitch.tv/pentaclick_tv" />
            </div>

            <a href="javascript:void(0);" class="button" id="editTeam"><?=t('edit_team')?></a>
        </form>
    </div>
    
    <div class="block streamers edit-streamers">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('team_members')?></h1>
        </div>
        <?
		if ($this->team->members) {
        	foreach($this->team->members as $v) {
        ?>
            <div class="block-content streamer" target="_blank" attr-id="<?=$v->id?>">
                <img class="game-logo" src="<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg" />
                <label class="streamer-name"><?=($v->display_name?$v->display_name:$v->name)?> (<?=$v->title?>)</label>
                
                <? if ($this->team->user_id_captain != $v->id) { ?>
                <span class="viewers editStreamerAction">
                	<a href="javascript:void(0);" id="changeTeamCaptain"><?=t('make_team_captain')?></a> |
                    <a href="javascript:void(0);" id="removeTeamMember"><?=t('kick_from_team')?></a>
                </span>
                <? } ?>
            </div>
        <?
        	}
        }
        ?>
    </div>
</div>