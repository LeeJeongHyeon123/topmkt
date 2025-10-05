<script>
/**
 * ApiClient 클래스
 *
 * 탑마케팅 프로젝트의 모든 API 호출을 통합 관리하는 재사용 가능한 HTTP 클라이언트
 *
 * 주요 기능:
 * - fetch API 래퍼 (GET, POST, PUT, DELETE)
 * - 자동 에러 처리 (Toast 통합)
 * - 자동 로딩 인디케이터 (Loading 통합)
 * - JWT/CSRF 토큰 자동 주입
 * - 응답 형태 정규화
 * - 인터셉터 시스템
 *
 * @package TOPMKT
 * @subpackage Components\ApiClient
 * @version 1.0.0
 * @since 2025-10-05 (v3.42.0)
 */

(function(window) {
    'use strict';

    /**
     * ApiClient 클래스
     */
    class ApiClient {
        constructor(config = {}) {
            // 기본 설정
            this.config = {
                baseURL: config.baseURL || '',
                timeout: config.timeout || 30000, // 30초
                headers: config.headers || {},

                // 자동 기능 활성화/비활성화
                autoLoading: config.autoLoading !== false, // 기본 활성화
                autoErrorHandling: config.autoErrorHandling !== false, // 기본 활성화
                autoTokenInjection: config.autoTokenInjection !== false, // 기본 활성화

                // 인증 실패 시 리다이렉트
                authRedirect: config.authRedirect !== false, // 기본 활성화
                authRedirectUrl: config.authRedirectUrl || '/auth/login',

                // 응답 형태 정규화
                normalizeResponse: config.normalizeResponse !== false // 기본 활성화
            };

            // 인터셉터
            this.requestInterceptors = [];
            this.responseInterceptors = [];
        }

        /**
         * 요청 인터셉터 추가
         * @param {Function} interceptor - (config) => config
         */
        addRequestInterceptor(interceptor) {
            if (typeof interceptor === 'function') {
                this.requestInterceptors.push(interceptor);
            }
        }

        /**
         * 응답 인터셉터 추가
         * @param {Function} interceptor - (response) => response
         */
        addResponseInterceptor(interceptor) {
            if (typeof interceptor === 'function') {
                this.responseInterceptors.push(interceptor);
            }
        }

        /**
         * JWT 토큰 가져오기
         * @returns {string|null}
         */
        getAuthToken() {
            return localStorage.getItem('jwt_token') || null;
        }

        /**
         * CSRF 토큰 가져오기
         * @returns {string|null}
         */
        getCsrfToken() {
            // 전역 함수 사용 (기존 코드 호환)
            if (typeof window.getCsrfToken === 'function') {
                return window.getCsrfToken();
            }

            // Meta 태그에서 가져오기
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            return metaTag ? metaTag.getAttribute('content') : null;
        }

        /**
         * 요청 헤더 구성
         * @param {object} customHeaders - 사용자 정의 헤더
         * @param {object} options - 요청 옵션
         * @returns {object}
         */
        buildHeaders(customHeaders = {}, options = {}) {
            const headers = {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...this.config.headers,
                ...customHeaders
            };

            // JWT 토큰 자동 주입
            if (this.config.autoTokenInjection && !options.noAuth) {
                const token = this.getAuthToken();
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
            }

            // CSRF 토큰 자동 주입 (POST, PUT, DELETE)
            if (this.config.autoTokenInjection && !options.noCsrf) {
                const method = options.method || 'GET';
                if (['POST', 'PUT', 'DELETE'].includes(method.toUpperCase())) {
                    const csrfToken = this.getCsrfToken();
                    if (csrfToken) {
                        headers['X-CSRF-Token'] = csrfToken;
                    }
                }
            }

            return headers;
        }

        /**
         * 요청 인터셉터 실행
         * @param {object} config - 요청 설정
         * @returns {object}
         */
        async runRequestInterceptors(config) {
            let modifiedConfig = { ...config };

            for (const interceptor of this.requestInterceptors) {
                try {
                    modifiedConfig = await interceptor(modifiedConfig);
                } catch (error) {
                    console.error('❌ 요청 인터셉터 오류:', error);
                }
            }

            return modifiedConfig;
        }

        /**
         * 응답 인터셉터 실행
         * @param {object} response - 응답 데이터
         * @returns {object}
         */
        async runResponseInterceptors(response) {
            let modifiedResponse = { ...response };

            for (const interceptor of this.responseInterceptors) {
                try {
                    modifiedResponse = await interceptor(modifiedResponse);
                } catch (error) {
                    console.error('❌ 응답 인터셉터 오류:', error);
                }
            }

            return modifiedResponse;
        }

        /**
         * 응답 정규화 (다양한 응답 형태를 통일)
         * @param {object} data - 원본 응답 데이터
         * @returns {object} { success, data, message }
         */
        normalizeResponse(data) {
            if (!this.config.normalizeResponse) {
                return data;
            }

            // 이미 정규화된 형태
            if (data.hasOwnProperty('success') && data.hasOwnProperty('data') && data.hasOwnProperty('message')) {
                return data;
            }

            // 패턴 1: { status: 'success', data: {...}, message: '...' }
            if (data.status === 'success') {
                return {
                    success: true,
                    data: data.data || null,
                    message: data.message || ''
                };
            }

            // 패턴 2: { success: true, ... }
            if (data.success === true) {
                return {
                    success: true,
                    data: data.data || data,
                    message: data.message || ''
                };
            }

            // 패턴 3: { data: { success: true, ... } }
            if (data.data && data.data.success === true) {
                return {
                    success: true,
                    data: data.data.data || data.data,
                    message: data.data.message || data.message || ''
                };
            }

            // 기본: 실패로 간주
            return {
                success: false,
                data: data,
                message: data.message || data.error || '요청 처리에 실패했습니다.'
            };
        }

        /**
         * HTTP 에러 처리
         * @param {Response} response - fetch Response 객체
         * @param {object} options - 요청 옵션
         */
        async handleHttpError(response, options = {}) {
            const status = response.status;
            let errorMessage = `HTTP ${status} 오류`;

            // 401 Unauthorized
            if (status === 401) {
                errorMessage = '인증이 필요합니다. 다시 로그인해주세요.';

                if (this.config.authRedirect && !options.noAuthRedirect) {
                    // Toast 표시 후 리다이렉트
                    if (window.Toast && this.config.autoErrorHandling) {
                        window.Toast.error(errorMessage);
                    }

                    setTimeout(() => {
                        window.location.href = this.config.authRedirectUrl;
                    }, 1500);
                }
            }

            // 403 Forbidden
            else if (status === 403) {
                errorMessage = '접근 권한이 없습니다.';
            }

            // 404 Not Found
            else if (status === 404) {
                errorMessage = '요청한 리소스를 찾을 수 없습니다.';
            }

            // 500 Internal Server Error
            else if (status === 500) {
                errorMessage = '서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
            }

            // 기타 에러
            else if (status >= 400) {
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.message || errorData.error || errorMessage;
                } catch (e) {
                    // JSON 파싱 실패 시 기본 메시지 사용
                }
            }

            // Toast 자동 표시
            if (window.Toast && this.config.autoErrorHandling && !options.noErrorToast) {
                window.Toast.error(errorMessage);
            }

            throw new Error(errorMessage);
        }

        /**
         * 네트워크 에러 처리
         * @param {Error} error - 에러 객체
         * @param {object} options - 요청 옵션
         */
        handleNetworkError(error, options = {}) {
            const errorMessage = error.message || '네트워크 오류가 발생했습니다.';

            // Toast 자동 표시
            if (window.Toast && this.config.autoErrorHandling && !options.noErrorToast) {
                window.Toast.error(errorMessage);
            }

            throw error;
        }

        /**
         * HTTP 요청 실행
         * @param {string} url - 요청 URL
         * @param {object} options - fetch 옵션
         * @returns {Promise<object>}
         */
        async request(url, options = {}) {
            const fullUrl = this.config.baseURL + url;

            // 로딩 인디케이터 시작
            const showLoading = this.config.autoLoading && !options.noLoading;
            if (showLoading && window.Loading) {
                window.Loading.show();
            }

            try {
                // 요청 설정 구성
                let requestConfig = {
                    method: options.method || 'GET',
                    headers: this.buildHeaders(options.headers, options),
                    ...options
                };

                // body가 있고 객체인 경우 JSON 문자열로 변환
                if (requestConfig.body && typeof requestConfig.body === 'object' && !(requestConfig.body instanceof FormData)) {
                    requestConfig.body = JSON.stringify(requestConfig.body);
                }

                // 요청 인터셉터 실행
                requestConfig = await this.runRequestInterceptors(requestConfig);

                // fetch 실행 (타임아웃 적용)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);

                const response = await fetch(fullUrl, {
                    ...requestConfig,
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                // HTTP 상태 코드 체크
                if (!response.ok) {
                    await this.handleHttpError(response, options);
                }

                // JSON 응답 파싱
                let data = await response.json();

                // 응답 정규화
                data = this.normalizeResponse(data);

                // 응답 인터셉터 실행
                data = await this.runResponseInterceptors(data);

                return data;

            } catch (error) {
                // AbortError (타임아웃)
                if (error.name === 'AbortError') {
                    this.handleNetworkError(new Error('요청 시간이 초과되었습니다.'), options);
                } else {
                    this.handleNetworkError(error, options);
                }
            } finally {
                // 로딩 인디케이터 종료
                if (showLoading && window.Loading) {
                    window.Loading.hide();
                }
            }
        }

        /**
         * GET 요청
         * @param {string} url - 요청 URL
         * @param {object} options - 요청 옵션
         * @returns {Promise<object>}
         */
        async get(url, options = {}) {
            return this.request(url, { ...options, method: 'GET' });
        }

        /**
         * POST 요청
         * @param {string} url - 요청 URL
         * @param {object} data - 요청 데이터
         * @param {object} options - 요청 옵션
         * @returns {Promise<object>}
         */
        async post(url, data = {}, options = {}) {
            return this.request(url, { ...options, method: 'POST', body: data });
        }

        /**
         * PUT 요청
         * @param {string} url - 요청 URL
         * @param {object} data - 요청 데이터
         * @param {object} options - 요청 옵션
         * @returns {Promise<object>}
         */
        async put(url, data = {}, options = {}) {
            return this.request(url, { ...options, method: 'PUT', body: data });
        }

        /**
         * DELETE 요청
         * @param {string} url - 요청 URL
         * @param {object} options - 요청 옵션
         * @returns {Promise<object>}
         */
        async delete(url, options = {}) {
            return this.request(url, { ...options, method: 'DELETE' });
        }
    }

    // 전역 인스턴스 생성
    window.ApiClient = new ApiClient();

    console.log('✅ ApiClient 클래스 로드 완료 (v3.42.0)');

})(window);
</script>
