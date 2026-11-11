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
| Y3 | [Add end screens to videos](https://support.google.com/youtube/answer/6388789) (YouTube Help) | Google | 2026-11-10 |
| Y4 | [Autoplay videos](https://support.google.com/youtube/answer/6327615) (YouTube Help) | Google | 2026-11-10 |
| V7 | [Tournament Operation Requirements](https://github.com/ValveSoftware/counter-strike_rules_and_regs/blob/main/tournament-operation-requirements.md) (last changed 2025-12-08, "added TO requirements for both ranked and unranked events") | Valve | 2026-11-11 |
| V8 | Valve Regional Standings, more of the model: [`model/data_loader.js`](https://github.com/ValveSoftware/counter-strike_regional_standings/blob/main/model/data_loader.js), [`model/team.js`](https://github.com/ValveSoftware/counter-strike_regional_standings/blob/main/model/team.js), [`model/ranking_context.js`](https://github.com/ValveSoftware/counter-strike_regional_standings/blob/main/model/ranking_context.js); the [`invitation`](https://github.com/ValveSoftware/counter-strike_regional_standings/tree/main/invitation) folder; the last line of [`live/2026/standings_global_2026_11_02.md`](https://github.com/ValveSoftware/counter-strike_regional_standings/blob/main/live/2026/standings_global_2026_11_02.md); the repository's commits (one release a month in 2026) | Valve | 2026-11-11 |
| V9 | Map pool changes in the game's update notes: ["Counter-Strike 2 Update"](https://store.steampowered.com/news/app/730/view/1790214123031344) (2025-01-28), ["Counter-Strike 2 Update"](https://store.steampowered.com/news/app/730/view/1805431065285705) (2025-07-16), ["Counter-Strike 2 Update"](https://store.steampowered.com/news/app/730/view/1822556746157780) (2026-01-21), ["Counter-Strike 2 Update"](https://store.steampowered.com/news/app/730/view/1836506165581827) (2026-07-08) and ["Season 5, Armory, and More"](https://store.steampowered.com/news/app/730/view/1836506165581825) (2026-07-08); every update note up to 2026-11-05 read for later changes, none found | Valve | 2026-11-11 |
| V10 | ["Your Time is Now"](https://store.steampowered.com/news/app/730/view/5141476355659151610) (2023-08-31): "a maximum of 24 rounds in regulation time (with a 6 round overtime in case of a tie) for Premier, Competitive, and the Majors" | Valve | 2026-11-11 |
| V11 | ["Premier for All"](https://store.steampowered.com/news/app/730/view/4026755437314208134) (2021-03-29, CS:GO): "work together to win 16 rounds" | Valve | 2026-11-11 |
| V12 | ["On The Other Hand..."](https://store.steampowered.com/news/app/730/view/5759616966666137028) (2024-04-25): "the Active Duty map pool (for both Premier and competitions)" | Valve | 2026-11-11 |
| V13 | Economy notes: ["Release Notes for 9/1/2023"](https://store.steampowered.com/news/app/730/view/5141476355663002229) ("Players who survive the last round before overtime no longer carry their items into overtime"), ["Release Notes for 11/2/2023"](https://store.steampowered.com/news/app/730/view/5288974951208087929) ("Fixed a case where players would fail to keep their weapons between rounds of overtime"), ["Release Notes for 4/25/2024"](https://store.steampowered.com/news/app/730/view/5759616966666131969) (kill rewards by weapon), ["Release Notes for 5/23/2024"](https://store.steampowered.com/news/app/730/view/5741605105608520854) ("the terrorist team award when bomb was planted but defused"; the "Loss Bonus" tooltip) | Valve | 2026-11-11 |
| V14 | [Counter-Strike 2](https://www.counter-strike.net/cs2) (Valve's page on what CS2 changed: smokes, sub-tick, maps; rendered in a browser) | Valve | 2026-11-11 |
| E1 | [ESL Pro Tour CS](https://pro.eslgaming.com/tour/cs/) (its series, "one of the two annual ESL Pro Tour Championship events", the upcoming tournaments) | ESL | 2026-11-11 |
| E2 | [ESL Pro Tour Counter-Strike: Game Specific Rules](https://drive.google.com/file/d/1ZMxPYAv__yDwI7rNTvCcB8ah_aPWV_gR/view) ("Date updated: 20 January 2026", linked from E1): 2.3.1 "Map pool", 2.4 "Match settings", 2.4.1 "Overtimes" | ESL | 2026-11-11 |
| B1 | [BLAST.tv: Counter-Strike tournaments](https://blast.tv/cs/tournaments) (upcoming BLAST Premier events; rendered in a browser) | BLAST | 2026-11-11 |
| P1 | [PGL: Events](https://www.pglesports.com/events) (upcoming events; rendered in a browser) | PGL | 2026-11-11 |
| S1 | [StarLadder](https://starladder.com/en/) (its news: StarSeries, "Stake Ranked, the premiere Counter-Strike 2 Tier 2 LAN tournament circuit", "LORGAR RANKINGS, an official CS2 Tier-2 tournament") | StarLadder | 2026-11-11 |
| R1 | `docs/feasibility/cs2-data-and-vods.md`, "What the VODs give away": the organisers' 69 linked VODs, read through the YouTube Data API | this site | 2026-11-08 |
| R2 | Brysbaert, M. (2019). [How many words do we read per minute? A review and meta-analysis of reading rate](https://doi.org/10.1016/j.jml.2019.104047). *Journal of Memory and Language*, 109, 104047 ([abstract](https://osf.io/preprints/psyarxiv/xynwg)) | the author | 2026-11-10 |

Valve's announcements were read through the Steam news API
(`https://api.steampowered.com/ISteamNews/GetNewsForApp/v2/?appid=730&feeds=steam_community_announcements`), the
same posts as the store pages linked above.

## CS2 Majors explained: format, invitations and stages (`how-cs2-majors-work.md`)

| Fact (as of November 2026) | Source |
|---|---|
| A Major runs under Valve's rules; its organiser adds its own rules for the rest | V1, foreword |
| Two Majors a year: Austin (ended in June 2025), Budapest (late November to mid-December 2025), Cologne (ended in June 2026), and the Singapore 2026 Major | V4, V5, V6, V2 |
| Valve's rules name the Singapore 2026 Major as the next one | V2 |
| 32 teams; 16 invited to Stage 1, 8 to Stage 2, 8 to Stage 3, from the Valve Regional Standings | V1, "Invitations" |
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

Left out because no primary source confirmed it when this guide was written: the prize pool, and the Singapore Major's
dates and organiser. PGL's own page now gives both (P1), and the season guide uses them.

## Watch CS2 VODs without spoilers (`watch-cs2-without-spoilers.md`)

| Fact (as of November 2026) | Source |
|---|---|
| A full-match video's length shows on its thumbnail and the player; a third map makes a best-of-three clearly longer | R1 |
| At ESL and PGL (the 69 VODs read), most full-match videos list one chapter per map played in the description, with highlight moments; the player shows them on the progress bar | R1 |
| Chapters come from timestamps in the description; YouTube can add automatic chapters; chapters may appear in transcripts | Y1 |
| In the sample checked by hand (6 thumbnails, ESL's and PGL's), the thumbnails showed both teams' logos and no score; titles name the teams and the event, not the result (ESL's and PGL's 69: `A vs B - Event - Stage`; StarLadder's, `A vs B - <event> \| CS2`, have no stage) | R1, and its "Official channel whitelist" for StarLadder |
| A later round's title (a semi-final naming two teams) gives away earlier results | R1 |
| At ESL and PGL, the main channels post Shorts naming who was eliminated or reached a final, and the VOD channels post only full matches; StarLadder keeps its full matches on its main channel, next to short clips | R1, and its "Official channel whitelist" (`app/domains/vods/channels.json`) |
| As of November 2026, F turns full screen on ("f: Activate full screen") | Y2 |
| A channel can add an end screen with links to other videos in a video's last seconds ("the last 5-20 seconds") | Y3 |
| At the end of a video the player shows what Autoplay will play next; as of November 2026, Autoplay can be switched off in the player and in settings | Y4 ("At the end of a video, the video player will show what's going to play next"; "Switch autoplay on or off") |

What the site does (its own card, teams on tap, a label that orders by stakes, no thumbnail or length) is the site's own
code: `app/domains/watch/README.md`. Suggestions beside the player, comments, social feeds and search results are
described as places that can show a result, with no claim about how often they do; the same for the player's control
bar, which "can show" when the video starts or pauses, while the mouse moves over the player, or on a phone after a tap
on the video. A tip to skip the pre-show with the keyboard was left out: seeking with the keys may show the progress
bar, which couldn't be checked without a browser that plays YouTube.

## CS2 formats explained: best-of-3, Swiss and brackets (`cs2-formats-explained.md`)

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

## The CS2 season: Majors, organisers and key events (`cs2-season-in-5-minutes.md`)

| Fact (as of November 2026) | Source |
|---|---|
| Organisers run their events under a license from Valve | V7, foreword |
| ESL runs the ESL Pro Tour: Intel Extreme Masters and ESL Pro League at the top, the ESL Challenger League below; two Championship events a year | E1 |
| BLAST runs BLAST Premier, with Open, Rivals and Bounty events; its 2027 list runs from January (Bounty) to November | B1 |
| PGL's events in Romania (Bucharest, Cluj-Napoca) and elsewhere in Europe and Asia (Lodz, China); the PGL CS2 Major Singapore, November 24 to December 13, 2026; its 2027 list starts with PGL Bucharest in mid-January | P1 |
| StarLadder runs StarSeries and Tier 2 circuits; it ran the Budapest Major 2025 | S1; V5 |
| Three more organisers in the site's match data, whose matches the site can't show (no full matches on YouTube): Perfect World, FISSURE, GAM3RS_X | `app/domains/matches/leagues.json`; R1; `app/domains/watch/list.ts` (`watchEntries` keeps only matches with a VOD) |
| Ranked events count toward the VRS; their direct invitations come from it, in order; open qualifiers may fill places | V7, 1.6, 3.2, 3.2.7, 3.8.1 |
| Unranked events may invite anyone; their total compensation is capped at $100,000 | V7, 2.2, 2.3 |
| Tier 1: at least 20 direct VRS invites, from the Global list or a regional one, starting with the #1 roster | V7, 3.7.1 to 3.7.3 |
| Tier 2: direct VRS invites only of rosters ranked lower than 12 on the Global VRS | V7, 3.8.2, 3.8.4 |
| A win over another team counts for more at an event with more prize money, up to $1,000,000 | V8 (`team.js`, `getCappedPrizePool`, the stakes modifier) |
| The Majors of 2025 and 2026: the first ended in June, the second runs from late November into December (Singapore: scheduled) | V4, V5, V6, P1 |
| ESL's 2027 list: IEM Kraków starts on January 27 (arena days February 4 to 7), Cologne in August (starts August 2) | E1 (each event's card: "Tournament starts on Jan 27", "Tournament starts on Aug 2") |
| A Major: Valve's rules, 32 teams from every region, items sold in the game | the Majors guide's rows: V1, V5, V6 |

## Valve Regional Standings: how the CS2 ranking works (`how-the-valve-regional-standings-work.md`)

The guide reproduces no list, rank, point value or match from Valve's repository; it explains the model and links
the repository (its license is unclear: `docs/feasibility/cs2-data-and-vods.md`, section 1).

| Fact (as of November 2026) | Source |
|---|---|
| The VRS is Valve's ranking of rosters, published monthly; a roster is five players, whatever the club | V7, 1.1, 1.2; V8 (commits) |
| A roster that shares enough players with a later one counts as the same | V8 (`data_loader.js`: "Rosters that share enough players with a more recent match are considered the same team") |
| Four lists: Global, Americas, Europe, Asia; a roster's region is that of most of its players | V7, 3.7.1; V1, "Roster Regional Assignment" |
| Only ranked events count; show matches don't; an event's matches count once it has ended | V7, 1.6; V8 (`data_loader.js`: `filterUnrankedMatches`, `filterShowmatches`, `filterInProgressEvents`) |
| A roster needs five matches and a win to be ranked | V3 (`ranking.js`: `matchesPlayed >= 5`, `distinctTeamsDefeated > 0`) |
| Six months of matches | V8 (`data_loader.js`: `6*30*24*3600`) |
| The starting value: four measures of equal weight ("Bounty Offered", "Bounty Collected", "Opponent Network", LAN wins), each of the ten best results | V3 (`SEED_MODIFIER_FACTORS`); V8 (`team.js`: `bucketSize = 10`, "there's never any harm in playing in a low-stakes match") |
| Results of the last month count in full, older ones less, down to nothing at the window's start, for every measure | V8 (`data_loader.js`: the one-month `graceperiod`; `ranking_context.js`: `getTimestampModifier`; `team.js`) |
| Prize money weighs only the Bounty Collected and Opponent Network wins: more at an event with more prize money, up to $1,000,000 | V8 (`team.js`: `stakesModifier`; the LAN wins and the prize money won are weighted by age only) |
| Then every match, oldest first, moves both rosters as a Glicko rating with a fixed deviation does ("glicko -> elo"), weighted by its age | V3 (`ranking.js`: `runMatches`, `setFixedRD`); V8 (`data_loader.js`: `informationContent`) |
| The Majors invite from the VRS; the places of every stage are shared out between the regions, each invited from its region's list; the global list only seeds | V1, "Invitations", "Regional Standing" |
| Invitation rules: lists, count and starting rank chosen by the organiser; the latest version on the invite date; versions in `invitation/`; in order; declines go down the list; at least three of the invited players play; open qualifiers fill the gaps; two wildcards per eight VRS invites, with their conditions | V7, 1.1, 3.2.1 to 3.2.7, 3.6 |
| Valve's files credit HLTV.org for the event data | V8 (the standings file's last line) |

## CS2 basics: what changed since CS:GO (`cs2-basics-for-returning-viewers.md`)

| Fact (as of November 2026) | Source |
|---|---|
| At most 24 rounds, a six-round overtime, for Premier, Competitive and the Majors | V10; V1, "Convars" (the Majors use Premier's defaults); every update note from September 2023 to 2026-11-05 searched for a change to the rounds or overtime, none found |
| At ESL: best out of 24; round time 1 minute 55 seconds; bomb timer 40 seconds; $800 start money; overtime best out of 6 with $12,500 start money, sides swapped at its half-time, overtimes until there is a winner | E2, 2.4, 2.4.1 |
| Survivors usually keep their items; not into overtime | V13 (9/1/2023: "no longer carry their items into overtime"; 11/2/2023: "keep their weapons between rounds of overtime") |
| Money from kills, from planting the bomb even when it is defused, and a loss bonus | V13 (4/25/2024, 5/23/2024) |
| The Active Duty pool: Ancient, Anubis, Cache, Dust II, Inferno, Mirage, Nuke | E2, 2.3.1 (as of 2026-01-20: Overpass, Inferno, Mirage, Nuke, Dust2, Ancient, Anubis), then V9 (2026-07-08: Cache in, Overpass out; no change since) |
| Changes one map at a time; Anubis back in January 2026 for Train, Cache in July 2026 for Overpass | V9 |
| The same pool in Premier and at competitions, the Majors included | V12; V1, "Maps" |
| CS:GO's competitive maps took 16 round wins | V11 |
| Smokes fill spaces, and bullets and HE grenades push them aside; sub-tick; maps rebuilt, upgraded or relit in Source 2, Dust II among the unchanged ones at launch (later update notes changed it in small ways) | V14 |

The buy words (full buy, eco round, force buy) are the community's own terms, explained, not facts that change. Left out
because no primary source confirmed it: the round win and loss bonus amounts, weapon prices, and whether money resets
at half-time.

## Official CS2 VODs: where to watch full matches (`where-to-watch-official-cs2-vods.md`)

The channel list isn't typed: `{{vodChannels}}` is filled from `app/domains/vods/channels.json` at build time
(`app/domains/guides/README.md`), with each channel's name and a link by its id. The rest:

| Fact (as of November 2026) | Source |
|---|---|
| How a channel was checked: linked by the organiser's site, or featured by the main channel it links; full-match channels only | R1, "Official channel whitelist" |
| Titles name the teams and the event, not the result; ESL's and PGL's name the stage, StarLadder's don't; a later round's title gives away earlier results | R1, "What the VODs give away", and its "Official channel whitelist" |
| ESL's and PGL's main channels post short clips naming who went through or was out | R1 |
| The video page shows the length, and often chapters, which can give away the map count | R1, "What the VODs give away" (most ESL and PGL VODs list one chapter per map); Y1 |
| Perfect World, FISSURE and GAM3RS_X post no full-match videos on YouTube (checked 2026-11-08; the Thunderpick World Championship's site through a Wayback snapshot of 2026-07-18; GAM3RS_X's own site links no YouTube) | R1, "The other 2026 S-Tier organisers" |
| At ESL and PGL, VODs were up within hours of each match (IEM Beijing 2026, PGL Masters Bucharest 2026) | R1, "Window and matches", "Numbers" ("every VOD was up within hours of its match") |
| The site's list shows only matches with a VOD; it re-checks daily that each featured VOD channel is still featured and that each channel's uploads playlist is the listed one, and stops reading one that fails; it re-reads each link at least weekly and drops deleted videos | `app/domains/watch/list.ts` (`watchEntries`); `app/domains/vods/README.md`; `app/domains/vods/refresh.ts` (`REFRESH_DAYS`) |

## Reading time

The guides' "N min read" and their 3 to 5 minutes use 238 words a minute, adults' silent reading of non-fiction: R2.
