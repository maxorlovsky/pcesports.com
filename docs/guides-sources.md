# Guides: sources

Every fact in a guide (`app/domains/guides/content/`) that can change, with the primary source it was checked
against and the date it was read. A fact with no source here doesn't go into a guide. Where a source also reports
results (Valve's announcements do), the guides take only formats, dates and rules from it, never who won.

The rating guide (`how-the-watch-rating-works.md`) needs no outside source: every number and label in it comes from the
`watch` domain's code at build time (`app/domains/guides/facts.ts`), and its rules are those of
`app/domains/watch/README.md`.

## Sources

| Id | Source | Owner | Read |
|---|---|---|---|
| V1 | [CS Major Supplemental Rulebook](https://github.com/ValveSoftware/counter-strike_rules_and_regs/blob/main/major-supplemental-rulebook.md) (last changed 2026-10-06) | Valve | 2026-11-10 |
| V2 | [Tournament Operation Requirements: exceptions](https://github.com/ValveSoftware/counter-strike_rules_and_regs/blob/main/tournament-operation-requirements-exceptions.md) (its "Singapore 2026 Major" row, added 2026-10-07), and the rulebook's [commit of 2026-10-06](https://github.com/ValveSoftware/counter-strike_rules_and_regs/commits/main/major-supplemental-rulebook.md), "Updating rules for Regional Assignment for the upcoming Singapore Major" | Valve | 2026-11-10 |
| V3 | Valve Regional Standings: the model it runs, [`model/ranking.js`](https://github.com/ValveSoftware/counter-strike_regional_standings/blob/main/model/ranking.js), and a roster's details page of the 2026-11-02 standings, [`live/2026/details/2026_11_02/`](https://github.com/ValveSoftware/counter-strike_regional_standings/tree/main/live/2026/details/2026_11_02) (each page explains the factors; the repository's readme describes an older model and qualifiers, and isn't used) | Valve | 2026-11-10 |
| V4 | ["BLAST.TV Austin Major 2025"](https://store.steampowered.com/news/app/730/view/1800357164422450) (2025-05-23), ["The Final Showdown in Austin"](https://store.steampowered.com/news/app/730/view/1802893906812947) (2025-06-22) | Valve | 2026-11-10 |
| V5 | ["The Starladder Budapest Major 2025"](https://store.steampowered.com/news/app/730/view/1816215235365195) (2025-11-12), ["Best of Five in Budapest"](https://store.steampowered.com/news/app/730/view/1818752592130605) (2025-12-13) | Valve | 2026-11-10 |
| V6 | ["IEM Cologne 2026"](https://store.steampowered.com/news/app/730/view/1833334318570412) (2026-05-22), ["Playoffs at the 'Cathedral of Counter-Strike'"](https://store.steampowered.com/news/app/730/view/1835871199302220) (2026-06-17), ["The IEM Cologne 2026 Grand Finals"](https://store.steampowered.com/news/app/730/view/1835871199310024) (2026-06-20) | Valve | 2026-11-10 |
| Y1 | [Video chapters](https://support.google.com/youtube/answer/9884579) (YouTube Help) | Google | 2026-11-10 |
| Y2 | [YouTube keyboard shortcuts](https://support.google.com/youtube/answer/7631406) (YouTube Help) | Google | 2026-11-10 |
| R1 | `docs/feasibility/cs2-data-and-vods.md`, "What the VODs give away": the organisers' 69 linked VODs, read through the YouTube Data API | this site | 2026-11-08 |
| R2 | Brysbaert, M. (2019). [How many words do we read per minute? A review and meta-analysis of reading rate](https://doi.org/10.1016/j.jml.2019.104047). *Journal of Memory and Language*, 109, 104047 ([abstract](https://osf.io/preprints/psyarxiv/xynwg)) | the author | 2026-11-10 |

Valve's announcements were read through the Steam news API
(`https://api.steampowered.com/ISteamNews/GetNewsForApp/v2/?appid=730&feeds=steam_community_announcements`), the
same posts as the store pages linked above.

## How the CS2 Majors work (`how-cs2-majors-work.md`)

| Fact (as of November 2026) | Source |
|---|---|
| A Major runs under Valve's rules; its organiser adds its own rules for the rest | V1, foreword |
| Two Majors a year: Austin (ended in June 2025), Budapest (late November to mid-December 2025), Cologne (ended in June 2026), and the Singapore 2026 Major | V4, V5, V6, V2 |
| Valve's rules name the Singapore 2026 Major as the next one | V2 |
| 32 teams; 16 invited to Stage 1, 8 to Stage 2, 8 to Stage 3, all from the Valve Regional Standings; no open qualifiers | V1, "Invitations" |
| Three stages of 16 teams in a Swiss format, 8 advancing from each, then single-elimination playoffs of the top 8 | V1, "Major Format", "Stage Seeding" |
| Swiss: opponents of the same record; three wins advance, three losses eliminate; elimination and advancement matches best-of-three, all others best-of-one; no rematch in a stage if possible | V1, "Swiss Bracket" |
| Seeding by the global VRS before the event; ties broken by the difficulty score (opponents' wins minus their losses) | V1, "Seeding" |
| Quarter-finals and semi-finals best-of-three | V1, "Single Elimination Bracket" |
| Grand final best-of-five at the last two Majors: first at Budapest (December 2025), again at Cologne (June 2026); Valve's written rules still say best-of-three for every playoff match | V5, V6; V1, "Single Elimination Bracket": "All matches are best of three" |
| The pick-ban order is set by Valve for every Major | V1, "Map Pick-Ban" |
| Three VRS regions: Europe, Americas, Asia; a roster's region is the region of most of its players | V1, "Regional Standing", "Roster Regional Assignment" |
| The places in Stages 2 and 3 are shared out between the regions by the previous Major: one Stage 2 place per team of the region that reached Stage 3, one Stage 3 place per team in its playoffs | V1, "Invitations" |
| The invitation goes to the roster; one that declines or isn't intact passes it to the next-highest ranked roster; a last-chance regional qualifier fills places no roster is left for | V1, "Invitation Process", "Team Requirements" |
| The VRS starts a roster from four factors: prize money won ("Bounty Offered"), beaten opponents' prize money ("Bounty Collected"), "Opponent Network" and LAN wins; each reads the roster's ten best results of the past six months; head-to-head results then adjust it; older results are weighted down ("Age Weight") | V3 (`SEED_MODIFIER_FACTORS` in `ranking.js`: `ownNetwork`, the number of teams beaten, has weight 0; a details page's "Starting Rank Value" and "Factors" notes; `model/data_loader.js` keeps six months of matches) |
| During a Major Valve sells stickers with team logos and player signatures; 50% of tournament item sales go to the organiser, the teams and the players | V5, V6 |
| Pick'Em challenge in the game | V5, V6 |
| At the Cologne 2026 Major, a weapon in the game could be made a souvenir of one of its matches, with team, map and player stickers | V6 ("Souvenir-O-Matic") |

Left out because no primary source confirmed it: the prize pool, and the Singapore Major's dates and organiser.

## How to watch CS2 without spoilers (`watch-cs2-without-spoilers.md`)

| Fact | Source |
|---|---|
| A full-match video's length shows on its thumbnail and the player; a third map makes a best-of-three clearly longer | R1 |
| At ESL and PGL (the 69 VODs read), most full-match videos list one chapter per map played in the description, with highlight moments; the player shows them on the progress bar | R1 |
| Chapters come from timestamps in the description; YouTube can add automatic chapters; chapters may appear in transcripts | Y1 |
| In the sample checked by hand (6 thumbnails, ESL's and PGL's), the thumbnails showed both teams' logos and no score; titles name the teams and the event, not the result (ESL's and PGL's 69: `A vs B - Event - Stage`; StarLadder's, `A vs B - <event> \| CS2`, have no stage) | R1, and its "Official channel whitelist" for StarLadder |
| A later round's title (a semi-final naming two teams) gives away earlier results | R1 |
| At ESL and PGL, the main channels post Shorts naming who was eliminated or reached a final, and the VOD channels post only full matches; StarLadder keeps its full matches on its main channel, next to short clips | R1, and its "Official channel whitelist" (`app/domains/vods/channels.json`) |
| As of November 2026, F turns full screen on ("f: Activate full screen") | Y2 |

What the site does (its own card, teams on tap, a label that orders by stakes, no thumbnail or length) is the site's own
code: `app/domains/watch/README.md`. Suggestions beside the player, comments, social feeds and search results are
described as places that can show a result, with no claim about how often they do; the same for the player's control
bar, which "can show" when the video starts or pauses and shows while the mouse moves over the player. A tip to skip
the pre-show with the keyboard was left out: seeking with the keys may show the progress bar, which couldn't be checked
without a browser that plays YouTube.

## CS2 formats explained (`cs2-formats-explained.md`)

| Fact | Source |
|---|---|
| At a Major: three wins advance, three losses eliminate; deciding matches best-of-three, the others best-of-one | V1, "Swiss Bracket" |
| In a best-of-one, maps are removed until one is left | V1, "Best of 1 Pick-Ban Process" |
| The stage and round names the site shows ("Playoffs" with "Lower Bracket Final", "Group Stage" with "Round 5", "Group A", "Winners Match", "Elimination Match", "Semifinal 1", "3rd Place Match") are PandaScore's | R1 (section 1, the PandaScore row) and the site's match data (`app/domains/matches`, `stage` and the round in `name`) |

Best-of-one, three and five, round robin, single and double elimination are described as formats in general, with no
event named. Where events differ, the guide says so rather than give one event's rule: how many teams a four-team
group or a group bracket sends through, whether a deciding match follows the Winners Match, and quarter-finals some
teams skip. The site's own match data shows each case (2026-11-10, by stage and round names only): IEM Beijing 2026's
groups are double-elimination brackets ending in an upper and a lower bracket final, with two quarter-finals in its
playoffs; BLAST Rivals Fall 2026's groups have a Winners Match and an Elimination Match and no deciding match, with two
quarter-finals in its playoffs.

## Reading time

The guides' "N min read" and their 3 to 5 minutes use 238 words a minute, adults' silent reading of non-fiction: R2.
