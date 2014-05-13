<section class="container page lol">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">League of Legends Tournament list</h1>
        </div>

        <? for($i=$this->currentTournament;$i>0;--$i) { ?>
        <a class="block-content <?=($i==$this->currentTournament?'active-tournament':'ended-tournament')?>" href="<?=_cfg('href')?>/leagueoflegends/<?=$i?>">
            <div class="left-part">
                <div class="title">Tournament #<?=$i?></div>
                <div class="participant_count"><?=(isset($this->teamsCount[$i])?$this->teamsCount[$i]:0)?> of 128 participants</div>
            </div>
            
            <div class="right-part">
                <div class="status"><?=($i==$this->currentTournament?'On Hold':'Ended')?></div>
                <div class="event-date">Event date: <?=$this->eventDates[$i]?></div>
                <div class="event-date">Prize pool: <?=($i==1?'80€ Cash':'280€ RP+Cash')?></div>
            </div>
            
            <div class="mid-part">
                <? if ($i != $this->currentTournament) { ?>
                    <div><img src="<?=_cfg('img')?>/gold-cup.png" /> <span class="first-place"><?=(isset($this->teamsPlaces[$i][1])?$this->teamsPlaces[$i][1]:null)?></span></div>
                    <div><img src="<?=_cfg('img')?>/silver-cup.png" /> <span class="second-place"><?=(isset($this->teamsPlaces[$i][2])?$this->teamsPlaces[$i][2]:null)?></span></div>
                    <div><img src="<?=_cfg('img')?>/bronze-cup.png" /> <span class="third-place"><?=(isset($this->teamsPlaces[$i][3])?$this->teamsPlaces[$i][3]:null)?></span></div>
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
			<ul>
			<li>EUW server</li>
			<li>Min teams: 16</li>
			<li>Anyone can participate if they are allowed by League of Legend rules</li>
			<li>Single elimination, Best of 1. Finals &#8211; Best of 3</li>
			<li>Semi finals, 3rd place and Finals games will be transmitted on twitch</li>
			</ul>
			<h1>Rules</h1>
			<p>1. Registration:</p>
			<p>1.1 Teams will be allowed to participate after completing registration</p>
			<p>1.2 Players must be able to enter Tournament Draft mode.</p>
			<p>1.3 Teams agree to these rules.</p>
			<p>1.4 Teams that have insufficient number of players will be disqualified from the tournament.</p>
			<p>1.5 You can cancel your participation in profiler (link received in email) day before the tournament starts.</p>
			<p>&nbsp;</p>
			<p>2. Substitute players:</p>
			<p>2.1 During the tournament teams can use any players specified during the team registration.</p>
			<p>2.2 Participation of players other than those specified in the application is prohibited and shall be counted as an automatic loss.</p>
			<p>2.3 In case of a replacement, the tournament prize will be given ONLY to 5 players that participated in finals.</p>
			<p>&nbsp;</p>
			<p>3. Pre-tournament preparation:</p>
			<p>3.1 The managers of the tournament (PentaClick eSports) create a game bracket.</p>
			<p>3.2 Team captains access profile page sent with registration email and see a chat with enemy team player captain and enemy team info.</p>
			<p>3.3 Team captains must talk between each other using chat system on profile page</p>
			<p>&nbsp;</p>
			<p>4. Preparation:</p>
			<p>4.1 Team captains invites all other players to the Custom Game Tournament Draft Mode. Also, on certain occasions managers have to be invited as spectators.</p>
			<p>4.2 Selection for the tournament:</p>
			<p>4.2.1 Inviting a team that is higher in the bracket.</p>
			<p>4.2.2 Invitation through captain.</p>
			<p>&nbsp;</p>
			<p>5. Timing:</p>
			<p>5.1 After appointed time, each team is given 15 minutes for preparations.</p>
			<p>5.2 Team, in which a team member did not show up within 15 minutes, will be disqualified. Team that was online must provide screenshot on profile page. If screenshot was not provided, both teams will be disqualified.</p>
			<p>&nbsp;</p>
			<p>6. Completion:</p>
			<p>6.1 Result of the match &#8211; notification comes to managers automatically.</p>
			<p>6.2 After being alerted managers of the tournament input results in the bracket, the winning team is waiting for the next opponent. They will appear ASAP on profile page.</p>
			<p>&nbsp;</p>
			<p>7. The match format:</p>
			<p>7.1 Custom game Tournament Draft mode.</p>
			<p>7.2 Map of the tournament: Summoner`s Rift.</p>
			<p>7.3 First team destroying enemy Nexus wins the game.</p>
			<p>&nbsp;</p>
			<p>8. Prohibited during tournament:</p>
			<p>8.1 Using map, client, server bugs.</p>
			<p>8.2 Use of any programs that provide an advantage over other competitors .</p>
			<p>8.3 Frequent Player reconnecting during the battle will be regarded as the use of game bugs in your advantage ( artificially created delay) after two warnings player will be disqualified.</p>
			<p>8.4 Do not include in your game name (Custom game) insults, abusive language, harmful expression towards other players race, nationality, religion.</p>
			<h1>Additional information</h1>
			<ul>
			<li>Players are required to attend the tournament using summoner names which were registered (and edited) before 24h tournament start.</li>
			<li>The Manager reserves the right to modify or add other rules.</li>
			</ul>
        </div>
    </div>
</div>