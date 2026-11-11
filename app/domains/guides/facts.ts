// What a guide may write as `{{name}}`: facts that come from the site's own code, so a guide can't fall behind it.
// The rating guide takes every number and label from here (README.md, "Facts from the code").
import { CLOSE_MINUTES, LABELS, LIST_DAYS, MUST_WATCH, OVERTIME_MINUTES, RESULTS_DAYS, UPSET_CHANCE, WORTH_A_WATCH, closeness, eventPoints, stagePoints, strengthPoints } from '../watch/index.ts';

/** What `stagePoints` gives a round of a stage, read from the function itself, so a change there reaches the guide. */
const stage = (round: string, stageName: string) => stagePoints({ name: `${round}: A vs B`, stage: stageName, teams: [] });
const event = (league: string, tier: string) => eventPoints({ league, serie: '', tier });

/** Each fact as the guide shows it. A number is written as code holds it: 3.5, 0.5, -0.5. */
export const GUIDE_FACTS: Record<string, string> = Object.fromEntries(Object.entries({
  mustWatch: MUST_WATCH,
  worthAWatch: WORTH_A_WATCH,
  labelMustWatch: LABELS[0],
  labelWorthAWatch: LABELS[1],
  labelForFans: LABELS[2],
  listDays: LIST_DAYS,
  resultsDays: RESULTS_DAYS,
  grandFinal: stage('Grand Final', 'Playoffs'),
  semiFinal: stage('Semifinal 1', 'Playoffs'),
  bracketFinal: stage('Lower Bracket Final', 'Playoffs'),
  otherPlayoff: stage('Quarterfinal 1', 'Playoffs'),
  thirdPlace: stage('3rd Place Match', 'Playoffs'),
  groupDecider: stage('Elimination Match', 'Group A'),
  otherGroup: stage('Round 1', 'Group Stage'),
  major: event('Major', 's'),
  tierS: event('IEM', 's'),
  tierA: event('IEM', 'a'),
  strengthMin: strengthPoints(Number.NEGATIVE_INFINITY, { median: 0, top: 1 }),
  strengthMax: strengthPoints(Number.POSITIVE_INFINITY, { median: 0, top: 1 }),
  upsetPercent: Math.round(UPSET_CHANCE * 100),
  upset: closeness({ bestOf: 1, maps: [], winnerChance: 0 }),
  overtimeMinutes: OVERTIME_MINUTES,
  overtimeMap: closeness({ bestOf: 1, maps: [{ seconds: OVERTIME_MINUTES * 60 }], winnerChance: null }),
  closeMinutes: CLOSE_MINUTES,
  closeMap: closeness({ bestOf: 1, maps: [{ seconds: CLOSE_MINUTES * 60 }], winnerChance: null }),
}).map(([name, value]) => [name, String(value)]));
