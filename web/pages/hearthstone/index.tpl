<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Hearthstone Tournament list</h1>
        </div>
        
        <? foreach($this->tournamentData as $k => $v) { ?>
        <a class="block-content <?=($k==$this->currentTournament?'active-tournament':'ended-tournament')?>" href="<?=_cfg('href')?>/hearthstone/<?=$k?>">
            <div class="left-part">
                <div class="title">Tournament #<?=$k?></div>
                <div class="participant_count"><?=(isset($v['teamsCount'])?$v['teamsCount']:0)?> of 512 participants</div>
            </div>
            
            <div class="right-part">
                <div class="status"><?=($k==$this->currentTournament?'Registration':'Ended')?></div>
                <div class="event-date">Event date: <?=$v['dates']?></div>
                <div class="event-date">Prize pool: <?=$v['prize']?></div>
            </div>
            
            <div class="mid-part">
                <? if ($k != $this->currentTournament) { ?>
                    <div><img src="<?=_cfg('img')?>/gold-cup.png" /> <span class="first-place"><?=(isset($v['places'][1])?$v['places'][1]:null)?></span></div>
                    <div><img src="<?=_cfg('img')?>/silver-cup.png" /> <span class="second-place"><?=(isset($v['places'][2])?$v['places'][2]:null)?></span></div>
                    <div><img src="<?=_cfg('img')?>/bronze-cup.png" /> <span class="third-place"><?=(isset($v['places'][3])?$v['places'][3]:null)?></span></div>
                <? } else { ?>
                    <div class="clear"></div>
                <? } ?>
            </div>
        </a>
        <? } ?>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Tournament rules</h1>
        </div>

        <div class="block-content tournament-rules">
            <h1>Format</h1>
            <p>EU server</p>
            <p>Min players: 16</p>
            <p>Anyone can participate if they are allowed by Hearthstone rules</p>
            <p>Single elimination, All games are Best of 3. </p>
            <p> </p>
            <h1>Rules</h1>
            <p><br />1. Registration:</p>
            <p>1.1 Only registered players are allowed in the tournament and those agreeing to these rules.</p>
            <p>1.2 Registration will open 2 weeks prior the tournament.</p>
            <p>1.3 You can cancel your registration in your profile 24 hours prior the tournament. A link to your profile has been to you during registration validation.</p>
            <p> </p>
            <p>2. Preparation:</p>
            <p>2.1 PentaClick eSports administration designate the starting time of the tournament.</p>
            <p>2.2 In your profile page you will get a link to temporary Battle chat between you and your opponent (for every new duel, a new chat will be created).</p>
            <p>2.2.1 You should only communicate with your opponent in your battle chat on our site, to avoid any problems.</p>
            <p>2.3 Players invite each other using a BattleTag which will be sen in the Battle chat on our site.</p>
            <p> </p>
            <p>3.Preparations for a battle:</p>
            <p>3.1 After appointed time, each player is given 15 minutes for preparations.</p>
            <p>3.2 A player who is absent will be disqualified in 15 minutes. It will be notified in the Battle chat</p>
            <p>3.2.1 The only exception is Hearthstone client problems. About that you will have to notify administration through Battle chat.</p>
            <p>3.3 During the Best of 3 matches a player can change cards in his deck, but cannot change the Hero.</p>
            <p> </p>
            <p>4. Ending a battle:</p>
            <p>4.1 The winning player uploads a screenshot of the victory screen in Battle chat.</p>
            <p>4.2 Tournament system updates the bracket.</p>
            <p>4.3 The winning player waits for his next opponent and a new link for Battle chat.</p>
            <p> </p>
            <p>5. Prohibited during tournament:</p>
            <p>5.1 Using game bugs.</p>
            <p>5.2 Using 3rd party programs.</p>
            <p>5.3 Use of any programs that provide an advantage over other competitors.</p>
            <p> </p>
            <p>6. Additional info:</p>
            <p>6.1 Players can only participate in the tournament using the BattleTag which was given when registering for the tournament.</p>
            <p>6.2 The administration reserves the right to add or change these rules.</p>
            <p>6.3 When registering for a tournament you automatically agree to these rules.</p>
        </div>
    </div>
</div>