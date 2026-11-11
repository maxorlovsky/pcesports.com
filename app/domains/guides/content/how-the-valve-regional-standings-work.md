---
title: Valve Regional Standings: how the CS2 ranking works
description: How Valve ranks CS2 rosters, which results count, and how the Valve Regional Standings determine invitations to Majors and other tournaments.
order: 6
---
**The short answer:** the Valve Regional Standings (VRS) rank CS2 teams using their results from the past six months. Valve publishes a new version every month. A team's position reflects what it has won, which opponents it has beaten and where it played. Majors and ranked events invite teams in ranking order, so that position determines which events a team can enter.

This guide explains the system. For the current ranking, see [Valve's own repository](https://github.com/ValveSoftware/counter-strike_regional_standings), which contains every list and the code used to calculate it.

## What it ranks

As of November 2026:

- **Rosters, not clubs.** The ranking follows a roster of five players, regardless of their club. After a player change, a roster can keep its results if enough players remain. Valve's code sets that threshold.
- **Four lists.** There is one global list and three regional ones: Europe, the Americas and Asia. A roster's region is the region most of its players come from.
- **Only ranked events count.** An organiser declares its event ranked or unranked. Only matches at ranked events go into the ranking. Show matches do not count. Matches from an event enter the ranking only after the event ends.
- **A minimum to be listed.** A roster needs at least five matches in the past six months, and at least one win, to enter the ranking.

## How Valve calculates the ranking

As of November 2026, Valve's code calculates the ranking in two steps.

### Step one: a starting value

Each roster receives a starting value based on four equally weighted measures:

- **Prize money won:** the prize money the roster has earned at events. Valve calls it the "Bounty Offered".
- **Prize money of the teams it beat:** a win over a team that has won a lot counts for more. Valve calls it the "Bounty Collected".
- **Opponent network:** a win over a team that has itself beaten many different teams counts for more.
- **LAN wins:** wins at events played in person, on a LAN, rather than online.

Each measure uses only the roster's ten best results. Playing an extra match at a small event therefore never lowers that measure.

Two factors affect how much each result counts:

- **Its age.** For every measure, results from the last month count in full. Older results gradually lose weight, reaching zero at six months.
- **The event's prize money.** For the prize money of the teams it beat and for its opponent network, a win at an event with more prize money counts for more, up to one million dollars.

### Step two: head-to-head results

The code then processes every match from the past six months, starting with the oldest. The winner's value goes up and the loser's goes down. Beating a stronger team causes a larger change, as in chess's Elo rating. Older matches have less effect.

The final values determine the order of rosters, on the global list and on their region's list.

### What it means for a team

- Winning moves a team up, especially when it beats strong opponents at big events.
- A good result fades over six months, so a team has to keep winning to stay high.
- Losing to a weaker team costs more than losing to a stronger one.

## How the ranking determines invitations

### At the Majors

Major invitations come from this ranking. Places in each stage are allocated among the three regions, with invitations drawn from each region's list. The global list only determines seeding once the teams have a place. [CS2 Majors explained: format, invitations and stages](/guides/how-cs2-majors-work) has the details.

### At other events

Valve's rules for tournament organisers set how a ranked event invites teams, as of November 2026:

- **The organiser chooses the lists.** It picks one or more of the four lists, how many teams to invite from each and the rank at which invitations begin.
- **The latest list counts.** The organiser uses the most recent version of the ranking on the day it sends the invitations. Valve stores the versions used for invitations in a separate folder in its repository.
- **In order.** Invitations follow the list in rank order. If a roster declines or cannot accept, the invitation passes to the next roster.
- **The players count.** At least three of the invited roster's players must play every match.
- **Open qualifiers fill the gaps.** If too few ranked rosters remain, the organiser may fill the remaining places with an open qualifier.
- **A few wildcards.** For every eight teams invited from the ranking, the organiser may choose two additional teams to invite. A wildcard team must be ranked below 12th on the global list. It must also have finished first or second at an event of the same tier or lower in the past six months, or have at least three players who were in a top-12 team during that period.

Tier 1 events must invite at least 20 rosters from the ranking, starting with the roster in first place on each list they use. Tier 2 events may only invite rosters from the ranking that are ranked below 12th on the global list. [The CS2 season: Majors, organisers and key events](/guides/cs2-season-in-5-minutes) explains what that means for which events count most.

## Where to find the current standings

Valve publishes the ranking, and the code that calculates it, in [its repository on GitHub](https://github.com/ValveSoftware/counter-strike_regional_standings). A new version comes out every month. Valve's files name HLTV.org as the source of the event data.

This site does not copy the standings. To check a team's current position, use Valve's repository.
