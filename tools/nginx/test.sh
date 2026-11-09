#!/bin/sh
# Tests tools/nginx/nginx.prod.conf in a throwaway, non-root nginx (the live `nginx -t` needs root):
#   1. the file's TLS rules (below), then `nginx -t` on the file as it is, with test ports, a self-signed certificate
#      and test DH parameters in place of the Let's Encrypt ones;
#   2. with a build (`npm run generate` -> .output/public, or the folder given as $1), it also runs that nginx on the
#      build and checks what each URL answers: pages, the 404 page, the redirects to www, old-urls.conf's 301s and
#      410s (docs/old-urls.md), Let's Encrypt's challenge, served from a scratch `acme` folder, and the three headers
#      of every HTTPS answer.
# Run it before committing an edit to either file: `npm run nginx:test`. It needs nginx and curl, not root.
set -eu

nginx_bin=${NGINX:-/usr/sbin/nginx}
repo_root=$(cd "$(dirname "$0")/../.." && pwd)
build_dir=${1:-$repo_root/.output/public}
work_dir=$(mktemp -d)
pid_file="$work_dir/nginx.pid"
cleanup() {
  if [ -s "$pid_file" ]; then kill "$(cat "$pid_file")" 2>/dev/null || true; fi
  rm -rf "$work_dir"
}
trap cleanup EXIT

http_port=${NGINX_TEST_HTTP_PORT:-18580}
https_port=${NGINX_TEST_HTTPS_PORT:-18543}

conf="$repo_root/tools/nginx/nginx.prod.conf"

# The TLS protocols every site on max-cloud's port 443 runs: they match the other sites' and change only together with
# them (README.md, "On max-cloud").
tls_protocols='TLSv1 TLSv1.1 TLSv1.2'

echo '== TLS rules'
# Read statement by statement, split as nginx's own reader splits them (ngx_conf_read_token: a # or a quote is special
# only where a word starts, a backslash escapes the next character, and a statement may span lines), in both files:
# - every HTTPS server block sets ssl_protocols once, to the line above: nginx adds up repeated ones, and one outside a
#   server block would reach every site on the server;
# - old-urls.conf, which the www block includes, sets no TLS setting at all;
# - the one include is old-urls.conf, so these rules read everything nginx reads: never a shared snippet
#   (/etc/nginx/snippets), which changes with the server, not with this repository;
# - no listen claims default_server (or `default`, its other spelling), which would make this site's TLS settings
#   the port's for every name no site claims.
rule_failures=$(cd "$repo_root/tools/nginx" && awk -v want="$tls_protocols" -v own=/var/www/pcesports.com/tools/nginx/old-urls.conf '
  function fail(message) { print FILENAME " line " start ": " message }
  function add_word() { words = words (words == "" ? "" : " ") word; word = "" }
  function statement(end,   name, args) {
    name = words
    sub(/ .*/, "", name)
    args = substr(words, length(name) + 2)
    if (words != "") {
      if (name == "include" && args != own) fail("includes " args ": the one include is " own)
      if (name == "listen" && args ~ /(^| )default(_server)?( |$)/) fail("a listen claims default_server")
      if (FILENAME == "old-urls.conf" && name ~ /^ssl_/) fail("a TLS setting (" name "), which belongs in nginx.prod.conf")
      if (FILENAME == "nginx.prod.conf" && end == ";" && name == "listen" && depth == 1 && args ~ /(^|:)443( |$)| ssl( |$)/) https = 1
      if (FILENAME == "nginx.prod.conf" && name == "ssl_protocols") {
        if (depth != 1 || !block) fail("ssl_protocols not directly in a server block")
        else protocols++
        if (args != want) fail("ssl_protocols " args ", expected " want)
      }
      if (end == "{") {
        depth++
        if (depth == 1 && name == "server") { block = start; https = 0; protocols = 0 }
      }
    }
    if (end == "}") {
      depth--
      if (depth == 0 && block) {
        if (https && protocols != 1) { start = block; fail("an HTTPS server block with " protocols " ssl_protocols, expected 1") }
        block = 0
      }
    }
    words = ""
  }
  FNR == 1 { depth = 0; block = 0; words = ""; word = ""; at_space = 1; comment = 0; escaped = 0; dq = 0; sq = 0; variable = 0; need_space = 0 }
  {
    line = $0 "\n"
    for (i = 1; i <= length(line); i++) {
      c = substr(line, i, 1)
      space = c == " " || c == "\t" || c == "\r" || c == "\n"
      if (comment) { if (c == "\n") comment = 0; continue }
      if (escaped) { escaped = 0; word = word c; continue }
      if (need_space) {
        need_space = 0
        if (space) { at_space = 1; continue }
        if (c == ";" || c == "{") { at_space = 1; statement(c); continue }
      }
      if (at_space) {
        if (space) continue
        if (c == ";" || c == "{" || c == "}") { statement(c); continue }
        if (c == "#") { comment = 1; continue }
        if (words == "") start = FNR
        at_space = 0
        if (c == "\"") { dq = 1; continue }
        if (c == "'"'"'") { sq = 1; continue }
        variable = c == "$"
        escaped = c == "\\"
        word = word c
        continue
      }
      if (c == "{" && variable) { word = word c; continue }
      variable = 0
      if (c == "\\") { escaped = 1; word = word c; continue }
      if (c == "$") { variable = 1; word = word c; continue }
      if (dq || sq) {
        if ((dq && c == "\"") || (sq && c == "'"'"'")) { dq = 0; sq = 0; need_space = 1; add_word() }
        else word = word c
        continue
      }
      if (space || c == ";" || c == "{") {
        add_word()
        at_space = 1
        if (!space) statement(c)
        continue
      }
      word = word c
    }
  }
' nginx.prod.conf old-urls.conf)
if [ -n "$rule_failures" ]; then
  echo "$rule_failures" | sed '/^$/d; s/^/FAIL /'
  exit 1
fi
echo "ok   ssl_protocols $tls_protocols once in every HTTPS server block, no TLS setting in old-urls.conf, no include but old-urls.conf, no default_server"

mkdir -p "$work_dir/conf" "$work_dir/temp" "$work_dir/acme/.well-known/acme-challenge"
echo 'challenge answer' > "$work_dir/acme/.well-known/acme-challenge/test-token"
openssl req -x509 -newkey rsa:2048 -nodes -days 1 -subj /CN=nginx-test \
  -keyout "$work_dir/conf/test.key" -out "$work_dir/conf/test.crt" 2>/dev/null
# RFC 7919's ffdhe2048, the group of certbot's ssl-dhparams.pem
openssl genpkey -genparam -algorithm DH -pkeyopt group:ffdhe2048 -out "$work_dir/conf/test-dhparams.pem"

# Test ports, the test certificate and DH parameters, and the repository's files and build in place of the live clone's
sed -E \
  -e "s/^([[:space:]]*listen[[:space:]]+(\[::\]:)?)80([[:space:];])/\1$http_port\3/" \
  -e "s/^([[:space:]]*listen[[:space:]]+(\[::\]:)?)443([[:space:];])/\1$https_port\3/" \
  -e "s#^([[:space:]]*ssl_certificate[[:space:]]+)[^;]+;#\\1$work_dir/conf/test.crt;#" \
  -e "s#^([[:space:]]*ssl_certificate_key[[:space:]]+)[^;]+;#\\1$work_dir/conf/test.key;#" \
  -e "s#^([[:space:]]*ssl_dhparam[[:space:]]+)[^;]+;#\\1$work_dir/conf/test-dhparams.pem;#" \
  -e "s#/var/www/pcesports.com/site#$build_dir#g" \
  -e "s#/var/www/pcesports.com/acme#$work_dir/acme#g" \
  -e "s#/var/www/pcesports.com/tools/nginx/#$repo_root/tools/nginx/#g" \
  "$conf" > "$work_dir/conf/site.conf"

cat > "$work_dir/conf/nginx.conf" <<CONF
pid $pid_file;
error_log $work_dir/error.log;
events {}
http {
    include /etc/nginx/mime.types;
    access_log off;
    client_body_temp_path $work_dir/temp/body;
    proxy_temp_path $work_dir/temp/proxy;
    fastcgi_temp_path $work_dir/temp/fastcgi;
    uwsgi_temp_path $work_dir/temp/uwsgi;
    scgi_temp_path $work_dir/temp/scgi;
    include $work_dir/conf/site.conf;
}
CONF

echo '== nginx -t'
"$nginx_bin" -t -e stderr -p "$work_dir" -c "$work_dir/conf/nginx.conf" 2>&1 | sed "s#$work_dir#<tmp>#g"
"$nginx_bin" -t -q -e stderr -p "$work_dir" -c "$work_dir/conf/nginx.conf" 2>/dev/null

if [ ! -f "$build_dir/index.html" ]; then
  echo "No build in $build_dir: run npm run generate to check the URLs too"
  exit 0
fi

echo "== URLs, served from $build_dir"
# Its log goes to a file: the daemon keeps the stream it was given open, so a pipe would never end
"$nginx_bin" -e "$work_dir/error.log" -p "$work_dir" -c "$work_dir/conf/nginx.conf" 2>> "$work_dir/error.log"
failures=0

# check <scheme> <host> <path> <status> [location | text the body must hold]
# An HTTPS answer that nginx's add_header covers (2xx and 3xx: the server block's headers have no `always`) must carry
# the three headers: a location with an add_header of its own would drop them.
check() {
  port=$https_port
  [ "$1" = http ] && port=$http_port
  # A response curl refuses (a broken header) counts as status 000, a failure, rather than stopping the script
  : > "$work_dir/headers"
  out=$(curl -sk -o "$work_dir/body" -D "$work_dir/headers" -w '%{http_code} %{redirect_url}' --resolve "$2:$port:127.0.0.1" "$1://$2:$port$3") || out="${out:-000 }"
  code=${out%% *}
  location=${out#* }
  ok=1
  [ "$code" = "$4" ] || ok=0
  if [ $# -ge 5 ]; then
    case "$5" in
      https://*) [ "$location" = "$5" ] || ok=0 ;;
      *) grep -qF "$5" "$work_dir/body" || ok=0 ;;
    esac
  fi
  missing=
  case "$1 $code" in
    'https 20'[0146] | 'https 30'[123478])
      for header in 'strict-transport-security: max-age=63072000; includeSubdomains' 'x-frame-options: DENY' \
        'x-content-type-options: nosniff'; do
        grep -qiF "$header" "$work_dir/headers" || missing="$missing, no ${header%%:*}"
      done
      [ -z "$missing" ] || ok=0
      ;;
  esac
  if [ $ok = 1 ]; then
    echo "ok   $1://$2$3 -> $code${location:+ $location}"
  else
    echo "FAIL $1://$2$3 -> $code${location:+ $location}$missing, expected $4${5:+ $5}"
    failures=$((failures + 1))
  fi
}

www=www.pcesports.com
canon=https://www.pcesports.com
# Hosts and HTTPS
check http pcesports.com /privacy?a=1 301 "$canon/privacy?a=1"
check http $www / 301 "$canon/"
check https pcesports.com /events/dota 301 "$canon/events/dota"
# Let's Encrypt's challenge, over plain HTTP for both names, from the acme folder, never from the build
check http pcesports.com /.well-known/acme-challenge/test-token 200 'challenge answer'
check http $www /.well-known/acme-challenge/test-token 200 'challenge answer'
check http $www /.well-known/acme-challenge/no-such-token 404
# Pages, readable without JavaScript
check https $www / 200 'What should I watch?'
check https $www /privacy 200 'What we collect'
check https $www /sitemap.xml 200 "<loc>$canon/privacy</loc>"
check https $www /robots.txt 200 "Sitemap: $canon/sitemap.xml"
check https $www /og.png 200
# One address per page
check https $www /privacy/ 301 "$canon/privacy"
check https $www /privacy.html 301 "$canon/privacy"
check https $www '/privacy.html?a=1' 301 "$canon/privacy?a=1"
# A carriage return in the address stays escaped in the redirect, never a header of its own. curl's redirect_url
# escapes it either way, so the raw HTTP/1.1 headers are read too
check https $www '/a%0dSet-Cookie:%20x=1.html' 301 "$canon/a%0DSet-Cookie:%20x=1"
: > "$work_dir/headers"
curl --http1.1 -sk -o /dev/null -D "$work_dir/headers" --resolve "$www:$https_port:127.0.0.1" "https://$www:$https_port/a%0dSet-Cookie:%20x=1.html" || true
if ! grep -q '^HTTP/1.1 301' "$work_dir/headers" || grep -q "$(printf '\r')Set-Cookie" "$work_dir/headers"; then
  echo 'FAIL /a%0dSet-Cookie:%20x=1.html puts a raw carriage return in its Location header'
  failures=$((failures + 1))
fi
check https $www /index.html 301 "$canon/"
check https $www '/index.html?a=1' 301 "$canon/?a=1"
# Not found
check https $www /no-such-page 404 'This page is not here'
check https $www /404 404
check https $www /200 404
check https $www /index 404
check https $www /404.html 404
check https $www /200.html 404
check https $www /_nuxt/no-such-file.js 404
# Old URLs that moved (docs/old-urls.md)
check https $www /events 301 "$canon/"
check https $www /events/ 301 "$canon/"
check https $www /events/counter-strike 301 "$canon/"
check https $www /events/counter-strike/ 301 "$canon/"
check https $www /en 301 "$canon/"
check https $www /en/ 301 "$canon/"
check https $www /ru 301 "$canon/"
check https $www /events/league-of-legends/ 410
check https $www /en/tournaments 410
check https $www /en/streams/1 410
# Old URLs gone for good, with the 404 page as their body
check https $www /events/counter-strike/1234-some-cup 410 'This page is not here'
check https $www /events/yEUAMbM 410
check https $www /user/someone 410
check https $www /article/april-report-2017 410
check https $www /blog 410
check https $www /blog/page/2 410
check https $www /en/member/Alkis 410
check https $www /en/leagueoflegends/eune 410
check https $www /en/smite/eu/2 410
check https $www /ru/smite/eu 410
check https $www /web/static/css/combined.css 410
check https $www /dist/assets/images/pce-logo-big.png 410
check https $www /assets/bg-BC430sfB.jpg 410
check https $www /api/ 410
check https $www /wp-api/pce-api/menu 410
check https $www /images/x.png 410
# Every page the sitemap lists answers 200: a line of old-urls.conf never shadows a page of the site
for loc in $(sed -n 's#.*<loc>\([^<]*\)</loc>.*#\1#p' "$build_dir/sitemap.xml"); do
  check https $www "${loc#"$canon"}" 200
done
# Built files: a page's own script, kept a year
asset=$(cd "$build_dir" && ls _nuxt/*.js | head -n 1)
check https $www "/$asset" 200
if ! curl -sk -o /dev/null -D - --resolve "$www:$https_port:127.0.0.1" "https://$www:$https_port/$asset" | grep -qi '^cache-control: max-age=31536000'; then
  echo "FAIL /$asset is not cached for a year"
  failures=$((failures + 1))
fi

# With OLD_URLS=<file> (a CSV whose first column is a path, like docs/old-urls.csv), also writes what each path
# answers to <file>.answers.csv: path,status,location
if [ -n "${OLD_URLS:-}" ]; then
  echo 'path,status,location' > "$OLD_URLS.answers.csv"
  tail -n +2 "$OLD_URLS" | cut -d, -f1 | while IFS= read -r path; do
    printf '%s,%s\n' "$path" "$(curl -sk -o /dev/null -w '%{http_code},%{redirect_url}' --resolve "$www:$https_port:127.0.0.1" "https://$www:$https_port$path")"
  done >> "$OLD_URLS.answers.csv"
  echo "Answers for $OLD_URLS written to $OLD_URLS.answers.csv"
fi

if [ -s "$work_dir/error.log" ]; then
  echo '== nginx error log'
  sed "s#$work_dir#<tmp>#g" "$work_dir/error.log"
fi
if [ "$failures" -gt 0 ]; then
  echo "$failures URL check(s) failed"
  exit 1
fi
echo 'All URL checks passed'
