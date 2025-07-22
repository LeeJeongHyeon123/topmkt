# 강의 신청 취소 기능 수정 가이드

## 🔍 문제 분석

**오류**: `CSRF 토큰이 유효하지 않습니다.`
**원인**: JavaScript에서 DELETE 요청 시 CSRF 토큰을 올바르게 전달하지 않음

## 🔧 해결 방법

### 1. JavaScript 수정 필요

강의 상세 페이지의 JavaScript에서 `cancelRegistration` 함수를 다음과 같이 수정:

```javascript
async function cancelRegistration() {
    try {
        // 메타 태그에서 CSRF 토큰 가져오기
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            alert('보안 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
            return;
        }

        const response = await fetch(`/api/lectures/${lectureId}/registration`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken  // 헤더로 전달
            },
            body: JSON.stringify({
                csrf_token: csrfToken  // 바디에도 포함
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert('신청이 취소되었습니다.');
            location.reload(); // 페이지 새로고침
        } else {
            alert('취소 실패: ' + result.message);
        }

    } catch (error) {
        console.error('신청 취소 오류:', error);
        alert('신청 취소 중 오류가 발생했습니다.');
    }
}
```

### 2. HTML 헤더에 CSRF 토큰 확인

강의 상세 페이지의 `<head>` 섹션에 다음이 있는지 확인:

```html
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
```

### 3. 임시 해결책 (개발용)

개발 환경에서만 사용할 임시 해결책으로 CSRF 검증을 우회:

```php
// 개발 환경에서만 CSRF 검증 우회
if (getenv('APP_ENV') === 'development' || $_SERVER['HTTP_HOST'] === 'localhost') {
    // CSRF 검증 스킵
} else {
    if (!$this->validateCsrfToken($csrfToken)) {
        return ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
    }
}
```

## 🚀 권장 수정 순서

1. **강의 상세 페이지 JavaScript 수정** (1번 방법)
2. **CSRF 토큰 메타 태그 확인** (2번 방법)
3. **실제 웹페이지에서 테스트**

## 📱 완전한 테스트 프로세스

1. 강의 신청
2. SMS 수신 확인
3. 신청 취소
4. 취소 확인

## 🔐 보안 참고사항

- CSRF 토큰은 반드시 검증해야 함
- 임시 해결책은 개발 환경에서만 사용
- 프로덕션에서는 완전한 CSRF 보호 필요

---

**다음 단계**: 강의 상세 페이지의 JavaScript 코드를 수정하여 CSRF 토큰을 올바르게 전달