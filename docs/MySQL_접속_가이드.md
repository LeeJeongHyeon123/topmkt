# 🚀 MySQL 접속 방법 - Claude 개발자 전용 가이드

## ⚠️ 중요! 이 방법들만 사용하세요!

### ✅ 올바른 접속 방법들

#### 1️⃣ 자동 스크립트 (가장 추천!)
```bash
./scripts/mysql_connect.sh
```

#### 2️⃣ 설정 파일 사용
```bash
mysql --defaults-file=/var/www/html/topmkt/.my.cnf
```

#### 3️⃣ 홈 디렉토리 설정 후 (일회성 설정)
```bash
# 한 번만 실행
cp /var/www/html/topmkt/.my.cnf ~/.my.cnf
chmod 600 ~/.my.cnf

# 그 후 계속 이렇게 사용
mysql
```

#### 4️⃣ 빠른 명령어들
```bash
./scripts/mysql_quick_commands.sh tables    # 테이블 목록
./scripts/mysql_quick_commands.sh users     # 사용자 목록  
./scripts/mysql_quick_commands.sh lectures  # 강의 목록
./scripts/mysql_quick_commands.sh events    # 이벤트 목록
./scripts/mysql_quick_commands.sh backup    # 백업
```

---

## ❌ 이렇게 하지 마세요! (Claude가 자주 실수하는 방법들)

```bash
# ❌ 비밀번호 직접 입력 (틀릴 확률 높음)
mysql -u root -pDnlszkem1!

# ❌ 비밀번호 프롬프트 (틀린 비밀번호 입력할 가능성)
mysql -u root -p

# ❌ 호스트 잘못 지정
mysql -h 211.110.140.147 -u root -pDnlszkem1!
```

---

## 🎯 정확한 접속 정보 (참고용)

- **호스트**: 127.0.0.1
- **포트**: 3306
- **사용자**: root
- **비밀번호**: Dnlszkem1!
- **데이터베이스**: TOPMKT (⚠️ 대문자 주의!)

---

## 🔥 리마인더 스크립트

언제든지 까먹으면 이 스크립트 실행:
```bash
./scripts/mysql_reminder.sh
```

---

## 💡 Claude 개발자를 위한 팁

1. **스크립트 활용**: `./scripts/mysql_connect.sh`가 가장 간단
2. **리마인더 사용**: 까먹으면 `./scripts/mysql_reminder.sh` 실행
3. **문서 확인**: CLAUDE.md 파일에도 같은 정보 있음

**🎉 이제 MySQL 비밀번호로 고생할 일이 없어요!**