<section class="container page team" attr-id="<?=$this->team->id?>">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=$this->team->name?> | <?=t('team_profile')?></h1>
        </div>
        <div class="block-content member">
            <div class="avatar">
                <img src="<?=_cfg('img')?>/avatar/<?=$this->member->avatar?>.jpg" />
            </div>
            <div class="buttons">
        		<? if ($this->team->user_id_captain == $this->data->user->id) { ?>
        			<a class="button" href="<?=_cfg('href')?>/team/<?=strtolower(urlencode($this->team->name))?>/manage"><?=t('manage_team')?></a>
        			<p class="small"><?=t('requests_to_join_team')?><!-- Requests to join team -->: <?=$requestsCount->count?></p>
                <? } else if (isset($this->data->user->id) && $this->data->user->id && $isMember === 1) { ?>
                    <a class="button" id="leaveTeam" href="javascript:void(0);" attr-msg="<?=t('are_you_sure_to_leave_team')?>"><?=t('leave_team')?></a>
        		<? } else if (isset($this->data->user->id) && $this->data->user->id && $requstToJoin === 0) { ?>
        			<a class="button hint" id="requestJoinTeam" href="javascript:void(0);" attr-msg="<?=t('join_team_request_hint')?>"><?=t('join_team_plus')?></a>
                    <!--Before you will be added to the team, team captain will have to approve you-->
        		<? } else if (isset($this->data->user->id) && $this->data->user->id && $requstToJoin === 1) { ?>
                    <a class="button hint" id="requestJoinTeam" href="javascript:void(0);" attr-msg="<?=t('join_team_request_cancel_hint')?>"><?=t('cancel_join_minus')?></a>
                    <!--You can cancel your request if you don't want to be added to the team-->
                <? } else { ?>
        			<a class="button disabled must-login" href="javascript:;">Login to join team</a>
        		<? } ?>
            </div>
            <div class="information">
                <p><label><?=t('name')?></label> <?=$this->team->name?></p>
                <p><label><?=t('tag')?></label> <?=$this->team->tag?></p>
                <p><label><?=t('registration_date')?></label> <?=date('d.m.Y', strtotime($this->team->registration_date))?></p>
                <p><label><?=t('members_count')?></label> <?=$this->team->members_count?> / 7</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <ul class="tabs">
    	<li class="active" attr-menu="board"><?=t('board')?></li>
    	<li class="" attr-menu="rooster"><?=t('rooster')?> (<?=$this->team->members_count?>)</li>
    	<li class="" attr-menu="about"><?=t('about')?></li>
    	<li class="" attr-menu="team-record"><?=t('team_record')?></li>
    </ul>

    <div class="block tabs-content">
    	<div class="tab-point tab-content-board block-content active">1</div>
    	<div class="tab-point tab-content-rooster member-teams">
    		<? if ($this->team->members) { ?>
    		<div class="block ">
    		    <? foreach ($this->team->members as $v) { ?>
    		    <a href="<?=_cfg('href')?>/member/<?=$v->name?>" class="block-content">
    		    	<img class="game-logo" src="<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg" />
    		        <label><?=$v->name?></label>
    		        <div class="summoner">
    		            <? if ($v->summoner && $v->league) { ?>
    		                <img class="division-logo" src="<?=_cfg('img')?>/leagues_small/<?=strtolower($v->league)?>_<?=$this->convertDivision($v->division)?>.png" />
    		            <? } else if ($v->summoner) { ?>
    		                <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/unranked.png" />
    		            <? } ?>
    		            <?=$v->summoner?>
    		        </div>
    		        <span href="javascript:void(0);" class="title right"><?=$v->title?></span>
    		        <div class="clear"></div>
    		    </a>
    		    <? } ?>
    		</div>
    		<? } ?>
    	</div>
    	<div class="tab-point tab-content-about block-content"><?=$this->team->description?></div>
    	<div class="tab-point tab-content-team-record block-content"><i>none</i></div>
	</div>
</div>