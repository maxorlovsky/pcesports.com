<section class="container page tournament">

<div class="left-containers">
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

    <? if ($this->regOpen == 1) { ?>
    <div class="block registration">
        <div class="block-header-wrapper">
            <h1>FIFA 2015 Tournament <?=t('sign_up')?></h1>
        </div>
        
        <div class="block-content signup">
            <div id="join-form">
                <p class="reg-completed success-add"><?=t('join_tournament_almost_done')?></p>

                <form id="da-form" method="post">
                    <div class="form-item" data-label="nickname">
                        <input type="text" name="nickname" placeholder="Nickname*" value="" />
                        <div class="message hidden"></div>
                    </div>

                    <div class="form-item" data-label="email">
                        <input type="text" name="email" placeholder="E-Pasts*" value="" />
                        <div class="message hidden"></div>
                    </div>
                    
                    <div class="form-item" data-label="name">
                        <input type="text" name="name" placeholder="Vārds un Uzvārds*" value="" />
                        <div class="message hidden"></div>
                    </div>
                    
                    <div class="form-item" data-label="agree">
                        <input type="checkbox" name="agree" id="agree" /><label for="agree">Es piekrītu noteikumiem</label>
                        <div class="message hidden"></div>
                    </div>
                    
                    <div class="clear"></div>
                </form>
                <div class="clear"></div>
                <a href="javascript:void(0);" class="button" id="register-in-tournament"><?=t('join_tournament')?></a>
            </div>
            
            <div class="tournament-rules">
                <img src="<?=_cfg('img')?>/fifa-2015.jpg" />
                <p>Spēļes iestatījumi: Periods 6 min.</p>
                <p>Trāvmas izslēgti.</p>
                <p>Prize pool: 1 vieta 50% no iemaksas +20 euro. 2 vieta 30% no iemaksas + 10 euro. 3. vieta 10% no iemaksas + 5 euro.</p>
                <p>Konts reģistrācijas apmaksai: Swedbank LV45HABA0551033763452 Ņikita Mihailovs</p>
                <p>Apmaksai pievienot sāvu nickname!</p>
                <p>Formāts: Group + single elimination bo3</p>
                <p>Kontākts pasts mileit22@gmail.com</p>
                <p>Adrese: Delisnack Jelgava, Pasta iela 45.  Sākums 13:00 4.oktobrī</p>
                <p>Regīstrācijas maksa 5 eur.</p>
                
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
            <img src="<?=_cfg('img')?>/fifa-2015.jpg" />
            <p>Spēļes iestatījumi: Periods 6 min.</p>
            <p>Trāvmas izslēgti.</p>
            <p>Prize pool: 1 vieta 50% no iemaksas +20 euro. 2 vieta 30% no iemaksas + 10 euro. 3. vieta 10% no iemaksas + 5 euro.</p>
            <p>Konts reģistrācijas apmaksai: Swedbank LV45HABA0551033763452 Ņikita Mihailovs</p>
            <p>Apmaksai pievienot sāvu nickname!</p>
            <p>Formāts: Group + single elimination bo3</p>
            <p>Kontākts pasts mileit22@gmail.com</p>
            <p>Adrese: Delisnack Jelgava, Pasta iela 45.  Sākums 13:00 4.oktobrī</p>
            <p>Regīstrācijas maksa 5 eur.</p>
            
            <div class="share-tournament">
                <h2><?=t('share_this_tournament')?></h2>
                <div class="addthis_sharing_toolbox"></div>
            </div>
        </div>
    </div>

    <? } ?>
    
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
                if ($v->checked_in == 1 && $v->verified == 1) {

                    //This must be here
                    ++$participantsCount;
                    ?>
                    <div class="block" title="<?=$v->name?>">
                        <div class="team-name"><?=$v->name?></div>
                        <span class="team-num">#<?=$participantsCount?></span>
                        <div class="clear"></div>
                    </div>
                    <?
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
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('verified_participants')?></h1>
        </div>
        
        <div class="block-content participants isotope-participants-verified">
        <?
        $participantsCount = 0;
        if ($this->participants) {
            foreach($this->participants as $v) {
                if ($v->verified == 1 && $v->checked_in != 1) {

                    //This must be here
                    ++$participantsCount;

                    ?>
                    <div class="block" title="<?=$v->name?>">
                        <div class="team-name"><?=$v->name?></div>
                        <span class="team-num">#<?=$participantsCount?></span>
                        <div class="clear"></div>
                    </div>
                    <?
                }
            }
        }

        if ($participantsCount == 0) {
            ?><p class="empty-list"><?=t('no_verified_players')?></p><?
        }
    ?>
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('pending_participants')?></h1>
        </div>
        
        <div class="block-content participants isotope-participants-pending">
        <?
        $participantsCount = 0;
        if ($this->participants) {
            foreach($this->participants as $v) {
                if ($v->verified == 0) {
                ++$participantsCount;
                    ?>
                    <div class="block" title="<?=$v->name?>">
                        <div class="team-name"><?=$v->name?></div>
                        <span class="team-num">#<?=$participantsCount?></span>
                        <div class="clear"></div>
                    </div>
                    <?
                }
            }
        }

        if ($participantsCount == 0) {
            ?><p class="empty-list"><?=t('no_registered_players')?></p><?
        }
    ?>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/jquery.isotope.min.js"></script>
<script>
$('#register-in-tournament').on('click', function() {
    PC.addParticipant('Fifa');
});
</script>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4dfdc8015d8f785b"></script>