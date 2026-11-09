// The three labels, best first: their own file, so the page imports them without the rating's code.
export const LABELS = ['Must watch', 'Worth a watch', 'For fans'] as const;
export type Label = (typeof LABELS)[number];
