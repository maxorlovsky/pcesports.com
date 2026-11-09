// What the home page shows the list with: the labels, best first, and the days it covers. Their own file, so the page
// imports them without the rating's code.
export const LABELS = ['Must watch', 'Worth a watch', 'For fans'] as const;
export type Label = (typeof LABELS)[number];

/** The home page lists the matches of this many days before the build. */
export const LIST_DAYS = 7;
