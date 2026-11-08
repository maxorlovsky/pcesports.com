#!/bin/sh
# Tests tools/nginx/nginx.prod.conf in a throwaway, non-root nginx (the live `nginx -t` needs root):
#   1. `nginx -t` on the file as it is, with test ports and a self-signed certificate in place of the Let's Encrypt
#      ones, and copies of the snippets it includes from /etc/nginx/snippets;
#   2. with a build (`npm run generate` -> .output/public, or the folder given as $1), it also runs that nginx on the
#      build and checks what each URL answers: pages, the 404 page, the redirects to www, and old-urls.conf's 301s and
#      410s (docs/old-urls.md).
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

mkdir -p "$work_dir/conf/snippets" "$work_dir/temp"
openssl req -x509 -newkey rsa:2048 -nodes -days 1 -subj /CN=nginx-test \
  -keyout "$work_dir/conf/test.key" -out "$work_dir/conf/test.crt" 2>/dev/null

for snippet in $(sed -nE 's/^[[:space:]]*include[[:space:]]+snippets\/([^;[:space:]]+);.*/\1/p' "$repo_root/tools/nginx/nginx.prod.conf" | sort -u); do
  if [ -r "/etc/nginx/snippets/$snippet" ]; then
    cp "/etc/nginx/snippets/$snippet" "$work_dir/conf/snippets/$snippet"
  else
    echo "snippets/$snippet is not in /etc/nginx/snippets" >&2
    exit 1
  fi
done

# Test ports, the test certificate, and the repository's files and build in place of the live clone's
sed -E \
  -e "s/^([[:space:]]*listen[[:space:]]+(\[::\]:)?)80([[:space:];])/\1$http_port\3/" \
  -e "s/^([[:space:]]*listen[[:space:]]+(\[::\]:)?)443([[:space:];])/\1$https_port\3/" \
  -e "s#^([[:space:]]*ssl_certificate[[:space:]]+)[^;]+;#\\1$work_dir/conf/test.crt;#" \
  -e "s#^([[:space:]]*ssl_certificate_key[[:space:]]+)[^;]+;#\\1$work_dir/conf/test.key;#" \
  -e "s#/var/www/pcesports.com/site#$build_dir#g" \
  -e "s#/var/www/pcesports.com/tools/nginx/#$repo_root/tools/nginx/#g" \
  "$repo_root/tools/nginx/nginx.prod.conf" > "$work_dir/conf/site.conf"

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
# nginx >= 1.25.1 warns that `listen ... http2` is deprecated; production's own config uses it, so it stays
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
check() {
  port=$https_port
  [ "$1" = http ] && port=$http_port
  out=$(curl -sk -o "$work_dir/body" -w '%{http_code} %{redirect_url}' --resolve "$2:$port:127.0.0.1" "$1://$2:$port$3")
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
  if [ $ok = 1 ]; then
    echo "ok   $1://$2$3 -> $code${location:+ $location}"
  else
    echo "FAIL $1://$2$3 -> $code${location:+ $location}, expected $4${5:+ $5}"
    failures=$((failures + 1))
  fi
}

www=www.pcesports.com
canon=https://www.pcesports.com
# Hosts and HTTPS
check http pcesports.com /privacy?a=1 301 "$canon/privacy?a=1"
check http $www / 301 "$canon/"
check https pcesports.com /events/dota 301 "$canon/events/dota"
# Pages, readable without JavaScript
check https $www / 200 'What should I watch?'
check https $www /privacy 200 'What we collect'
check https $www /sitemap.xml 200 "<loc>$canon/privacy</loc>"
check https $www /robots.txt 200 "Sitemap: $canon/sitemap.xml"
check https $www /og.png 200
# One address per page
check https $www /privacy/ 301 "$canon/privacy"
check https $www /privacy.html 301 "$canon/privacy"
check https $www /index.html 301 "$canon/"
# Not found
check https $www /no-such-page 404 'This page is not here'
check https $www /404 404
check https $www /404.html 404
check https $www /200.html 404
check https $www /_nuxt/no-such-file.js 404
# Old URLs that moved (docs/old-urls.md)
check https $www /events 301 "$canon/"
check https $www /events/ 301 "$canon/"
check https $www /events/counter-strike 301 "$canon/"
check https $www /events/league-of-legends/ 301 "$canon/"
check https $www /en 301 "$canon/"
check https $www /en/ 301 "$canon/"
check https $www /ru 301 "$canon/"
check https $www /en/tournaments 301 "$canon/"
check https $www /en/streams/1 301 "$canon/"
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

if grep -v -e 'http2' -e 'ssl_stapling' "$work_dir/error.log" | grep -q .; then
  echo '== nginx error log'
  grep -v -e 'http2' -e 'ssl_stapling' "$work_dir/error.log" | sed "s#$work_dir#<tmp>#g"
fi
if [ "$failures" -gt 0 ]; then
  echo "$failures URL check(s) failed"
  exit 1
fi
echo 'All URL checks passed'
