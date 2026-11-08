// --from YYYY-MM-DD --to YYYY-MM-DD (UTC, `to` exclusive). Default: the 14 days before today (UTC),
// so today's matches, which can't have a VOD yet, stay out.
export function window(argv) {
    const arg = (name) => {
        const i = argv.indexOf(`--${name}`);
        return i >= 0 ? argv[i + 1] : undefined;
    };
    const day = (s) => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) throw new Error(`Bad date ${s}, expected YYYY-MM-DD`);
        return new Date(`${s}T00:00:00Z`);
    };
    const today = new Date(new Date().toISOString().slice(0, 10) + 'T00:00:00Z');
    const to = arg('to') ? day(arg('to')) : today;
    const from = arg('from') ? day(arg('from')) : new Date(to.getTime() - 14 * 86400000);
    if (!(from < to)) throw new Error('--from must be before --to');
    return { from, to };
}
