    </main>

    <!-- 🚀 모바일 최적화 푸터 -->
    <footer class="modern-footer">
        <div class="footer-container">
            <!-- 📱 모바일/태블릿 전용 간소화 푸터 -->
            <div class="footer-mobile">
                <!-- 로고만 (간소화) -->
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-rocket footer-logo-icon"></i>
                        <span class="footer-logo-text">탑마케팅</span>
                    </div>
                </div>

                <!-- 주요 링크 (가로 배치) -->
                <nav class="footer-nav">
                    <a href="/community" class="footer-nav-link">커뮤니티</a>
                    <a href="/lectures" class="footer-nav-link">강의</a>
                    <a href="/events" class="footer-nav-link">행사</a>
                    <a href="/notices" class="footer-nav-link">공지</a>
                </nav>

                <!-- 인라인 연락처 (더 간소화) -->
                <div class="footer-contact-inline">
                    <a href="tel:1577-9794">📞 1577-9794</a>
                    <a href="mailto:jh@wincard.kr">✉️ jh@wincard.kr</a>
                </div>

                <!-- 저작권 및 정책 -->
                <div class="footer-legal">
                    <div class="footer-copyright">
                        <span>&copy; <?= date('Y') ?> 탑마케팅</span>
                        <span class="footer-company">(주)윈카드</span>
                    </div>
                    <div class="footer-policies">
                        <a href="/terms" class="footer-policy-link">이용약관</a>
                        <span class="footer-divider">|</span>
                        <a href="/privacy" class="footer-policy-link">개인정보처리방침</a>
                    </div>
                </div>
            </div>

            <!-- 💻 데스크톱 전용 상세 푸터 -->
            <div class="footer-desktop">
                <div class="footer-desktop-content">
                    <!-- 로고 및 설명 -->
                    <div class="footer-desktop-brand">
                        <div class="footer-logo">
                            <i class="fas fa-rocket footer-logo-icon"></i>
                            <span class="footer-logo-text">탑마케팅</span>
                        </div>
                        <p class="footer-description">
                            글로벌 네트워크 마케팅 리더들의 커뮤니티<br>
                            함께 성장하고 성공을 만들어가는 플랫폼
                        </p>
                    </div>

                    <!-- 서비스 링크 -->
                    <div class="footer-desktop-section">
                        <h3 class="footer-section-title">서비스</h3>
                        <ul class="footer-section-links">
                            <li><a href="/community">커뮤니티</a></li>
                            <li><a href="/lectures">강의 일정</a></li>
                            <li><a href="/events">행사 일정</a></li>
                            <li><a href="/notices">공지사항</a></li>
                        </ul>
                    </div>

                    <!-- 정책 링크 -->
                    <div class="footer-desktop-section">
                        <h3 class="footer-section-title">정책</h3>
                        <ul class="footer-section-links">
                            <li><a href="/terms">이용약관</a></li>
                            <li><a href="/privacy">개인정보처리방침</a></li>
                        </ul>
                    </div>

                    <!-- 연락처 -->
                    <div class="footer-desktop-section">
                        <h3 class="footer-section-title">연락처</h3>
                        <div class="footer-desktop-contact">
                            <p><a href="tel:1577-9794">📞 1577-9794</a></p>
                            <p><a href="mailto:jh@wincard.kr">✉️ jh@wincard.kr</a></p>
                        </div>
                    </div>
                </div>

                <!-- 데스크톱 하단 정보 -->
                <div class="footer-desktop-bottom">
                    <div class="footer-desktop-copyright">
                        <p>&copy; <?= date('Y') ?> 탑마케팅. All rights reserved.</p>
                        <p class="footer-company-details">
                            상호명: (주)윈카드 | 대표자: 이정현 | 사업자등록번호: 133-88-02437<br>
                            주소: 서울시 금천구 가산디지털1로 204, 반도 아이비밸리 6층
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🎨 모바일 최적화 푸터 스타일 -->
        <style>
        /* 기본 푸터 컨테이너 */
        .modern-footer {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* 📱 모바일/태블릿 푸터 (기본 표시) */
        .footer-mobile {
            display: block;
            padding: 15px 0 10px;
        }

        .footer-desktop {
            display: none;
        }

        /* 브랜드 영역 */
        .footer-brand {
            text-align: center;
            margin-bottom: 12px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 0px;
        }

        .footer-logo-icon {
            font-size: 24px;
            color: #3b82f6;
        }

        .footer-logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #111827; /* 더 진한 검은색 */
        }

        .footer-tagline {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        /* 네비게이션 (가로 배치) */
        .footer-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .footer-nav-link {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            font-size: 15px; /* 더 큰 폰트 */
            font-weight: 600; /* 더 굵게 */
            color: #1f2937; /* 더 진한 색상 */
            text-decoration: none;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
        }

        .footer-nav-link:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
            color: #3b82f6;
            transform: translateY(-1px);
        }

        /* 인라인 연락처 */
        .footer-contact-inline {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-bottom: 18px;
            margin-top: 8px;
            font-size: 13px;
        }

        .footer-contact-inline a {
            color: #374151; /* 더 진한 색상 */
            font-weight: 500; /* 더 굵게 */
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-contact-inline a:hover {
            color: #3b82f6;
        }

        .footer-contact-inline span {
            color: #d1d5db;
        }

        /* 법적 정보 */
        .footer-legal {
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .footer-copyright {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 14px; /* 더 큰 폰트 */
            font-weight: 500; /* 더 굵게 */
            color: #374151; /* 더 진한 색상 */
        }

        .footer-company {
            padding: 2px 8px;
            background: rgba(107, 114, 128, 0.1);
            border-radius: 8px;
            font-size: 12px;
        }

        .footer-policies {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .footer-policy-link {
            color: #374151; /* 더 진한 색상 */
            font-weight: 500; /* 더 굵게 */
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-policy-link:hover {
            color: #3b82f6;
        }

        .footer-divider {
            color: #d1d5db;
        }

        /* 💻 데스크톱 푸터 (768px 이상) */
        @media (min-width: 769px) {
            .footer-mobile {
                display: none;
            }

            .footer-desktop {
                display: block;
                padding: 40px 0 20px;
            }

            .footer-desktop-content {
                display: grid;
                grid-template-columns: 2fr 1fr 1fr 1.5fr;
                gap: 40px;
                margin-bottom: 30px;
            }

            .footer-desktop-brand .footer-logo {
                justify-content: flex-start;
                margin-bottom: 15px;
            }

            .footer-description {
                font-size: 14px;
                color: #6b7280;
                line-height: 1.6;
                margin: 0;
            }

            .footer-section-title {
                font-size: 16px;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 15px;
            }

            .footer-section-links {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .footer-section-links li {
                margin-bottom: 8px;
            }

            .footer-section-links a {
                font-size: 14px;
                color: #6b7280;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .footer-section-links a:hover {
                color: #3b82f6;
            }

            .footer-desktop-contact p {
                margin-bottom: 8px;
                font-size: 14px;
            }

            .footer-desktop-contact a {
                color: #6b7280;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .footer-desktop-contact a:hover {
                color: #3b82f6;
            }

            .footer-desktop-bottom {
                padding-top: 20px;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .footer-desktop-copyright p {
                margin-bottom: 8px;
                font-size: 13px;
                color: #6b7280;
            }

            .footer-company-details {
                font-size: 12px;
                line-height: 1.5;
            }
        }

        /* 📱 작은 모바일 최적화 (480px 이하) */
        @media (max-width: 480px) {
            .footer-container {
                padding: 0 15px;
            }

            .footer-mobile {
                padding: 15px 0 10px;
            }

            .footer-nav {
                gap: 6px;
            }

            .footer-nav-link {
                padding: 6px 12px;
                font-size: 13px;
            }

            .footer-contact-inline {
                flex-direction: column;
                gap: 8px;
            }

            .footer-copyright {
                flex-direction: column;
                gap: 4px;
            }
        }
        </style>
    </footer>

    <!-- 스크립트 -->
    <script>

            // 드롭다운 메뉴
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');

                if (toggle && menu) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        dropdown.classList.toggle('active');
                    });

                    // 외부 클릭시 드롭다운 닫기
                    document.addEventListener('click', function(e) {
                        if (!dropdown.contains(e.target)) {
                            dropdown.classList.remove('active');
                        }
                    });
                }
            });

            // 알림 자동 사라짐
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
    </script>
    
    <!-- 레이지 로딩 스크립트 -->
    <?php
    if (file_exists(SRC_PATH . '/helpers/LazyLoadHelper.php')) {
        require_once SRC_PATH . '/helpers/LazyLoadHelper.php';
        echo LazyLoadHelper::getScript();
    }
    ?>
    
    <!-- 앱 푸시 토큰 연동 스크립트 -->
    <script>
      // 앱-웹 통신 브리지
    (function() {
        // 앱에서 푸시 토큰 받기
        window.addEventListener('message', function(event) {
            try {
                const data = JSON.parse(event.data);
                
                if (data.type === 'FCM_TOKEN') {
                    
                    // 로컬 스토리지에 저장
                    localStorage.setItem('app_push_token', data.token);

                    // 🚀 Phase 2: ApiClient로 서버에 토큰 저장
                    ApiClient.post('/api/save-push-token', {
                        token: data.token,
                        platform: 'mobile'
                    }, {
                        noLoading: true, // 백그라운드 작업이므로 로딩 UI 숨김
                        noErrorToast: true // 푸시 토큰 저장 실패는 사용자에게 표시하지 않음
                    })
                    .then(result => {
                    })
                    .catch(err => {

                        // 푸시 토큰 저장 실패는 치명적이지 않으므로 무시
                    });
                }
            } catch (e) {
            }
        });

        // 앱에 푸시 토큰 요청
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify({
                type: 'REQUEST_PUSH_TOKEN'
            }));
        }
    })();
    </script>

<!-- 🚀 v3.29.0: 통합 글자 수 카운터 시스템 -->
<?php require_once __DIR__ . '/../includes/char-counter.js.php'; ?>

<!-- 🚀 v3.30.0: 통합 Toast 알림 시스템 -->
<?php require_once __DIR__ . '/../includes/toast.js.php'; ?>

<!-- 🚀 v3.31.0: 통합 Loading 인디케이터 시스템 -->
<?php require_once __DIR__ . '/../includes/loading.js.php'; ?>

<!-- 🚀 v3.57.0: 통합 유틸리티 함수 (debounce, throttle, once, sleep) -->
<?php require_once __DIR__ . '/../includes/utils.js.php'; ?>

<!-- 🚀 v3.36.0: Modal.confirm() 시스템 -->
<script src="/assets/js/modal.js?v=<?= time() ?>"></script>

<!-- 🚀 v3.41.0: 통합 FormValidator 시스템 -->
<?php require_once __DIR__ . '/../includes/form-validator.js.php'; ?>

<!-- 🚀 v3.42.0: 통합 ApiClient HTTP 클라이언트 시스템 -->
<?php require_once __DIR__ . '/../includes/api-client.js.php'; ?>

<!-- 🚀 v3.62.0: 날짜/시간 포맷 유틸리티 시스템 -->
<?php require_once __DIR__ . '/../includes/date-utils.js.php'; ?>

<!-- 🚀 v3.65.0: 클립보드 복사 유틸리티 시스템 -->
<?php require_once __DIR__ . '/../includes/clipboard-utils.js.php'; ?>

<!-- 🚀 v3.88.0: FCM 앱 브릿지 시스템 -->
<script src="/assets/js/fcm-app-bridge.js?v=<?= time() ?>"></script>

</body>
</html> 