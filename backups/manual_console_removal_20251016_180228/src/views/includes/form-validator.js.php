<script>
/**
 * FormValidator 클래스
 *
 * 탑마케팅 프로젝트의 모든 폼 검증을 통합 관리하는 재사용 가능한 JavaScript 클래스
 *
 * 주요 기능:
 * - 이메일 형식 검증
 * - 전화번호 형식 검증 (한국 휴대폰 번호)
 * - 비밀번호 강도 검증
 * - 에러 메시지 표시/제거
 * - 실시간 검증 UI 업데이트
 *
 * @package TOPMKT
 * @subpackage Components\FormValidator
 * @version 1.0.0
 * @since 2025-10-05 (v3.41.0)
 */

(function(window) {
    'use strict';

    /**
     * FormValidator 클래스
     */
    class FormValidator {
        constructor() {
            // 정규식 패턴
            this.patterns = {
                // 이메일: 기본적인 이메일 형식 (example@domain.com)
                email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,

                // 전화번호: 한국 휴대폰 번호 (010-1234-5678, 01012345678 모두 허용)
                phone: /^(010|011|016|017|018|019)[-]?\d{3,4}[-]?\d{4}$/,

                // 전화번호 (엄격): 하이픈 필수 (010-1234-5678)
                phoneStrict: /^010-\d{3,4}-\d{4}$/,

                // 비밀번호 요구사항
                passwordLength: /.{8,}/, // 최소 8자
                passwordLetter: /[a-zA-Z]/, // 영문자 포함
                passwordNumber: /[0-9]/, // 숫자 포함
                passwordSpecial: /[!@#$%^&*(),.?":{}|<>]/, // 특수문자 포함

                // 보안 패턴 (취약한 비밀번호 감지)
                passwordRepeat: /(.)\1{2,}/, // 3글자 이상 연속 반복
                passwordSequence: /(?:abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz|012|123|234|345|456|567|678|789)/i
            };
        }

        /**
         * 이메일 형식 검증
         * @param {string} email - 검증할 이메일 주소
         * @returns {boolean} 유효 여부
         */
        isValidEmail(email) {
            if (!email || typeof email !== 'string') {
                return false;
            }
            return this.patterns.email.test(email.trim());
        }

        /**
         * 전화번호 형식 검증 (유연한 검증)
         * @param {string} phone - 검증할 전화번호
         * @returns {boolean} 유효 여부
         */
        isValidPhone(phone) {
            if (!phone || typeof phone !== 'string') {
                return false;
            }
            // 공백 제거 후 검증
            const cleanPhone = phone.replace(/\s/g, '');
            return this.patterns.phone.test(cleanPhone);
        }

        /**
         * 전화번호 형식 검증 (엄격한 검증, 하이픈 필수)
         * @param {string} phone - 검증할 전화번호
         * @returns {boolean} 유효 여부
         */
        isValidPhoneStrict(phone) {
            if (!phone || typeof phone !== 'string') {
                return false;
            }
            return this.patterns.phoneStrict.test(phone.trim());
        }

        /**
         * 비밀번호 요구사항 검증
         * @param {string} password - 검증할 비밀번호
         * @returns {object} 검증 결과 { isValid, requirements }
         */
        validatePassword(password) {
            if (!password || typeof password !== 'string') {
                return {
                    isValid: false,
                    requirements: {
                        length: false,
                        letter: false,
                        number: false,
                        special: false
                    }
                };
            }

            const requirements = {
                length: this.patterns.passwordLength.test(password),
                letter: this.patterns.passwordLetter.test(password),
                number: this.patterns.passwordNumber.test(password),
                special: this.patterns.passwordSpecial.test(password)
            };

            const isValid = Object.values(requirements).every(req => req === true);

            return {
                isValid,
                requirements
            };
        }

        /**
         * 비밀번호 강도 계산
         * @param {string} password - 검증할 비밀번호
         * @returns {object} 강도 정보 { score, label, className }
         */
        calculatePasswordStrength(password) {
            if (!password || password.length === 0) {
                return { score: 0, label: '', className: '' };
            }

            let score = 0;
            const checks = {
                length: password.length >= 8,
                longLength: password.length >= 12,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
                noRepeat: !this.patterns.passwordRepeat.test(password),
                noSequence: !this.patterns.passwordSequence.test(password)
            };

            // 점수 계산
            if (checks.length) score += 1;
            if (checks.longLength) score += 1;
            if (checks.lowercase) score += 1;
            if (checks.uppercase) score += 1;
            if (checks.number) score += 1;
            if (checks.special) score += 1;
            if (checks.noRepeat) score += 1;
            if (checks.noSequence) score += 1;

            // 강도 분류
            if (score >= 7) return { score, label: '매우 강함', className: 'strong' };
            if (score >= 5) return { score, label: '강함', className: 'good' };
            if (score >= 3) return { score, label: '보통', className: 'fair' };
            return { score, label: '약함', className: 'weak' };
        }

        /**
         * 비밀번호 일치 검증
         * @param {string} password - 원본 비밀번호
         * @param {string} passwordConfirm - 확인 비밀번호
         * @returns {boolean} 일치 여부
         */
        validatePasswordMatch(password, passwordConfirm) {
            if (!password || !passwordConfirm) {
                return false;
            }
            return password === passwordConfirm;
        }

        /**
         * 필드 에러 표시
         * @param {HTMLElement|string} field - 필드 요소 또는 선택자
         * @param {string} message - 에러 메시지
         */
        showFieldError(field, message) {
            const element = typeof field === 'string' ? document.querySelector(field) : field;
            if (!element) {
                console.warn('FormValidator: 필드를 찾을 수 없습니다:', field);
                return;
            }

            // 기존 에러 메시지 제거
            this.clearFieldError(element);

            // 필드에 에러 클래스 추가
            element.classList.add('error', 'invalid');
            element.classList.remove('valid');

            // 에러 메시지 요소 생성
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message field-error';
            errorDiv.textContent = message;
            errorDiv.setAttribute('data-field-error', '');

            // 에러 메시지 삽입
            if (element.parentNode) {
                element.parentNode.insertBefore(errorDiv, element.nextSibling);
            }
        }

        /**
         * 필드 에러 제거
         * @param {HTMLElement|string} field - 필드 요소 또는 선택자
         */
        clearFieldError(field) {
            const element = typeof field === 'string' ? document.querySelector(field) : field;
            if (!element) {
                return;
            }

            // 에러 클래스 제거
            element.classList.remove('error', 'invalid');

            // 에러 메시지 제거
            const errorMessage = element.parentNode?.querySelector('[data-field-error]');
            if (errorMessage) {
                errorMessage.remove();
            }
        }

        /**
         * 폼 전체 에러 표시
         * @param {object} errors - 에러 객체 { fieldId: message, ... }
         * @param {string} formSelector - 폼 선택자 (기본: '#registrationForm')
         */
        showFormErrors(errors, formSelector = '#registrationForm') {
            const form = document.querySelector(formSelector);
            if (!form) {
                console.warn('FormValidator: 폼을 찾을 수 없습니다:', formSelector);
                return;
            }

            // 기존 에러 메시지 모두 제거
            this.clearFormErrors(formSelector);

            // 에러 표시
            for (const [field, message] of Object.entries(errors)) {
                if (field === 'general') {
                    // 일반 에러는 폼 상단에 표시
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message general-error';
                    errorDiv.textContent = message;
                    errorDiv.setAttribute('data-general-error', '');
                    form.insertBefore(errorDiv, form.firstChild);
                } else {
                    // 필드별 에러
                    const element = document.getElementById(field);
                    if (element) {
                        this.showFieldError(element, message);
                    }
                }
            }
        }

        /**
         * 폼 전체 에러 제거
         * @param {string} formSelector - 폼 선택자 (기본: '#registrationForm')
         */
        clearFormErrors(formSelector = '#registrationForm') {
            const form = document.querySelector(formSelector);
            if (!form) {
                return;
            }

            // 일반 에러 메시지 제거
            const generalErrors = form.querySelectorAll('[data-general-error]');
            generalErrors.forEach(el => el.remove());

            // 필드 에러 메시지 제거
            const fieldErrors = form.querySelectorAll('[data-field-error]');
            fieldErrors.forEach(el => el.remove());

            // 모든 입력 필드의 에러 클래스 제거
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.classList.remove('error', 'invalid');
            });
        }

        /**
         * 필드 값 검증 (빈 값 체크)
         * @param {string} value - 검증할 값
         * @param {string} fieldName - 필드 이름 (에러 메시지용)
         * @returns {object} { isValid, message }
         */
        validateRequired(value, fieldName = '필드') {
            const trimmedValue = typeof value === 'string' ? value.trim() : value;
            if (!trimmedValue || trimmedValue.length === 0) {
                return {
                    isValid: false,
                    message: `${fieldName}은(는) 필수 입력 항목입니다.`
                };
            }
            return { isValid: true, message: '' };
        }

        /**
         * 문자열 길이 검증
         * @param {string} value - 검증할 값
         * @param {number} min - 최소 길이
         * @param {number} max - 최대 길이
         * @param {string} fieldName - 필드 이름
         * @returns {object} { isValid, message }
         */
        validateLength(value, min, max, fieldName = '필드') {
            const length = value ? value.length : 0;

            if (min && length < min) {
                return {
                    isValid: false,
                    message: `${fieldName}은(는) 최소 ${min}자 이상이어야 합니다.`
                };
            }

            if (max && length > max) {
                return {
                    isValid: false,
                    message: `${fieldName}은(는) 최대 ${max}자까지 입력 가능합니다.`
                };
            }

            return { isValid: true, message: '' };
        }
    }

    // 전역 인스턴스 생성
    window.FormValidator = new FormValidator();

    console.log('✅ FormValidator 클래스 로드 완료 (v3.41.0)');

})(window);
</script>
