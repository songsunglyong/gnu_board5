# Docker 배포 결과

## 2026-11-16

[우람북스 데모](https://gnuboard5-demo.gamecha.xyz/)를 서버의 `/opt/gamecha-gnuboard5-demo`에 배포했다. [게시판](https://gnuboard5-demo.gamecha.xyz/bbs/board.php?bo_table=free)과 [관리자 로그인](https://gnuboard5-demo.gamecha.xyz/adm/)을 사용할 수 있다.

## 배포 구성

| 항목 | 확인 결과 |
|---|---|
| 그누보드 | 5.6.48, 공식 커밋 `29d4070701715ab39e2ccec664661ff9584a4569` |
| PHP | 8.5.12, Apache 2.4.68 |
| DB | MariaDB 11.4.13 |
| Docker / Compose | 29.9.3 / v5.7.1 |
| 앱 바인딩 | `127.0.0.1:8085` |
| DB 볼륨 | `gamecha-gnuboard5-demo-db` |
| data 볼륨 | `gamecha-gnuboard5-demo-data` |
| 테마 | `uram` |
| 콘텐츠 | DB의 대메뉴 및 홈 데이터 8행 |
| ERP 예제 | 같은 프로젝트의 내부 컨테이너, 호스트 포트 게시 없음 |
| Caddy 사이트 파일 | `/etc/caddy/sites.d/gnuboard5-demo.caddy`, `root:caddy`, 640 |
| data 권한 | `www-data:www-data`, 디렉터리 750 / 파일 640 |

기존 프로젝트 문서와 로컬 설치 구조를 먼저 확인했다. 배포는 새 전용 DB에 우람북스 데모 데이터로 설치했으며 로컬 회원·비밀번호·업로드를 복사하지 않았다. 실제 환경 파일·비밀번호는 서버 전용 파일로 생성했고 Git과 이미지 빌드 컨텍스트에서 제외했다. 최초 관리자 ID는 `uram_admin`이며 초기 비밀번호는 서버의 제한된 `deploy/secrets/admin_password` 파일에 보관한다. 비밀번호 내용은 기록하거나 출력하지 않았다.

## 기존 서비스와 설정 보존

- 최초 점검에서 기존 포트폴리오는 루프백 3000으로 서비스 중이었다. 배포 중 추가된 별도 ERP·JSP 프로젝트도 확인했으며 해당 서비스의 구성을 수정하지 않았다.
- 새 Compose 프로젝트만 생성·재생성했다. 기존 포트폴리오 컨테이너는 재시작 없이 계속 동작했고, 최종 로컬 및 공개 HTTPS 응답은 200이었다.
- SSH·방화벽 설정을 변경하지 않았다.
- Caddy 메인 파일은 import 구조를 유지했다. 전용 사이트 파일만 추가하고, `caddy validate` 성공과 메인 파일 해시 불변을 확인한 뒤 reload했다.

## DNS와 HTTPS

- 서버의 기본 DNS, Cloudflare `1.1.1.1`, Google `8.8.8.8`에서 일반 A 조회와 `+dnssec` 옵션 조회가 모두 `NOERROR`, `46.250.249.145`를 반환했다.
- AAAA 조회는 `NOERROR`이며 주소 응답이 없었다.
- 위 검사는 DNSSEC 옵션을 사용한 응답 일치 검사다. 서명된 DNS 응답임을 의미하는 AD 플래그는 확인되지 않았다.
- Caddy가 Let's Encrypt 인증서를 발급했다. 인증서의 도메인 일치와 신뢰 체인을 검증했으며 HTTPS 응답은 200, 인증서 검증 결과는 0이었다.
- 확인한 인증서 만료 시각은 2027-02-14 13:29:13 UTC다.
- HTTP는 HTTPS로 308 이동한다. 인증서 검증을 비활성화하지 않았다.
- 기존 포트폴리오도 최종 HTTPS 요청에서 200과 정상 인증서 검증 결과를 반환했다. 해당 도메인의 DNS·인증서 설정을 수정하지 않았다.

## 기능 및 영속성 검증

- HTTPS 홈과 전집 상세 페이지에서 DB 콘텐츠를 확인했다.
- 서버의 비밀 파일을 메모리에서 읽어 관리자 로그인과 실제 글쓰기 화면을 확인했다.
- 그누보드 쓰기 토큰을 발급받고 게시글과 PNG 첨부를 HTTP로 저장했다.
- 게시글 ID 1과 첨부 이미지 원본의 SHA-256이 일치함을 확인했다. 검증 게시글은 영속성 확인 자료로 남겼다.
- 설치 경로, DB 설정, 세션 및 캐시 경로는 외부 요청에 403을 반환했다.
- ERP 주문 조회가 로그인 후 정상이며 취소 제외 합계는 338,000원이었다.
- 웹 실행 계정으로 캐시 파일을 작성했다.
- 이 프로젝트의 앱·DB·ERP 컨테이너를 `--force-recreate --wait`로 재생성한 뒤 같은 게시글·첨부 체크섬·캐시 파일을 다시 확인했다. 호스트 재부팅은 수행하지 않았다.
- 최종 앱·DB·내부 ERP 상태는 모두 healthy다.

재현 가능한 명령과 검증 스크립트 사용법은 [Docker 배포 안내](../deploy/README.md)에 있다.

## 남은 작업

- 최초 관리자 이메일을 실제 수신 가능한 주소로 변경한다.
- 데모의 메일 발송은 비활성화되어 있다. 메일 기반 비밀번호 찾기 등이 필요하면 SMTP를 별도 구성한다.
- 정기 백업 및 복구 훈련 정책을 정한다.
- 기존 미완료 항목인 웹 회원가입의 CAPTCHA부터 가입 완료까지 수동 확인한다.

## 자동등록방지 수정 · 2026-11-17

- 증상: 회원가입에서 CAPTCHA 자리에 빈 초기 이미지가 남았다. 테마가 jQuery 및 그누보드 공통 파일에 `type="module"`을 적용하여, 동기 실행되는 스킨/CAPTCHA 코드보다 늦게 로딩되는 것이 원인이었다.
- `theme/uram/head.sub.php`에서 공통 스크립트의 동기 실행을 복구했다. 테마 `site.js`만 모듈로 유지한다. 공식 그누보드 원본은 수정하지 않았다.
- 음성 파일의 403도 수정했다. Apache에서 생성된 CAPTCHA MP3의 정확한 파일명 형식만 허용하고 `Cache-Control: no-store`를 적용한다. 캐시 디렉터리 목록, 임의 MP3, 세션과 DB 설정은 계속 403/404다.
- `python3 deploy/verify-captcha.py https://gnuboard5-demo.gamecha.xyz` 통과: 회원가입 폼의 스크립트 순서, 모듈 오용 방지, JPEG 생성 및 새로고침 결과 변경, 잘못된 답 거절, 음성 파일 응답, 비공개 경로 차단, 정상 HTTPS 검증.
- 동일한 CAPTCHA를 사용하는 회원정보 찾기 화면에서 실제 160×60 이미지 표시와 새로고침 후 URL 변경·이미지 로딩, 콘솔 오류 없음도 확인했다. 이메일 발송은 제출하지 않았다.
- 앱 이미지만 재빌드하고 `up -d --no-deps --wait app`으로 교체했다. DB·ERP 컨테이너와 영속 볼륨, 다른 프로젝트 서비스는 유지했다. Apache 설정 및 테마 PHP 문법 검사를 통과했고 앱·DB·ERP가 healthy다.
- CAPTCHA 정답 입력 및 회원가입 완료는 수행하지 않았다. 해당 수동 확인 항목은 계속 미완료로 남긴다.

## PHP 보안 업데이트 · 2026-11-21 (서버 배포 대기)

- 근거: [KISA PHP 제품 보안 업데이트 권고(2026-11-19)](https://www.boho.or.kr/kr/bbs/view.do?bbsId=B0000133&pageIndex=1&nttId=72318&menuNo=205020). PHP 8.5는 8.5.13 미만이 영향 대상이다.
- 소스: `deploy/Dockerfile`이 `php:8.5.13-apache-bookworm`을 사용하고, `scripts/check-php.php`가 8.5.13 이상을 요구한다.
- **현재 서버는 아직 PHP 8.5.12 이미지로 동작 중이다.** 아래 순서로 배포한다(Apache 패치 때와 같은 방식).
  1. 서버에서 DB·data 백업과 현재 이미지 롤백 태그(`rollback-php-app-20261121`, `rollback-php-erp-20261121`)를 만든다.
  2. 최신 소스를 반영하고 `docker compose build --pull app erp` 후 `up -d --no-deps --wait app erp`로 앱·ERP만 교체한다. DB와 볼륨은 유지한다.
  (1~3은 `deploy/upgrade-php.sh`가 한 번에 수행한다: `ssh root@서버 'bash -s' < deploy/upgrade-php.sh`. 실행 전 로컬에서 변경분을 커밋·푸시해 서버가 `git pull`로 받을 수 있게 한다.)
  3. `docker compose exec app php -v`가 8.5.13이고 Apache가 2.4.71 이상인지 확인한 뒤 `deploy/verify.py`, `deploy/verify-captcha.py`, 공개 HTTPS 응답을 검증한다.
