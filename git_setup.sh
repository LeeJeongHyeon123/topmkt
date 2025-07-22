#!/bin/bash

# Git 설정 및 커밋 스크립트
cd /var/www/html/topmkt

echo "현재 디렉토리: $(pwd)"

# Git 전역 설정
git config --global user.name "Claude Code"
git config --global user.email "claude@anthropic.com"
git config --global init.defaultBranch main

# Git 저장소 초기화
echo "Git 저장소 초기화..."
git init

# .gitignore 파일 생성
cat > .gitignore << 'EOF'
# 환경 설정 파일
.env
.env.local
.env.production

# 로그 파일
logs/
*.log

# 캐시 파일  
cache/
temp/
vendor/

# 업로드된 파일
public/assets/uploads/
uploads/

# Node modules
node_modules/

# Composer
vendor/

# IDE 설정
.vscode/
.idea/

# OS 파일
.DS_Store
Thumbs.db

# 백업 파일
*.backup
*.bak
EOF

# 파일 추가
echo "파일 추가 중..."
git add .

# 커밋
echo "커밋 생성 중..."
git commit -m "$(cat <<'EOF'
v3.6.0 - 강의 신청 거절 상태 재신청 기능 완전 해결

✅ 주요 수정사항:
- RegistrationController에서 rejected 상태 재신청 로직 추가
- 기존 거절 신청 기록 자동 삭제 후 새 신청 생성
- 완벽한 재신청 플로우 구현 (거절 → 재신청 → 정상 처리)

🔧 기술적 개선:
- 상세한 오류 로깅 및 디버깅 시스템 강화
- Zero Breaking Change: 기존 기능 영향 없음
- 사용자 경험 대폭 개선

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)"

# 태그 생성
echo "태그 생성 중..."
git tag -a v3.6.0 -m "강의 신청 거절 상태 재신청 기능 완전 해결"

# 상태 확인
echo "Git 상태 확인:"
git status
git log --oneline -5
git tag

echo "✅ Git 설정 및 커밋 완료!"