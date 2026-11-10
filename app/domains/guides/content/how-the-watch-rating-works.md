---
title: How "What should I watch?" rates matches
description: How PCesports labels each finished CS2 match: what was at stake plus how close it got, the three labels, and why it only rates after the match.
order: 4
---
**The short answer:** every finished top-tier CS2 match with an official full-match video gets one of three labels: {{labelMustWatch}}, {{labelWorthAWatch}} or {{labelForFans}}. The label adds up two things. The stakes are what the match decided, known before it started. Closeness is how close it got, known only after it ended. The label never tells you who won, the score or how many maps were played.

## Stakes: known before the match

The stakes add up three parts.

**The stage:**

- a grand final: {{grandFinal}}
- a semi-final, upper and lower bracket ones included: {{semiFinal}}
- an upper or lower bracket final in the playoffs: {{bracketFinal}}
- any other playoff match: {{otherPlayoff}}
- a third-place match: {{thirdPlace}}
- a group or Swiss match that can knock a team out or send it through: {{groupDecider}}
- any other group or Swiss match: {{otherGroup}}

**The event:**

- a Major: {{major}}
- an event in PandaScore's top tier, tier s: {{tierS}}
- an event in its next tier, tier a: {{tierA}}

**The teams:** how strong both teams are, from {{strengthMin}} for two of the weaker teams to {{strengthMax}} for two of the strongest. Strength comes from a team rating that replays every top-tier result of the last {{resultsDays}} days. Every win raises a team's rating and every loss lowers it, more so when the result was unexpected. Two teams of middling strength score about nothing here.

**An example:** a Major's grand final starts with {{grandFinal}} for the stage and {{major}} for the event. The teams' strength comes on top, before anyone knows how the match went. A first group match at a smaller event can start at {{otherGroup}} for the stage and {{tierA}} for the event, and needs strong teams and a close match to climb.

## Closeness: known after the match

Closeness is the strongest of these, not their sum:

- {{upset}} for **an upset**: the winner had less than a {{upsetPercent}}% chance by the team ratings before the match.
- {{overtimeMap}} for **a very long map**: one of the maps lasted {{overtimeMinutes}} minutes or more. Every map that long in the site's measurements went to overtime.
- {{closeMap}} for **a long map**: one of them lasted {{closeMinutes}} minutes or more.

Closeness only reads the maps every series of its length has to play: in a best-of-three, the maps before a possible third one. Whether a series went the distance is never part of it.

## The three labels

The rating is the stakes plus closeness:

- **{{labelMustWatch}}:** a rating of {{mustWatch}} or more. High stakes and a close match, or the very highest stakes. Start here.
- **{{labelWorthAWatch}}:** a rating of {{worthAWatch}} or more. A good match, if you have the evening.
- **{{labelForFans}}:** a rating below {{worthAWatch}}. Mostly for those who follow the event or the teams closely.

On the home page, the matches are grouped by label, best first. Within a label they are ordered by stakes, then by date, newest first.

## Why it only rates after the match

Before a match, all anyone can say is what is at stake. Whether it was worth your evening depends on how it went. So the site waits until a match is over, then lists the finished matches of the last {{listDays}} days that have the organiser's official full-match video.

Rating after the match means the label knows something about the result. It is built so that you can't read the result off it:

- **No map count.** Closeness never reads whether a series went the distance or how many maps it took.
- **No single reason.** Closeness takes only the strongest signal, so a label never says "it was an upset" or "it went to overtime".
- **No telling order.** Matches that share a label are ordered by stakes, which were known before the match, so a match's place in the list says nothing more.
- **No teams on show.** The teams stay hidden until you tap, because a later round's teams tell you who won the earlier ones.

## What it doesn't use

- **No YouTube data.** Views, likes, video lengths and comments never go into the rating.
- **One source for results.** The results come from PandaScore only, which is why every page says "Source: PandaScore".
- **No opinions.** No one picks matches by hand. Every match goes through the same rules.

To watch the matches you pick without spoiling them, read [How to watch CS2 without spoilers](/guides/watch-cs2-without-spoilers).
