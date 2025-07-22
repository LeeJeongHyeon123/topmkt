#!/bin/bash
# MySQL 접속 스크립트 - 정확한 비밀번호로 자동 접속
# 사용법: ./scripts/mysql_connect.sh

echo "=== 탑마케팅 MySQL 접속 스크립트 ==="
echo "호스트: 127.0.0.1"
echo "사용자: root"
echo "데이터베이스: TOPMKT"
echo ""

# 정확한 비밀번호로 MySQL 접속
mysql -h 127.0.0.1 -u root -pDnlszkem1! TOPMKT

echo ""
echo "MySQL 연결이 종료되었습니다."