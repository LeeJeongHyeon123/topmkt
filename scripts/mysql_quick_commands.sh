#!/bin/bash
# MySQL 빠른 명령어 모음집
# 사용법: ./scripts/mysql_quick_commands.sh [명령어]

DB_CONFIG="--defaults-file=/var/www/html/topmkt/.my.cnf"

echo "=== 탑마케팅 MySQL 빠른 명령어 ==="

case "$1" in
    "connect"|"")
        echo "MySQL 접속 중..."
        mysql $DB_CONFIG
        ;;
    "status")
        echo "MySQL 서버 상태 확인..."
        mysqladmin $DB_CONFIG status
        ;;
    "tables")
        echo "테이블 목록 조회..."
        mysql $DB_CONFIG -e "SHOW TABLES;"
        ;;
    "users")
        echo "사용자 목록 조회..."
        mysql $DB_CONFIG -e "SELECT id, username, email, role, created_at FROM users LIMIT 10;"
        ;;
    "lectures")
        echo "강의 목록 조회..."
        mysql $DB_CONFIG -e "SELECT id, title, instructor_name, start_date, status FROM lectures LIMIT 10;"
        ;;
    "events")
        echo "이벤트 목록 조회..."
        mysql $DB_CONFIG -e "SELECT id, title, start_date, location_type, status FROM lectures WHERE content_type='event' LIMIT 10;"
        ;;
    "backup")
        echo "데이터베이스 백업 중..."
        BACKUP_FILE="/var/www/html/topmkt/backup_$(date +%Y%m%d_%H%M%S).sql"
        mysqldump $DB_CONFIG TOPMKT > "$BACKUP_FILE"
        echo "백업 완료: $BACKUP_FILE"
        ;;
    "help")
        echo "사용 가능한 명령어:"
        echo "  connect  - MySQL 접속 (기본값)"
        echo "  status   - MySQL 서버 상태"
        echo "  tables   - 테이블 목록"
        echo "  users    - 사용자 목록"
        echo "  lectures - 강의 목록"
        echo "  events   - 이벤트 목록"
        echo "  backup   - 데이터베이스 백업"
        echo "  help     - 이 도움말"
        ;;
    *)
        echo "알 수 없는 명령어: $1"
        echo "사용법: $0 [connect|status|tables|users|lectures|events|backup|help]"
        exit 1
        ;;
esac