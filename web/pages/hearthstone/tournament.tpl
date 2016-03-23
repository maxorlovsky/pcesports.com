<div class="hidden popup" id="rules-window">
    <div class="rules-inside">
        <h1>Hearthstone rules</h1>
        <?=t('hearthstone_tournament_rules')?>
    </div>
</div>

<section class="container page tournament hs">

<div class="left-containers">
    <? if (t('hs_tournament_vod_'.$this->server.'_'.$this->pickedTournament) != 'hs_tournament_vod_'.$this->server.'_'.$this->pickedTournament) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('broadcast_vod')?></h1>
        </div>
        
        <div class="block-content vods">
            <iframe src="//www.youtube.com/embed/<?=t('hs_tournament_vod_'.$this->server.'_'.$this->pickedTournament)?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <? } ?>

    <? if ($this->winners) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('winners')?></h1>
        </div>
        
        <div class="block-content places">
            <div class="third hint" attr-msg="<?=$this->winners[3]?>"><p><?=$this->winners[3]?></p></div>
            <div class="second hint" attr-msg="<?=$this->winners[2]?>"><p><?=$this->winners[2]?></p></div>
            <div class="first hint" attr-msg="<?=$this->winners[1]?>"><p><?=$this->winners[1]?></p></div>
        </div>
    </div>
    <? } ?>

	<? if ($this->data->settings['tournament-reg-hs'] == 1 && $this->data->settings['tournament-season-hs'] == $this->server && $this->pickedTournament == $this->currentTournament) { ?>
	<div class="block registration">
		<div class="block-header-wrapper">
			<h1>Season <?=$this->server?> Tournament #<?=$this->currentTournament?> <?=t('sign_up')?></h1>
		</div>
		
		<div class="block-content signup">
			<div id="join-form">
                <p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>

				<form id="da-form" method="post">
                    <? if ($this->data->user->battletag) { ?>
                        <div class="form-item" data-label="battletag">
                            Battle Tag: <strong><?=$this->data->user->battletag?></strong>
                        </div>
                    <? } else { ?>
                        <div class="form-item" data-label="battletag">
                            <input type="text" name="battletag" placeholder="<?=t('battle_tag')?>*" value="<?=($this->data->user->battletag?$this->data->user->battletag:null)?>" />
                            <div class="message hidden"></div>
                        </div>
                    <? } ?>

                    <? if (!isset($this->data->user->email) && !$this->data->user->email) { ?>
                        <div class="form-item" data-label="email">
                            <input type="text" name="email" placeholder="Email*" value="" />
                            <div class="message hidden"></div>
                        </div>
                    <? } ?>
                    
                    <? for ($i=1; $i <= $this->maxHeroes; ++$i) { ?>
                    <div class="form-item" data-label="hero<?=$i?>">
                        <select class="hero<?=$i?>" name="hero<?=$i?>">
                            <option value="0"><?=t('pick_hero')?></option>
                            <? foreach($this->heroes as $k => $v) { ?>
                                <option value="<?=$k?>"><?=ucfirst($v)?></option>
                            <? } ?>
                        </select>
                        <div class="message hidden"></div>
                    </div>
                    <? } ?>

                    <div class="form-item" data-label="agree">
                        <input type="checkbox" name="agree" id="agree" /><label for="agree"><?=t('agree_with_rules_hs')?></label>
                        <div class="message hidden"></div>
                    </div>
                    
                    <div class="heroes-images">
                        <h6><?=t('your_classes')?></h6>
                        <? for ($i=1;$i<=$this->maxHeroes;++$i) { ?>
                        <div id="hero<?=$i?>img" class="hsicons" attr-picked=""></div>
                        <? } ?>
                    </div>
                    <div class="clear"></div>
				</form>
				<div class="clear"></div>
				<a href="javascript:void(0);" class="button" id="register-in-tournament"><?=t('join_tournament')?></a>
			</div>

            <div class="tournament-rules">
                <?=str_replace(
                    array('%startTime%', '%registrationTime%', '%checkInTime%', '%prize%'),
                    array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $tournamentRow->prize),
                    t('hs_tournament_information')
                )?>
                <div>
                    <a href="javascript:;" class="rules"><?=t('global_tournament_rules')?></a>
                </div>
                
                <div class="share-tournament">
                    <h2><?=t('share_this_tournament')?></h2>
                    <div class="addthis_sharing_toolbox"></div>
                </div>
            </div>

            <div class="clear"></div>
        </div>
	</div>

	<? } else { ?>

    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('information')?></h1>
        </div>
        
        <div class="block-content tournament-rules">
			<?=str_replace(
                array('%startTime%', '%registrationTime%', '%checkInTime%', '%prize%'),
                array($tournamentTime['start'], $tournamentTime['registration'], $tournamentTime['checkin'], $tournamentRow->prize),
                t('hs_tournament_information')
            )?>
            <div>
                <a href="javascript:;" class="rules"><?=t('global_tournament_rules')?></a>
            </div>
            
            <div class="share-tournament">
                <h2><?=t('share_this_tournament')?></h2>
                <div class="addthis_sharing_toolbox"></div>
            </div>
        </div>
    </div>

    <? } ?>
    
    <?
    //Deprecated groups
    if ($this->pickedTournament < 6 && $this->server == 's1') {
    ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>

        <div class="block-content groups">
            <?
            $j = 0;
            foreach($this->groups as $k => $v) {
            ?>
                <div class="group">
                    <div class="header">
                        <h3><?=t('group')?> <?=$v?></h3>
                        <span class="place"><?=t('place_in_group')?></span>
                        <div class="clear"></div>
                    </div>
                    <div class="group-list">
                        <?
                        if ($this->participants) {
                            $i = 1;
                            foreach($this->participants as $p) {
                                if ($p->seed_number == $k && $p->approved == 1) {
                                    $wonClass = '';
                                    if ( ($this->pickedTournament == 1 && isset($p->contact_info->place) && $p->contact_info->place == 1) ||
                                         ($this->pickedTournament >= 2 && isset($p->contact_info->place) && $p->contact_info->place >= 2)
                                       ){
                                        //$wonClass = 'player-won';
                                    }
                                ?>
                                <div class="holder <?=$wonClass?>">
                                    <span class="player-num"><?=$i?></span>
                                    <? if ($p->user_id != 0) { ?>
                                        <a class="player-name" href="<?=_cfg('href')?>/member/<?=$p->name?>" title="<?=$p->battletag?>">
                                            <?=$p->name?>
                                        </a>
                                    <? } else { ?>
                                        <span class="player-name">
                                            <?=$p->battletag?>
                                        </span>
                                    <? } ?>
                                    <div class="player-heroes">
                                        <div class="hsicons-small <?=$this->heroes[$p->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$p->contact_info->hero1])?>"></div>
                                        <div class="hsicons-small <?=$this->heroes[$p->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$p->contact_info->hero2])?>"></div>
                                        <div class="hsicons-small <?=$this->heroes[$p->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$p->contact_info->hero3])?>"></div>
                                        <? if (isset($this->heroes[$p->contact_info->hero4]) && $this->heroes[$p->contact_info->hero4]) { ?>
                                        <div class="hsicons-small <?=$this->heroes[$p->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$p->contact_info->hero4])?>"></div>
                                        <? } ?>
                                    </div>
                                    <span class="player-score">
                                        <?=(isset($p->contact_info->place)&&$p->contact_info->place?$p->contact_info->place:0)?>
                                    </span>
                                    <div class="clear"></div>
                                </div>
                                <?
                                ++$i;
                                }
                            }
                            
                            if ($i == 1) {
                                ?><div class="holder empty"><?=t('group_empty')?></div><?
                            }
                        }
                        else {
                            ?><div class="holder empty"><?=t('group_empty')?></div><?
                        }
                        ?>
                    </div>
                </div>
            <?
                if ($j%2 == 1) {
                    echo '<div class="clear"></div>';
                }
                
                ++$j;
            }
            ?>
			<div class="clear"></div>
        </div>
    </div>
    <? } else { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('participants')?></h1>
        </div>
        
        <div class="block-content participants isotope-participants">
        <?
        $participantsCount = 0;
        $i = 0;
        if ($this->participants) {
            foreach($this->participants as $v) {
                //if ($v->checked_in == 1 && $v->verified == 1) {
                if ($v->checked_in == 1) {

                    //This must be here
                    ++$participantsCount;

                    if ($v->user_id != 0) {
                    ?>
                    <div class="block hoverable" title="<?=$v->battletag?>" style="background-image: url('<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg');">
                        <a href="<?=_cfg('href').'/member/'.$v->name?>">
                            <div class="team-name" title="<?=$v->battletag?>"><?=$v->name?></div>
                            <span class="team-num">#<?=$participantsCount?></span>
                            <div class="clear"></div>
                            <div class="player-heroes">
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                                <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                                    <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                                <? } ?>
                            </div>
                        </a>
                    </div>
                    <?
                    }
                    else {
                    ?>
                    <div class="block" title="<?=$v->battletag?>">
                        <div class="team-name"><?=$v->battletag?></div>
                        <span class="team-num">#<?=$participantsCount?></span>
                        <div class="clear"></div>
                        <div class="player-heroes">
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                            <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                            <? } ?>
                        </div>
                    </div>
                    <?
                    }
                    ++$i;
                }
            }
        }

        if ($participantsCount == 0) {
            ?><p class="empty-list"><?=t('no_checked_in_players')?></p><?
        }
    ?>
        </div>
    </div>
    
    <?/*
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('verified_participants')?></h1>
        </div>
        
        <div class="block-content participants isotope-participants-verified">
        <?
        $participantsCount = 0;
        if ($this->participants) {
            foreach($this->participants as $v) {
                //if ($v->verified == 1 && $v->checked_in != 1) {
                if ($v->checked_in != 1) {
                    //This must be here
                    ++$participantsCount;

                    if ($v->user_id != 0) {
                    ?>
                    <div class="block hoverable" title="<?=$v->battletag?>" style="background-image: url('<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg');">
                        <a href="<?=_cfg('href').'/member/'.$v->name?>">
                            <div class="team-name" title="<?=$v->battletag?>"><?=$v->name?></div>
                            <span class="team-num">#<?=$participantsCount?></span>
                            <div class="clear"></div>
                            <div class="player-heroes">
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                                <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                                    <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                                <? } ?>
                            </div>
                        </a>
                    </div>
                    <?
                    }
                    else {
                    ?>
                    <div class="block" title="<?=$v->battletag?>">
                        <div class="team-name"><?=$v->battletag?></div>
                        <span class="team-num">#<?=$participantsCount?></span>
                        <div class="clear"></div>
                        <div class="player-heroes">
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                            <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                            <? } ?>
                        </div>
                    </div>
                    <?
                    }
                }
            }
        }

        if ($participantsCount == 0) {
            ?><p class="empty-list"><?=t('no_verified_players')?></p><?
        }
    ?>
        </div>
    </div>*/?>
    <? } ?>
    
    <? if ($this->participants) { ?>
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('pending_participants')?></h1>
        </div>
        
        <div class="block-content participants isotope-participants-pending">
        <?
        $participantsCount = 0;
        if ($this->participants) {
            foreach($this->participants as $v) {
                //if ($v->verified == 0) {
                if ($v->checked_in == 0 && $v->approved == 1) {
                ++$participantsCount;
                    if ($v->user_id != 0) {
                    ?>
                    <div class="block hoverable" title="<?=$v->battletag?>" style="background-image: url('<?=_cfg('img')?>/avatar/<?=$v->avatar?>.jpg');">
                        <a href="<?=_cfg('href').'/member/'.$v->name?>">
                            <div class="team-name" title="<?=$v->battletag?>"><?=$v->name?></div>
                            <span class="team-num">#<?=$participantsCount?></span>
                            <div class="clear"></div>
                            <div class="player-heroes">
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                                <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                                    <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                                <? } ?>
                            </div>
                        </a>
                    </div>
                    <?
                    }
                    else {
                    ?>
                    <div class="block" title="<?=$v->battletag?>">
                        <div class="team-name"><?=$v->battletag?></div>
                        <span class="team-num">#<?=$participantsCount?></span>
                        <div class="clear"></div>
                        <div class="player-heroes">
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero1]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero1])?>"></div>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero2]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero2])?>"></div>
                            <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero3]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero3])?>"></div>
                            <? if (isset($this->heroes[$v->contact_info->hero4]) && $this->heroes[$v->contact_info->hero4]) { ?>
                                <div class="hsicons-small <?=$this->heroes[$v->contact_info->hero4]?> hint" attr-msg="<?=ucfirst($this->heroes[$v->contact_info->hero4])?>"></div>
                            <? } ?>
                        </div>
                    </div>
                    <?
                    }
                }
            }
        }

        if ($participantsCount == 0) {
            ?><p class="empty-list"><?=t('no_registered_players')?></p><?
        }
    ?>
        </div>
    </div>
    <? } ?>
    
    <? if ($i >= 2) { ?>
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('brackets')?></h1>
        </div>

        <div class="block-content participants <? if (_cfg('https') != 1) {?>hidden<?}?>">
            <?=t('challonge_available_http_only')?> <a href="http://pentaclick.challonge.com/hs<?=$this->server?><?=$this->pickedTournament?>" target="_blank">http://pentaclick.challonge.com/hs<?=$this->server?><?=$this->pickedTournament?></a>
        </div>
        <div class="block-content challonge-brackets <? if (_cfg('https') == 1) {?>hidden<?}?>">
            <div id="challonge"></div>
        </div>
    </div>
	<? } ?>
</div>

<script src="<?=_cfg('static')?>/js/jquery.challonge.js"></script>
<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
var heroes = {
    1: 0,
    2: 0,
    3: 0,
    4: 0
};

var maxHeroes = <?=$this->maxHeroes?>;

$('.hero1, .hero2, .hero3, .hero4').on('change keyup', function() {
    var getClass = $(this).attr('class');
    var name = $(this).find(':selected').text();
    var id = $(this).find(':selected').val();
    var num = getClass.substr(-1);
    
    var picked = $('#'+getClass+'img').attr('attr-picked');
    $('#'+getClass+'img').removeClass(picked);
    
    if (id == 0) {
        $('#'+getClass+'img').removeClass('active');
    }
    else {
        $('#'+getClass+'img').addClass(name.toLowerCase());
        $('#'+getClass+'img').addClass('active');
        $('#'+getClass+'img').attr('attr-picked', name.toLowerCase());
    }
    
    heroes[num] = id;
    
    $.each($('#da-form select'), function(k, v) {
        $(this).find('option').attr('disabled', false);
    });
    
    $.each(heroes, function(k, v) {
        if (v != 0) {
            for (i=1; i <= maxHeroes; ++i) {
                if (i != k) {
                    $('.hero'+i).find('option[value="'+v+'"]').attr('disabled', true);
                }
            }
        }
    });
});

$('#register-in-tournament').on('click', function() {
    PC.addParticipant('HS');
});

var participantsNumber = <?=$participantsCount?>;
if (participantsNumber > 100) {
    challongeHeight = 3500;
}
else if (participantsNumber > 50) {
    challongeHeight = 1800;
}
else if (participantsNumber > 25) {
    challongeHeight = 950;
}
else {
    challongeHeight = 550;
}

if ($('#challonge').length) {
    $('#challonge').height(challongeHeight);
    
    <? if ($this->server == 's2') { ?>
        $('#challonge').challonge('hs<?=$this->server.$this->pickedTournament?>', {
            subdomain: 'pentaclick',
            theme: '1',
            multiplier: '1.0',
            match_width_multiplier: '0.7',
            show_final_results: '0',
            show_standings: '0',
            overflow: '0'
        });
    <? } else { ?>
        $('#challonge').challonge('<?=($this->pickedTournament<6?'hl1_'.$this->pickedTournament:'hs'.$this->server.$this->pickedTournament)?>', {
            subdomain: 'pentaclick',
            theme: '1',
            multiplier: '1.0',
            match_width_multiplier: '0.7',
            show_final_results: '0',
            show_standings: '0',
            overflow: '0'
        });
    <? } ?>
}
</script>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4dfdc8015d8f785b"></script>