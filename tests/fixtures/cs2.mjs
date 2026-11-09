// A small fake world for the data job's tests: PandaScore matches and YouTube uploads, and a fetch that answers the
// four YouTube list endpoints and PandaScore's /csgo/matches, /csgo/tournaments and /csgo/matches/past from them,
// counting the calls.
export const ESL_ARCHIVES = 'UCXQexglLCaJyTImYLmSO9Ng';
export const ESL_MAIN = 'UCPq2ETz4aAGo2Z-8JisDPIA';
export const PGL_VODS = 'UC5nM2vKeikf6_gk45gyM87g';
export const PGL_MAIN = 'UC5jpxDZx4yoBo324pMQ91Ww';
export const BLAST_VODS = 'UC9O88aQhTVtoxVrCVQalTLQ';
export const BLAST_MAIN = 'UC9k--dE_UE0Faxzgb_DDkYQ';

const TEAMS = {
  navi: { id: 3216, slug: 'natus-vincere-cs-go', name: 'Natus Vincere', acronym: 'NAVI' },
  falcons: { id: 3212, slug: 'falcons-esports', name: 'Team Falcons', acronym: 'FAL' },
  mouz: { id: 3240, slug: 'mousesports-cs-go', name: 'MOUZ', acronym: 'MOUZ' },
  parivision: { id: 133719, slug: 'parivision', name: 'PARIVISION', acronym: 'PRV' },
  vitality: { id: 3455, slug: 'vitality-cs-go', name: 'Vitality', acronym: 'VIT' },
};

/**
 * A PandaScore match: `teams` are keys of TEAMS; the first one wins a finished match, 2-1 by default (`score`), and
 * `minutes` are its maps' lengths.
 */
export function rawMatch({ id, teams = ['navi', 'falcons'], start, status = 'finished', league = 'IEM', serie = 'Beijing 2026', tier = 's', name, stage = 'Group Stage', tournamentId = 100, score = [2, 1], bestOf = 3, minutes = [40, 45, 42] }) {
  const opponents = teams.map((key) => ({ opponent: TEAMS[key] }));
  const finished = status === 'finished';
  return {
    id,
    name: name ?? `Match ${id}: ${teams.map((key) => TEAMS[key].acronym).join(' vs ')}`,
    status,
    scheduled_at: start,
    begin_at: status === 'not_started' ? null : start,
    end_at: status === 'finished' ? new Date(Date.parse(start) + 2 * 3600e3).toISOString() : null,
    number_of_games: bestOf,
    winner_id: finished ? TEAMS[teams[0]].id : null,
    forfeit: false,
    modified_at: start,
    tournament_id: tournamentId,
    league: { name: league },
    serie: { full_name: serie },
    tournament: { name: stage, tier },
    opponents,
    results: opponents.map(({ opponent }, i) => ({ team_id: opponent.id, score: finished ? score[i] : 0 })),
    games: Array.from({ length: bestOf }, (_, i) => ({
      position: i + 1,
      status: finished && i < score[0] + score[1] ? 'finished' : 'not_started',
      length: finished && i < score[0] + score[1] ? (minutes[i] ?? 40) * 60 : null,
    })),
  };
}

/** A YouTube upload as videos.list returns it, flattened. */
export function upload({ id, channelId = ESL_ARCHIVES, title, publishedAt, duration = 'PT1H45M', live = 'none' }) {
  return { id, channelId, title, publishedAt, duration, liveBroadcastContent: live };
}

/**
 * The fake APIs. `world.matches` (PandaScore raw), `world.uploads` (by channel id, newest first), `world.featured`
 * (parent channel -> listed channel ids), `world.fail` (endpoint names that answer HTTP 500).
 */
export function fakeApis(world) {
  const calls = [];
  const featured = world.featured ?? { [ESL_MAIN]: [ESL_ARCHIVES], [PGL_MAIN]: [PGL_VODS], [BLAST_MAIN]: [BLAST_VODS] };
  const answer = (body, status = 200) => new Response(JSON.stringify(body), { status, headers: { 'content-type': 'application/json' } });
  async function fetch(url) {
    const { hostname, pathname, searchParams } = new URL(url);
    const endpoint = pathname.split('/').pop();
    calls.push({ hostname, endpoint, params: Object.fromEntries(searchParams) });
    if ((world.fail ?? []).includes(endpoint)) return answer({ error: { message: 'backend error' } }, 500);
    if (hostname === 'api.pandascore.co') {
      const page = Number(searchParams.get('page'));
      const per = Number(searchParams.get('per_page'));
      let items = world.matches;
      // A year of results: the tier s and a tournaments, then their past matches.
      if (pathname === '/csgo/tournaments') {
        const tiers = searchParams.get('filter[tier]').split(',');
        items = [...new Map(world.matches.filter((match) => tiers.includes(match.tournament.tier)).map((match) => [match.tournament_id, { id: match.tournament_id }])).values()];
      } else if (pathname === '/csgo/matches/past') {
        const ids = searchParams.get('filter[tournament_id]').split(',').map(Number);
        items = world.matches.filter((match) => ids.includes(match.tournament_id) && match.status === 'finished');
      }
      return answer(items.slice((page - 1) * per, page * per));
    }
    const all = Object.values(world.uploads).flat();
    switch (endpoint) {
      case 'channels':
        return answer({ items: searchParams.get('id').split(',').map((id) => ({ id, contentDetails: { relatedPlaylists: { uploads: `UU${id.slice(2)}` } } })) });
      case 'channelSections':
        return answer({ items: [{ contentDetails: { channels: featured[searchParams.get('channelId')] ?? [] } }] });
      case 'playlistItems': {
        const channel = `UC${searchParams.get('playlistId').slice(2)}`;
        return answer({ items: (world.uploads[channel] ?? []).map((video) => ({ snippet: { publishedAt: video.publishedAt }, contentDetails: { videoId: video.id } })) });
      }
      case 'videos': {
        const ids = searchParams.get('id').split(',');
        const items = all.filter((video) => ids.includes(video.id)).map((video) => ({
          id: video.id,
          snippet: { channelId: video.channelId, title: video.title, publishedAt: video.publishedAt, liveBroadcastContent: video.liveBroadcastContent },
          contentDetails: { duration: video.duration },
        }));
        return answer({ items });
      }
      default:
        return answer({ error: { message: `no fake for ${endpoint}` } }, 404);
    }
  }
  return { fetch, calls, units: () => calls.filter((call) => call.hostname === 'www.googleapis.com').length };
}
