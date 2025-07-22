#!/bin/bash
# .my.cnf 사용법 데모 스크립트

echo "=== .my.cnf 파일 사용법 데모 ==="
echo ""

echo "1. 현재 .my.cnf 파일 내용:"
echo "================================"
cat /var/www/html/topmkt/.my.cnf
echo ""

echo "2. 홈 디렉토리에 복사하기:"
echo "================================"
echo "cp /var/www/html/topmkt/.my.cnf ~/.my.cnf"
echo "chmod 600 ~/.my.cnf"
echo ""

echo "3. 사용 방법들:"
echo "================================"
echo "# 방법 A: 특정 파일 지정"
echo "mysql --defaults-file=/var/www/html/topmkt/.my.cnf"
echo ""
echo "# 방법 B: 홈 디렉토리 파일 사용 (복사 후)"
echo "mysql                    # 비밀번호 입력 없이 바로 접속!"
echo "mysqldump topmkt > backup.sql   # 덤프도 비밀번호 없이!"
echo "mysqladmin status        # 상태 확인도 비밀번호 없이!"
echo ""

echo "4. 보안 주의사항:"
echo "================================"
echo "- 파일 권한은 반드시 600 (본인만 읽기/쓰기)"
echo "- 비밀번호가 평문으로 저장되므로 파일 관리 주의"
echo "- 공유 서버에서는 사용 금지"
echo ""

echo "5. 장점:"
echo "================================"
echo "✅ 비밀번호 입력 불필요"
echo "✅ 스크립트 자동화 가능"
echo "✅ 모든 MySQL 도구에서 사용 가능"
echo "✅ MySQL 공식 기능"
echo ""

echo "6. 실제 테스트 해보기:"
echo "================================"
echo "다음 명령어를 실행해보세요:"
echo ""
echo "# 홈 디렉토리에 복사"
echo "cp /var/www/html/topmkt/.my.cnf ~/.my.cnf"
echo "chmod 600 ~/.my.cnf"
echo ""
echo "# 테스트"
echo "mysql -e \"SELECT 'Success!' as result\""