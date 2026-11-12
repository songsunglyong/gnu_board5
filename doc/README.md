# 설치 및 실행

Git, Python 3, PHP 8.5.12 이상인 안정판 8.5.x, MariaDB가 필요하다. PHP 확장은 실행 스크립트가 확인한다. 패치 전 PHP를 선택하면 서버가 시작되지 않는다. PHP 8.5는 그누보드7의 Composer 요구사항 `^8.2`에도 해당한다.

[PHP 8.5.12 공식 발표](https://www.php.net/releases/8_5_12.php) · [그누보드7 PHP 요구사항](https://github.com/gnuboard/g7/blob/main/composer.json)

```sh
git clone https://github.com/songsunglyong/gnu_board5.git
cd gnu_board5
sh scripts/prepare.sh
```

1. 로컬 MariaDB를 실행한다. 관리 계정으로 아래 SQL을 실행하고 예시 비밀번호는 별도로 정한 값으로 바꾼다.

   ```sql
   CREATE DATABASE g5_sample CHARACTER SET utf8mb4;
   CREATE USER 'g5_sample'@'127.0.0.1' IDENTIFIED BY 'replace-with-your-password';
   GRANT ALL PRIVILEGES ON g5_sample.* TO 'g5_sample'@'127.0.0.1';
   ```

2. 저장소 루트에서 `sh scripts/serve.sh`를 실행한다. PHP 경로를 지정하려면 `PHP_BIN=/path/to/php sh scripts/serve.sh`를 사용한다. 기본 포트는 8080이며 `PORT`로 바꿀 수 있다.
3. [로컬 설치 화면](http://127.0.0.1:8080/install/)에서 DB 호스트 `127.0.0.1`, 사용자와 DB 이름 `g5_sample`, 앞에서 설정한 DB 비밀번호를 입력한다. 테이블 접두사는 `g5_`를 사용하고 쇼핑몰 설치는 해제한다. 관리자 계정은 이 화면에서 정한다.
4. 다른 터미널에서 `php scripts/configure-board.php`를 실행한다. 서버에서 별도 PHP를 선택했다면 이 명령도 같은 바이너리로 실행한다. 이 명령은 기본 자유게시판을 샘플 게시판으로 설정한다. 이어서 같은 PHP로 `php scripts/configure-aram.php`를 실행해 우람북스 전용 DB 테이블과 초기 데이터를 설치한다.
5. [샘플 시작 화면](http://127.0.0.1:8080/)에서 회원가입 후 일반 회원으로 로그인한다. [샘플 게시판](http://127.0.0.1:8080/bbs/board.php?bo_table=free)에서 글을 작성하고, 목록과 상세 화면에서 확인하고, 수정한 뒤 삭제한다. 로그아웃 상태에서는 글을 조회할 수 있고 글쓰기에는 로그인이 필요하다.

PHP와 MariaDB는 로컬 개발용으로 사용한다. DB 설정과 데이터는 Git에서 제외되는 `.runtime/`에 저장된다.

## 검증 환경

PHP 8.5.12와 MariaDB 11.4.13에서 설치, 일반 회원 로그인·로그아웃, 게시글 CRUD, 비회원 읽기와 쓰기 제한, 다른 회원의 수정 제한을 확인했다. 테스트 회원은 검증용 DB에 준비했으며, 웹 회원가입의 CAPTCHA는 수동 확인 항목이다.

## ERP 주문 조회 샘플

그누보드 서버와 MariaDB를 실행한 상태에서 별도 터미널로 모의 ERP를 시작한다.

```sh
sh scripts/serve-erp.sh
```

서버 실행에 별도 PHP를 사용했다면 이 명령에도 같은 `PHP_BIN`을 지정한다. 모의 ERP는 `127.0.0.1:8081`을 사용하므로 그누보드의 `PORT`에는 다른 포트를 사용한다. 그누보드 서버의 PHP에서 `allow_url_fopen`을 활성화한다.

로그인 후 [ERP 주문 조회](http://127.0.0.1:8080/sample/orders.php)를 직접 연다. 기본 데이터에는 주문 4건이 있으며, 취소 1건을 제외한 주문금액 합계는 338,000원이다. 서울 샘플 판매처와 주문 확정 상태를 함께 선택하면 1건, 116,000원이 표시된다.

그누보드5는 `GET http://127.0.0.1:8081/api/v1/orders`에서 `orders` 배열을 읽는다. `sample-erp/orders.json`에 가상 도서와 주문 데이터가 있다. 모의 ERP를 중지하면 주문 화면에 오류 안내와 HTTP 502 응답이 표시된다.

PHP 8.5.12의 로컬 HTTP 환경에서 비회원 로그인 이동, 로그인 후 주문 4건과 합계, 판매처·상태 조합 필터, 빈 결과, 모의 ERP 중단 시 502 응답을 확인했다.

## 우람북스 홈페이지 샘플

그누보드 설치 후 [로컬 홈페이지](http://127.0.0.1:8080/)를 연다. `베이비올 → 베이비올 아기 → 특장점`, `소통해요 → FAQ → 사용 방법`, `아람 → 정품 판매처 → 서울` 순서로 3단계 탐색을 실습할 수 있다. 좁은 화면에서는 상단 **메뉴**를 열고 대메뉴 옆 펼침 버튼을 누른다.

전집 소개·표지, 게시글, 판매처는 데모 자료다. 제품상담신청과 고객센터는 안내 화면을 제공한다. 비회원은 페이지 하단에서 로그인과 회원가입을 열 수 있다.


메뉴, 홈 소개, 전집 상세, 게시글, FAQ, 회사 소개와 판매처 데이터는 그누보드와 같은 MariaDB의 `g5_aram_content`에서 읽는다. 테이블 접두사는 설치된 그누보드 설정을 따른다. 각 행의 `page_key`는 대메뉴 키이며 `payload` JSON에 하위 메뉴와 본문이 들어간다. 관리 전용 편집 화면은 제공하지 않는다.

초기화 명령 `php scripts/configure-aram.php`는 누락된 대메뉴 데이터만 추가하므로 재실행 시 저장된 본문을 보존한다. `scripts/aram-seed.php`는 설치 시에만 사용한다. DB 조회 실패나 초기 데이터 미설치 시 홈페이지는 HTTP 503을 반환한다.

기존 설치에서 같은 초기화 명령을 실행하면 저장된 콘텐츠의 이전 브랜드·전집명이 우람북스 명칭으로 바뀐다. 다른 본문과 URL 키는 보존한다.
