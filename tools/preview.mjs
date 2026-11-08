// Serves the static build (.output/public) the way production's nginx does (tools/nginx/nginx.prod.conf), for a look
// before a pull request: /<name> answers <name>.html, and a missing file the 404 page with a 404.
//   npm run preview   (after npm run generate; PORT=<port> picks the port, 3000 by default)
// Redirects and old URLs are nginx's own: `npm run nginx:test` checks those.
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { extname, join, normalize, resolve } from 'node:path';

const ROOT = resolve(import.meta.dirname, '..', '.output', 'public');
const PORT = Number(process.env.PORT ?? 3000);
const TYPES = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json',
  '.xml': 'application/xml',
  '.txt': 'text/plain; charset=utf-8',
  '.png': 'image/png',
  '.ico': 'image/x-icon',
  '.woff2': 'font/woff2',
};

async function file(path) {
  try {
    return (await stat(path)).isFile() ? path : null;
  } catch {
    return null;
  }
}

try {
  await stat(join(ROOT, 'index.html'));
} catch {
  console.error(`No build in ${ROOT}: run npm run generate first`);
  process.exit(1);
}

createServer(async (request, response) => {
  let path;
  try {
    path = normalize(decodeURIComponent(new URL(request.url ?? '/', 'http://localhost').pathname));
  } catch {
    response.writeHead(400).end();
    return;
  }
  const found = path.includes('\0') ? null : (path === '/' ? await file(join(ROOT, 'index.html')) : (await file(join(ROOT, path))) ?? (await file(join(ROOT, `${path}.html`))));
  const served = found && found.startsWith(ROOT) ? found : join(ROOT, '404.html');
  response.writeHead(served === found ? 200 : 404, { 'content-type': TYPES[extname(served)] ?? 'application/octet-stream' });
  response.end(await readFile(served));
}).listen(PORT, '127.0.0.1', () => console.log(`Previewing ${ROOT} at http://localhost:${PORT}`));
