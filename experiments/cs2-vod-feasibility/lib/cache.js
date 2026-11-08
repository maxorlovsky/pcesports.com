import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

export const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
export const CACHE_DIR = join(ROOT, 'cache');
export const OUT_DIR = join(ROOT, 'out');

const keyFile = (ns, key) => join(CACHE_DIR, ns, `${createHash('sha1').update(key).digest('hex')}.json`);

export async function cached(ns, key, produce) {
    const file = keyFile(ns, key);
    try {
        return JSON.parse(await readFile(file, 'utf8')).value;
    } catch {
        const value = await produce();
        await mkdir(dirname(file), { recursive: true });
        await writeFile(file, JSON.stringify({ key, fetchedAt: new Date().toISOString(), value }));
        return value;
    }
}

export async function writeJson(name, data) {
    await mkdir(OUT_DIR, { recursive: true });
    await writeFile(join(OUT_DIR, name), `${JSON.stringify(data, null, 2)}\n`);
}

export async function readJson(name) {
    return JSON.parse(await readFile(join(OUT_DIR, name), 'utf8'));
}
