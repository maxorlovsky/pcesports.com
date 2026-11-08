# Feasibility: CS2 match data sources and official YouTube VOD matching

Research date: 2026-11-08. Experiment code: [`experiments/cs2-vod-feasibility/`](../../experiments/cs2-vod-feasibility/README.md). The data job built on it: `tools/data-job/` (`README.md`, "Data job").

**YouTube data stripped (2026-11-08).** YouTube's Developer Policies let API data be kept for 30 days at most [Y2], so the YouTube data this report quoted as evidence is gone from this file: video titles, lengths, publish times and chapters, and the figures computed from them (upload delays, length ranges, chapter counts). What stays is YouTube's ids (videos, channels), the counts of what was read, and the conclusions. The file's git history keeps the original text.

**Question.** Can a hobby site get tier-1 CS2 match data and the matching official full-match VODs reliably, legally and for free?

**Short answer: yes, with PandaScore's free plan for the match data. The owner chose it on 2026-11-08 (section 6, item 1).**

- **VODs: feasible.** The organisers' own YouTube VOD channels, read through their upload playlists, gave 69 of 69 tier-1 matches over 15 days their full-match VOD:
  - 0 wrong links
  - each VOD up within hours of the match
  - 25 quota units for the whole backfill
- **Match data: PandaScore's free plan works for this.** Tested with the owner's token on the same 15 days:
  - PandaScore's top two tiers held exactly the same 69 matches as Liquipedia's S-Tier.
  - Winner, series score, maps played and best-of agreed on all 69, and every record was final within 30 minutes of the match ending.
  - Linking VODs from PandaScore's list picked the same 69 videos.
  - **Terms risk (accepted):** PandaScore's terms are written for "any professional, to the exclusion of any consumer as well as any non-professional". They also list results under the paid plan although the free API returns them, and they exclude "any marketing of said data". The owner decided to go ahead without asking PandaScore (section 6, item 1), so this stays an accepted risk (section 4).
- **Liquipedia's free API is out:**
  - Its MediaWiki API, which the experiment first used, now says "Use of the API for non-editing purposes is not supported".
  - Its data API (LPDB) is free only for sites that "do not monetize", and paid plans started at $49/month per data type per game in June 2026.
  - That makes LPDB the paid fallback.
- **HLTV and Strafe forbid this use.**

The other caveats are a narrow event sample, and spoilers that come from the VODs' length and chapters rather than their titles. Details below.

## 1. Data sources

All terms were read on 2026-11-08 from each source's own pages (Liquipedia's also on 2026-11-07, see below); every terms claim below links its quote ([L1] etc., listed after the table). Pages that block automated clients were read from a dated Wayback snapshot, noted with the quote. I registered no account. The only keys used were the owner's: a YouTube API key and a PandaScore token (the owner registered with PandaScore).

| Source | Covers (schedule / results / tier + stage / teams) | Freshness | Limits | Key / sign-up | Cost | Commercial use on an ad-supported site | Attribution | Verdict |
|---|---|---|---|---|---|---|---|---|
| **Liquipedia MediaWiki API** (used by the experiment) | Yes / yes, per map with scores / yes: `liquipediatier`, stage headings, plus editors' per-map VOD links / yes | Volunteer-edited live: on 2026-11-07 the IEM Beijing playoff section was edited 10 times between 08:24 and 11:36 UTC while both semi-finals were played (revision history). The PGL grand-final VOD link was still missing 8 days later | 1 request per 2 s, `action=parse` 1 per 30 s, custom User-Agent with contact info, no scraping of HTML pages [L2, L3, L4] | None | Free [L1] | **No longer for this use.** On 2026-11-07 the terms offered it "for use in your own projects" [L1]. On 2026-11-08 they say it is for editing and "Use of the API for non-editing purposes is not supported" [L10]. The content license itself, CC-BY-SA 3.0, allows commercial use with attribution and share-alike [L5, L6, L7] | Required: "content is from Liquipedia" plus a link [L6] | **Not for the product** under the current terms. The experiment's data was fetched on 2026-11-08 (see section 6) |
| Liquipedia LPDB API ("data API") | Same data as structured queries (matches, tournaments, teams and more, per game) [L11] | Same | "the rate limit your plan provides" [L8]: 1,000 requests per hour on Basic [L11] | "Upon approved request" [L8], an application form [L11] | **Free only for** educational, non-commercial ("do not monetize") and community-feature projects, with open-source code [L12]. **Paid:** Basic "$49/month per Data Type", per game [L11] (June 2026 snapshot) | Allowed on a paid plan; the free plan excludes monetized sites [L12] | Same license [L5] | **The clean paid option.** One CS data type at the June 2026 price is $49/month, about 25 to 50 % of the site's revenue target |
| PandaScore (free "Fixtures" plan, **tested**) | **Measured on the 15-day window:** schedule, results (winner, series score, winner per map), best-of, teams with acronyms, league/serie/tournament names. The top two tiers held the same 69 matches as Liquipedia's S-Tier. **No map names, per-map scores or per-match VOD links** in the free list (its `streams_list` has live-stream and full-day broadcast links only). Stage plus round is detailed: "Playoffs" with "Lower Bracket Final", "Group Stage" with "Round 5". Tiers are in-house [P5]: IEM Beijing "s", PGL Masters Bucharest "a" (Liquipedia: both S-Tier) | Every one of the 69 records was last edited 30 min or less after its match ended (median 5 min); "in real-time" per its docs [P4] | 1,000 requests per hour [P1]; the window took 7 requests; no browser calls (server only) [P4] | Token on every request [P8] (the owner signed up) | Free plan 0 EUR, paid "From 400€ per videogame per month" [P2] | **Unclear.** Reproducing data "only on its website, for information purposes, and to the exclusion of any marketing of said data" is allowed [P10], but the terms are for "any professional, to the exclusion of any consumer as well as any non-professional" [P9], and rights come "By taking out a Subscription and paying its price" [P11] | Required: "Source: PandaScore" [P12] | **Recommended, and chosen by the owner** (section 6). The free plan does return results, as its docs say [P3]; the terms list results only under the paid Historical plan [P6, P7], one of the accepted terms risks |
| Valve Regional Standings ([GitHub repo](https://github.com/ValveSoftware/counter-strike_regional_standings)) | **No schedule.** Monthly standings in Markdown, plus per-roster tables of matches (date, opponent, W/L, weights) with no event, score, stage or tier. A one-year match sample JSON (2022-08 to 2023-08) is never updated | Monthly, around the first Monday (2026 commits from 01-05 to 11-02, one in September on a Tuesday/Wednesday); the 2026-11-02 release holds matches up to the last days of October (the #1 roster's newest is 2026-10-31), so it lags a few days at release and up to 5 weeks before the next | GitHub's own | None | Free | **Unclear:** no license file (GitHub reports none) and no reuse terms in the README. Every file credits HLTV as the event-data provider [V1] | None stated | Not a match source. Useful later for the Regional Standings guides, license to be settled first |
| HLTV.org | Everything (schedule, results, stats) | Live | No official API found; Cloudflare blocks automated clients | - | - | **Forbidden:** no commercial exploitation, no scraping or data mining, no similar website [H1, H2, H3], "personal use only" [H4] | - | **Ruled out**, not used |
| GRID (grid.gg) | Tier-1 CS2 circuits incl. IEM, ESL Pro League, BLAST [G2] | - | Old Open Access: 10 requests per minute, never real time [G4] | Application | Open Access page now redirects to plans; "Every plan on this page is commercial and custom-priced to the use case." [G1] | Old Open Access terms: only for the stated purpose, up to 10,000 EUR yearly revenue, data treated as confidential [G3] | - | Not usable today |
| FACEIT Data API | faceit.com competitions [F1]; tier-1 events don't run on FACEIT (inferred) | - | Not found | API key, FACEIT account [F1, F2] | Not found | Terms page blocked to automated clients; not found | - | Not a tier-1 source |
| start.gg API | Community tournaments; tier-1 CS2 isn't there (inferred) | - | 80 requests per 60 s [S1] | Token | Free | **Allowed:** ads may appear next to the data [S2] | Required [S3] | Not a tier-1 source |
| bo3.gg | Claims all CS2 tournaments [B1] (rendered page) | - | No official API; `robots.txt` disallows `/api/` | - | - | Reproduction "allowed only with an active hyperlink" [B2], but without an API it would mean scraping | Hyperlink [B2] | Not used (no API) |
| Abios (now Kambi) | Calendars, results, brackets [A1] | - | 3 requests per s [A1] | Sales contact | Not published | Not found | - | Commercial, sales-only |
| Strafe | - | - | No API found | - | - | **Forbidden:** "not for any commercial use" [ST1] | - | **Ruled out** |

The script uses only the Liquipedia MediaWiki API, the PandaScore API and the YouTube Data API. No source whose terms forbid scraping or commercial use was touched by it.

### Quotes

Liquipedia
- [L1] "Liquipedia is pleased to provide free access to the information in our wikis through the MediaWiki API for use in your own projects." <https://liquipedia.net/api-terms-of-use> as read on **2026-11-07**. That sentence is gone from the page as read on 2026-11-08 (see [L10]); I couldn't fetch a dated archive copy (Wayback refused with HTTP 429)
- [L2] "Rate limit all HTTP requests to no more than 1 request per 2 seconds. API "action=parse" requests should not exceed 1 request per 30 seconds as these are more resource intensive." (same page)
- [L3] "Use a custom HTTP "User-Agent" header in your requests that identifies your project / use of the API, and includes contact information." (same page)
- [L4] "Automated access to non-API endpoints (i.e., generated HTML pages) is not permitted." (same page)
- [L5] "Liquipedia content is licensed under CC-BY-SA 3.0, which requires that you attribute Liquipedia as the source of your data." (same page)
- [L6] "How you word the attribution is up to you, but it should at minimum include that the content is from Liquipedia, and a link to Liquipedia or the article itself." and "SA Derivative works have to be shared using the same license" <https://liquipedia.net/commons/Liquipedia:Copyrights> (read through the API)
- [L7] "Share — copy and redistribute the material in any medium or format for any purpose, even commercially." <https://creativecommons.org/licenses/by-sa/3.0/us/deed.en>
- [L8] "Upon approved request Liquipedia grants access to the information in our wikis through the LiquipediaDB API for use in your own projects." and "Keep all your requests within the rate limit your plan provides." <https://liquipedia.net/api-terms-of-use> (2026-11-08; on 2026-11-07 the second sentence read "Rate limit all requests to no more than 60 requests per 1 hour.")
- [L10] "The MediaWiki API is designed for users who are creating / editing pages and fetching machine-readable page data to support this process. Use of the API for non-editing purposes is not supported - please see our data API if you are interested in using data from Liquipedia for other purposes ." <https://liquipedia.net/api-terms-of-use> as read on 2026-11-08
- [L11] "Basic $49/month per Data Type*", "* Data Types reflected on a per game basis." and the plan table's "Requests per hour" of 1000 for Basic. <http://web.archive.org/web/20260618191933/https://liquipedia.net/api>. The live page <https://liquipedia.net/api> answers automated clients with a "Verify you are human" challenge, so I couldn't read today's version: today's plans and prices are unconfirmed
- [L12] "Free access to the LPDB API (subject to our Terms of Use ) is strictly reserved for the following use cases:", "Non-Commercial Public Websites: Projects that serve the public interest but do not monetize and do not duplicate existing Liquipedia features." and "If your project does not meet these criteria, please select one of our commercial plans." (same snapshot)
- [L9] "Liquipedia reserves the right to do any of the following, at any time, without notice: (1) to modify, suspend or terminate operation of or access to the Liquipedia API, or any portion of the API, for any reason; ..." (same page)

PandaScore
- [P1] "All accounts start out on the free plan which comes with a 1000 API requests per hour limitation." <https://www.pandascore.co/pricing>
- [P2] "Historical & Post-Match Data From 400€ per videogame per month*" (same page)
- [P3] "Fixtures updates for matches are provided in in sync with public live streams, informing when a match begins or ends, the final match score and the winning team." and "The Fixtures Only plan is available to all users for free." <https://developers.pandascore.co/docs/introduction>
- [P4] "Results will be available in real-time, and detailed post-game statistics will be available typically within 15 minutes of the game ending." and "The PandaScore API does not support direct browser-to-API requests from client-side JavaScript." <https://developers.pandascore.co/docs/frequently-asked-questions>
- [P5] "Tournament tiers are assigned in-house, meaning tiering could differ from other sources." <https://developers.pandascore.co/docs/tournaments-in-depth>
- [P6] "The calendar of past and future events; • Pre-match data: information about tournaments, teams, players; • A maximum rate of one thousand (1k) requests per hour." (clause 2.1, the Fixture Subscription) <https://www.pandascore.co/terms-and-condition>
- [P7] "2.2 The Historical Data Subscription: • All services offered by the Fixture Subscription; • Post-match data: results and details by match;" (same page)
- [P8] "Each request via the programming interface must include the token." (same page)
- [P9] "1.1 The purpose of these Terms and Conditions is to determine the conditions under which any professional, to the exclusion of any consumer as well as any non-professional (hereinafter the " Customer ") may subscribe to data and statistical analysis services ..." (same page)
- [P10] "6.3 Except for the Low Latency Subscription, PandaScore authorises the Customer to reproduce the data to which it has had access through Subscription but only on its website, for information purposes, and to the exclusion of any marketing of said data." (same page)
- [P11] "6.2 By taking out a Subscription and paying its price, PandaScore authorises the Customer to access its Database and to use the data contained therein in accordance with this Subscription." (same page)
- [P12] "... the Customer shall affix to any document and any medium reproducing all or part of the PandaScore data, the source as follows: "Source: PandaScore"." (same page)

Valve
- [V1] "Event data for Regional Standings provided by HLTV.org" (the last line of every standings file, e.g. [`live/2026/standings_global_2026_11_02.md`](https://github.com/ValveSoftware/counter-strike_regional_standings/blob/main/live/2026/standings_global_2026_11_02.md))

HLTV (hltv.org answers automated clients with a Cloudflare challenge; read from <http://web.archive.org/web/20261003051459/https://www.hltv.org/terms>, terms "Effective 27th. January 2025")
- [H1] "You are not allowed to: commercially exploit the Website or any of its content in any manner;"
- [H2] "conduct, facilitate or organize data mining or web scraping in relation to the Website and/or any of the content; or"
- [H3] "construct and build a similar or competitive website, product, or service."
- [H4] "6.1. Our Website and services are provided for personal use only."

GRID
- [G1] "Every plan on this page is commercial and custom-priced to the use case." <https://grid.gg/plans/> (where <https://grid.gg/open-access/> now redirects)
- [G2] "GRID covers CS2 circuits including IEM Krakow, IEM Rio, IEM Cologne Major, IEM Atlanta, ESL Pro League, BLAST Premier, and the Champion of Champions Tour (CCT)." <https://www.grid.gg/data/games/counter-strike-2/>
- [G3] "Any use of Data Assets beyond a cumulative yearly revenue in the amount of 10.000 EUR requires a separate individual license agreement with GRID." <https://cdn.grid.gg/gridgg/GRID_Open_Data_Platform_Agreement_05.04.2022.pdf>
- [G4] "The User therefore will never be provided with real time live Data Assets." and "The Series State API can be queried up to 10 times per minute." (same PDF)

FACEIT
- [F1] "Using the Data API, you can access all publicly available information on faceit.com" <https://docs.faceit.com/docs/data-api>
- [F2] "You must be a registered FACEIT user to get access and if you don’t have an account you can sign up here." <https://docs.faceit.com/getting-started/Guides/build-faceit-apps>

start.gg
- [S1] "You may not average more than 80 requests per 60 seconds ." <https://developer.start.gg/docs/rate-limits>
- [S2] "This prohibition on using START.GG APIs Data for advertising or marketing purposes does not prohibit surfacing advertisements on your application or website in association with data obtained from the START.GG services via the START.GG APIs." <https://www.start.gg/about/apitos>
- [S3] "Attribution: By using the START.GG APIs you agree to provide attribution to START.GG in connection with any content you receive and display or otherwise reproduce from the START.GG services via the START.GG APIs." (same page)

bo3.gg, Abios, Strafe
- [B1] "We cover all CS2 tournaments worldwide, ensuring data reliability and relevance." <https://bo3.gg/>
- [B2] "Full or partial distribution or reproduction of site materials is allowed only with an active hyperlink to this website." <https://bo3.gg/wiki/use-of-services>
- [A1] "Get an overview of esports matches with calendars and results for each esports title." with the package limits "3 requests Per second", "5 requests Burst" <https://abiosgaming.com/esports-data-api>
- [ST1] "you agree to use the Services for your own private purposes only and not for any commercial use, such as marketing or sale of goods or Services;" <https://www.strafe.com/terms-conditions/>

YouTube (for the VOD side)
- [Y1] "Projects that enable the YouTube Data API have a default quota allocation of 100 search.list calls, 100 videos.insert calls, and 10,000 units per day combined for all other endpoints." <https://developers.google.com/youtube/v3/determine_quota_cost>
- [Y2] "API Clients may temporarily store limited amounts of Non-Authorized Data for as long as is necessary for the purposes of the API Client but not longer than 30 calendar days." <https://developers.google.com/youtube/terms/developer-policies>
- [Y3] "... or sell advertising, sponsorships, or promotions on any page or screen that contains YouTube API Data unless other data, content, or material not obtained from YouTube appears on the same page and offers enough independent value to justify such sales if the YouTube API Data were removed." (same page)
- [Y5] "Your API Clients must not (i) replace API Data with similar, independently calculated data, or (ii) access or use API Data to create new or derived data or metrics." <https://developers.google.com/youtube/terms/developer-policies>
- [Y4] "You and your API Clients must not, and must not encourage, enable, or require others to, directly or indirectly, scrape YouTube Applications or Google Applications , or obtain scraped YouTube data or content." (same page)

## 2. VOD matching

### Window and matches

- **Window:** 2026-10-24 to 2026-11-07 (UTC, 15 days; `--from 2026-10-24 --to 2026-11-08`).
  - It starts one day earlier than "the 14 days before the run" so that PGL's event is complete.
  - It stops before 2026-11-08, the day of the run, so IEM Beijing's last two matches are out. The third-place match (NAVI vs BetBoom, 04:00 UTC) already had its VOD a few hours after the start. The grand final was still being played when the run started.
  - **The owner's default window gives the same result.** For the 14 days before the run (`--from 2026-10-25 --to 2026-11-08`): 61 matches, all 61 linked, 60 match Liquipedia's own link and 1 has no Liquipedia link yet, 0 wrong.
- **"Tier-1"** means Liquipedia's S-Tier (`liquipediatier=S-Tier` in the event infobox, listed by `{{TournamentsList|tier=1}}`), the card's default.
- **Events in the window:** Liquipedia's S-Tier list had two, read with `{{TournamentsList|tier=1|year=2026}}`.
  - IEM Beijing 2026 (organiser ESL, 2026-11-02 to 08): 28 matches.
  - PGL Masters Bucharest 2026 (organiser PGL, 2026-10-24 to 31): 41 matches.
  - No other S-Tier event overlapped the window. The next one, BLAST Rivals Fall 2026, starts on 2026-11-11.
- **Total:** 69 played matches, all Bo3 except one Bo5 (the PGL grand final).

### Official channel whitelist

Only these channels are read (the experiment's `channels.json`; the data job's whitelist is `app/domains/vods/channels.json`). Every one was verified the same way:
1. The organiser's own website links the main channel.
2. That main channel's own "featured channels" section (`channelSections.list`, set by the channel owner) lists the VOD channel. The script re-checks step 2 on every run and stops if a link disappears.

| Organiser | Channel | Role | How it was verified |
|---|---|---|---|
| ESL | ESL Counter-Strike `@eslcs` (UCPq2ETz4aAGo2Z-8JisDPIA) | live streams, clips | Linked as `youtube.com/@ESLCS` from <https://pro.eslgaming.com/tour/cs/>, the `web=` link of the IEM Beijing Liquipedia page |
| ESL | ESL Archives `@eslarchives` (UCXQexglLCaJyTImYLmSO9Ng) | **full-match VODs** | Linked as `youtube.com/channel/UCXQexglLCaJyTImYLmSO9Ng` from the same ESL page, and listed in @eslcs's featured-channels section |
| ESL | ESL Counter-Strike B `@eslcsb` (UCoxKq27R4jcotw4pLjb1jZQ) | second stream | Linked as `youtube.com/@ESLCSB/streams` from the same ESL page, and listed in @eslcs's section |
| PGL | PGL `@pgl` (UC5jpxDZx4yoBo324pMQ91Ww) | live streams, clips | Linked as `youtube.com/pgl` from <https://www.pglesports.com/> |
| PGL | PGL VOD's `@pgl_vods` (UC5nM2vKeikf6_gk45gyM87g) | **full-match VODs** | Listed in @pgl's featured-channels section. The organiser's site doesn't link it directly |
| BLAST | BLAST Premier `@blastpremier` (UC9k--dE_UE0Faxzgb_DDkYQ) | streams, clips | Linked as `youtube.com/blastpremier` from <https://blastpremier.com/> |
| BLAST | BLAST Premier VOD Archive `@blastpremiervod` (UC9O88aQhTVtoxVrCVQalTLQ) | **full-match VODs** | Listed in @blastpremier's featured-channels section (re-checked 2026-11-08). No BLAST event was in the window; its recent uploads are one full-match VOD per match, titled `A vs B - <event> - ...`. To be tested in practice on BLAST Rivals Fall 2026 (2026-11-11 to 15), with a hand-checked sample of 20 links or more |
| StarLadder | StarLadder CS2 `@starladder_cs` (UCx1kGL8Qox1jEiHRMsnn9Ag) | **full-match VODs**, on the main channel | Not in the raw HTML of <https://starladder.com/en/>, which loads its links in the browser: the site's own socials API, <https://json-api.starladder.com/en/v1.0/newstarladder/socials>, returns `https://www.youtube.com/user/cssltv`, and the JS of <https://major.starladder.com/en/> links `https://www.youtube.com/@starladder_cs`; both resolve to this channel id. Its featured channels are a live-stream channel (whole broadcast days) and a highlights channel, neither a VOD channel. The main channel holds one full-match VOD per match next to short clips, titled `A vs B - <event> \| CS2`. Tested on StarSeries Fall 2026 (see "Numbers") |

**Lookup by handle alone is not safe.** `@starladder` resolves to an unrelated personal channel, and `@blastcs` to an empty one.

**The other 2026 S-Tier organisers** (checked 2026-11-08, the same two steps; their PandaScore leagues are in `app/domains/matches/leagues.json`, so their matches are in the data, without VODs):

| Organiser (PandaScore league) | Result | How |
|---|---|---|
| Perfect World (CS Asia Championships) | **Not a VOD source: no full-match VODs on YouTube** | Its site <https://www.pwesports.cn/> links the channel `youtube.com/channel/UC69Mepk2QzyQCL3PwJt21Eg`, which features no other channel and has no recent uploads. The CS Asia Championships site, <https://cac.pwesports.cn/>, names only Chinese platforms |
| FISSURE (FISSURE PLAYGROUND) | **Not a VOD source: no per-match VODs** | The event page <https://fissure.pro/events/fpg2-cs> links `youtube.com/@fissure_cs2` (UCunL4ORE8hiPOJUmOQrVeaQ, Russian), which features no channel and holds whole broadcast days and highlights. An English channel, UCh4neU0C-6XB3OhOzPEAMVg, is linked by neither the site nor the main channel: **not verified**. It holds day streams and highlights, no full matches |
| GAM3RS_X (Thunderpick World Championship) | **Not a VOD source: no per-match VODs** | GAM3RS_X's own site <https://gam3rsx.com/> links no YouTube. The event site, read from a Wayback snapshot of 2026-07-18 because it blocks automated clients, links `youtube.com/@thunderpickesports` (UCDs5D1luLRP2oo3lk71MaCA), which features no channel and holds whole broadcast days and highlights |

Two traps in what those channels hold, for whoever adds one later: highlight videos titled like a match can be long enough for the matcher's length limits, and whole-day streams titled after a match are only kept out by the 6-hour limit. A channel joins the whitelist only for full-match VODs.

### How a match is linked

The script (`match.js`) picks one video per match. It must:
- come from a whitelisted channel of the match's organiser
- be published 0 to 72 h after the start (Liquipedia's scheduled start; PandaScore's actual start, `begin_at`)
- have a title of the form `<A> vs <B> - <event words> - ...` that names both teams in either order. Names come from the match source (Liquipedia's full, short and bracket names, or PandaScore's name and acronym; with and without "Team"/"Esports"/"Gaming"), plus `aliases.json`
- be 20 min to 6 h long, so neither a clip nor a whole broadcast day

If several videos qualify, the earliest one after the start wins.

Every link was then compared with the per-map VOD links that Liquipedia's editors add to the match (`|vod=` in the wikitext). Those are an independent reference, curated by hand.

### Numbers

| | IEM Beijing (ESL) | PGL Bucharest (PGL) | Total |
|---|---|---|---|
| Matches played in the window | 28 | 41 | **69** |
| Got a full-match VOD | 28 | 41 | **69** |
| No VOD | 0 | 0 | **0** |
| Wrong links | 0 | 0 | **0** |
| Same video as Liquipedia's own link | 28 | 40 | 68 |
| Liquipedia had no link yet | 0 | 1 (the grand final, still missing 8 days later) | 1 |

- **Without any manual alias** (`node match.js --no-manual-aliases`): 67 of 69. The two misses were ESL titles spelling PARIVISION as "PVISION". One alias in `aliases.json` closes the gap, so an alias table is a running chore: every new team spelling an organiser invents needs an entry.
- **Upload delay:** every VOD was up within hours of its match. PGL and ESL upload the edited VOD soon after each match ends.
- **One VOD per match** at both organisers, not one per map. Per-map videos would need a different matcher.
- **Rematches.** 2 matches had a second qualifying video: the same pair met again within 72 h (NAVI vs Falcons at IEM, Vitality vs NAVI at PGL). "Earliest after the start" picked correctly both times. If the first meeting's VOD were missing, though, the second meeting's VOD would be linked to the first match. A production matcher should also require the video to come out before the pair's next meeting starts.
- **The trap a naive matcher falls into.** On PGL's main channel, 12 of the 41 matches had a live-stream VOD titled after that match, in the form `[A] <team> vs. <team> - <event> - <stage>`. Those videos are whole broadcast days, far longer than one match. Matching titles without the VOD channel and the length limit would have produced 12 wrong links. The ESL main channel names its day streams by event and day, not by match, so it doesn't have this problem.
- **Stage labels aren't reliable.** ESL left the stage of its third-place VOD's title empty. PGL titled the playoff-deciding Swiss matches (the high pool of rounds 3 and 4, and round 5) "Playoffs". The matcher therefore never relies on the stage in a title.
- **Liquipedia flags aren't fully reliable either.** The match-level `finished=` flag was blank on one finished match (9z vs BetBoom). The script counts a match as played once one team has won the majority of maps, not by that flag.

### The same matching from PandaScore's data

`run.js --source pandascore` took the match list from PandaScore instead (tiers `s` and `a`; league names mapped to organisers in `leagues.json`), and `compare-pandascore.js` paired it with Liquipedia's.

| | Result |
|---|---|
| Matches in the window (PandaScore, all tiers) | 654: 28 "s", 41 "a", 14 "b", 183 "c", 388 "d" |
| Kept (`s` + `a`) / paired with Liquipedia's 69 | 69 / 69, none extra on either side |
| Winner, series score, maps played, best-of | agree on 69 / 69 each |
| Start time | PandaScore's `begin_at` (actual start) is 1 min before to 19 min after Liquipedia's scheduled start, median +4 min |
| VODs linked from PandaScore's list | 69 / 69, and the same video as the Liquipedia run for all 69 |
| Result freshness | each record last edited 1 to 30 min after its `end_at` (median 5 min). This is an upper bound on when the result landed |
| StarSeries Fall 2026 (StarLadder, PandaScore tier "a", 2026-09-17 to 20), checked with the data job's matcher on 2026-11-08 | 14 finished matches, 14 full-match-length uploads on @starladder_cs: 10 linked at first, the other 4 missed because StarLadder writes MIBR as "MIBR.LOS"; with that alias in `app/domains/vods/aliases.json`, 14 of 14, each to the upload naming its two teams, both rematch pairs (Vitality vs FURIA, Aurora vs Vitality) to their own meeting |

PandaScore's free list has no per-match VOD links, so a PandaScore-only product loses the Liquipedia cross-check. That check agreed on all 68 links it could cover here.

### Hand-checked sample (28 matches)

For each match I compared:
- the Liquipedia match (teams, start, maps in order, score) with the linked video's title, publish time and length
- the video's chapter list in its description, where there is one, against Liquipedia's maps
- Liquipedia's per-map timestamps (`?t=`), which must fall inside the video

All 28 are correct. `#` is the match's row in the script's `out/results.md`. The sample covers both events and both main stages (roughly every 3rd row), plus both rematch pairs, both PVISION matches, the PGL grand final and the PGL third-place match. Scores are listed only here, for checking; the site wouldn't show them. The videos' titles, lengths and chapters, which the table first quoted, are stripped (see the top).

| # | Start (UTC) | Event | Match (Liquipedia, score) | Linked VOD | Maps (Liquipedia) | Liquipedia's link | Verdict |
|---|---|---|---|---|---|---|---|
| 1 | 2026-11-02 02:00 | IEM Beijing | Legacy vs The MongolZ (1-2) | [twMlSwT9qeI](https://youtu.be/twMlSwT9qeI) | Mirage, Dust II, Ancient | same video | OK |
| 4 | 2026-11-02 11:45 | IEM Beijing | Natus Vincere vs Team Falcons (2-0) | [jGjLsrSWvgc](https://youtu.be/jGjLsrSWvgc) | Ancient, Mirage | same video | OK |
| 7 | 2026-11-04 09:30 | IEM Beijing | The MongolZ vs FURIA (2-1) | [lW4fo1YSfCA](https://youtu.be/lW4fo1YSfCA) | Inferno, Cache, Dust II | same video | OK |
| 10 | 2026-11-04 04:35 | IEM Beijing | Natus Vincere vs B8 (2-0) | [-HjoIrPc5Sc](https://youtu.be/-HjoIrPc5Sc) | Ancient, Dust II | same video | OK |
| 12 | 2026-11-04 10:20 | IEM Beijing | Natus Vincere vs Team Falcons (2-1) | [O1bVHYEiggI](https://youtu.be/O1bVHYEiggI) | Ancient, Inferno, Mirage | same video | OK |
| 14 | 2026-11-02 07:00 | IEM Beijing | 9z Team vs BetBoom Team (1-2) | [rHFwWNsahxE](https://youtu.be/rHFwWNsahxE) | Dust II, Ancient, Mirage | same video | OK |
| 17 | 2026-11-03 06:00 | IEM Beijing | MOUZ vs BetBoom Team (2-0) | [oxhuCuce5FI](https://youtu.be/oxhuCuce5FI) | Ancient, Mirage | same video | OK |
| 19 | 2026-11-04 07:00 | IEM Beijing | MOUZ vs PARIVISION (2-0) | [HE5dLEyXPOg](https://youtu.be/HE5dLEyXPOg) | Ancient, Mirage | same video | OK |
| 22 | 2026-11-04 02:00 | IEM Beijing | Team Liquid vs TYLOO (2-0) | [Bg0rtkwvWPI](https://youtu.be/Bg0rtkwvWPI) | Mirage, Anubis | same video | OK |
| 25 | 2026-11-06 06:30 | IEM Beijing | PARIVISION vs Natus Vincere (1-2) | [8K77MlqK0AY](https://youtu.be/8K77MlqK0AY) | Mirage, Inferno, Ancient | same video | OK |
| 26 | 2026-11-06 11:00 | IEM Beijing | FURIA vs BetBoom Team (1-2) | [lwjvtUp-ets](https://youtu.be/lwjvtUp-ets) | Ancient, Dust II, Nuke | same video | OK |
| 27 | 2026-11-07 06:30 | IEM Beijing | The MongolZ vs Natus Vincere (2-0) | [xieUDAz7D1E](https://youtu.be/xieUDAz7D1E) | Cache, Inferno | same video | OK |
| 28 | 2026-11-07 09:45 | IEM Beijing | MOUZ vs BetBoom Team (2-0) | [JGvHerBaz5E](https://youtu.be/JGvHerBaz5E) | Ancient, Mirage | same video | OK |
| 30 | 2026-10-24 07:00 | PGL Bucharest | Aurora Gaming vs The MongolZ (1-2) | [6PbaO0zFSks](https://youtu.be/6PbaO0zFSks) | Inferno, Nuke, Dust II | same video | OK |
| 33 | 2026-10-24 12:50 | PGL Bucharest | Legacy vs Nemiga Gaming (0-2) | [HJG1DT3uV5E](https://youtu.be/HJG1DT3uV5E) | Ancient, Dust II | same video | OK |
| 35 | 2026-10-24 16:15 | PGL Bucharest | G2 Esports vs Natus Vincere (0-2) | [1Zuz3qKR-xM](https://youtu.be/1Zuz3qKR-xM) | Nuke, Dust II | same video | OK |
| 38 | 2026-10-25 09:40 | PGL Bucharest | Team Vitality vs The MongolZ (2-0) | [Tnrz0adGkbk](https://youtu.be/Tnrz0adGkbk) | Inferno, Cache | same video | OK |
| 42 | 2026-10-25 13:00 | PGL Bucharest | G2 Esports vs Aurora Gaming (0-2) | [jGDacZjkyNo](https://youtu.be/jGDacZjkyNo) | Cache, Inferno | same video | OK |
| 46 | 2026-10-26 17:00 | PGL Bucharest | BetBoom Team vs Natus Vincere (2-0) | [QQNRNtZDJ1E](https://youtu.be/QQNRNtZDJ1E) | Dust II, Nuke | same video | OK |
| 49 | 2026-10-26 13:40 | PGL Bucharest | Team Spirit vs FURIA (2-1) | [YwF-Dqki9Xw](https://youtu.be/YwF-Dqki9Xw) | Nuke, Ancient, Mirage | same video | OK |
| 54 | 2026-10-27 14:30 | PGL Bucharest | Team Spirit vs MOUZ (1-2) | [urtM5q6WVYo](https://youtu.be/urtM5q6WVYo) | Nuke, Dust II, Mirage | same video | OK |
| 55 | 2026-10-27 17:50 | PGL Bucharest | Team Vitality vs Natus Vincere (2-0) | [c_fWIKhjYsg](https://youtu.be/c_fWIKhjYsg) | Inferno, Dust II | same video | OK |
| 59 | 2026-10-28 11:00 | PGL Bucharest | The MongolZ vs Nemiga Gaming (0-2) | [q1tDOoUWykU](https://youtu.be/q1tDOoUWykU) | Cache, Dust II | same video | OK |
| 63 | 2026-10-29 13:25 | PGL Bucharest | MOUZ vs Aurora Gaming (2-0) | [bbNscBSqFng](https://youtu.be/bbNscBSqFng) | Mirage, Cache | same video | OK |
| 64 | 2026-10-29 10:15 | PGL Bucharest | Team Vitality vs Natus Vincere (2-1) | [BEVTiiowoMA](https://youtu.be/BEVTiiowoMA) | Cache, Mirage, Dust II | same video | OK |
| 66 | 2026-10-30 15:00 | PGL Bucharest | FURIA vs MOUZ (2-1) | [SqCRvXv7EPw](https://youtu.be/SqCRvXv7EPw) | Dust II, Mirage, Cache | same video | OK |
| 68 | 2026-10-31 11:00 | PGL Bucharest | FURIA vs Nemiga Gaming (3-2) | [wDj3pcOgu4Q](https://youtu.be/wDj3pcOgu4Q) | Ancient, Dust II, Cache, Mirage, Nuke | no Liquipedia link yet | OK |
| 69 | 2026-10-31 08:00 | PGL Bucharest | MOUZ vs Team Vitality (2-0) | [qriZVRUZu20](https://youtu.be/qriZVRUZu20) | Nuke, Inferno | same video | OK |

### What the VODs give away

The site wants people to watch on their own schedule, so here is what a VOD leaks before anyone presses play. The findings come from `match.js` over all 69 linked VODs (its figures, computed from YouTube's data, are stripped; see the top). The thumbnails were checked by eye on 6 of them.

| Leak | Finding | Severity for the site |
|---|---|---|
| Score in the title or description | **None** of the 69: titles are `A vs B - Event - Stage` at both organisers | none |
| Winner listed first in the title | About half, so a coin flip: the order doesn't follow the result | none |
| Thumbnail | Neutral cards with both team logos and "VS" (6 of 6 checked: 3 ESL, 3 PGL), no score | none in the sample |
| **Map chapters in the description** | **Most** list one chapter per map played, which shows whether a Bo3 went to 2 or 3 maps. Both organisers' chapters also name highlight moments | high: the YouTube player shows chapters on the progress bar |
| **Video length** | A 3-map Bo3 runs clearly longer than a 2-map one: one length cut-off guessed the map count right for the large majority of the Bo3 VODs | high: the length shows on every thumbnail and on the player |
| Per-map split | Neither organiser splits by map: one video per match, so the number of videos doesn't leak anything | none |
| Stage in the title | Some titles have a stage word (quarter-final, semi-final, grand final, plus PGL's "Playoffs" on playoff-deciding Swiss matches). The *existence* of a semi-final VOD naming two teams tells you who won both quarter-finals | medium: it leaks other matches, not this one |
| Same channel's other uploads | The organiser main channels post Shorts naming who was eliminated or reached a final | medium: only if the site embeds the channel or the viewer browses it. The VOD channels themselves post only full matches |

**What it means for the product:**
- Linking the VOD as-is spoils the map count, through the length and the chapters.
- A spoiler-safe "watch" button should:
  - show the site's own card instead of YouTube's thumbnail and length
  - open the video through an embed or a link with `t=` set past the pre-show. The first chapter starts at 0, so a direct link lands on map 1, and chapters on the progress bar remain a risk once playback starts
- Stage words in titles mean the site shouldn't list upcoming playoff VODs by their YouTube titles.

## 3. Quota

YouTube Data API v3 gives each project 10,000 units per day by default ("Projects that enable the YouTube Data API have a default quota allocation of 100 search.list calls, 100 videos.insert calls, and 10,000 units per day combined for all other endpoints.", <https://developers.google.com/youtube/v3/determine_quota_cost>). `channels.list`, `channelSections.list`, `playlistItems.list` and `videos.list` each cost 1 unit per call (same page). `search.list` isn't used anywhere. It no longer has a unit cost: it has its own bucket of 100 calls per day ("search list 100 quota per day. Each call costs 1 quota.", same page), far too small for polling.

**Measured.** A cold run of the experiment cost **25 units**: 7 `channels.list`, 3 `channelSections.list`, 8 `playlistItems.list` and 7 `videos.list`. That covers a 15-day backfill of 7 channels and 195 uploads.

**Measured on the data job (2026-11-08)**, which polls only the 4 VOD channels (ESL, PGL, BLAST, StarLadder): a run with nothing new costs 4 units (one `playlistItems.list` per channel), plus 1 when it has uploads to read (`videos.list`: new ones and those first seen live), plus 1 when a linked video is due for its weekly refresh (one `videos.list` per 50, on whichever run they fall due), plus 4 once a day for the whitelist check (`channels.list` once, `channelSections.list` per parent). A cold first run cost 9 and a 15-day backfill (`--since`) 10. A day of 15-minute runs is 96 x 4 = **384 units** when nothing is new and **about 580** on a busy day (every run at 6, plus the check): under 6% of the 10,000. A whitelist check that keeps failing retries every run, up to 4 units more each, which still stays under 10%. The job logs each run's units and the day's total.

**Estimated daily use for the site.** It polls only the VOD channels:

- **Per poll:** 1 `playlistItems.list` per channel, which returns the newest 50 uploads, plus 1 `videos.list` for all new ids together (only when there are new ones). The uploads playlist is ordered by each item's `snippet.publishedAt`, when it went public, so a poller can stop at the first item older than its last poll. For streams that time differs by state: it was the live start for those still live, and a time after the end for the finished ones. While a scheduled stream is live, `contentDetails.videoPublishedAt` and `videos.list`'s `publishedAt` still hold the day it was created, days earlier (seen on @eslcs), so neither can be the stop key, and a poller must re-read an id it first saw live (length 0).
- **Once a day:** 1 `channelSections.list` per VOD channel's parent, to re-check the whitelist.
- **For the 30-day storage rule (see risks):** `videos.list` on stored VODs costs 1 unit per 50 videos. 1,000 stored VODs = 20 units per refresh.

| Setup | Units per day | Share of 10,000 |
|---|---|---|
| 3 VOD channels (ESL, PGL, BLAST), every 15 min | 96 polls x (3 + 1) = 384, + 3 = **~390** | 4% |
| 4 VOD channels (ESL, PGL, BLAST, StarLadder: the whitelist since 2026-11-08), every 15 min | 96 x (4 + 1 + 1) = 576, + 4 = **~580** | 6% |
| 4 VOD channels, every 5 min | 288 x 6 = 1,728, + 4 = **~1,732** | 17% |

Polling every 15 min is plenty, because the VODs appeared hours after the start. Even the worst case stays far under the free quota. These are worst-case figures that assume a `videos.list` call on every poll (and, for the data job's row, a refresh call too).

## 4. Risks

| Risk | What could happen | Mitigation |
|---|---|---|
| **License: Liquipedia CC-BY-SA 3.0** | Attribution is mandatory [L5, L6]. Share-alike applies to "derivative works" [L6]. If the site's match pages count as adaptations of Liquipedia content, that content has to be offered under CC-BY-SA too. Bare facts (who played whom, the score) are usually not copyrightable, but I can't tell you for sure how far share-alike reaches here; this is not legal advice. Team logos and other images on Liquipedia have separate licenses [L6] | Credit Liquipedia with a link on every page that shows its data; don't copy its images or logos; keep the site's own rankings and explanations as separate, own text. Owner decision 2 |
| **Single match-data source** | PandaScore can change the free plan, its terms or its tiers; its tiers are its own [P5] (PGL Masters Bucharest was "a"). Liquipedia's API can change or stop "at any time, without notice" [L9], and its terms did change during this experiment [L1, L10]. PandaScore's terms may not cover the site at all; the owner accepted that risk without asking PandaScore (owner decision 1). Liquipedia's results are entered by volunteers (fast at S-Tier events, see Freshness), and a tier can be downgraded after the fact (Liquipedia's S-Tier page notes a possible downgrade "depending on the average rank of attending teams") | Keep the source behind one adapter that outputs the same match shape, as the experiment does for PandaScore and the MediaWiki API (`run.js --source`), so LPDB can replace PandaScore; pick tier-1 by organiser plus tier rather than tier alone; cache everything; alert when an event stops changing during its dates |
| **Liquipedia API rules** | Using the MediaWiki API for data is no longer supported [L10]. Breaking the rate limit, using a generic User-Agent or reading HTML pages (forbidden [L4]) can get the server's IP blocked | The experiment's client now refuses network calls unless `LIQUIPEDIA_MEDIAWIKI_OK=1` is set. It spaces requests 2.5 s / 31 s, caches every response and sends a project User-Agent with the site and repo URLs but no email, although Liquipedia's example includes one [L3] |
| **YouTube API policies** | Stored API data must be deleted or refreshed within 30 days [Y2]. Ads are only allowed on a page with YouTube API data if the page's other content has "enough independent value" without it [Y3]. Scraping YouTube is forbidden [Y4], so there is no legal no-key fallback such as reading YouTube's HTML | Refresh stored VOD records with `videos.list` (1 unit per 50) at least every 30 days and drop deleted ones. The YouTube data this report quoted as evidence was stripped on 2026-11-08 (see the top); the page's value is the ranking and explanation, the VOD link is an extra; the key stays server-side |
| **YouTube's derived-data rule** | API clients must not "use API Data to create new or derived data or metrics" [Y5]. The product's core step, deciding from a video's title which match it shows, arguably creates new data from API data. The policy's own examples are about metrics (likes turned into percentages), so I read it as aimed at metrics rather than at linking a video, but I can't tell you for sure. This report's length and chapter analysis (section 2) is a one-off measurement for the card's spoiler question, and is exactly the kind of derived metric the product must not compute | The product shows only YouTube's own data as returned and stores no derived metrics. **Decided (owner, 2026-11-08):** the reading above is accepted (section 6, decision 5) |
| **Quota** | Exceeding 10,000 units per day stops all reads until the next day | Estimated use is 4 to 17 % (section 3). An extension needs an API Compliance Audit |
| **Spoilers** | Video length and map chapters reveal how many maps were played, in most VODs (section 2) | Show the site's own card and a deep link, not YouTube's thumbnail and length. Owner decision 3 |
| **Whitelist drift** | Organisers add, rename or retire channels; new team spellings appear in titles ("PVISION", "MIBR.LOS"). Perfect World, FISSURE and GAM3RS_X put no full-match VODs on YouTube, so their events get none | The data job re-checks each VOD channel's `featuredBy` link and uploads playlist daily and stops reading a channel that fails; its report lists matches without a VOD after 12 h and unmatched uploads, to fix aliases from |
| **Narrow sample** | 15 days, 2 events, 2 organisers, all English titles, plus StarLadder's StarSeries (above). Per-map uploads weren't seen | The data job's hand-checked sample on BLAST Rivals Fall 2026 (2026-11-11 to 15), then the PGL Major Singapore (from 2026-11-24) |
| **Reliability** | No VOD for a match (none in this sample), or a rematch linked to the wrong meeting when the first VOD is missing | A rematch guard (built in the data job: a VOD must come out before the pair's next meeting starts; the experiment's matcher, with the first meeting's upload removed, did link the wrong video); Liquipedia's own `vod=` links as a cross-check (they agreed on all 68 they had). PandaScore has no such links, so on PandaScore alone, log matches with several candidate uploads for a look |

## 5. Recommendation

**VODs: build on the organisers' official YouTube VOD channels**, read through upload playlists with the whitelist and matcher from this experiment.
- It's free and within YouTube's policies.
- In this window it gave 69 of 69 tier-1 matches the correct full-match VOD within hours, at about 25 quota units.
- The matcher needs only teams, start time, event and organiser from the match source (for PandaScore, the organiser comes from `leagues.json`), so it works with either option below.
- YouTube's rule against "new or derived data" [Y5]: the owner accepted the reading that linking a VOD to its match by title is not a derived metric (section 6, decision 5).

**Match data: PandaScore's free plan, with Liquipedia LPDB as the paid fallback.** In order of preference:

1. **PandaScore free plan (recommended).**
   - Measured on this window, it has everything the matcher and a "What should I watch?" page need: the same 69 tier-1 matches, correct results, results final within 30 minutes, and the same 69 VOD links.
   - It costs nothing and used 7 of its 1,000 requests per hour for the 15-day window.
   - Credit "Source: PandaScore" [P12].
   - Its terms are unclear on four points, a risk the owner accepted (section 6, item 1).
   - **Limits:** its tiers are its own (PGL Masters Bucharest was "a"), so the site needs its own tier rule, e.g. "s" and "a" from known organisers. There are no map names or per-map scores on the free plan.
2. **Liquipedia LPDB, paid.**
   - It's the richer data this experiment first used: Liquipedia's tiers, map names, per-map scores and per-map VOD links. The license is clear (CC-BY-SA, credit Liquipedia).
   - At the June 2026 price it was $49/month per data type [L11]; today's price is unconfirmed.
3. **Not HLTV, Strafe:** their terms forbid it [H1-H4, ST1].
4. **Not the Liquipedia MediaWiki API** under its current terms [L10], unless Liquipedia explicitly says this use is fine.
5. **Valve's repo:** not a match source, but the official input for the later Regional Standings guides once its reuse terms are clear.

**Liquipedia's own `vod=` links** could replace the YouTube matching (68 of 69 here), but they come with the same access question, and the PGL grand final still had none 8 days later while the organiser's VOD was up within hours. Use them as a cross-check, not as the source.

## 6. Decisions for the owner

1. **Match-data source. Decided (owner, 2026-11-08): PandaScore free plan first**, with the "Source: PandaScore" credit, and no request to PandaScore about its terms. The terms risk this accepts is four points: they address "any professional, to the exclusion of any consumer as well as any non-professional" [P9]; they list results under the paid Historical plan [P6, P7], though the free API and docs [P3] give them; they allow reproduction on the Customer's website "to the exclusion of any marketing of said data" [P10]; and they grant rights "By taking out a Subscription and paying its price" [P11], while the free plan costs nothing.
   - The other options were **lpdb-paid** (Liquipedia's data API on a paid plan, for Liquipedia's tiers, map names, per-map scores and per-map VOD links; $49/month per data type per game in June 2026 [L11]) and **none-yet**. LPDB stays the fallback if PandaScore's plan or terms change.

   The experiment's Liquipedia client stays switched off (`LIQUIPEDIA_MEDIAWIKI_OK`). The experiment fetched its Liquipedia data on 2026-11-08 under the terms as read on 2026-11-07 [L1]. I can't tell exactly when that day the new text [L10] went up, so those runs may already have fallen under it. No Liquipedia call was made after I noticed the change.
2. **Liquipedia licensing** (only if the site falls back to LPDB). Accept CC-BY-SA 3.0 for the site: a visible "Data from Liquipedia" credit with a link on every page that uses it, and share-alike for anything that counts as an adaptation of Liquipedia content. If share-alike is unacceptable, get legal advice or ask Liquipedia about other terms.
3. **Spoiler policy.** Show the VOD as the site's own card with a deep link (no YouTube thumbnail, length or chapters before the click), or accept that the YouTube page reveals the map count.
4. **Whitelist scope. Done (2026-11-08):** StarLadder verified and whitelisted, BLAST re-checked; Perfect World, FISSURE and GAM3RS_X have no full-match VODs on YouTube (section 2). BLAST in practice: the hand-checked sample on BLAST Rivals Fall 2026.
5. **YouTube's derived-data rule** [Y5]. **Decided (owner, 2026-11-08): the reading is accepted.** Linking a match to its VOD by title is not a "derived metric"; the product shows YouTube's data as returned and computes no metrics from it.
6. **Next cards** this report suggests, now that item 1 is decided:
   - (a) rerun the experiment on BLAST Rivals Fall 2026 and the PGL Major Singapore, with PandaScore (`run.js --source pandascore`). With the Liquipedia client off there are no reference VOD links, so that card hand-checks the links (at least 20, as here)
   - (b) a scheduled fetcher (match source + VOD channels, 15-minute polling, 30-day refresh) that writes match/VOD records: built, `tools/data-job/`
   - (c) the "What should I watch?" ranking on top of that data
