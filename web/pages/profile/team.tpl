<section class="container page contacts" ng-app="pcesports" ng-controller="Team" ng-cloak>

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('add_new_team')?></h1>
        </div>
        <form class="block-content contact-form profile" name="form">
            <div id="ngError" ng-show="error"><p>{{error}}</p></div>
            
            <div class="fields">
                <label for="name"><?=t('team_name')?></label>
                <input name="name" id="name" ng-model="name" type="text" value="" placeholder="<?=t('team_name')?>*" ng-minlength="3" ng-maxlength="60" ng-pattern="/^[a-z0-9\s-_]+$/i" ng-model-options="{ updateOn: 'blur' }" required />
            </div>
            <div id="ngError" ng-show="form.name.$error.required && form.name.$touched"><p><?=t('team_name_is_empty')?></p></div>
            <div id="ngError" ng-show="form.name.$error.minlength"><p><?=t('team_name_is_too_small_or_big')?></p></div>
            <div id="ngError" ng-show="form.name.$error.maxlength"><p><?=t('team_name_is_too_small_or_big')?></p></div>
            <div id="ngError" ng-show="form.name.$error.pattern"><p><?=t('team_name_have_forbidden_letters')?></p></div>

            <div class="fields">
                <label for="tag"><?=t('team_tag')?></label>
                <input name="tag" id="tag" ng-model="tag" type="text" value="" placeholder="<?=t('team_tag')?>*" ng-minlength="2" ng-maxlength="5" ng-pattern="/^[A-Z0-9]+$/" ng-model-options="{ updateOn: 'blur' }" required />
                <div class="small"><?=t('team_tag_explanation')?></div>
            </div>
            <div id="ngError" ng-show="form.tag.$error.required && form.tag.$touched"><p><?=t('team_tag_is_empty')?></p></div>
            <div id="ngError" ng-show="form.tag.$error.minlength"><p><?=t('team_tag_is_too_small_or_big')?></p></div>
            <div id="ngError" ng-show="form.tag.$error.maxlength"><p><?=t('team_tag_is_too_small_or_big')?></p></div>
            <div id="ngError" ng-show="form.tag.$error.pattern"><p><?=t('team_tag_have_forbidden_letters')?></p></div>

            <div class="fields">
                <label for="description"><?=t('team_description')?></label>
                <input name="description" id="description" ng-model="description" type="text" value="" placeholder="<?=t('team_description')?>" ng-maxlength="500" ng-model-options="{ updateOn: 'blur' }" />
                <div class="small"><?=t('team_description_explanation')?></div>
            </div>
            <div id="ngError" ng-show="form.description.$error.maxlength"><p><?=t('team_description_is_too_big')?></p></div>

            <div class="fields">
                <label for="password"><?=t('team_password')?></label>
                <input name="password" id="password" ng-model="password" type="text" value="" placeholder="<?=t('team_password')?>*" ng-minlength="3" ng-model-options="{ updateOn: 'blur' }" required />
                <div class="small"><?=t('team_password_explanation')?></div>
            </div>
            <div id="ngError" ng-show="form.password.$error.required && form.password.$touched"><p><?=t('team_password_is_empty')?></p></div>
            <div id="ngError" ng-show="form.password.$error.minlength"><p><?=t('team_password_is_too_small')?></p></div>

            <div class="fields"><?=t('currently_all_team_reg_as_lol')?></div>
            
            <a href="javascript:void(0);" class="button {{button}}" ng-click="addTeam();"><?=t('add_team')?></a>
        </form>
    </div>
</div>