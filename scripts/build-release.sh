#!/usr/bin/env bash
#
# Sunucuya elle yüklenecek sürüm paketini üretir (bkz. CPANEL_DEPLOY.md).
#
#   scripts/build-release.sh [commit] [çıktı-klasörü]
#
# Commit'in temiz bir kopyasından (git archive; commit'lenmemiş değişiklikler girmez) production vendor/
# (composer --no-dev) ve derlenmiş asset'ler (npm run build) hazırlanır. Sunucuya ait olanlar (.env, storage)
# ve geliştirme dosyaları pakete girmez; storage/ yalnızca boş klasör yapısı olarak girer. Çıktı:
# <çıktı>/<ad>-release-<sha>.zip; önceki sürüm paketleri silinir (CI'da açılmış klasör de kalır).
# CI (.github/workflows/release.yml) de bu script'i kullanır; paket içeriği tek yerde tanımlıdır.

set -euo pipefail

ROOT="$(git rev-parse --show-toplevel)"
REF="${1:-HEAD}"
OUT="${2:-$ROOT/..}"
SHA="$(git -C "$ROOT" rev-parse --short "$REF")"
# Paket adı: RELEASE_NAME (varsayılan: proje klasörünün adı).
PREFIX="${RELEASE_NAME:-$(basename "$ROOT")}-release"
NAME="$PREFIX-$SHA"

# storage/ yalnızca boş klasör yapısıyla (yer tutucu .gitignore dosyaları) girer: ilk kurulumda Laravel'in yazacağı
# klasörler hazır olur, sonraki deploy'larda paket sunucudaki log, cache ve yüklemelere dokunmaz. Bu kurallar EXCLUDES'tan
# önce gelmeli (rsync ilk eşleşen kuralı uygular; '.git*' yer tutucuları da eler).
STORAGE_SKELETON=(
    --exclude='/storage/framework/testing/'
    --include='/storage/' --include='/storage/**/' --include='/storage/**/.gitignore'
    --exclude='/storage/**'
)

# Pakete girmeyenler (rsync deseni; "/" ile başlayan yalnızca kökte eşleşir).
EXCLUDES=(
    '.git*'
    '/node_modules' '/tests' '/deploy' '/design-system' '/scripts'
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

rsync -a "${STORAGE_SKELETON[@]}" "${EXCLUDES[@]/#/--exclude=}" "$WORK/src/" "$OUT/$NAME/"
(cd "$OUT/$NAME" && zip -rqX "$OUT/$NAME.zip" .)

# Çıktı klasöründe yalnızca yeni zip kalır: önceki sürümlerin zip/klasörleri ve açılmış kopya silinir. CI'da açılmış
# kopya kalır, çünkü release.yml artifact olarak onu yükler.
find "$OUT" -maxdepth 1 -name "$PREFIX-*" ! -name "$NAME" ! -name "$NAME.zip" -exec rm -rf {} +
[ -n "${CI:-}" ] || rm -rf "${OUT:?}/$NAME"

echo "==> $OUT/$NAME.zip ($(du -h "$OUT/$NAME.zip" | cut -f1), $(zipinfo -1 "$OUT/$NAME.zip" | grep -vc '/$') dosya)"
