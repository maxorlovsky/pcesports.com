<template>
<div class="user">
    <div class="block">
        <div class="block-content semi-widths">
            <loading v-if="loading"></loading>

            <div class="alert alert-danger" v-if="userError" v-html="userError"></div>

            <section class="profile-wrapper" v-if="Object.keys(user).length">
                <h4>{{user.name}} | User profile</h4>

                <div class="avatar">
                    <img v-bind:src="'/dist/assets/images/avatar/' +  user.avatar + '.jpg'" alt="Avatar" />
                </div>
                <div class="information">
                    <p><label>Name</label> {{user.name}}</p>
                    <p v-if="user.battletag"><label>Battle Tag</label> {{user.battletag}}</p>
                    <p><label>Registration date</label> {{user.registration_date}}</p>
                    <p><label>Points</label> {{user.experience}}</p>
                </div>

                <!-- <div class="summoners">
                    <a href="http://<?=$v->region?>.op.gg/summoner/?userName=<?=$v->name?>"
                        target="_blank"
                        class="block-content summoner"
                        v-for="value in user.summoners">
                        <? if ($v->league) { ?>
                            <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/<?=strtolower($v->league)?>_<?=$this->convertDivision($v->division)?>.png" />
                        <? } else { ?>
                            <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/unranked.png" />
                        <? } ?>
                        <label class="summoner-name"><?=$v->name?></label>
                        <span href="javascript:void(0);" class="region right"><?=$v->regionName?></span>
                    </a>
                </div> -->
            </section>
        </div>
    </div>
</div>

<!--
<div class="members">
    <? if ($this->member->summoners) { ?>
    <div class="block summoners member-summoners">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('summoners_accounts')?></h1>
        </div>
        <? foreach ($this->member->summoners as $v) { ?>
        <a href="http://<?=$v->region?>.op.gg/summoner/?userName=<?=$v->name?>" target="_blank" class="block-content summoner">
            <? if ($v->league) { ?>
                <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/<?=strtolower($v->league)?>_<?=$this->convertDivision($v->division)?>.png" />
            <? } else { ?>
                <img class="game-logo" src="<?=_cfg('img')?>/leagues_small/unranked.png" />
            <? } ?>
            <label class="summoner-name"><?=$v->name?></label>
            <span href="javascript:void(0);" class="region right"><?=$v->regionName?></span>
            <div class="clear"></div>
        </a>
        <? } ?>
    </div>
    <? } ?>
    
    <? if ($this->member->tournaments) { ?>
    <div class="block member-tournaments">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participated_in_tournaments')?></h1>
        </div>
        <? foreach ($this->member->tournaments as $v) { ?>
        <a href="<?=_cfg('href')?>legacy/<?=$this->fullGameName[$v->game]?>/<?=$v->server?>/<?=$v->tournament_id?>" class="block-content tournament-info place-<?=$v->place?>">
            <img class="game-logo" src="<?=_cfg('img')?>/<?=str_replace('lan', '', $v->game)?>-logo-small.png">
            <label class="tournament-name">
                <?=t($this->fullGameName[$v->game])?> 
                <? if ($v->server) { ?>(<?=strtoupper($v->server)?>)<?}?> 
                #<?=$v->tournament_id?>
            </label> - <?=$v->name?>
            <span class="right place">
                <? if ($v->place>=1 && $v->place<=3) { ?>
                    <img src="<?=_cfg('img')?>/<?=$this->places[$v->place]?>-cup.png" />
                <? } ?>
                <?=$v->place?> place
            </span>
            <div class="clear"></div>
        </a>
        <? } ?>
    </div>
    <? } ?>

    <div class="block member-achievements">
        <a name="achievements"></a>
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=$this->member->name?>'s <?=t('achievements')?></h1>
        </div>

        <div class="block-content">
            <? foreach ($this->member->achievements as $v) { ?>
                <div class="achievement <?=($v->locked!==0?'locked':'hint')?>" attr-msg="Unlocked on <?=date('d M Y @ H:i', strtotime($v->date))?>">
                    <div class="image">
                        <? if ($v->image) { ?>
                            <img src="<?=_cfg('img').'/achievements/'.$v->image?>" />
                        <? } else { ?>
                            No image
                        <? } ?>
                    </div>
                    <div class="points"><?=$v->points?></div>
                    <div class="name"><?=$v->name?></div>
                    <div class="text"><?=$v->description?></div>
                    <? if ($v->locked !== 0 && $v->requirement != 1) { ?>
                        <div class="line-bar" attr-goal="<?=$v->requirement?>" attr-current="<?=$v->current?>">
                            <div><span></span></div>
                            <span id="gathered"></span>
                        </div>
                    <? } ?>
                </div>
            <? } ?>
        </div>
    </div>

</div>
-->
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

// Components
import loading from '../../components/loading/loading.vue';

const userPage = {
    components: {
        loading
    },
    data: function() {
        return {
            loading: true,
            userError: '',
            user: {},
        };
    },
    created: function() {
        const userName = this.$route.params.name;

        axios.get(`${pce.apiUrl}/user-data/${userName}`)
        .then((response) => {
            this.user = response.data.user;

            functions.setUpCustomMeta(`${this.user.name} profile`, `${this.user.name} profile`)

            this.loading = false;
        })
        .catch((error) => {
            this.userError = error.response.data.message;
            this.loading = false;
        });
    }
};

// Routing
pce.routes.push({
    path: '/user/:name',
    component: userPage,
    meta: {
        title: '',
        description: 'User profile'
    }
});

export default userPage;
</script>