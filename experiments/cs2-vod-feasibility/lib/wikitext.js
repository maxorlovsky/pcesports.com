// A small wikitext reader: enough to pull Liquipedia's {{Match}} templates apart.

/** Every `{{Name ...}}` occurrence (balanced braces), with its offset. */
export function findTemplates(text, name) {
    const out = [];
    const re = new RegExp(`\\{\\{\\s*${name}\\s*(?=[|}\\n])`, 'g');
    let m;
    while ((m = re.exec(text))) {
        const end = closingBraces(text, m.index);
        if (end < 0) break;
        out.push({ start: m.index, body: text.slice(m.index + 2, end) });
        re.lastIndex = end + 2;
    }
    return out;
}

function closingBraces(text, start) {
    let depth = 0;
    for (let i = start; i < text.length - 1; i++) {
        if (text[i] === '{' && text[i + 1] === '{') { depth++; i++; } else if (text[i] === '}' && text[i + 1] === '}') {
            depth--;
            if (depth === 0) return i;
            i++;
        }
    }
    return -1;
}

/** Top-level parameters of a template body: { 1: 'positional', key: 'value' }. */
export function params(body) {
    const parts = [];
    let depth = 0;
    let link = 0;
    let cur = '';
    for (let i = 0; i < body.length; i++) {
        const two = body.slice(i, i + 2);
        if (two === '{{') { depth++; cur += two; i++; continue; }
        if (two === '}}') { depth--; cur += two; i++; continue; }
        if (two === '[[') { link++; cur += two; i++; continue; }
        if (two === ']]') { link--; cur += two; i++; continue; }
        if (body[i] === '|' && depth === 0 && link === 0) { parts.push(cur); cur = ''; continue; }
        cur += body[i];
    }
    parts.push(cur);
    const out = {};
    let pos = 0;
    for (const part of parts.slice(1)) {
        const clean = part.replace(/<!--[\s\S]*?-->/g, '');
        const eq = clean.indexOf('=');
        const brace = clean.indexOf('{{');
        if (eq > 0 && (brace < 0 || eq < brace)) out[clean.slice(0, eq).trim()] = clean.slice(eq + 1).trim();
        else out[++pos] = clean.trim();
    }
    return out;
}

/** The headings (== ... ==) in force at each offset, outermost first. */
export function headingAt(text, offset) {
    const stack = [];
    const re = /^(={2,6})\s*(.+?)\s*\1\s*$/gm;
    let m;
    while ((m = re.exec(text)) && m.index < offset) {
        const level = m[1].length;
        stack.length = Math.max(0, level - 2);
        stack[level - 2] = m[2].replace(/\{\{Stage\|([^}]*)\}\}/, '$1');
    }
    return stack.filter(Boolean);
}
