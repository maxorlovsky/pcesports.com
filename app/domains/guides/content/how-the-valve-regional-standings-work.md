---
title: How the Valve Regional Standings work
description: What Valve's ranking of CS2 teams is, how it is worked out in outline, and how it decides who gets invited to the Majors and other events.
order: 6
---
**The short answer:** the Valve Regional Standings (VRS) are Valve's own ranking of CS2 teams. Valve publishes them every month. A team's place comes from its results of the past six months: what it has won, whom it has beaten and where. The Majors and the ranked events invite teams from this ranking, in order, so a team's place decides where it gets to play.

This guide explains the system. For the current ranking, see [Valve's own repository](https://github.com/ValveSoftware/counter-strike_regional_standings), where Valve publishes every list and the code that works it out.

## What it ranks

As of November 2026:

- **Rosters, not clubs.** The ranking counts a roster: five particular players, whichever club they play for. A roster that changes some of its players can keep its results if enough of the same players stay. Valve's code decides how many.
- **Four lists.** There is one world list and three regional ones: Europe, the Americas and Asia. A roster's region is the region most of its players come from.
- **Only ranked events count.** An organiser declares its event ranked or unranked. Only matches at ranked events go into the ranking. Show matches don't count, and an event's matches count only once the event has ended.
- **A minimum to be listed.** A roster needs at least five matches in the past six months, and at least one win, to get a place.

## How it is worked out, in outline

Valve's code works in two steps, as of November 2026.

### Step one: a starting value

Every roster first gets a starting value from four measures, which count the same:

- **Prize money won:** what the roster itself has won at events. Valve calls it the "Bounty Offered".
- **Prize money of the teams it beat:** a win over a team that has won a lot counts for more. Valve calls it the "Bounty Collected".
- **Opponent network:** a win over a team that has itself beaten many different teams counts for more.
- **LAN wins:** wins at events played in person, on a LAN, rather than online.

Each measure reads only the roster's ten best results. So an extra match at a small event never lowers it.

Two more things set how much a result weighs:

- **Its age.** For every measure, results from the last month count in full. Older ones count less and less, down to nothing at six months.
- **The event's prize money.** For the prize money of the teams it beat and for its opponent network, a win at an event with more prize money counts for more, up to one million dollars.

### Step two: head-to-head results

Then every match of the past six months is played through, oldest first. The winner's value goes up and the loser's goes down. Beating a stronger team moves the values more, as in the Elo rating chess uses. Older matches move the values less.

The rosters are then ranked by their final value, on the world list and on their region's list.

### What it means for a team

- A team climbs by winning, and most by beating strong teams at big events.
- A good result fades over six months, so a team has to keep winning to stay high.
- Losing to a weaker team costs more than losing to a stronger one.

## How it decides invitations

### At the Majors

Valve invites the teams at a Major from this ranking. The places in Stages 2 and 3 are shared out between the three regions. [How the CS2 Majors work](/guides/how-cs2-majors-work) has the details.

### At other events

Valve's rules for tournament organisers set how a ranked event invites teams, as of November 2026:

- **The organiser chooses the lists.** It picks one or more of the four lists, how many teams to invite from each and the place to start from.
- **The latest list counts.** The organiser uses the most recent version of the ranking on the day it sends the invitations. Valve keeps the versions that count for invitations in a folder of their own in its repository.
- **In order.** The invitations go down the list, place by place. A roster that says no, or can't take the place, passes it to the next one.
- **The players count.** At least three of the invited roster's players must play every match.
- **Open qualifiers fill the gaps.** If too few ranked rosters are left, the organiser may fill the remaining places with an open qualifier.
- **A few wildcards.** For every eight teams invited from the ranking, the organiser may invite two more of its own choice. A wildcard team must be ranked below 12th in the world. It must also have finished first or second at an event of the same tier or lower, or have at least three players who were in a top-12 team, in the past six months.

Tier 1 events must invite at least 20 rosters from the ranking, starting with the roster in first place on the list they use. Tier 2 events may only invite rosters from the ranking that are ranked below 12th in the world. [The CS2 season in 5 minutes](/guides/cs2-season-in-5-minutes) explains what that means for which events count most.

## Where to see it

Valve publishes the ranking, and the code that works it out, in [its repository on GitHub](https://github.com/ValveSoftware/counter-strike_regional_standings). A new version comes out every month. Valve's files name HLTV.org as the source of the event data.

This site doesn't copy the ranking. For who is where right now, Valve's repository is the place to look.
