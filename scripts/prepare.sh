#!/bin/sh
set -eu
cd "$(dirname "$0")/.."

mkdir -p .runtime
if [ -e .runtime/gnuboard5 ]; then
    echo '그누보드5가 이미 준비되어 있습니다. 기존 소스와 데이터를 유지합니다.'
    exit 0
fi

stage=$(mktemp -d .runtime/prepare.XXXXXX)
trap 'rm -rf "$stage"' EXIT
git -c advice.detachedHead=false clone --quiet --depth 1 --branch v5.6.48 \
    https://github.com/gnuboard/gnuboard5.git "$stage/source"
expected=29d4070701715ab39e2ccec664661ff9584a4569
[ "$(git -C "$stage/source" rev-parse HEAD)" = "$expected" ] || {
    echo '공식 배포 커밋이 예상 값과 일치하지 않습니다.' >&2
    exit 1
}

mkdir "$stage/web"
git -C "$stage/source" archive HEAD | tar -xf - -C "$stage/web"
mkdir -m 700 "$stage/web/data"
ln -s ../../sample "$stage/web/sample"
# macOS와 Linux에서 기존 디렉터리가 생겼을 때 그 안으로 이동하지 않는다.
python3 - "$stage/web" <<'PY'
import os
import sys
os.rename(sys.argv[1], ".runtime/gnuboard5")
PY
echo '그누보드5 5.6.48 준비 완료'
