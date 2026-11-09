// The watch domain's rules: the team ratings, the rating and its labels, the order, and that neither the list nor
// what the page reads holds a result.
import assert from 'node:assert/strict';
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test } from 'node:test';
import { toMatch } from '../app/domains/matches/index.ts';
import {
  LIST_DAYS, RESULTS_DAYS, WATCH_DATA, closeness, eventPoints, labelOf, mergeResults, rate, recentEntries, replay, shownEntries, stagePoints,
  toResult, watchEntries,
} from '../app/domains/watch/index.ts';
import { rawMatch } from './fixtures/cs2.mjs';

const DAY = 24 * 3600e3;
const START = '2026-11-02T02:00:00Z';
const at = (days) => new Date(Date.parse(START) + days * DAY).toISOString();
const NAVI = 3216;
const FALCONS = 3212;
const SCALE = { median: 1500, top: 1600 };

test('a result is a finished, played tier s or a series between two teams; a later read of it replaces it', () => {
  assert.deepEqual(toResult(rawMatch({ id: 1, start: START })), { id: 1, at: START, tournamentId: 100, teams: [NAVI, FALCONS], winnerId: NAVI, bestOf: 3 });
  assert.equal(toResult(rawMatch({ id: 2, start: START, status: 'running' })), null);
  assert.equal(toResult(rawMatch({ id: 3, start: START, tier: 'b' })), null);
  assert.equal(toResult({ ...rawMatch({ id: 4, start: START }), forfeit: true }), null);
  // Any league counts: the ratings see every tier s and a match, not only the site's
  assert.equal(toResult(rawMatch({ id: 5, start: START, league: 'Esports World Cup' }))?.id, 5);

  const now = new Date(at(1));
  const old = toResult(rawMatch({ id: 6, start: at(-RESULTS_DAYS - 1) }));
  const first = toResult(rawMatch({ id: 7, start: START }));
  const corrected = { ...first, winnerId: FALCONS };
  const earlier = toResult(rawMatch({ id: 8, start: at(-1) }));
  assert.deepEqual(mergeResults([old, first], [corrected, earlier], now), [earlier, corrected]);
});

test('the replay rates each team before each series, and counts its wins and losses in that tournament', () => {
  const results = [
    toResult(rawMatch({ id: 1, start: at(0) })),
    toResult(rawMatch({ id: 2, start: at(1), teams: ['falcons', 'navi'], tournamentId: 200 })),
    toResult(rawMatch({ id: 3, start: at(2) })),
  ];
  const before = replay(results);
  assert.deepEqual([...before.get(1).ratings.values()], [1500, 1500]);
  const [navi, falcons] = [before.get(2).ratings.get(NAVI), before.get(2).ratings.get(FALCONS)];
  assert.ok(navi > 1500 && falcons < 1500 && Math.abs(navi + falcons - 3000) < 1e-9);
  // Each tournament keeps its own record
  assert.deepEqual(before.get(2).records.get(NAVI), [0, 0]);
  assert.deepEqual(before.get(3).records.get(NAVI), [1, 0]);
  assert.deepEqual(before.get(3).records.get(FALCONS), [0, 1]);
});

test('stakes: what the stage decides, then the event', () => {
  const stage = (name, tournament = 'Playoffs', records) => stagePoints({ name: `${name}: A vs B`, stage: tournament, teams: [{ id: 1 }, { id: 2 }] }, records);
  assert.equal(stage('Grand Final'), 3);
  assert.equal(stage('Semifinal 1'), 2);
  assert.equal(stage('Lower Bracket Final'), 2);
  assert.equal(stage('Quarterfinal 2'), 1);
  assert.equal(stage('Third Place Match'), 0.5);
  assert.equal(stage('Upper Bracket Quarterfinal 1', 'Group A'), 0);
  assert.equal(stage('Lower bracket semifinal 1', 'Group A'), 1);
  assert.equal(stage('Decider Match', 'Group B'), 1);
  assert.equal(stage('Winners match', 'Group B'), 1);
  // Swiss: a team on two wins or two losses plays for advancing or going home
  assert.equal(stage('Round 3', 'Stage 1', new Map([[1, [1, 1]], [2, [1, 1]]])), 0);
  assert.equal(stage('Round 3', 'Stage 1', new Map([[1, [2, 0]], [2, [2, 0]]])), 1);
  assert.equal(stage('Round 4', 'Stage 1', new Map([[1, [1, 2]], [2, [2, 1]]])), 1);

  assert.equal(eventPoints({ league: 'IEM', serie: 'Cologne Major 2026', tier: 's' }), 1);
  assert.equal(eventPoints({ league: 'IEM', serie: 'Beijing 2026', tier: 's' }), 0.5);
  assert.equal(eventPoints({ league: 'PGL Masters', serie: 'Bucharest 2026', tier: 'a' }), 0);
});

test('closeness reads only the maps every series plays, and its strongest signal', () => {
  const maps = (...minutes) => minutes.map((m) => ({ seconds: m * 60 }));
  // A best-of-three: map 3 is never read, so a series that went the distance scores as one that did not
  assert.equal(closeness({ bestOf: 3, maps: maps(40, 45), winnerChance: 0.6 }), 0);
  assert.equal(closeness({ bestOf: 3, maps: maps(40, 45, 90), winnerChance: 0.6 }), 0);
  assert.equal(closeness({ bestOf: 3, maps: maps(52, 45), winnerChance: 0.6 }), 0.5);
  assert.equal(closeness({ bestOf: 3, maps: maps(40, 61), winnerChance: 0.6 }), 1);
  // A best-of-five reads maps 1 to 3
  assert.equal(closeness({ bestOf: 5, maps: maps(40, 40, 65), winnerChance: 0.6 }), 1);
  assert.equal(closeness({ bestOf: 5, maps: maps(40, 40, 40, 70), winnerChance: 0.6 }), 0);
  // An upset counts as much as an overtime, never more: the label can't say which it was
  assert.equal(closeness({ bestOf: 3, maps: maps(40, 40), winnerChance: 0.3 }), 1);
  assert.equal(closeness({ bestOf: 3, maps: maps(70, 40), winnerChance: 0.3 }), 1);
  assert.equal(closeness({ bestOf: 3, maps: maps(40, 40), winnerChance: null }), 0);
  assert.equal(closeness({ bestOf: 1, maps: [{ seconds: null }], winnerChance: 0.5 }), 0);

  assert.deepEqual([3.5, 3.49, 1.75, 1.74].map(labelOf), ['Must watch', 'Worth a watch', 'Worth a watch', 'For fans']);
});

test('a 2-0 and a 2-1 with the same first two maps get the same rating: the label never tells the map count', () => {
  const final = (id, score) => toMatch(rawMatch({ id, start: START, name: 'Grand Final: A vs B', stage: 'Playoffs', score, minutes: [52, 44, 70] }));
  const results = [toResult(rawMatch({ id: 9, start: at(-5) }))];
  const before = replay(results);
  for (const [id, other] of [[2, [2, 1]], [3, [2, 0]]]) {
    assert.deepEqual(rate(final(1, [2, 0]), before.get(1), SCALE), rate(final(id, other), before.get(id), SCALE));
  }
  // Unrated teams: no strength, no upset
  assert.deepEqual(rate(final(1, [2, 0]), undefined, SCALE), { label: 'Must watch', stakes: 3.5 });
  assert.equal(rate({ ...final(4, [2, 0]), status: 'running' }, undefined, SCALE), null);
  assert.equal(rate({ ...final(5, [2, 0]), forfeit: true }, undefined, SCALE), null);
});

test('the list: matches with a VOD only, by label, then stakes, then newest, with nothing in it but what the page shows', () => {
  const now = new Date(at(3));
  const raws = [
    rawMatch({ id: 1, start: at(0), name: 'Quarterfinal 1: A vs B', stage: 'Playoffs' }),
    rawMatch({ id: 2, start: at(1), name: 'Grand Final: A vs B', stage: 'Playoffs', teams: ['mouz', 'vitality'] }),
    rawMatch({ id: 3, start: at(0.5), name: 'Round 1: A vs B', stage: 'Group Stage', teams: ['parivision', 'navi'] }),
    rawMatch({ id: 4, start: at(0.2), name: 'Semifinal 1: A vs B', stage: 'Playoffs', minutes: [55, 40] }),
    rawMatch({ id: 5, start: at(2), name: 'Semifinal 2: A vs B', stage: 'Playoffs' }),
    rawMatch({ id: 6, start: at(2.5), name: 'Grand Final: A vs B', stage: 'Playoffs', status: 'running' }),
  ];
  const matches = raws.map(toMatch);
  const links = [1, 2, 3, 4, 6].map((matchId) => ({ matchId, videoId: `vod${matchId}`, channelId: 'c' }));
  const entries = watchEntries(matches, links, raws.map(toResult).filter(Boolean), now);
  assert.deepEqual(entries.map((entry) => [entry.matchId, entry.label]), [[2, 'Must watch'], [4, 'Worth a watch'], [1, 'For fans'], [3, 'For fans']]);
  assert.deepEqual(entries[0], {
    matchId: 2, start: at(1), event: 'IEM Beijing 2026', stage: 'Playoffs · Grand Final', teams: ['MOUZ', 'Vitality'], label: 'Must watch', videoId: 'vod2',
  });
  assert.doesNotMatch(JSON.stringify(entries), /score|winner|mapSeconds|stakes|"2-1"/);

  // The page: the last LIST_DAYS only, cut to what it shows
  const shown = recentEntries(entries, new Date(Date.parse(at(0.3)) + LIST_DAYS * DAY));
  assert.deepEqual(shown.map((entry) => entry.videoId), ['vod2', 'vod3']);
  assert.deepEqual(Object.keys(shown[0]).sort(), ['date', 'event', 'label', 'stage', 'teams', 'videoId']);
  assert.equal(shown[0].date, at(1).slice(0, 10));
});

test('the page reads the data file at build time, and lists nothing without one', () => {
  const root = mkdtempSync(join(tmpdir(), 'pce-watch-'));
  try {
    assert.deepEqual(shownEntries(new Date(at(1)), root), []);
    mkdirSync(join(root, WATCH_DATA, '..'), { recursive: true });
    const entry = { matchId: 1, start: START, event: 'IEM Beijing 2026', stage: 'Playoffs', teams: ['A', 'B'], label: 'For fans', videoId: 'v' };
    writeFileSync(join(root, WATCH_DATA), JSON.stringify({ source: 'PandaScore', entries: [entry] }));
    assert.deepEqual(shownEntries(new Date(at(1)), root).map((shown) => shown.videoId), ['v']);
  } finally {
    rmSync(root, { recursive: true, force: true });
  }
});
