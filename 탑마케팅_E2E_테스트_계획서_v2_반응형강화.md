# 탑마케팅 플랫폼 End-to-End 테스트 계획서 v2.0 (반응형 강화)

**문서 버전:** v2.0 (반응형 UI 테스트 대폭 강화)  
**작성일:** 2025-08-15  
**작성자:** Claude (Ultra Think 모드)  
**테스트 대상:** 탑마케팅 플랫폼 1차 개발 완료 버전  
**테스트 URL:** https://www.topmktx.com  
**테스트 환경:** Playwright MCP 헤드리스 모드 (완전 자동화)

---

## 📋 테스트 개요

### 🎯 테스트 목표
- 탑마케팅 플랫폼의 **완전무결한 품질 보증**을 위한 포괄적 E2E 테스트
- **8개 브레이크포인트**에서의 완벽한 반응형 UI 검증
- **3가지 사용자 유형별** 핵심 기능 및 UX 완전 테스트
- **Playwright 헤드리스 모드** 기반 **3개 브라우저 병렬** 실행

### 🔍 테스트 범위 (대폭 확장)
- **총 테스트 케이스:** **420개** (기존 240개 → **180개 추가**)
- **테스트 카테고리:** **10개** (반응형, 접근성 카테고리 추가)
- **브레이크포인트:** **8개** (Ultra Large 2560px ~ Mobile Small 320px)
- **테스트 방식:** **3개 브라우저 병렬** 처리 (반응형 전용 브라우저 추가)

### 📊 강화된 테스트 구성
- **기존 기능 테스트:** **240개** (일반 유저, 기업 관리자, 탑마케팅 관리자)
- **반응형 UI 테스트:** **120개** ⭐ **신규 추가**
- **접근성 테스트:** **35개** ⭐ **신규 추가**  
- **크로스 브라우저 테스트:** **25개** ⭐ **신규 추가**

### ⏰ 예상 테스트 시간
- **전체 테스트:** **12-15시간** (완전무결한 품질 보증)
- **반응형 테스트 단독:** **4-5시간** (8개 브레이크포인트 × 15개 페이지)
- **병렬 처리 효율성:** **3개 브라우저** 동시 실행으로 시간 단축

---

## 🏗️ 강화된 테스트 아키텍처

### 🎭 Playwright 헤드리스 모드 최적화
```javascript
// 3개 브라우저 병렬 처리 구조
E2ETestRunner {
  // Browser Group A: 일반 유저 + 기능 테스트
  BrowserA: { viewport: '1920x1080', userAgent: 'Desktop' }
  
  // Browser Group B: 관리자 + 성능 테스트  
  BrowserB: { viewport: '1366x768', userAgent: 'Desktop' }
  
  // Browser Group C: 반응형 + 접근성 테스트 ⭐ 신규
  BrowserC: { viewport: 'Dynamic', userAgent: 'Multi-Device' }
  
  // 헤드리스 모드 전용 최적화
  - deviceEmulation(): 8개 디바이스 자동 전환
  - viewportScaling(): 동적 뷰포트 조정
  - touchEmulation(): 터치 인터페이스 시뮬레이션
  - orientationChange(): 가로/세로 자동 전환
}
```

### 📱 8개 정밀 브레이크포인트 체계
```javascript
const breakpoints = {
  // 🖥️ 데스크톱 환경
  'ultra-large':     { width: 2560, height: 1440, device: '4K Monitor' },
  'large-desktop':   { width: 1440, height: 900,  device: 'Large Desktop' },
  'desktop':         { width: 1024, height: 768,  device: 'Standard Desktop' },
  
  // 📱 태블릿 환경
  'tablet-landscape': { width: 1024, height: 768,  device: 'iPad Landscape', touch: true },
  'tablet-portrait':  { width: 768,  height: 1024, device: 'iPad Portrait', touch: true },
  
  // 📱 모바일 환경
  'mobile-large':     { width: 414,  height: 896,  device: 'iPhone 11 Pro Max', touch: true },
  'mobile-medium':    { width: 375,  height: 667,  device: 'iPhone SE', touch: true },
  'mobile-small':     { width: 320,  height: 568,  device: 'iPhone 5', touch: true }
};
```

---

## 🎨 반응형 UI 테스트 (120개 케이스) ⭐ 신규 추가

### 1. 핵심 페이지별 반응형 테스트 (80개 케이스)

#### 1.1 메인 페이지 반응형 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 메인 그리드 레이아웃 | 4컬럼 + 여백 적절 배치 | 🔴 Critical |
| **Large Desktop (1440px)** | 메인 그리드 적응 | 4컬럼 유지, 간격 조정 | 🔴 Critical |
| **Desktop (1024px)** | 컬럼 축소 적응 | 3컬럼으로 자동 조정 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 인터페이스 | 버튼 크기 44px+ 확장 | 🟡 High |
| **Tablet Portrait (768px)** | 세로 레이아웃 | 2컬럼 그리드 배치 | 🔴 Critical |
| **Mobile Large (414px)** | 모바일 최적화 | 1컬럼 세로 스택 배치 | 🔴 Critical |
| **Mobile Medium (375px)** | 표준 모바일 | 네비게이션 햄버거 메뉴 | 🔴 Critical |
| **Mobile Small (320px)** | 소형 모바일 | 최소 너비 유지 | 🟡 High |
| **오리엔테이션 변경** | 가로↔세로 전환 | 레이아웃 즉시 적응 | 🟡 High |
| **동적 리사이징** | 브라우저 크기 조정 | 실시간 그리드 재배치 | 🟡 High |

#### 1.2 커뮤니티 목록 페이지 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 게시글 그리드 | 4컬럼 카드 레이아웃 | 🟡 High |
| **Large Desktop (1440px)** | 3컬럼 적응 | 여백과 함께 3컬럼 배치 | 🟡 High |
| **Desktop (1024px)** | 2컬럼 + 사이드바 | 콘텐츠 + 필터 사이드바 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 최적화 | 터치 친화적 버튼/링크 | 🟡 High |
| **Tablet Portrait (768px)** | 사이드바 접기 | 메인 콘텐츠 우선 표시 | 🔴 Critical |
| **Mobile Large (414px)** | 리스트 뷰 | 1컬럼 리스트 형태 | 🔴 Critical |
| **Mobile Medium (375px)** | 필터 하단 배치 | 필터링 버튼 하단 이동 | 🟡 High |
| **Mobile Small (320px)** | 최소 너비 대응 | 텍스트 줄바꿈 처리 | 🟡 High |
| **페이지네이션** | 모바일 페이징 | 터치 친화적 페이징 | 🟡 High |
| **검색 UI** | 모바일 검색 | 전체 너비 검색바 | 🟡 High |

#### 1.3 커뮤니티 상세 페이지 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 콘텐츠 + 사이드바 | 적절한 여백과 2컬럼 | 🟡 High |
| **Large Desktop (1440px)** | 가독성 최적화 | 최적 줄길이 유지 | 🟡 High |
| **Desktop (1024px)** | 사이드바 축소 | 콘텐츠 영역 확장 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 댓글 | 터치 친화적 댓글 UI | 🟡 High |
| **Tablet Portrait (768px)** | 세로 스크롤 | 사이드바 하단 이동 | 🔴 Critical |
| **Mobile Large (414px)** | 모바일 댓글 | 풀스크린 댓글 작성 | 🔴 Critical |
| **Mobile Medium (375px)** | 이미지 최적화 | 반응형 이미지 크기 | 🟡 High |
| **Mobile Small (320px)** | 텍스트 가독성 | 최소 폰트 크기 유지 | 🟡 High |
| **좋아요 버튼** | 터치 인터페이스 | 충분한 터치 영역 | 🟡 High |
| **공유 기능** | 모바일 공유 | 네이티브 공유 API | 🟢 Medium |

#### 1.4 강의 목록 페이지 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 캘린더 + 리스트 | 좌우 분할 레이아웃 | 🔴 Critical |
| **Large Desktop (1440px)** | 캘린더 축소 | 적절한 비율 유지 | 🔴 Critical |
| **Desktop (1024px)** | 탭 전환 UI | 캘린더/리스트 탭 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 캘린더 | 터치 친화적 날짜 선택 | 🟡 High |
| **Tablet Portrait (768px)** | 세로 스택 | 캘린더 위, 리스트 아래 | 🔴 Critical |
| **Mobile Large (414px)** | 모바일 캘린더 | 월간 뷰 최적화 | 🔴 Critical |
| **Mobile Medium (375px)** | 스와이프 네비게이션 | 좌우 스와이프 월 이동 | 🟡 High |
| **Mobile Small (320px)** | 미니 캘린더 | 주간 뷰로 변경 | 🟡 High |
| **강의 카드** | 카드 반응형 | 정보 배치 자동 조정 | 🟡 High |
| **필터링** | 모바일 필터 | 드롭다운 최적화 | 🟡 High |

#### 1.5 관리자 대시보드 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 대시보드 그리드 | 6개 위젯 2x3 배치 | 🔴 Critical |
| **Large Desktop (1440px)** | 위젯 재배치 | 4개 위젯 2x2 배치 | 🔴 Critical |
| **Desktop (1024px)** | 사이드바 접기 | 토글 사이드바 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 대시보드 | 터치 친화적 위젯 | 🟡 High |
| **Tablet Portrait (768px)** | 세로 스택 | 위젯 세로 배치 | 🔴 Critical |
| **Mobile Large (414px)** | 모바일 관리 | 핵심 기능만 표시 | 🔴 Critical |
| **Mobile Medium (375px)** | 간소화 UI | 필수 메뉴만 노출 | 🔴 Critical |
| **Mobile Small (320px)** | 최소 관리 | 기본 기능 유지 | 🟡 High |
| **차트 반응형** | 그래프 크기 조정 | 화면에 맞는 차트 | 🟡 High |
| **테이블 반응형** | 데이터 테이블 | 가로 스크롤 처리 | 🟡 High |

#### 1.6 프로필 페이지 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 프로필 레이아웃 | 좌우 분할 + 여백 | 🟡 High |
| **Large Desktop (1440px)** | 정보 배치 | 프로필 + 활동 내역 | 🟡 High |
| **Desktop (1024px)** | 컴팩트 뷰 | 세로 스택 배치 | 🟡 High |
| **Tablet Landscape (1024px)** | 터치 편집 | 터치 친화적 편집 UI | 🟡 High |
| **Tablet Portrait (768px)** | 세로 최적화 | 프로필 상단 고정 | 🟡 High |
| **Mobile Large (414px)** | 모바일 프로필 | 아바타 + 기본 정보 | 🔴 Critical |
| **Mobile Medium (375px)** | 간소화 표시 | 핵심 정보만 표시 | 🔴 Critical |
| **Mobile Small (320px)** | 미니멀 프로필 | 필수 정보만 유지 | 🟡 High |
| **이미지 업로드** | 모바일 업로드 | 카메라/갤러리 선택 | 🟡 High |
| **소셜 링크** | 링크 배치 | 아이콘 + 텍스트 조합 | 🟢 Medium |

#### 1.7 채팅 페이지 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 채팅 + 사이드바 | 3컬럼 레이아웃 | 🟡 High |
| **Large Desktop (1440px)** | 채팅 + 목록 | 2컬럼 레이아웃 | 🔴 Critical |
| **Desktop (1024px)** | 토글 사이드바 | 채팅 우선 표시 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 타이핑 | 가상 키보드 대응 | 🔴 Critical |
| **Tablet Portrait (768px)** | 세로 채팅 | 입력창 하단 고정 | 🔴 Critical |
| **Mobile Large (414px)** | 풀스크린 채팅 | 전체 화면 채팅 모드 | 🔴 Critical |
| **Mobile Medium (375px)** | 모바일 최적화 | 메시지 버블 크기 조정 | 🔴 Critical |
| **Mobile Small (320px)** | 컴팩트 채팅 | 최소 영역으로 압축 | 🟡 High |
| **파일 업로드** | 모바일 첨부 | 터치 친화적 첨부 UI | 🟡 High |
| **이모지 선택** | 이모지 패널 | 반응형 이모지 그리드 | 🟢 Medium |

#### 1.8 공지사항 페이지 (10개 케이스)
| 브레이크포인트 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------------|--------------|-----------|----------|
| **Ultra Large (2560px+)** | 공지 + 사이드바 | 콘텐츠 + 관련 공지 | 🟡 High |
| **Large Desktop (1440px)** | 가독성 최적화 | 적절한 줄 길이 유지 | 🟡 High |
| **Desktop (1024px)** | 이미지 최적화 | 콘텐츠 너비 맞춤 | 🔴 Critical |
| **Tablet Landscape (1024px)** | 터치 스크롤 | 부드러운 터치 스크롤 | 🟡 High |
| **Tablet Portrait (768px)** | 세로 읽기 | 세로 스크롤 최적화 | 🔴 Critical |
| **Mobile Large (414px)** | 모바일 읽기 | 폰트 크기 자동 조정 | 🔴 Critical |
| **Mobile Medium (375px)** | 이미지 모달 | 터치 친화적 모달 | 🔴 Critical |
| **Mobile Small (320px)** | 최소 가독성 | 최소 폰트 크기 보장 | 🟡 High |
| **첨부 이미지** | 이미지 그리드 | 반응형 이미지 그리드 | 🟡 High |
| **공유 기능** | 모바일 공유 | 네이티브 공유 연동 | 🟢 Medium |

### 2. 터치 인터페이스 테스트 (25개 케이스)

#### 2.1 터치 제스처 테스트 (15개 케이스)
| 제스처 유형 | 테스트 케이스 | 예상 결과 | 우선순위 |
|------------|--------------|-----------|----------|
| **탭 (Tap)** | 버튼/링크 탭 | 정확한 클릭 이벤트 발생 | 🔴 Critical |
| **더블 탭** | 이미지 확대 | 확대/축소 토글 | 🟡 High |
| **긴 터치** | 컨텍스트 메뉴 | 우클릭 메뉴 표시 | 🟢 Medium |
| **스와이프 (좌우)** | 이미지 갤러리 | 이전/다음 이미지 이동 | 🟡 High |
| **스와이프 (상하)** | 페이지 스크롤 | 부드러운 스크롤 | 🔴 Critical |
| **핀치 줌** | 이미지/지도 확대 | 확대/축소 동작 | 🟡 High |
| **드래그** | 모달 창 이동 | 드래그 앤 드롭 | 🟢 Medium |
| **풀 투 리프레시** | 페이지 새로고침 | 당겨서 새로고침 | 🟢 Medium |
| **터치 피드백** | 햅틱 피드백 | 터치 시 시각적 피드백 | 🟡 High |
| **터치 영역 크기** | 최소 44px 보장 | WCAG 터치 타겟 기준 | 🟡 High |
| **터치 정확도** | 모서리 터치 | 화면 가장자리 터치 감지 | 🟡 High |
| **멀티 터치** | 두 손가락 조작 | 동시 터치 처리 | 🟢 Medium |
| **터치 지연** | 터치 응답 속도 | 300ms 이하 응답 | 🟡 High |
| **터치 캔슬** | 터치 취소 처리 | 터치 시작 후 이동 시 취소 | 🟢 Medium |
| **가상 키보드** | 키보드 출현 대응 | 레이아웃 자동 조정 | 🔴 Critical |

#### 2.2 모바일 네비게이션 (10개 케이스)
| 네비게이션 유형 | 테스트 케이스 | 예상 결과 | 우선순위 |
|----------------|--------------|-----------|----------|
| **햄버거 메뉴** | 메뉴 토글 | 부드러운 슬라이드 애니메이션 | 🔴 Critical |
| **탭 네비게이션** | 하단 탭 | 활성 탭 하이라이트 | 🟡 High |
| **브레드크럼** | 경로 표시 | 축약된 경로 표시 | 🟡 High |
| **백 버튼** | 뒤로가기 | 히스토리 기반 뒤로가기 | 🔴 Critical |
| **검색 바** | 모바일 검색 | 전체 너비 검색 인터페이스 | 🟡 High |
| **필터 버튼** | 필터 패널 | 하단에서 올라오는 패널 | 🟡 High |
| **플로팅 버튼** | FAB 버튼 | 우하단 고정 액션 버튼 | 🟢 Medium |
| **스와이프 네비** | 좌우 스와이프 | 페이지/탭 전환 | 🟡 High |
| **무한 스크롤** | 페이지 로딩 | 스크롤 시 자동 로딩 | 🟡 High |
| **풀다운 새로고침** | 새로고침 제스처 | 당겨서 새로고침 | 🟢 Medium |

### 3. 동적 레이아웃 테스트 (15개 케이스)

#### 3.1 뷰포트 변경 테스트 (8개 케이스)
| 변경 유형 | 테스트 케이스 | 예상 결과 | 우선순위 |
|----------|--------------|-----------|----------|
| **브라우저 리사이징** | 실시간 크기 조정 | 레이아웃 즉시 적응 | 🔴 Critical |
| **오리엔테이션 변경** | 가로↔세로 전환 | 0.5초 이내 레이아웃 전환 | 🔴 Critical |
| **줌 레벨 변경** | 브라우저 확대/축소 | 비례 확대 및 레이아웃 유지 | 🟡 High |
| **풀스크린 모드** | F11 풀스크린 | 최대 화면 활용 | 🟢 Medium |
| **멀티 모니터** | 듀얼 모니터 이동 | 해상도 변경 대응 | 🟢 Medium |
| **키보드 출현** | 가상 키보드 | 콘텐츠 영역 자동 조정 | 🔴 Critical |
| **사이드바 토글** | 관리자 사이드바 | 콘텐츠 영역 확장/축소 | 🟡 High |
| **모달 오버레이** | 모달 창 출현 | 배경 스크롤 방지 | 🟡 High |

#### 3.2 콘텐츠 적응 테스트 (7개 케이스)
| 적응 유형 | 테스트 케이스 | 예상 결과 | 우선순위 |
|----------|--------------|-----------|----------|
| **텍스트 줄바꿈** | 긴 제목 처리 | 자동 줄바꿈 | 🔴 Critical |
| **이미지 스케일링** | 이미지 크기 조정 | 비율 유지하며 축소/확대 | 🔴 Critical |
| **테이블 반응형** | 데이터 테이블 | 가로 스크롤 또는 카드 변환 | 🟡 High |
| **폰트 크기** | 가독성 유지 | 최소/최대 폰트 크기 보장 | 🟡 High |
| **버튼 크기** | 터치 타겟 | 최소 44px 유지 | 🟡 High |
| **여백 조정** | 패딩/마진 | 화면 크기 비례 여백 | 🟡 High |
| **애니메이션** | 모션 최적화 | 성능 기반 애니메이션 조정 | 🟢 Medium |

---

## 🔧 접근성 테스트 (35개 케이스) ⭐ 신규 추가

### 1. 키보드 접근성 (15개 케이스)
| 분류 | 테스트 케이스 | 예상 결과 | 우선순위 |
|------|--------------|-----------|----------|
| **Tab 네비게이션** | 논리적 탭 순서 | 좌상→우하 순서로 포커스 이동 | 🔴 Critical |
| **Tab 트래핑** | 모달 내 탭 순환 | 모달 내에서만 포커스 이동 | 🔴 Critical |
| **Shift+Tab** | 역방향 탭 | 이전 요소로 포커스 이동 | 🔴 Critical |
| **Enter 키** | 링크/버튼 활성화 | Enter로 클릭 동작 실행 | 🔴 Critical |
| **Space 키** | 버튼/체크박스 | Space로 버튼 클릭/체크 | 🔴 Critical |
| **ESC 키** | 모달/드롭다운 닫기 | ESC로 오버레이 닫기 | 🔴 Critical |
| **화살표 키** | 드롭다운 네비게이션 | 화살표로 옵션 선택 | 🟡 High |
| **Home/End 키** | 시작/끝 이동 | 목록의 처음/마지막 이동 | 🟡 High |
| **Page Up/Down** | 페이지 스크롤 | 키보드 스크롤 지원 | 🟡 High |
| **포커스 표시** | 포커스 링/아웃라인 | 명확한 포커스 인디케이터 | 🔴 Critical |
| **포커스 순서** | 스킵 링크 | "본문으로 바로가기" 링크 | 🟡 High |
| **키보드 트랩** | 트랩 방지 | 키보드 사용자 갇힘 방지 | 🔴 Critical |
| **커스텀 컨트롤** | 드롭다운/슬라이더 | 키보드로 조작 가능 | 🟡 High |
| **폼 네비게이션** | 폼 필드 순서 | 논리적 필드 순서 | 🟡 High |
| **에러 포커스** | 에러 시 포커스 | 에러 필드로 자동 포커스 | 🟡 High |

### 2. 스크린 리더 지원 (12개 케이스)
| 분류 | 테스트 케이스 | 예상 결과 | 우선순위 |
|------|--------------|-----------|----------|
| **Alt 텍스트** | 이미지 대체 텍스트 | 모든 이미지에 의미있는 alt | 🔴 Critical |
| **ARIA 레이블** | 폼 요소 레이블 | label/aria-label 제공 | 🔴 Critical |
| **ARIA 역할** | 커스텀 요소 | 적절한 role 속성 | 🟡 High |
| **ARIA 상태** | 동적 상태 변경 | aria-expanded, aria-checked | 🟡 High |
| **ARIA 설명** | 추가 설명 | aria-describedby 활용 | 🟡 High |
| **랜드마크** | 페이지 구조 | main, nav, aside, footer | 🟡 High |
| **제목 구조** | h1-h6 계층 | 논리적 제목 구조 | 🔴 Critical |
| **링크 텍스트** | 링크 목적 | "여기" 대신 구체적 텍스트 | 🟡 High |
| **리스트 구조** | ul/ol/dl | 의미있는 리스트 마크업 | 🟡 High |
| **테이블 헤더** | th/caption | 데이터 테이블 구조 | 🟡 High |
| **라이브 리전** | 동적 업데이트 | aria-live 영역 설정 | 🟡 High |
| **언어 속성** | lang 속성 | 페이지/요소 언어 명시 | 🟡 High |

### 3. 시각적 접근성 (8개 케이스)
| 분류 | 테스트 케이스 | 예상 결과 | 우선순위 |
|------|--------------|-----------|----------|
| **색상 대비** | WCAG AA 기준 | 4.5:1 대비 비율 준수 | 🔴 Critical |
| **색상 의존성** | 색상 외 정보 | 색상만으로 의미 전달 금지 | 🔴 Critical |
| **폰트 크기** | 최소 폰트 크기 | 16px 이상 본문 텍스트 | 🟡 High |
| **줄 간격** | 행간 설정 | 1.5배 이상 행간 | 🟡 High |
| **확대 지원** | 200% 확대 | 기능성 유지하며 확대 | 🔴 Critical |
| **모션 감소** | 애니메이션 제어 | prefers-reduced-motion 지원 | 🟡 High |
| **고대비 모드** | Windows 고대비 | 고대비 모드 대응 | 🟢 Medium |
| **다크 모드** | 다크 테마 | 다크 모드 색상 대비 | 🟢 Medium |

---

## ⚡ 크로스 브라우저 테스트 (25개 케이스) ⭐ 신규 추가

### 1. 브라우저별 호환성 (15개 케이스)
| 브라우저 | 테스트 케이스 | 예상 결과 | 우선순위 |
|---------|--------------|-----------|----------|
| **Chrome (Latest)** | 모든 기능 동작 | 100% 기능 동작 | 🔴 Critical |
| **Firefox (Latest)** | 모든 기능 동작 | 100% 기능 동작 | 🔴 Critical |
| **Safari (Latest)** | 모든 기능 동작 | 99% 기능 동작 | 🟡 High |
| **Edge (Latest)** | 모든 기능 동작 | 100% 기능 동작 | 🟡 High |
| **Chrome Mobile** | 모바일 기능 | 터치 최적화 동작 | 🔴 Critical |
| **Safari iOS** | iOS 기능 | iOS 네이티브 동작 | 🔴 Critical |
| **Samsung Internet** | 안드로이드 기능 | 안드로이드 최적화 | 🟡 High |
| **웹뷰** | 앱 내 브라우저 | 웹뷰 환경 동작 | 🟡 High |
| **구형 Chrome (2년 이전)** | 기본 기능 | 핵심 기능 유지 | 🟢 Medium |
| **구형 Safari (iOS 14)** | iOS 호환성 | 기본 기능 동작 | 🟢 Medium |
| **인터넷 익스플로러** | 기본 지원 | 폴백 메시지 표시 | 🟢 Medium |
| **JavaScript 비활성화** | 기본 기능 | 핵심 콘텐츠 접근 가능 | 🟢 Medium |
| **CSS 비활성화** | 텍스트 접근성 | 구조화된 콘텐츠 표시 | 🟢 Medium |
| **이미지 비활성화** | 대체 텍스트 | alt 텍스트로 의미 전달 | 🟡 High |
| **쿠키 비활성화** | 기본 기능 | 로그인 없이 콘텐츠 접근 | 🟡 High |

### 2. 성능 브라우저별 (10개 케이스)
| 성능 지표 | 테스트 케이스 | 목표값 | 우선순위 |
|----------|--------------|--------|----------|
| **Chrome 로딩 속도** | 페이지 로드 시간 | < 3초 | 🔴 Critical |
| **Firefox 로딩 속도** | 페이지 로드 시간 | < 3초 | 🔴 Critical |
| **Safari 로딩 속도** | 페이지 로드 시간 | < 4초 | 🟡 High |
| **모바일 Chrome 속도** | 3G 환경 로딩 | < 5초 | 🔴 Critical |
| **모바일 Safari 속도** | 3G 환경 로딩 | < 5초 | 🔴 Critical |
| **메모리 사용량** | Chrome 메모리 | < 100MB | 🟡 High |
| **CPU 사용률** | JavaScript 실행 | < 50% CPU | 🟡 High |
| **배터리 효율성** | 모바일 배터리 | 최적화된 사용 | 🟢 Medium |
| **캐시 효율성** | 재방문 속도 | < 1초 | 🟡 High |
| **오프라인 기능** | 서비스 워커 | 기본 캐시 동작 | 🟢 Medium |

---

## 🚀 강화된 테스트 실행 계획

### 📅 상세 테스트 일정 (12-15시간)
| 단계 | 테스트 대상 | 브라우저 그룹 | 소요시간 | 병렬 실행 |
|------|------------|-------------|---------|----------|
| **1단계** | 일반 유저 기본 기능 | Browser A | 2시간 | A 단독 |
| **2단계** | 반응형 UI (8 브레이크포인트) | Browser C | 4시간 | C 단독 |
| **3단계** | 기업 관리자 기능 | Browser B | 2시간 | B 단독 |
| **4단계** | 탑마케팅 관리자 기능 | Browser A | 1.5시간 | A 단독 |
| **5단계** | 접근성 + 터치 인터페이스 | Browser C | 2시간 | C 단독 |
| **6단계** | 크로스 브라우저 테스트 | A + B + C | 1.5시간 | 3개 병렬 |
| **7단계** | 성능 + 보안 테스트 | A + B | 2시간 | 2개 병렬 |
| **8단계** | 최종 통합 검증 | A + B + C | 1시간 | 3개 병렬 |

### 🎭 Playwright 헤드리스 설정 강화
```javascript
// 강화된 3개 브라우저 설정
const enhancedTestConfig = {
  // Browser A: 일반 기능 테스트
  browserA: {
    viewport: { width: 1920, height: 1080 },
    deviceScaleFactor: 1,
    isMobile: false,
    hasTouch: false,
    headless: true,
    args: ['--disable-web-security', '--allow-running-insecure-content']
  },
  
  // Browser B: 관리자 + 성능 테스트  
  browserB: {
    viewport: { width: 1366, height: 768 },
    deviceScaleFactor: 1,
    isMobile: false,
    hasTouch: false,
    headless: true,
    args: ['--memory-pressure-off', '--max-old-space-size=4096']
  },
  
  // Browser C: 반응형 + 접근성 테스트
  browserC: {
    viewport: { width: 375, height: 667 }, // 시작은 모바일
    deviceScaleFactor: 2,
    isMobile: true,
    hasTouch: true,
    headless: true,
    // 동적 뷰포트 변경 기능
    dynamicViewport: true,
    breakpoints: breakpoints // 8개 브레이크포인트 순환
  },
  
  // 병렬 실행 최적화
  parallel: {
    maxBrowsers: 3,
    startDelay: 3000, // 3초 간격으로 브라우저 시작
    networkThrottle: false, // 로컬 테스트이므로 네트워크 제한 없음
    retries: 3, // 실패 시 3회 재시도
    timeout: 30000 // 30초 타임아웃
  }
};
```

### 🔧 반응형 테스트 전용 헬퍼 함수
```javascript
class ResponsiveTestHelpers extends E2ETestHelpers {
  // 동적 뷰포트 변경
  async changeViewport(page, breakpoint) {
    const config = breakpoints[breakpoint];
    await page.setViewportSize({ 
      width: config.width, 
      height: config.height 
    });
    
    // 터치 설정
    if (config.touch) {
      await page.emulateTouch(true);
    }
    
    // 안정화 대기
    await this.waitForStableState(page);
    await this.waitForLayoutShift(page);
  }
  
  // 레이아웃 시프트 대기
  async waitForLayoutShift(page) {
    await page.waitForTimeout(1000); // CSS 전환 대기
    await page.waitForLoadState('networkidle');
  }
  
  // 터치 제스처 시뮬레이션
  async simulateSwipe(page, selector, direction) {
    const element = await page.$(selector);
    const box = await element.boundingBox();
    
    const startX = box.x + box.width / 2;
    const startY = box.y + box.height / 2;
    
    let endX, endY;
    switch (direction) {
      case 'left': endX = startX - 200; endY = startY; break;
      case 'right': endX = startX + 200; endY = startY; break;
      case 'up': endX = startX; endY = startY - 200; break;
      case 'down': endX = startX; endY = startY + 200; break;
    }
    
    await page.touchscreen.tap(startX, startY);
    await page.mouse.move(startX, startY);
    await page.mouse.down();
    await page.mouse.move(endX, endY);
    await page.mouse.up();
    
    await this.waitForStableState(page);
  }
  
  // 핀치 줌 시뮬레이션
  async simulatePinch(page, selector, scale) {
    const element = await page.$(selector);
    const box = await element.boundingBox();
    
    // 핀치 제스처 시뮬레이션 로직
    await page.evaluate((scale) => {
      window.dispatchEvent(new WheelEvent('wheel', {
        deltaY: scale > 1 ? -100 : 100,
        ctrlKey: true
      }));
    }, scale);
    
    await this.waitForStableState(page);
  }
  
  // 오리엔테이션 변경
  async changeOrientation(page, orientation) {
    const viewport = page.viewportSize();
    
    if (orientation === 'portrait') {
      await page.setViewportSize({
        width: Math.min(viewport.width, viewport.height),
        height: Math.max(viewport.width, viewport.height)
      });
    } else {
      await page.setViewportSize({
        width: Math.max(viewport.width, viewport.height),
        height: Math.min(viewport.width, viewport.height)
      });
    }
    
    await this.waitForLayoutShift(page);
  }
  
  // 접근성 스캔
  async runAccessibilityCheck(page) {
    // axe-core를 이용한 접근성 검사
    await page.addScriptTag({ 
      url: 'https://unpkg.com/axe-core@latest/axe.min.js' 
    });
    
    const results = await page.evaluate(() => {
      return axe.run();
    });
    
    return {
      violations: results.violations,
      passes: results.passes.length,
      incomplete: results.incomplete.length
    };
  }
  
  // 성능 메트릭 수집
  async collectPerformanceMetrics(page) {
    const metrics = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0];
      return {
        loadTime: navigation.loadEventEnd - navigation.fetchStart,
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.fetchStart,
        firstPaint: performance.getEntriesByName('first-paint')[0]?.startTime,
        firstContentfulPaint: performance.getEntriesByName('first-contentful-paint')[0]?.startTime,
        largestContentfulPaint: window.LCP || 0
      };
    });
    
    return metrics;
  }
}
```

### 📊 강화된 성공 기준
- **전체 테스트 성공률:** **98% 이상** (420개 케이스 기준)
- **Critical 기능:** **100%** 성공 필수
- **반응형 테스트:** **95% 이상** (8개 브레이크포인트)
- **접근성 WCAG 2.1 AA:** **90% 이상** 준수
- **크로스 브라우저:** **주요 브라우저 95% 이상**
- **성능 목표:** **모든 성능 기준 달성**
- **보안 취약점:** **0건** 필수

### 🎯 테스트 완료 후 산출물
1. **📊 종합 테스트 리포트** - 420개 케이스 결과 요약
2. **📱 반응형 호환성 매트릭스** - 8 x 15 브레이크포인트 테스트 결과
3. **♿ 접근성 준수 보고서** - WCAG 2.1 AA 기준 상세 분석
4. **🌐 크로스 브라우저 호환성 차트** - 브라우저별 기능 지원 현황
5. **🔧 개선 권고사항** - 발견된 이슈별 우선순위 및 해결 방안
6. **📸 시각적 회귀 테스트** - 브레이크포인트별 스크린샷 비교
7. **⚡ 성능 벤치마크** - 디바이스별 로딩 속도 및 최적화 제안

---

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[{"id": "1", "content": "\uae30\uc874 \ud14c\uc2a4\ud2b8 \uacc4\ud68d\uc11c \uac80\ud1a0 \ubc0f \ubc18\uc751\ud615 UI \ud14c\uc2a4\ud2b8 \uac15\ud654", "status": "completed"}, {"id": "2", "content": "8\uac1c \ube0c\ub808\uc774\ud06c\ud3ec\uc778\ud2b8 \uc138\ubd84\ud654 \uc124\uacc4", "status": "completed"}, {"id": "3", "content": "\ubaa8\ub4e0 \uc8fc\uc694 \ud398\uc774\uc9c0 \ubc18\uc751\ud615 \ud14c\uc2a4\ud2b8 \ucf00\uc774\uc2a4 \uc791\uc131", "status": "completed"}, {"id": "4", "content": "\ud130\uce58 \uc778\ud130\ub799\uc158 \ubc0f \uc131\ub2a5 \ud14c\uc2a4\ud2b8 \ucd94\uac00", "status": "completed"}, {"id": "5", "content": "\uac15\ud654\ub41c \ud14c\uc2a4\ud2b8 \uacc4\ud68d\uc11c \uc5c5\ub370\uc774\ud2b8", "status": "completed"}]