# 132번 강의 강사 이미지 표시 문제 해결 가이드

## 문제 상황
132번 강의의 강사 이미지가 표시되지 않는 문제

## 구현된 해결 방안

### 1. 디버깅 정보 출력 기능 추가

#### 강의 상세 페이지 디버깅
- **위치**: `/src/views/lectures/detail.php` (1105-1134 라인)
- **사용법**: 강의 상세 페이지 URL에 `?debug` 파라미터 추가
- **예시**: `/lectures/132?debug`

**디버깅 정보 내용:**
- 강의 ID
- instructor_name 필드 값
- instructor_info 필드 값  
- instructors_json 필드 값
- JSON 파싱 결과
- 각 강사 이미지 파일 존재 여부 및 경로

#### 독립적인 디버깅 스크립트
- **파일**: `/public/debug_lecture_132.php`
- **접근**: 웹에서 직접 접근 가능
- **기능**: 132번 강의의 상세 데이터 분석

### 2. 향상된 Fallback 처리 시스템

#### 기본 강사 이미지 자동 할당
- **위치**: `/src/views/lectures/detail.php` (1175-1196 라인)
- **기능**: 
  - 강사 이미지가 없거나 파일이 존재하지 않을 때 기본 이미지 자동 선택
  - 강사 이름 해시 기반 일관된 이미지 할당
  - 6개의 기본 강사 이미지 중 선택

**사용되는 기본 이미지들:**
```
/assets/uploads/instructors/instructor-1.jpg
/assets/uploads/instructors/instructor-2.jpg  
/assets/uploads/instructors/instructor-3.jpg
/assets/uploads/instructors/instructor-kim.jpg
/assets/uploads/instructors/instructor-lee.jpg
/assets/uploads/instructors/instructor-park.jpg
```

#### 향상된 CSS 스타일
- **기능**:
  - 로딩 상태 애니메이션
  - 오류 상태 시각적 표시
  - 플레이스홀더 개선 (강사 아이콘 배지 추가)
  - 호버 효과 개선

#### JavaScript 이미지 로딩 개선
- **위치**: `/src/views/lectures/detail.php` (1838-1886 라인)
- **기능**:
  - 이미지 로딩 상태 모니터링
  - 로딩 실패 시 자동 fallback 처리
  - 콘솔 로깅으로 디버깅 지원

### 3. 132번 강의 데이터 업데이트 도구

#### 자동 업데이트 스크립트
- **파일**: `/public/update_lecture_132_instructor.php`
- **기능**:
  - 132번 강의의 instructors_json 필드 자동 생성
  - 기본 강사 이미지 자동 할당
  - 업데이트 결과 실시간 확인

#### SQL 스크립트
- **파일**: `/fix_lecture_132_instructor_images.sql`
- **기능**:
  - 132번 강의 단일 업데이트
  - 전체 강의 일괄 업데이트 옵션
  - 강사명 기반 자동 이미지 할당

## 사용 방법

### 1. 즉시 문제 해결
```bash
# 웹에서 직접 실행
curl http://yoursite.com/update_lecture_132_instructor.php
```

### 2. 디버깅 정보 확인
```bash
# 브라우저에서 접근
http://yoursite.com/lectures/132?debug

# 또는 독립 스크립트로
http://yoursite.com/debug_lecture_132.php
```

### 3. 전체 강의 일괄 처리 (선택사항)
```sql
-- SQL 스크립트 실행
mysql -u username -p database_name < fix_lecture_132_instructor_images.sql
```

## 개선된 기능들

### 1. 이미지 로딩 상태 표시
- 로딩 중: 회전하는 스피너 애니메이션
- 성공: 부드러운 페이드인 효과
- 실패: 오류 표시 배지와 플레이스홀더

### 2. 플레이스홀더 개선
- 강사 이름 첫 글자 표시
- 강사 아이콘 배지 추가
- 그라데이션 배경
- 호버 효과

### 3. 자동 이미지 선택 알고리즘
```php
// 강사 이름 기반 일관된 이미지 선택
$nameHash = crc32($name);
$selectedImage = $defaultImages[$nameHash % count($defaultImages)];
```

### 4. 실시간 디버깅
- 브라우저 콘솔에서 이미지 로딩 상태 확인
- HTML 주석으로 서버사이드 디버깅 정보 제공

## 파일 구조

```
/workspace/
├── src/views/lectures/detail.php          # 메인 강의 상세 페이지 (개선됨)
├── public/
│   ├── debug_lecture_132.php              # 디버깅 스크립트
│   ├── update_lecture_132_instructor.php  # 업데이트 스크립트
│   └── assets/uploads/instructors/        # 강사 이미지 폴더
├── fix_lecture_132_instructor_images.sql  # SQL 업데이트 스크립트
└── fix_lecture_132_instructor_images.md   # 이 가이드 문서
```

## 테스트 방법

### 1. 기본 테스트
```bash
# 1. 디버깅 정보 확인
curl http://yoursite.com/debug_lecture_132.php

# 2. 업데이트 실행
curl http://yoursite.com/update_lecture_132_instructor.php

# 3. 결과 확인
curl http://yoursite.com/lectures/132?debug
```

### 2. 브라우저 테스트
1. `/lectures/132` 페이지 방문
2. 강사 이미지 영역 확인
3. 개발자 도구 콘솔에서 로딩 메시지 확인
4. `?debug` 파라미터 추가하여 디버깅 정보 확인

## 향후 개선 사항

### 1. 이미지 최적화
- WebP 형식 지원
- 반응형 이미지 (srcset)
- 지연 로딩 (Intersection Observer)

### 2. 캐싱 개선
- 브라우저 캐시 최적화
- CDN 지원

### 3. 접근성 개선
- alt 텍스트 자동 생성
- 스크린 리더 지원

## 문제 해결

### 일반적인 문제들

1. **이미지가 여전히 표시되지 않음**
   - 파일 권한 확인: `chmod 644 /path/to/image`
   - 웹서버 재시작
   - 브라우저 캐시 삭제

2. **JSON 파싱 오류**
   - 데이터베이스 문자셋 확인 (UTF-8)
   - JSON 데이터 유효성 검사

3. **기본 이미지 파일 없음**
   - `/public/assets/uploads/instructors/` 폴더 존재 확인
   - 기본 이미지 파일들 업로드

## 지원

문제가 지속되면 다음 정보와 함께 보고:
1. 브라우저 콘솔 로그
2. 디버깅 스크립트 출력 결과
3. 네트워크 탭에서 이미지 요청 상태
4. 서버 에러 로그