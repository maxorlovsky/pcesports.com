# Feasibility: CS2 match data sources and official YouTube VOD matching

Research date: 2026-11-08. Experiment code: [`experiments/cs2-vod-feasibility/`](../../experiments/cs2-vod-feasibility/README.md).

**Question.** Can a hobby site get tier-1 CS2 match data and the matching official full-match VODs reliably, legally and for free?

**Short answer: the VOD side, yes. The match-data side, not for free as of 2026-11-08.**

- **VODs: feasible.** The organisers' own YouTube VOD channels, read through their upload playlists, gave 69 of 69 tier-1 matches over 15 days their full-match VOD:
  - 0 wrong links
  - each VOD up 2 to 6 hours after the match started
  - 25 quota units for the whole backfill
- **Match data: no free source with clear terms for an ad-supported site.**
  - The experiment got its match list from Liquipedia's MediaWiki API, which Liquipedia's terms offered "for use in your own projects" when I read them on 2026-11-07.
  - Read again on 2026-11-08, those terms say "Use of the API for non-editing purposes is not supported" and point to Liquipedia's data API (LPDB).
  - LPDB's free plan is "strictly reserved" for uses that include non-commercial sites that "do not monetize". Its paid plans started at $49/month per data type per game in June 2026.
  - PandaScore's free plan is the only other free candidate, and its terms are unclear for a hobby site.
  - HLTV and Strafe forbid it.
- **The decision this needs from the owner** is section 6, item 1: pay for LPDB, or check PandaScore's terms with them first.

The other caveats are a narrow event sample, and spoilers that come from the VODs' length and chapters rather than their titles. Details below.

## 1. Data sources

All terms were read on 2026-11-08 from each source's own pages (Liquipedia's also on 2026-11-07, see below); every terms claim below links its quote ([L1] etc., listed after the table). Pages that block automated clients were read from a dated Wayback snapshot, noted with the quote. No account was registered and no key other than the owner's YouTube key was used.

| Source | Covers (schedule / results / tier + stage / teams) | Freshness | Limits | Key / sign-up | Cost | Commercial use on an ad-supported site | Attribution | Verdict |
|---|---|---|---|---|---|---|---|---|
| **Liquipedia MediaWiki API** (used by the experiment) | Yes / yes, per map with scores / yes: `liquipediatier`, stage headings, plus editors' per-map VOD links / yes | Volunteer-edited live: on 2026-11-07 the IEM Beijing playoff section was edited 10 times between 08:24 and 11:36 UTC while both semi-finals were played (revision history). The PGL grand-final VOD link was still missing 8 days later | 1 request per 2 s, `action=parse` 1 per 30 s, custom User-Agent with contact info, no scraping of HTML pages [L2, L3, L4] | None | Free [L1] | **No longer for this use.** On 2026-11-07 the terms offered it "for use in your own projects" [L1]. On 2026-11-08 they say it is for editing and "Use of the API for non-editing purposes is not supported" [L10]. The content license itself, CC-BY-SA 3.0, allows commercial use with attribution and share-alike [L5, L6, L7] | Required: "content is from Liquipedia" plus a link [L6] | **Not for the product** under the current terms. The experiment's data was fetched on 2026-11-08 (see section 6) |
| Liquipedia LPDB API ("data API") | Same data as structured queries (matches, tournaments, teams and more, per game) [L11] | Same | "the rate limit your plan provides" [L8]: 1,000 requests per hour on Basic [L11] | "Upon approved request" [L8], an application form [L11] | **Free only for** educational, non-commercial ("do not monetize") and community-feature projects, with open-source code [L12]. **Paid:** Basic "$49/month per Data Type", per game [L11] (June 2026 snapshot) | Allowed on a paid plan; the free plan excludes monetized sites [L12] | Same license [L5] | **The clean paid option.** One CS data type at the June 2026 price is $49/month, about 25 to 50 % of the site's revenue target |
| PandaScore (free "Fixtures" plan) | Schedule, tournaments with an in-house S-D tier, teams [P3, P5, P6]. **Results unclear on the free plan:** the docs say fixtures carry "the final match score and the winning team" [P3], the terms put "Post-match data: results" in the paid Historical plan [P7] | Results "in real-time" [P4] | 1,000 requests per hour [P1]; no browser calls (server only) [P4] | Token on every request [P8]; sign-up needed. `app.pandascore.co/signup` works, `pandascore.co/signup` is a 404 | Free plan 0 EUR, paid "From 400€ per videogame per month" [P2] | **Unclear.** Showing data "only on its website, for information purposes" is allowed [P10], but the terms are for "any professional, to the exclusion of any consumer as well as any non-professional" [P9], and rights come "By taking out a Subscription and paying its price" [P11] | Required: "Source: PandaScore" [P12] | Not tested (no token; owner couldn't register). Option 2 in section 5, only after PandaScore confirms its terms cover a hobby site |
| Valve Regional Standings ([GitHub repo](https://github.com/ValveSoftware/counter-strike_regional_standings)) | **No schedule.** Monthly standings in Markdown, plus per-roster tables of matches (date, opponent, W/L, weights) with no event, score, stage or tier. A one-year match sample JSON (2022-08 to 2023-08) is never updated | Monthly, around the first Monday (2026 commits from 01-05 to 11-02, one in September on a Tuesday/Wednesday); the 2026-11-02 release holds matches up to the last days of October (the #1 roster's newest is 2026-10-31), so it lags a few days at release and up to 5 weeks before the next | GitHub's own | None | Free | **Unclear:** no license file (GitHub reports none) and no reuse terms in the README. Every file credits HLTV as the event-data provider [V1] | None stated | Not a match source. Useful later for the Regional Standings guides, license to be settled first |
| HLTV.org | Everything (schedule, results, stats) | Live | No official API found; Cloudflare blocks automated clients | - | - | **Forbidden:** no commercial exploitation, no scraping or data mining, no similar website [H1, H2, H3], "personal use only" [H4] | - | **Ruled out**, not used |
| GRID (grid.gg) | Tier-1 CS2 circuits incl. IEM, ESL Pro League, BLAST [G2] | - | Old Open Access: 10 requests per minute, never real time [G4] | Application | Open Access page now redirects to plans; "Every plan on this page is commercial and custom-priced to the use case." [G1] | Old Open Access terms: only for the stated purpose, up to 10,000 EUR yearly revenue, data treated as confidential [G3] | - | Not usable today |
| FACEIT Data API | faceit.com competitions [F1]; tier-1 events don't run on FACEIT (inferred) | - | Not found | API key, FACEIT account [F1, F2] | Not found | Terms page blocked to automated clients; not found | - | Not a tier-1 source |
| start.gg API | Community tournaments; tier-1 CS2 isn't there (inferred) | - | 80 requests per 60 s [S1] | Token | Free | **Allowed:** ads may appear next to the data [S2] | Required [S3] | Not a tier-1 source |
| bo3.gg | Claims all CS2 tournaments [B1] (rendered page) | - | No official API; `robots.txt` disallows `/api/` | - | - | Reproduction "allowed only with an active hyperlink" [B2], but without an API it would mean scraping | Hyperlink [B2] | Not used (no API) |
| Abios (now Kambi) | Calendars, results, brackets [A1] | - | 3 requests per s [A1] | Sales contact | Not published | Not found | - | Commercial, sales-only |
| Strafe | - | - | No API found | - | - | **Forbidden:** "not for any commercial use" [ST1] | - | **Ruled out** |

The script uses only the Liquipedia MediaWiki API and the YouTube Data API. No source whose terms forbid scraping or commercial use was touched by it.

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
- [L11] "Basic $49/month per Data Type*", "* Data Types reflected on a per game basis." and the plan table's "Requests per hour" of 1000 for Basic. <http://web.archive.org/web/20260618191933/https://liquipedia.net/api>. The live page <https://liquipedia.net/api> answers automated clients with a "Verify you are human" challenge, so I couldn't read today's version; the second reviewer reported that it now lists only Enterprise plans, which I couldn't verify
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
- [Y4] "You and your API Clients must not, and must not encourage, enable, or require others to, directly or indirectly, scrape YouTube Applications or Google Applications , or obtain scraped YouTube data or content." (same page)

## 2. VOD matching

### Window and matches

- **Window:** 2026-10-24 to 2026-11-07 (UTC, 15 days; `--from 2026-10-24 --to 2026-11-08`).
  - It starts one day earlier than "the 14 days before the run" so that PGL's event is complete.
  - It stops before 2026-11-08, the day of the run, so IEM Beijing's last two matches are out. The third-place match (NAVI vs BetBoom, 04:00 UTC) already had its VOD 2.6 h after the start. The grand final was still being played when the run started.
  - **The owner's default window gives the same result.** For the 14 days before the run (`--from 2026-10-25 --to 2026-11-08`): 61 matches, all 61 linked, 60 match Liquipedia's own link and 1 has no Liquipedia link yet, 0 wrong.
- **"Tier-1"** means Liquipedia's S-Tier (`liquipediatier=S-Tier` in the event infobox, listed by `{{TournamentsList|tier=1}}`), the card's default.
- **Events in the window:** Liquipedia's S-Tier list had two, read with `{{TournamentsList|tier=1|year=2026}}`.
  - IEM Beijing 2026 (organiser ESL, 2026-11-02 to 08): 28 matches.
  - PGL Masters Bucharest 2026 (organiser PGL, 2026-10-24 to 31): 41 matches.
  - No other S-Tier event overlapped the window. The next one, BLAST Rivals Fall 2026, starts on 2026-11-11.
- **Total:** 69 played matches, all Bo3 except one Bo5 (the PGL grand final).

### Official channel whitelist

Only these channels are read (`channels.json`). Every one was verified the same way:
1. The organiser's own website links the main channel.
2. That main channel's own "featured channels" section (`channelSections.list`, set by the channel owner) lists the VOD channel. The script re-checks step 2 on every run and stops if a link disappears.

| Organiser | Channel | Role | How it was verified |
|---|---|---|---|
| ESL | ESL Counter-Strike `@eslcs` (UCPq2ETz4aAGo2Z-8JisDPIA) | live streams, clips | Linked as `youtube.com/@ESLCS` from <https://pro.eslgaming.com/tour/cs/>, the `web=` link of the IEM Beijing Liquipedia page |
| ESL | ESL Archives `@eslarchives` (UCXQexglLCaJyTImYLmSO9Ng) | **full-match VODs** | Linked as `youtube.com/channel/UCXQexglLCaJyTImYLmSO9Ng` from the same ESL page, and listed in @eslcs's "More ESL Counter-Strike!" section |
| ESL | ESL Counter-Strike B `@eslcsb` (UCoxKq27R4jcotw4pLjb1jZQ) | second stream | Linked as `youtube.com/@ESLCSB/streams` from the same ESL page, and listed in @eslcs's section |
| PGL | PGL `@pgl` (UC5jpxDZx4yoBo324pMQ91Ww) | live streams, clips | Linked as `youtube.com/pgl` from <https://www.pglesports.com/>. The API reports this channel's `customUrl` as `@pgl` |
| PGL | PGL VOD's `@pgl_vods` (UC5nM2vKeikf6_gk45gyM87g) | **full-match VODs** | Listed in @pgl's "Follow Our Channels" section. The organiser's site doesn't link it directly |
| BLAST | BLAST Premier `@blastpremier` (UC9k--dE_UE0Faxzgb_DDkYQ) | streams, clips | Linked as `youtube.com/blastpremier` from <https://blastpremier.com/>. The API reports `customUrl` `@blastpremier` |
| BLAST | BLAST Premier VOD Archive `@blastpremiervod` (UC9O88aQhTVtoxVrCVQalTLQ) | **full-match VODs** | Listed in @blastpremier's "Featured channels" section. No BLAST event was in the window, so it's whitelisted but untested |

**Lookup by handle alone is not safe.** `@starladder` resolves to an unrelated personal channel ("April Knapp", 5 subscribers). `@blastcs` resolves to an empty "Blast Major" channel with 0 subscribers.

StarLadder, Perfect World, FISSURE, GAM3RS_X (Thunderpick) and the other S-Tier organisers aren't verified yet. Each needs the same two-step check before its events can get VODs.

### How a match is linked

The script (`match.js`) picks one video per match. It must:
- come from a whitelisted channel of the match's organiser
- be published 0 to 72 h after the scheduled start
- have a title of the form `<A> vs <B> - <event words> - ...` that names both teams in either order. Names come from Liquipedia (full, short and bracket names, with and without "Team"/"Esports"/"Gaming"), plus `aliases.json`
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
- **Upload delay** after the scheduled start: median 3.1 h, maximum 5.9 h (the Bo5 grand final). PGL and ESL upload the edited VOD soon after each match ends.
- **One VOD per match** at both organisers, not one per map. Per-map videos would need a different matcher.
- **Rematches.** 2 matches had a second qualifying video: the same pair met again within 72 h (NAVI vs Falcons at IEM, Vitality vs NAVI at PGL). "Earliest after the start" picked correctly both times. If the first meeting's VOD were missing, though, the second meeting's VOD would be linked to the first match. A production matcher should also require the video to come out before the pair's next meeting starts.
- **The trap a naive matcher falls into.** On PGL's main channel, 12 of the 41 matches had a live-stream VOD titled after that match, e.g. `[A] G2 Esports vs. Natus Vincere - PGL Masters Bucharest 2026 - Group Stage: Opening Round`. Those videos are whole broadcast days of 7 to 12 hours. Matching titles without the VOD channel and the length limit would have produced 12 wrong links. The ESL main channel names its day streams `IEM Beijing 2026 - Day N - FULL SHOW`, so it doesn't have this problem.
- **Stage labels aren't reliable.** ESL titled its third-place VOD `NAVI vs. BB Team - IEM Beijing 2026 -`, with the stage left empty. PGL titled the playoff-deciding Swiss matches (the high pool of rounds 3 and 4, and round 5) "Playoffs". The matcher therefore never relies on the stage in a title.
- **Liquipedia flags aren't fully reliable either.** The match-level `finished=` flag was blank on one finished match (9z vs BetBoom). The script counts a match as played once one team has won the majority of maps, not by that flag.

### Hand-checked sample (28 matches)

For each match I compared:
- the Liquipedia match (teams, start, maps in order, score) with the linked video's title, publish time and length
- the video's chapter list in its description, where there is one, against Liquipedia's maps
- Liquipedia's per-map timestamps (`?t=`), which must fall inside the video

All 28 are correct. `#` is the match's row in the script's `out/results.md`. The sample covers both events and both main stages (roughly every 3rd row), plus both rematch pairs, both PVISION matches, the PGL grand final and the PGL third-place match. Scores are listed only here, for checking; the site wouldn't show them.

| # | Start (UTC) | Event | Match (Liquipedia, score) | Linked VOD | After start, length | Maps (Liquipedia) | Chapters in the description | Liquipedia's link | Verdict |
|---|---|---|---|---|---|---|---|---|---|
| 1 | 2026-11-02 02:00 | IEM Beijing | Legacy vs The MongolZ (1-2) | [twMlSwT9qeI](https://youtu.be/twMlSwT9qeI) "Legacy vs. TheMongolz - IEM Beijing 2026 - Group A" | +4.1 h, 154 min | Mirage, Dust II, Ancient | Mirage, Dust2, Ancient | same video | OK |
| 4 | 2026-11-02 11:45 | IEM Beijing | Natus Vincere vs Team Falcons (2-0) | [jGjLsrSWvgc](https://youtu.be/jGjLsrSWvgc) "Falcons vs. NAVI - IEM Beijing 2026 - Group A" | +3 h, 93 min | Ancient, Mirage | Ancient, Mirage | same video | OK |
| 7 | 2026-11-04 09:30 | IEM Beijing | The MongolZ vs FURIA (2-1) | [lW4fo1YSfCA](https://youtu.be/lW4fo1YSfCA) "TheMongolz vs. FURIA - IEM Beijing 2026 - Group A" | +4.5 h, 159 min | Inferno, Cache, Dust II | none | same video | OK |
| 10 | 2026-11-04 04:35 | IEM Beijing | Natus Vincere vs B8 (2-0) | [-HjoIrPc5Sc](https://youtu.be/-HjoIrPc5Sc) "NAVI vs. B8 - IEM Beijing 2026 - Group A" | +2.8 h, 101 min | Ancient, Dust II | Ancient, Dust2 | same video | OK |
| 12 | 2026-11-04 10:20 | IEM Beijing | Natus Vincere vs Team Falcons (2-1) | [O1bVHYEiggI](https://youtu.be/O1bVHYEiggI) "NAVI vs. Falcons - IEM Beijing 2026 - Group A" | +3.7 h, 132 min | Ancient, Inferno, Mirage | Ancient, Inferno, Mirage | same video | OK |
| 14 | 2026-11-02 07:00 | IEM Beijing | 9z Team vs BetBoom Team (1-2) | [rHFwWNsahxE](https://youtu.be/rHFwWNsahxE) "9z vs. BB Team - IEM Beijing 2026 - Group B" | +3.9 h, 132 min | Dust II, Ancient, Mirage | Dust2, Ancient, Mirage | same video | OK |
| 17 | 2026-11-03 06:00 | IEM Beijing | MOUZ vs BetBoom Team (2-0) | [oxhuCuce5FI](https://youtu.be/oxhuCuce5FI) "MOUZ vs. BB Team - IEM Beijing 2026 - Group B" | +2.6 h, 98 min | Ancient, Mirage | Ancient, Mirage | same video | OK |
| 19 | 2026-11-04 07:00 | IEM Beijing | MOUZ vs PARIVISION (2-0) | [HE5dLEyXPOg](https://youtu.be/HE5dLEyXPOg) "PVISION vs. MOUZ - IEM Beijing 2026 - Group B" | +2.5 h, 87 min | Ancient, Mirage | Ancient, Mirage | same video | OK |
| 22 | 2026-11-04 02:00 | IEM Beijing | Team Liquid vs TYLOO (2-0) | [Bg0rtkwvWPI](https://youtu.be/Bg0rtkwvWPI) "Team Liquid vs. TYLOO - IEM Beijing 2026 - Group B" | +2.4 h, 84 min | Mirage, Anubis | Mirage, Anubis | same video | OK |
| 25 | 2026-11-06 06:30 | IEM Beijing | PARIVISION vs Natus Vincere (1-2) | [8K77MlqK0AY](https://youtu.be/8K77MlqK0AY) "PVISION vs. NAVI - IEM Beijing 2026 - Quarter-final" | +4.9 h, 180 min | Mirage, Inferno, Ancient | Mirage, Inferno, Ancient | same video | OK |
| 26 | 2026-11-06 11:00 | IEM Beijing | FURIA vs BetBoom Team (1-2) | [lwjvtUp-ets](https://youtu.be/lwjvtUp-ets) "FURIA vs. BB Team - IEM Beijing 2026 - Quarter-final" | +4.7 h, 162 min | Ancient, Dust II, Nuke | Ancient, Dust2, Nuke | same video | OK |
| 27 | 2026-11-07 06:30 | IEM Beijing | The MongolZ vs Natus Vincere (2-0) | [xieUDAz7D1E](https://youtu.be/xieUDAz7D1E) "TheMongolz vs. NAVI - IEM Beijing 2026 - Semi-final" | +3.1 h, 114 min | Cache, Inferno | Cache, Inferno | same video | OK |
| 28 | 2026-11-07 09:45 | IEM Beijing | MOUZ vs BetBoom Team (2-0) | [JGvHerBaz5E](https://youtu.be/JGvHerBaz5E) "MOUZ vs. BB Team - IEM Beijing 2026 - Semi-final" | +2.5 h, 88 min | Ancient, Mirage | Ancient, Mirage | same video | OK |
| 30 | 2026-10-24 07:00 | PGL Bucharest | Aurora Gaming vs The MongolZ (1-2) | [6PbaO0zFSks](https://youtu.be/6PbaO0zFSks) "Aurora vs TheMongolz - PGL Masters Bucharest 2026 - ROUND #1" | +4.4 h, 149 min | Inferno, Nuke, Dust II | none | same video | OK |
| 33 | 2026-10-24 12:50 | PGL Bucharest | Legacy vs Nemiga Gaming (0-2) | [HJG1DT3uV5E](https://youtu.be/HJG1DT3uV5E) "Legacy vs Nemiga - PGL Masters Bucharest 2026 - ROUND #1" | +4.1 h, 150 min | Ancient, Dust II | none | same video | OK |
| 35 | 2026-10-24 16:15 | PGL Bucharest | G2 Esports vs Natus Vincere (0-2) | [1Zuz3qKR-xM](https://youtu.be/1Zuz3qKR-xM) "G2 vs NAVI - PGL Masters Bucharest 2026 - ROUND #1" | +3.9 h, 145 min | Nuke, Dust II | none | same video | OK |
| 38 | 2026-10-25 09:40 | PGL Bucharest | Team Vitality vs The MongolZ (2-0) | [Tnrz0adGkbk](https://youtu.be/Tnrz0adGkbk) "Vitality vs TheMongolz - PGL Masters Bucharest 2026 - ROUND #2" | +2.2 h, 79 min | Inferno, Cache | Inferno, Cache | same video | OK |
| 42 | 2026-10-25 13:00 | PGL Bucharest | G2 Esports vs Aurora Gaming (0-2) | [jGDacZjkyNo](https://youtu.be/jGDacZjkyNo) "G2 vs Aurora - PGL Masters Bucharest 2026 - ROUND #2" | +4 h, 124 min | Cache, Inferno | none | same video | OK |
| 46 | 2026-10-26 17:00 | PGL Bucharest | BetBoom Team vs Natus Vincere (2-0) | [QQNRNtZDJ1E](https://youtu.be/QQNRNtZDJ1E) "BB Team vs NAVI - PGL Masters Bucharest 2026 - Playoffs" | +2.6 h, 100 min | Dust II, Nuke | Dust2, Nuke | same video | OK |
| 49 | 2026-10-26 13:40 | PGL Bucharest | Team Spirit vs FURIA (2-1) | [YwF-Dqki9Xw](https://youtu.be/YwF-Dqki9Xw) "Spirit vs FURIA - PGL Masters Bucharest 2026 - ROUND #3" | +4 h, 144 min | Nuke, Ancient, Mirage | Nuke, Ancient, Mirage | same video | OK |
| 54 | 2026-10-27 14:30 | PGL Bucharest | Team Spirit vs MOUZ (1-2) | [urtM5q6WVYo](https://youtu.be/urtM5q6WVYo) "Spirit vs MOUZ - PGL Masters Bucharest 2026 - Playoffs" | +4 h, 143 min | Nuke, Dust II, Mirage | none | same video | OK |
| 55 | 2026-10-27 17:50 | PGL Bucharest | Team Vitality vs Natus Vincere (2-0) | [c_fWIKhjYsg](https://youtu.be/c_fWIKhjYsg) "Vitality vs NAVI - PGL Masters Bucharest 2026 - Playoffs" | +2.5 h, 82 min | Inferno, Dust II | Inferno, Dust2 | same video | OK |
| 59 | 2026-10-28 11:00 | PGL Bucharest | The MongolZ vs Nemiga Gaming (0-2) | [q1tDOoUWykU](https://youtu.be/q1tDOoUWykU) "TheMongolz vs Nemiga - PGL Masters Bucharest 2026 - Playoffs" | +3.3 h, 116 min | Cache, Dust II | none | same video | OK |
| 63 | 2026-10-29 13:25 | PGL Bucharest | MOUZ vs Aurora Gaming (2-0) | [bbNscBSqFng](https://youtu.be/bbNscBSqFng) "Aurora vs MOUZ - PGL Masters Bucharest 2026 - Quarter-final" | +2.2 h, 75 min | Mirage, Cache | Mirage, Cache | same video | OK |
| 64 | 2026-10-29 10:15 | PGL Bucharest | Team Vitality vs Natus Vincere (2-1) | [BEVTiiowoMA](https://youtu.be/BEVTiiowoMA) "Vitality vs NAVI - PGL Masters Bucharest 2026 - Quarter-final" | +4.1 h, 128 min | Cache, Mirage, Dust II | Cache, Mirage, Dust2 | same video | OK |
| 66 | 2026-10-30 15:00 | PGL Bucharest | FURIA vs MOUZ (2-1) | [SqCRvXv7EPw](https://youtu.be/SqCRvXv7EPw) "MOUZ vs FURIA - PGL Masters Bucharest 2026 - Semi-final" | +3.8 h, 134 min | Dust II, Mirage, Cache | Dust2, Mirage, Cache | same video | OK |
| 68 | 2026-10-31 11:00 | PGL Bucharest | FURIA vs Nemiga Gaming (3-2) | [wDj3pcOgu4Q](https://youtu.be/wDj3pcOgu4Q) "Nemiga vs FURIA - PGL Masters Bucharest 2026 - Grand Final" | +5.9 h, 194 min | Ancient, Dust II, Cache, Mirage, Nuke | none | no Liquipedia link yet | OK |
| 69 | 2026-10-31 08:00 | PGL Bucharest | MOUZ vs Team Vitality (2-0) | [qriZVRUZu20](https://youtu.be/qriZVRUZu20) "Vitality vs MOUZ - PGL Masters Bucharest 2026 - Match for 3rd Place" | +2.7 h, 84 min | Nuke, Inferno | Nuke, Inferno | same video | OK |

### What the VODs give away

The site wants people to watch on their own schedule, so here is what a VOD leaks before anyone presses play. Numbers are from `match.js` over all 69 linked VODs. The thumbnails were checked by eye on 6 of them.

| Leak | Finding | Severity for the site |
|---|---|---|
| Score in the title or description | **None** of the 69: titles are `A vs B - Event - Stage` at both organisers | none |
| Winner listed first in the title | 35 of 69, so a coin flip: the order doesn't follow the result | none |
| Thumbnail | Neutral cards with both team logos and "VS" (6 of 6 checked: 3 ESL, 3 PGL), no score | none in the sample |
| **Map chapters in the description** | **56 of 69** list one chapter per map played, e.g. `00:00:00 Ancient Round 1`, `00:39:32 Mirage Round 1`. That shows whether a Bo3 went to 2 or 3 maps. Both organisers' chapters also name highlight moments ("w0nderful 3k & blindfolded kill") | high: the YouTube player shows chapters on the progress bar |
| **Video length** | Bo3 VODs that ended 2-0 run 68 to 150 min, and 2-1 ones run 116 to 181 min. One cut-off at 124 min guesses the map count right for 63 of 68 Bo3 VODs (fitted on the same sample, so slightly optimistic) | high: the length shows on every thumbnail and on the player |
| Per-map split | Neither organiser splits by map: one video per match, so the number of videos doesn't leak anything | none |
| Stage in the title | 20 of 69 titles have a stage word ("Quarter-final", "Semi-final", "Grand Final", plus PGL's "Playoffs" on playoff-deciding Swiss matches). The *existence* of `MOUZ vs. BB Team - Semi-final` tells you who won both quarter-finals | medium: it leaks other matches, not this one |
| Same channel's other uploads | The organiser main channels post Shorts such as "The moment NAVI were eliminated from #IEM Beijing 2026" and "Another GRAND FINAL secured for MOUZ" | medium: only if the site embeds the channel or the viewer browses it. The VOD channels themselves post only full matches |

**What it means for the product:**
- Linking the VOD as-is spoils the map count, through the length and the chapters.
- A spoiler-safe "watch" button should:
  - show the site's own card instead of YouTube's thumbnail and length
  - open the video through an embed or a link with `t=` set past the pre-show. The first chapter starts at 0, so a direct link lands on map 1, and chapters on the progress bar remain a risk once playback starts
- Stage words in titles mean the site shouldn't list upcoming playoff VODs by their YouTube titles.

## 3. Quota

YouTube Data API v3 gives each project 10,000 units per day by default ("Projects that enable the YouTube Data API have a default quota allocation of 100 search.list calls, 100 videos.insert calls, and 10,000 units per day combined for all other endpoints.", <https://developers.google.com/youtube/v3/determine_quota_cost>). `channels.list`, `channelSections.list`, `playlistItems.list` and `videos.list` each cost 1 unit per call (same page). `search.list` isn't used anywhere. It no longer has a unit cost: it has its own bucket of 100 calls per day ("search list 100 quota per day. Each call costs 1 quota.", same page), far too small for polling.

**Measured.** A cold run of the experiment cost **25 units**: 7 `channels.list`, 3 `channelSections.list`, 8 `playlistItems.list` and 7 `videos.list`. That covers a 15-day backfill of 7 channels and 195 uploads.

**Estimated daily use for the site.** It polls only the VOD channels:

- **Per poll:** 1 `playlistItems.list` per channel, which returns the newest 50 uploads, plus 1 `videos.list` for all new ids together (only when there are new ones).
- **Once a day:** 1 `channelSections.list` per VOD channel's parent, to re-check the whitelist.
- **For the 30-day storage rule (see risks):** `videos.list` on stored VODs costs 1 unit per 50 videos. 1,000 stored VODs = 20 units per refresh.

| Setup | Units per day | Share of 10,000 |
|---|---|---|
| 3 VOD channels (ESL, PGL, BLAST), every 15 min | 96 polls x (3 + 1) = 384, + 3 = **~390** | 4% |
| 7 VOD channels (ESL, PGL, BLAST + StarLadder, Perfect World, FISSURE, GAM3RS_X), every 15 min | 96 x (7 + 1) = 768, + 7 = **~775** | 8% |
| 7 VOD channels, every 5 min | 288 x 8 = 2,304, + 7 = **~2,311** | 23% |

Polling every 15 min is plenty, because the VODs appeared 2 to 6 h after the start. Even the worst case stays far under the free quota. These are worst-case figures that assume a `videos.list` call on every poll.

## 4. Risks

| Risk | What could happen | Mitigation |
|---|---|---|
| **License: Liquipedia CC-BY-SA 3.0** | Attribution is mandatory [L5, L6]. Share-alike applies to "derivative works" [L6]. If the site's match pages count as adaptations of Liquipedia content, that content has to be offered under CC-BY-SA too. Bare facts (who played whom, the score) are usually not copyrightable, but I can't tell you for sure how far share-alike reaches here; this is not legal advice. Team logos and other images on Liquipedia have separate licenses [L6] | Credit Liquipedia with a link on every page that shows its data; don't copy its images or logos; keep the site's own rankings and explanations as separate, own text. Owner decision 2 |
| **Single source: Liquipedia** | The API can change or stop "at any time, without notice" [L9], and its terms did change during this experiment [L1, L10]. Results are entered by volunteers (fast at S-Tier events, see Freshness), and a tier can be downgraded after the fact (Liquipedia's S-Tier page notes a possible downgrade "depending on the average rank of attending teams") | Use LPDB on a paid plan, not the MediaWiki API; cache everything; alert when an event stops changing during its dates. A second source (PandaScore) would need its terms settled first |
| **Liquipedia API rules** | Using the MediaWiki API for data is no longer supported [L10]. Breaking the rate limit, using a generic User-Agent or reading HTML pages (forbidden [L4]) can get the server's IP blocked | The experiment's client now refuses network calls unless `LIQUIPEDIA_MEDIAWIKI_OK=1` is set. It spaces requests 2.5 s / 31 s, caches every response and sends a project User-Agent with the site and repo URLs but no email, although Liquipedia's example includes one [L3] |
| **YouTube API policies** | Stored API data must be deleted or refreshed within 30 days [Y2]. Ads are only allowed on a page with YouTube API data if the page's other content has "enough independent value" without it [Y3]. Scraping YouTube is forbidden [Y4], so there is no legal no-key fallback such as reading YouTube's HTML | Refresh stored VOD records with `videos.list` (1 unit per 50) at least every 30 days and drop deleted ones; the page's value is the ranking and explanation, the VOD link is an extra; the key stays server-side |
| **Quota** | Exceeding 10,000 units per day stops all reads until the next day | Estimated use is 4 to 23 % (section 3). An extension needs an API Compliance Audit |
| **Spoilers** | Video length and map chapters reveal how many maps were played: the chapters in 56 of 69 VODs, the length in 63 of 68 Bo3 VODs (section 2) | Show the site's own card and a deep link, not YouTube's thumbnail and length. Owner decision 3 |
| **Whitelist drift** | Organisers add, rename or retire channels; new team spellings appear in titles ("PVISION"). BLAST's VOD channel is verified but untested, and at least four other 2026 S-Tier organisers (StarLadder, Perfect World, FISSURE, GAM3RS_X) aren't verified at all | Re-check `featuredBy` on every run (already in `fetch-vods.js`); log matches without a VOD after 12 h and unmatched uploads, and fix aliases from that log |
| **Narrow sample** | 15 days, 2 events, 2 organisers, all English titles. Chinese-language or per-map uploads (other organisers) weren't seen | Rerun the experiment on BLAST Rivals Fall 2026 (2026-11-11 to 15) and on the PGL Major Singapore (from 2026-11-24) before building the product |
| **Reliability** | No VOD for a match (none in this sample), or a rematch linked to the wrong meeting when the first VOD is missing | The rematch guard in section 2; Liquipedia's own `vod=` links as a cross-check (they agreed on all 68 they had) |

## 5. Recommendation

**VODs: build on the organisers' official YouTube VOD channels**, read through upload playlists with the whitelist and matcher from this experiment.
- It's free and within YouTube's policies.
- In this window it gave 69 of 69 tier-1 matches the correct full-match VOD within 6 hours, at about 25 quota units.
- The matcher needs only teams, start time, event and organiser from the match source, so it works with any of the options below.

**Match data: none of the free sources is good enough as of 2026-11-08.** In order of preference:

1. **Liquipedia LPDB, paid (recommended if the owner accepts the cost).**
   - It's the same data this experiment used: tier, stage, per-map scores and per-map VOD links, from the best-kept CS2 wiki.
   - The license is clear (CC-BY-SA, credit Liquipedia).
   - At the June 2026 price, one data type for CS was $49/month [L11]. Whether "matches" alone is enough, and today's price, need Liquipedia's answer (their form or Discord).
2. **PandaScore free plan, only after PandaScore confirms two things in writing:**
   - that a one-person ad-supported site is a "professional" Customer under their terms [P9]
   - that the free plan returns match results [P3 vs P7]

   It's free, but it needs a sign-up: `app.pandascore.co/signup` works, while the URL that 404s is `pandascore.co/signup`. Its tiers are its own, not Liquipedia's [P5].
3. **Not HLTV, Strafe:** their terms forbid it [H1-H4, ST1].
4. **Not the Liquipedia MediaWiki API** under its current terms [L10], unless Liquipedia explicitly says this use is fine.
5. **Valve's repo:** not a match source, but the official input for the later Regional Standings guides once its reuse terms are clear.

**Liquipedia's own `vod=` links** could replace the YouTube matching (68 of 69 here), but they come with the same access question, and the PGL grand final still had none 8 days later while the organiser's VOD was up within 6 hours. Use them as a cross-check, not as the source.

## 6. Decisions for the owner

1. **Match-data source.** Choose one:
   - **lpdb-paid:** apply for LPDB on a paid plan ($49/month per data type per game in June 2026 [L11]; confirm today's price and which data types CS2 needs)
   - **pandascore-check:** email PandaScore the two questions in section 5 first, and sign up only if both answers are yes
   - **none-yet:** park the product until one of these is settled

   Until then the experiment's Liquipedia client stays switched off (`LIQUIPEDIA_MEDIAWIKI_OK`). The experiment fetched its Liquipedia data on 2026-11-08 under the terms as read on 2026-11-07 [L1]. I can't tell exactly when that day the new text [L10] went up, so those runs may already have fallen under it. No Liquipedia call was made after I noticed the change.
2. **Liquipedia licensing** (if lpdb-paid). Accept CC-BY-SA 3.0 for the site: a visible "Data from Liquipedia" credit with a link on every page that uses it, and share-alike for anything that counts as an adaptation of Liquipedia content. If share-alike is unacceptable, get legal advice or ask Liquipedia about other terms.
3. **Spoiler policy.** Show the VOD as the site's own card with a deep link (no YouTube thumbnail, length or chapters before the click), or accept that the YouTube page reveals the map count.
4. **Whitelist scope.** Verify the remaining S-Tier organisers (StarLadder, Perfect World, FISSURE, GAM3RS_X, BLAST in practice) the same two-step way before launch.
5. **Next cards** this report suggests, once item 1 is decided:
   - (a) rerun the experiment on BLAST Rivals Fall 2026 and the PGL Major Singapore, with the chosen match source
   - (b) a scheduled fetcher (match source + VOD channels, 15-minute polling, 30-day refresh) that writes match/VOD records
   - (c) the "What should I watch?" ranking on top of that data
