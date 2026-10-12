#!/usr/bin/env bash
#
# Sunucuya elle yüklenecek sürüm paketini üretir (bkz. CPANEL_DEPLOY.md).
#
#   scripts/build-release.sh [commit] [çıktı-klasörü]
#
# Commit'in temiz bir kopyasından (git archive; commit'lenmemiş değişiklikler girmez) production vendor/
# (composer --no-dev) ve derlenmiş asset'ler (npm run build) hazırlanır. Sunucuya ait olanlar (.env, storage)
# ve geliştirme dosyaları pakete girmez. Çıktı: <çıktı>/<ad>-release-<sha>/ ve aynı adlı .zip.
# CI (.github/workflows/release.yml) de bu script'i kullanır; paket içeriği tek yerde tanımlıdır.

set -euo pipefail

ROOT="$(git rev-parse --show-toplevel)"
REF="${1:-HEAD}"
OUT="${2:-$ROOT/..}"
SHA="$(git -C "$ROOT" rev-parse --short "$REF")"
# Paket adı: RELEASE_NAME (varsayılan: proje klasörünün adı).
NAME="${RELEASE_NAME:-$(basename "$ROOT")}-release-$SHA"

# Pakete girmeyenler (rsync deseni; "/" ile başlayan yalnızca kökte eşleşir).
EXCLUDES=(
    '.git*'
    '/node_modules' '/tests' '/storage' '/deploy' '/design-system' '/scripts'
    '/.env*' '/.claude' '/docker-compose*.yml' '/phpunit.xml' '/*.md'
    '/public/hot' '/public/storage' '/bootstrap/ssr'
)

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

echo "==> $NAME: kaynak ($REF)"
mkdir -p "$WORK/src"
git -C "$ROOT" archive "$SHA" | tar -x -C "$WORK/src"

echo "==> composer install --no-dev"
(cd "$WORK/src" && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist --quiet)

echo "==> npm ci && npm run build"
(cd "$WORK/src" && npm ci --silent && npm run build --silent)

mkdir -p "$OUT"
OUT="$(cd "$OUT" && pwd)"
rm -rf "${OUT:?}/$NAME" "$OUT/$NAME.zip"

rsync -a "${EXCLUDES[@]/#/--exclude=}" "$WORK/src/" "$OUT/$NAME/"
(cd "$OUT/$NAME" && zip -rqX "$OUT/$NAME.zip" .)

echo "==> $OUT/$NAME.zip ($(du -h "$OUT/$NAME.zip" | cut -f1), $(find "$OUT/$NAME" -type f | wc -l | tr -d ' ') dosya)"
