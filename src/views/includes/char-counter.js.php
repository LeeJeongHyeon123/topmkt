<script>
/**
 * 탑마케팅 통합 글자 수 카운터 시스템
 * v3.29.0 - 2025-10-04
 *
 * 35개 인스턴스의 중복 코드를 제거하고 통일된 글자 수 카운터 제공
 */
class CharacterCounter {
    /**
     * 글자 수 카운터 초기화
     * @param {HTMLElement|string} inputElement - 입력 필드 또는 선택자
     * @param {HTMLElement|string} counterElement - 카운터 표시 요소 또는 선택자
     * @param {number} maxLength - 최대 글자 수
     * @param {Object} options - 옵션 설정
     */
    constructor(inputElement, counterElement, maxLength, options = {}) {
        // 요소 가져오기
        this.input = typeof inputElement === 'string'
            ? document.querySelector(inputElement)
            : inputElement;

        this.counter = typeof counterElement === 'string'
            ? document.querySelector(counterElement)
            : counterElement;

        if (!this.input || !this.counter) {
            console.error('❌ CharacterCounter: 요소를 찾을 수 없습니다', {inputElement, counterElement});
            return;
        }

        this.maxLength = maxLength;

        // 옵션 설정
        this.options = {
            warningThreshold: options.warningThreshold || 0.8,  // 80%에서 경고
            errorThreshold: options.errorThreshold || 0.9,      // 90%에서 오류
            useLocaleString: options.useLocaleString !== false,  // 천 단위 구분자 사용 여부 (기본값: true)
            showMaxLength: options.showMaxLength !== false,     // 최대 글자 수 표시 여부 (기본 true)
            warningClass: options.warningClass || 'warning',    // 경고 클래스명
            errorClass: options.errorClass || 'error',          // 오류 클래스명
            successClass: options.successClass || 'success'     // 성공 클래스명 (선택)
        };

        this.init();
    }

    /**
     * 초기화
     */
    init() {
        // 초기 카운터 업데이트
        this.updateCounter();

        // 입력 이벤트 리스너 등록
        this.input.addEventListener('input', () => this.updateCounter());

        console.log('✅ CharacterCounter 초기화 완료:', {
            input: this.input.id || this.input.name,
            maxLength: this.maxLength
        });
    }

    /**
     * 카운터 업데이트
     */
    updateCounter() {
        const currentLength = this.input.value.length;

        // 카운터 텍스트 업데이트
        if (this.options.useLocaleString) {
            // 천 단위 구분자 사용 (예: 1,234 / 2,000자)
            if (this.options.showMaxLength) {
                this.counter.textContent = `${currentLength.toLocaleString()} / ${this.maxLength.toLocaleString()}`;
            } else {
                this.counter.textContent = currentLength.toLocaleString();
            }
        } else {
            // 일반 표시 (예: 1234 / 2000)
            if (this.options.showMaxLength) {
                this.counter.textContent = `${currentLength} / ${this.maxLength}`;
            } else {
                this.counter.textContent = currentLength;
            }
        }

        // 클래스 업데이트 (상태 표시)
        this.updateClass(currentLength);

        // 스타일 업데이트 (색상 변경)
        this.updateStyle(currentLength);
    }

    /**
     * 클래스 업데이트
     */
    updateClass(currentLength) {
        const percentage = currentLength / this.maxLength;

        // 기존 클래스 제거
        this.counter.classList.remove(this.options.warningClass, this.options.errorClass);
        if (this.options.successClass) {
            this.counter.classList.remove(this.options.successClass);
        }

        // 새 클래스 추가
        if (currentLength >= this.maxLength * this.options.errorThreshold) {
            this.counter.classList.add(this.options.errorClass);
        } else if (currentLength >= this.maxLength * this.options.warningThreshold) {
            this.counter.classList.add(this.options.warningClass);
        } else if (this.options.successClass && currentLength > 0) {
            this.counter.classList.add(this.options.successClass);
        }
    }

    /**
     * 스타일 업데이트 (인라인 색상)
     */
    updateStyle(currentLength) {
        const percentage = currentLength / this.maxLength;

        // 부모 요소에 색상 적용 (레거시 호환)
        const parent = this.counter.parentElement;
        if (!parent) return;

        if (currentLength >= this.maxLength) {
            // 최대치 도달: 빨간색
            parent.style.color = '#dc2626';
        } else if (currentLength >= this.maxLength * this.options.errorThreshold) {
            // 90% 이상: 빨간색
            parent.style.color = '#dc2626';
        } else if (currentLength >= this.maxLength * this.options.warningThreshold) {
            // 80% 이상: 주황색
            parent.style.color = '#f59e0b';
        } else if (currentLength > 0) {
            // 입력 중: 초록색 (선택사항)
            parent.style.color = '#10b981';
        } else {
            // 기본: 회색
            parent.style.color = '#6b7280';
        }
    }

    /**
     * 현재 글자 수 가져오기
     */
    getCurrentLength() {
        return this.input.value.length;
    }

    /**
     * 최대 글자 수 초과 여부 확인
     */
    isOverLimit() {
        return this.getCurrentLength() > this.maxLength;
    }

    /**
     * 정적 메서드: 자동 초기화
     * data-char-counter 속성을 가진 모든 요소를 자동으로 초기화
     */
    static autoInit() {
        const elements = document.querySelectorAll('[data-char-counter]');
        const counters = [];

        elements.forEach(input => {
            const counterId = input.getAttribute('data-char-counter');
            const maxLength = parseInt(input.getAttribute('maxlength') || input.getAttribute('data-max-length'));
            const counter = document.getElementById(counterId) || document.querySelector(`[data-counter-for="${input.id}"]`);

            if (counter && maxLength) {
                const options = {
                    useLocaleString: input.getAttribute('data-use-locale') === 'true',
                    warningThreshold: parseFloat(input.getAttribute('data-warning-threshold')) || 0.8,
                    errorThreshold: parseFloat(input.getAttribute('data-error-threshold')) || 0.9
                };

                counters.push(new CharacterCounter(input, counter, maxLength, options));
            }
        });

        console.log(`✅ CharacterCounter 자동 초기화 완료: ${counters.length}개`);
        return counters;
    }
}

// 전역 객체에 등록 (레거시 호환)
window.CharacterCounter = CharacterCounter;

// DOM 로드 완료 시 자동 초기화 (선택사항)
// document.addEventListener('DOMContentLoaded', () => CharacterCounter.autoInit());

console.log('🚀 TOPMKT CharacterCounter 로드됨 (v3.29.0)');
</script>
