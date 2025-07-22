# MySQLi 확장 설치 명령어

## 현재 상황
- ✅ php-mysqlnd: 이미 설치됨
- ❌ php-mysqli: 설치 필요
- ❌ php-curl: 설치 필요

## 설치 명령어

```bash
# MySQLi 확장 설치
sudo dnf install php-mysqli

# 또는 모듈에서 설치
sudo dnf module install php:7.4/common

# cURL도 함께 설치
sudo dnf install php-curl

# 웹서버 재시작
sudo systemctl restart httpd
sudo systemctl restart php-fpm

# 설치 확인
php -r "var_dump(extension_loaded('mysqli'));"
php -r "var_dump(extension_loaded('curl'));"
```

## 대안 방법 (모듈 방식)

```bash
# 사용 가능한 PHP 모듈 확인
sudo dnf module list php

# PHP 7.4 모듈에서 필요한 확장 설치
sudo dnf module install php:7.4/common
sudo dnf install php-mysqli php-curl

# 재시작
sudo systemctl restart httpd php-fpm
```

## 설치 후 확인

웹브라우저에서 접속:
https://www.topmktx.com/debug_registration_api.php

결과에서 다음이 표시되어야 함:
- ✅ MySQLi 확장: 설치됨
- ✅ curl 확장: 설치됨