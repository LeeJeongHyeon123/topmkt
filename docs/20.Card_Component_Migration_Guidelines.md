# Card 컴포넌트 마이그레이션 가이드라인

## 📋 개요

**버전**: v3.38.0
**작성일**: 2025-10-05
**목적**: Card 컴포넌트 적용 가능 여부 판단 기준 및 마이그레이션 방법 제공

---

## ✅ 마이그레이션 적합한 경우

### 1. 단순한 정적 카드 구조

**적합 조건**:
- 카드 내용이 정적이거나 단순한 PHP 변수 출력
- JavaScript 동적 업데이트 없음
- 복잡한 조건문/반복문 없음

**예시**:
```php
// ❌ Before (마이그레이션 전)
<div class="stat-card">
    <div class="stat-header">
        <div class="stat-icon primary">📚</div>
    </div>
    <div class="stat-value"><?= number_format($stats['total_lectures']) ?></div>
    <div class="stat-label">등록된 강의</div>
</div>

// ✅ After (마이그레이션 후)
<?= Card::stat([
    'icon' => '📚',
    'value' => number_format($stats['total_lectures']),
    'label' => '등록된 강의',
    'variant' => 'primary'
]) ?>
```

**효과**:
- 코드 줄 수: 8줄 → 6줄 (25% 감소)
- 가독성: 향상
- 유지보수성: 향상

---

### 2. 반복되는 카드 패턴

**적합 조건**:
- 동일한 카드 구조가 여러 번 반복
- 각 카드의 내용만 다름
- 중복 코드 제거 효과가 큼

**예시**:
```php
// ❌ Before (56줄 - 4개 feature-card)
<div class="feature-card">
    <div class="feature-icon">
        <div class="icon-bg blue">
            <i class="fas fa-users"></i>
        </div>
    </div>
    <h3>커뮤니티 네트워킹</h3>
    <p>전문가들과 연결</p>
    <a href="/community" class="feature-link">
        <span>시작하기</span>
        <i class="fas fa-arrow-right"></i>
    </a>
</div>
<!-- 나머지 3개 카드... -->

// ✅ After (36줄 - Card::feature 4개)
<?= Card::feature([
    'icon' => 'fas fa-users',
    'iconBg' => 'blue',
    'title' => '커뮤니티 네트워킹',
    'description' => '전문가들과 연결',
    'link' => '/community',
    'linkText' => '시작하기'
]) ?>
<!-- 나머지 3개 카드... -->
```

**효과**:
- 코드 줄 수: 56줄 → 36줄 (36% 감소)
- 중복 제거: 완벽
- 일관성: 보장

---

## ❌ 마이그레이션 부적합한 경우

### 1. 문자열 연결 방식 HTML 생성

**부적합 이유**: PHP 컴포넌트 직접 호출 불가

**예시**:
```php
// ❌ 마이그레이션 불가
$content = '
    <div class="stat-card primary">
        <div class="stat-number">' . number_format($todayStats['signups']) . '</div>
        <div class="stat-label">오늘 신규 가입</div>
    </div>
';
```

**해결책**: 문자열 연결 방식을 PHP 템플릿 방식으로 리팩토링 후 적용

---

### 2. JavaScript 동적 업데이트 카드

**부적합 이유**: DOM ID/클래스 참조 필요

**예시**:
```php
// ❌ 마이그레이션 불가
<div class="stat-card">
    <h3>총 회원 수</h3>
    <div class="stat-number" id="totalUsers">로딩 중...</div>
</div>

<script>
// JavaScript에서 #totalUsers 업데이트
document.getElementById('totalUsers').textContent = data.totalUsers;
</script>
```

**해결책**:
1. 카드 구조 유지하고 CSS만 통합
2. 또는 data-attribute 기반으로 리팩토링

---

### 3. 복잡한 내부 구조 (조건문/반복문)

**부적합 이유**: 가독성 저하 및 유지보수 어려움

**예시**:
```php
// ❌ 마이그레이션 불가
<div class="profile-card">
    <h2 class="card-title">
        <i class="fas fa-newspaper"></i> 최근 게시글
    </h2>
    <?php if (!empty($recentPosts)): ?>
        <ul class="activity-list">
            <?php foreach ($recentPosts as $post): ?>
                <li class="activity-item">
                    <div class="activity-title">
                        <a href="/community/posts/<?= $post['id'] ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </div>
                    <div class="activity-meta">
                        <span>📅 <?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                        <span>👁️ <?= number_format($post['view_count'] ?? 0) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="activity-empty">
            아직 작성한 게시글이 없습니다.
        </div>
    <?php endif; ?>
</div>
```

**이유**:
- Card::profile()로 전환 시 content 파라미터가 너무 복잡해짐
- 가독성 오히려 저하
- 디버깅 어려움

**해결책**: 원본 구조 유지 (마이그레이션 하지 않음)

---

## 📊 마이그레이션 의사결정 플로우차트

```
카드 마이그레이션 판단
    │
    ├─ 문자열 연결 방식? ──Yes──> ❌ 불가 (리팩토링 필요)
    │   No
    │   │
    ├─ JavaScript 동적 업데이트? ──Yes──> ❌ 불가 (CSS만 통합)
    │   No
    │   │
    ├─ 복잡한 조건문/반복문? ──Yes──> ❌ 불가 (원본 유지)
    │   No
    │   │
    ├─ 단순 정적 구조? ──Yes──> ✅ 마이그레이션 적합!
    │
    └─ 반복 패턴 다수? ──Yes──> ✅ 마이그레이션 강력 권장!
```

---

## 🎯 실제 마이그레이션 결과 (v3.38.0)

### 성공 사례 (2개 파일, 8개 카드)

| 파일 | 카드 타입 | Before | After | 감소율 |
|------|----------|--------|-------|--------|
| registrations/dashboard.php | stat-card (4개) | 32줄 | 28줄 | 12% |
| home/index.php | feature-card (4개) | 56줄 | 36줄 | 36% |
| **합계** | **8개 카드** | **88줄** | **64줄** | **27%** |

**성공 이유**:
- ✅ 단순 정적 구조
- ✅ JavaScript 없음
- ✅ 복잡한 로직 없음

---

### 보류 사례 (19개 카드)

| 파일 | 카드 타입 | 개수 | 보류 이유 |
|------|----------|------|----------|
| admin/dashboard.php | stat-card | 4개 | 문자열 연결 방식 |
| admin/dashboard_new.php | stat-card | 4개 | 문자열 연결 방식 |
| admin/users/list_direct.php | stat-card | 3개 | JavaScript 동적 업데이트 |
| user/profile.php | profile-card | 7개 | 복잡한 리스트/조건문 구조 |
| lectures/detail.php | sidebar-card | 4개 | JavaScript 강결합 + 복잡 구조 |

**보류 이유**:
- ❌ Card 컴포넌트로 전환 시 가독성 저하
- ❌ 유지보수 어려움
- ❌ 기술적 제약 (문자열 연결, DOM 참조)

---

## 📝 마이그레이션 체크리스트

마이그레이션 전에 다음을 확인하세요:

- [ ] 1. 카드가 정적 구조인가?
- [ ] 2. JavaScript 동적 업데이트가 없는가?
- [ ] 3. 복잡한 조건문/반복문이 없는가?
- [ ] 4. 마이그레이션 후 가독성이 유지되는가?
- [ ] 5. 코드 줄 수가 감소하는가?
- [ ] 6. 유지보수가 쉬워지는가?

**모두 ✅이면 마이그레이션 진행, 하나라도 ❌이면 보류**

---

## 🚀 권장 사항

### 즉시 마이그레이션 권장
- ✅ 통계 대시보드의 단순 stat-card
- ✅ 홈페이지의 feature-card
- ✅ 단순 정보 표시 카드

### 마이그레이션 보류 권장
- ⏸️ JavaScript 동적 업데이트 카드
- ⏸️ 복잡한 리스트/테이블 포함 카드
- ⏸️ 문자열 연결 방식 생성 카드

### 대안
- **CSS 통합**: `/public/assets/css/cards.css` 생성하여 스타일만 통합
- **클래스 표준화**: `.stat-card`, `.feature-card` 등 클래스명 통일
- **점진적 리팩토링**: 문자열 연결 → 템플릿 방식으로 먼저 변경

---

## 📖 참고 자료

- Card 컴포넌트 소스: `/src/components/ui/Card.php`
- 검증 스크립트: `/scripts/check_component_violations.sh`
- 사용 예시: `/tmp/card_component_summary.md`
- QA 보고서: `/tmp/card_qa_report.sh`

---

**작성자**: Claude (Ultra Think Mode)
**마지막 업데이트**: 2025-10-05
