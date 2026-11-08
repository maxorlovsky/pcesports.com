# vods

The organisers' official full-match VODs on YouTube: which channels count, reading their uploads, linking each VOD to
its match, and YouTube's 30-day rule. The data job (`tools/data-job/`) runs it. Depends on `matches`.

- `index.ts` (plain TypeScript, for the data job and later the app):
  - `youtubeClient`: `channels.list`, `channelSections.list`, `playlistItems.list` and `videos.list`, 1 unit each,
    counted. `search.list` is never called.
  - `linkVods`, `candidates`, `missingVods`, `unmatchedUploads`: the matcher and what the job's report lists.
  - `dueForRefresh`, `applyRefresh`, `pruneUploads`: the refresh and how long uploads are kept.
  - `CHANNELS_LIST`, `VOD_CHANNELS`; the types `VodLink`, `Upload`, `Channel`.
- `channels.json`: the whitelist, with how each channel was verified (`docs/feasibility/cs2-data-and-vods.md`,
  section 2). `aliases.json`: team spellings organisers use that PandaScore's names miss, by PandaScore team slug.
- `data/vods.json`: not in the code branches. The data job publishes it on the `data` branch, and
  `tools/data-job/pull.mjs` puts it here for a build (git ignores the folder). Shape: `{ vods: VodLink[] }`.

Invariants:

- **Only whitelisted channels.** A channel joins `channels.json` only after the two-step check: the organiser's own
  site links the main channel, and the main channel's featured-channels section lists the VOD channel. Lookup by
  handle alone is not safe. The job re-checks daily that each VOD channel's parent still features it and that its
  uploads playlist is the listed one, and stops reading a channel that fails, saying so.
- **The matcher:** a video from the organiser's VOD channel, published from the match's start until 72 h later and
  before the same two teams meet again (the rematch guard), titled `A vs B - <event words> ...` with both teams in
  either order, and 20 min to 6 h long (no clips, no broadcast days, no stream still live). Only a played match
  (running or finished, no forfeit) gets one. Any rematch not canceled or forfeited ends the window, even one still
  marked as yet to start, as a status can lag: a missing VOD beats a wrong one. The earliest one wins, and a video
  links one match only. A run whose PandaScore read failed links nothing new: its matches may be stale.
- **Stored links:** a link stays while its match is there and played and its channel is listed. While its upload is
  still kept locally, it must also still fall in the match's window, so a rematch that PandaScore reported late takes
  its VOD back. Once the upload is gone, the link stays as it is: it can't be re-derived without a title.
- **Committed data holds YouTube's ids only:** `matchId`, `videoId`, `channelId`. Titles, lengths and publish times
  stay in the job's local state, are used only to match, and go `KEEP_UPLOADS_DAYS` (14) after they were last read.
  Nothing is computed from YouTube data beyond that match (no lengths turned into map counts, nothing from chapters).
- **YouTube's 30-day rule:** each linked video is re-read with `videos.list` every `REFRESH_DAYS` (7). One YouTube no
  longer returns (deleted, private) or that moved channel is dropped, and any link last read `MAX_AGE_DAYS` (30) ago
  is dropped even when the refresh fails. A new link is held to the same bar: an upload read `REFRESH_DAYS` ago or
  more (one that waited out a PandaScore outage, say) is read again before it is linked, and an upload past
  `KEEP_UPLOADS_DAYS` is pruned before the run matches anything.
- **No spoilers before the click:** what shows a VOD shows the site's own card and a deep link, never YouTube's
  thumbnail, length or chapters (`AGENTS.md`).
