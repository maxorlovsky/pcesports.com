---
title: How PCesports rates CS2 matches to watch
description: How PCesports combines stakes and closeness to label finished CS2 matches, what each label means, and how the rating avoids revealing results.
order: 4
---
**The short answer:** each finished CS2 match in PandaScore's top two tiers gets a label if it has an official full-match VOD: {{labelMustWatch}}, {{labelWorthAWatch}} or {{labelForFans}}. The rating combines stakes and closeness. Stakes describe what the match would decide, which was known before it began. Closeness describes how close it was, which is known only afterwards. The label reveals neither the winner nor the score or number of maps played.

## Stakes: known before the match

The stakes combine points for the stage, the event and the teams.

**The stage:**

- a grand final: {{grandFinal}}
- in the playoffs, a semi-final, including those in the upper and lower bracket: {{semiFinal}}
- in the playoffs, an upper or lower bracket final: {{bracketFinal}}
- in the playoffs, a third-place match: {{thirdPlace}}
- any other playoff match: {{otherPlayoff}}
- in a group, a round whose name suggests it may decide who advances or is eliminated (an elimination match, a decider, a winners match, an upper bracket final or any lower bracket match): {{groupDecider}}
- in a Swiss stage, a round where one of the teams has two wins or two losses: {{groupDecider}}
- any other group or Swiss match: {{otherGroup}}

The site identifies the stage from PandaScore's round names. Those names help estimate which matches decide qualification or elimination. They do not describe every event's format exactly.

**The event:**

- a Major: {{major}}
- an event in PandaScore's highest tier, tier s: {{tierS}}
- an event in its second tier, tier a: {{tierA}}

**The teams:** points for the strength of both teams, from {{strengthMin}} for two weaker teams to {{strengthMax}} for two of the strongest. A separate team rating calculates strength by replaying every result in PandaScore's top two tiers from the last {{resultsDays}} days. A win raises the team's rating; a loss lowers it. Unexpected results cause larger changes. Two teams of average strength contribute roughly zero points here.

## Closeness: known after the match

Only the strongest of these signals contributes closeness points. The site does not add them together:

- {{upset}} for **an upset**: the team ratings gave the winner less than a {{upsetPercent}}% chance before the match.
- {{overtimeMap}} for **a very long map**: one of the maps lasted {{overtimeMinutes}} minutes or more. Every map of that length in the site's measurements reached overtime.
- {{closeMap}} for **a long map**: one of the maps lasted {{closeMinutes}} minutes or more.

Closeness uses only the maps that every series of that length must play. For a best-of-three, that means the maps before a possible third. Playing the full possible series never contributes to closeness.

## The three labels

Adding stakes and closeness gives the final rating:

- **{{labelMustWatch}}:** a rating of {{mustWatch}} or more. These matches combine high stakes with closeness, or have the highest stakes alone. Start here.
- **{{labelWorthAWatch}}:** a rating of {{worthAWatch}} or more. A good match to choose when you have an evening free.
- **{{labelForFans}}:** a rating below {{worthAWatch}}. Mainly for viewers who closely follow the teams or the event.

On the home page, matches appear in groups by label, with the highest label first. Within each group, stakes determine the order, followed by date, newest first.

## Why it only rates after the match

Before a match begins, only its stakes are known. Whether it is worth watching also depends on how it plays out. The site therefore waits until the match ends. It lists finished matches from the last {{listDays}} days that have an official full-match VOD from the organiser.

A label calculated after the match uses information about the result. The rating is designed to keep that result hidden:

- **No map count.** Closeness ignores the number of maps played and whether the series used every possible map.
- **No single reason.** Closeness takes only the strongest signal, so a label never says "it was an upset" or "it went to overtime".
- **Ordering keeps results hidden.** Matches with the same label are ordered by stakes, which were known beforehand. A match's position reveals nothing more about its result.
- **Hidden team names.** The teams stay hidden until you tap, because a later round's teams tell you who won the earlier ones.

## What it doesn't use

- **No YouTube data.** Views, likes, video lengths and comments never go into the rating.
- **One source for results.** The results come from PandaScore only, which is why every page says "Source: PandaScore".
- **No opinions.** No one picks matches by hand. The same rules apply to every match.

For ways to avoid spoilers while watching your chosen matches, read [Watch CS2 VODs without spoilers](/guides/watch-cs2-without-spoilers).
