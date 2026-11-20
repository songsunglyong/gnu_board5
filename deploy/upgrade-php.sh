#!/usr/bin/env bash
# PHP 8.5.13 업데이트 배포 스크립트. 서버에서 실행한다.
#   ssh root@46.250.249.145 'bash -s' < deploy/upgrade-php.sh
# 순서: 소스 반영 확인 → 백업 → 롤백 태그 → 앱·ERP 이미지 재빌드 → 앱·ERP만 교체 → 검증.
# DB 컨테이너와 볼륨은 교체하지 않는다. 비밀번호는 출력하지 않는다.
set -euo pipefail

cd /opt/gamecha-gnuboard5-demo
DAY="$(date +%Y%m%d)"
IMAGE="gamecha-gnuboard5-demo-app"
DC=(docker compose --env-file deploy/.env -f deploy/compose.yaml)
URL="https://gnuboard5-demo.gamecha.xyz"

on_error() {
  echo
  echo "!! 실패했습니다. 롤백이 필요하면:"
  echo "   docker tag ${IMAGE}:rollback-php-app-${DAY} ${IMAGE}:local"
  echo "   ${DC[*]} up -d --no-deps --wait app erp"
}
trap on_error ERR

echo "== 1. 소스 반영 확인"
git pull --ff-only
grep -q 'php:8.5.13-apache' deploy/Dockerfile \
  || { echo "deploy/Dockerfile이 8.5.13이 아닙니다. 로컬에서 커밋·푸시했는지 확인하세요."; exit 1; }
grep -q '80513' scripts/check-php.php \
  || { echo "scripts/check-php.php가 8.5.13 요구로 바뀌지 않았습니다."; exit 1; }

echo "== 2. 백업 (DB, data 볼륨, 배포 설정)"
BK="$(mktemp -d "$PWD/backups/php-upgrade-${DAY}-XXXXXX" 2>/dev/null || { mkdir -p "$PWD/backups"; mktemp -d "$PWD/backups/php-upgrade-${DAY}-XXXXXX"; })"
chmod 700 "$BK"
"${DC[@]}" exec -T db sh -c 'mariadb-dump -uroot -p"$(cat /run/secrets/db_root_password)" --all-databases --single-transaction' | gzip > "$BK/db.sql.gz"
docker run --rm -v gamecha-gnuboard5-demo-data:/d:ro -v "$BK":/b alpine tar czf /b/data.tgz -C /d .
cp deploy/compose.yaml deploy/Dockerfile "$BK"/
test -s "$BK/db.sql.gz" && test -s "$BK/data.tgz"
echo "백업 위치: $BK"

echo "== 3. 롤백 태그"
docker tag "${IMAGE}:local" "${IMAGE}:rollback-php-app-${DAY}"
docker tag "${IMAGE}:local" "${IMAGE}:rollback-php-erp-${DAY}"

echo "== 4. 이미지 재빌드 (앱·ERP 공용 이미지)"
"${DC[@]}" build --pull app

echo "== 5. 앱·ERP 교체 (DB 유지)"
"${DC[@]}" up -d --no-deps --wait app erp

echo "== 6. 검증"
for svc in app erp; do
  echo "-- $svc"
  "${DC[@]}" exec -T "$svc" php -v | head -1
  "${DC[@]}" exec -T "$svc" php -r 'exit(PHP_VERSION_ID >= 80513 ? 0 : 1);'
done
"${DC[@]}" exec -T app apache2 -v | head -1
"${DC[@]}" exec -T app apache2ctl configtest
"${DC[@]}" ps
echo "-- 공개 HTTPS"
curl -fsS -o /dev/null -w "홈 HTTP %{http_code}\n" "$URL/"
curl -fsS -o /dev/null -w "게시판 HTTP %{http_code}\n" "$URL/bbs/board.php?bo_table=free"
if command -v python3 >/dev/null; then
  python3 deploy/verify-captcha.py "$URL"
else
  echo "python3가 없어 verify-captcha.py는 건너뜁니다."
fi
echo
echo "완료: PHP 8.5.13 적용. 이전 이미지는 ${IMAGE}:rollback-php-app-${DAY} 태그로 보관 중입니다."
