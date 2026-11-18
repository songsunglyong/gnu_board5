# Apache 2.4.71 보안 패치

이 패치는 기존 그누보드 앱 이미지를 입력받아 Apache 패키지 네 개만 업데이트한다. 입력 이미지의 PHP 버전·앱 파일·ENTRYPOINT·CMD는 유지한다. 영속 볼륨과 실행 환경 설정은 기존 Compose를 사용한다.

```sh
docker build --build-arg APP_IMAGE=gamecha-gnuboard5-demo-app:local \
  -f deploy/Dockerfile.apache-security \
  -t gamecha-gnuboard5-demo-app:apache-patched .
docker run --rm --entrypoint apache2 gamecha-gnuboard5-demo-app:apache-patched -v
```

기존 이미지는 먼저 별도 롤백 태그로 보관한다. 빌드 후 앱·내부 ERP 컨테이너를 새 이미지로 교체해야 실제 런타임에 반영된다. DB 컨테이너와 볼륨은 교체하지 않는다.

2026-11-18 서버에서 이 Dockerfile 빌드와 Apache 2.4.71을 확인했다. 운영 앱·내부 ERP의 Apache 2.4.71·Syntax OK·healthy·HTTPS 검증도 완료했다. 전체 배포·테마 변경은 이 보안 커밋에 포함하지 않았다.
