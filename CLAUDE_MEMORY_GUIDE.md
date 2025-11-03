# Claude Code 메모리 관리 가이드

## 🚨 문제 상황 (2025-10-21 해결)

### 발생했던 문제:
- **증상**: Claude Code가 갑자기 "Killed" 메시지와 함께 강제 종료
- **원인**: 시스템 메모리 부족으로 OOM Killer가 프로세스 강제 종료
- **환경**: 31GB RAM 서버에서 여러 개의 Claude Code 동시 실행

```bash
Out of memory: Killed process 659665 (claude)
- 가상 메모리: 34GB 사용 (시스템 메모리 초과)
- 실제 메모리: 1.1GB 사용
```

---

## ✅ 해결 완료 사항

### 1. claude-auto.sh 개선
**변경 사항**:
- ❌ 자동 프로세스 종료 로직 제거 (여러 개 동시 실행 지원)
- ✅ 메모리 부족 시 경고만 표시
- ✅ 사용자가 직접 결정하도록 개선

**메모리 설정**:
```bash
NODE_OPTIONS="--max-old-space-size=8192"  # 8GB (유지)
```

### 2. 새로운 도구 추가

#### claude-monitor.sh - 실시간 모니터링
```bash
# 1회 확인
./scripts/claude-monitor.sh

# 실시간 모니터링 (5초마다 갱신)
./scripts/claude-monitor.sh --watch
```

**출력 예시**:
```
📊 Claude Code 메모리 모니터링
================================

💾 시스템 메모리:
   총: 31GB | 사용: 28GB | 여유: 2.5GB | 사용률: 90.3%

🤖 실행 중인 Claude 프로세스:
   총 3개 프로세스 (메모리 사용: 1.2GB)

   PID      CPU%   MEM%   RSS(MB)  실행시간   터미널
   --------------------------------------------------------
   123456   15.2%  1.8%   580.2    02:15:30   pts/1
   234567   8.3%   0.9%   290.5    01:42:10   pts/2
   345678   12.1%  1.2%   380.8    00:35:20   pts/3

⚡ 주의: 사용 가능한 메모리가 2500MB입니다.
   추가 Claude 프로세스 실행 시 주의가 필요합니다.
```

#### cleanup-claude-processes.sh - 안전한 정리
```bash
./scripts/cleanup-claude-processes.sh
```

**특징**:
- ✅ 현재 실행 중인 프로세스는 자동 보호
- ✅ 오래된 좀비 프로세스만 종료
- ✅ 종료 전 확인 메시지 표시

---

## 📋 사용 권장 사항

### 1. Claude Code 실행 전 확인
```bash
# 메모리 상태 확인
./scripts/claude-monitor.sh

# 사용 가능 메모리가 1GB 미만이면 정리
./scripts/cleanup-claude-processes.sh
```

### 2. 동시 실행 개수 제한
**권장 개수 (31GB RAM 기준)**:
- ✅ **3개 이하**: 안전 (각 프로세스 최대 8GB 사용 가능)
- ⚠️ **4개**: 주의 (메모리 부족 가능성)
- ❌ **5개 이상**: 위험 (OOM Killer 발동 가능)

**계산 공식**:
```
안전한 프로세스 수 = (사용 가능 메모리 - 2GB 버퍼) / 8GB
                 = (31GB - 2GB) / 8GB
                 ≈ 3개
```

### 3. 프로세스별 터미널 관리
```bash
# 터미널 1 (topmkt 프로젝트)
cd /var/www/html/topmkt
./scripts/claude-auto.sh

# 터미널 2 (eatple 프로젝트)
cd /var/www/html/eatple
claude

# 터미널 3 (기타 작업)
claude
```

### 4. 장시간 미사용 프로세스 정리
```bash
# 1시간 이상 실행된 프로세스 확인
ps aux | grep claude | awk '$10 > "01:"'

# 필요 없는 프로세스 직접 종료
kill -9 [PID]
```

---

## 🔧 메모리 부족 시 대처법

### 단계 1: 현재 상태 확인
```bash
./scripts/claude-monitor.sh
```

### 단계 2: 불필요한 프로세스 확인
```bash
# 실행 시간 순으로 정렬
ps aux | grep claude | sort -k10
```

### 단계 3: 선택적 종료
```bash
# 특정 PID만 종료
kill -9 [PID]

# 또는 자동 정리 스크립트 실행
./scripts/cleanup-claude-processes.sh
```

### 단계 4: 메모리 캐시 정리 (선택)
```bash
# 페이지 캐시만 삭제 (안전)
sync
echo 1 > /proc/sys/vm/drop_caches

# dentries와 inodes까지 삭제 (더 많은 메모리 확보)
echo 3 > /proc/sys/vm/drop_caches
```

---

## 🚫 하지 말아야 할 것

### ❌ 절대 하지 마세요:
```bash
# 모든 claude 프로세스 강제 종료 (현재 작업 중인 것도 종료됨)
pkill -9 claude  # ← 위험!

# 메모리 설정을 너무 낮게 조정
export NODE_OPTIONS="--max-old-space-size=2048"  # ← 성능 저하
```

### ✅ 대신 이렇게 하세요:
```bash
# 안전한 정리 스크립트 사용
./scripts/cleanup-claude-processes.sh

# 메모리 설정 유지 (8GB)
# claude-auto.sh에 이미 설정되어 있음
```

---

## 📊 메모리 사용량 예측

### Claude Code 1개당 평균 사용량:
- **최소**: 200MB (유휴 상태)
- **평균**: 500MB~1GB (일반 작업)
- **최대**: 8GB (대용량 파일 처리, 복잡한 분석)

### 시나리오별 예상 사용량:

**안전 (3개 동시 실행)**:
```
3개 × 1GB (평균) = 3GB
여유 메모리: 31GB - 28GB(기존) - 3GB = 0GB (여유 없음)
→ 기존 사용량을 줄여야 함
```

**권장 (2개 동시 실행)**:
```
2개 × 1GB (평균) = 2GB
여유 메모리: 4GB (안전)
```

---

## 🔔 경고 신호

### OOM Killer 발동 징후:
1. `free -h`로 확인 시 available < 500MB
2. 스왑 메모리 100% 사용
3. 시스템이 전체적으로 느려짐
4. `dmesg | tail`에 "Out of memory" 메시지

### 즉시 조치:
```bash
# 1. 모니터링 확인
./scripts/claude-monitor.sh

# 2. 긴급 정리
./scripts/cleanup-claude-processes.sh

# 3. 캐시 정리
sync && echo 3 > /proc/sys/vm/drop_caches
```

---

## 📁 관련 파일

- **자동 실행 스크립트**: `/var/www/html/topmkt/scripts/claude-auto.sh`
- **모니터링 도구**: `/var/www/html/topmkt/scripts/claude-monitor.sh`
- **정리 도구**: `/var/www/html/topmkt/scripts/cleanup-claude-processes.sh`
- **대화 복구 가이드**: `/var/www/html/topmkt/CLAUDE_HISTORY_RECOVERY.md`

---

## 💡 팁

### 1. 터미널별 프로젝트 고정
```bash
# ~/.bashrc에 alias 추가
alias topmkt='cd /var/www/html/topmkt && ./scripts/claude-auto.sh'
alias eatple='cd /var/www/html/eatple && claude'
```

### 2. 메모리 모니터링 자동화
```bash
# watch 명령어로 실시간 모니터링
watch -n 5 './scripts/claude-monitor.sh'
```

### 3. 작업 완료 후 즉시 종료
```bash
# Claude 작업 완료 후
exit  # 또는 Ctrl+D

# 터미널도 닫기
```

---

**작성일**: 2025-10-21
**작성자**: Claude (Anthropic)
**버전**: v1.0
