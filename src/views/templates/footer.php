    </main>

    <!-- 푸터 -->
    <footer class="main-footer modern-footer">
        <div class="container">
            <div class="footer-content">
                <!-- 상단 영역 -->
                <div class="footer-top">
                    <div class="footer-section">
                        <div class="footer-logo">
                            <div class="logo-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <span class="logo-text">탑마케팅</span>
                        </div>
                        <p class="footer-description">
                            글로벌 네트워크 마케팅 리더들의 커뮤니티<br>
                            함께 성장하고 성공을 만들어가는 플랫폼
                        </p>

                    </div>

                    <div class="footer-section">
                        <h3 class="footer-title">서비스</h3>
                        <ul class="footer-links">
                            <li><a href="/community">커뮤니티</a></li>
                            <li><a href="/lectures">강의 일정</a></li>
                            <li><a href="/events">행사 일정</a></li>
                            <li><a href="/notices">공지사항</a></li>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <li><a href="/auth/login">로그인</a></li>
                                <li><a href="/auth/signup">회원가입</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>



                    <div class="footer-section">
                        <h3 class="footer-title">정책</h3>
                        <ul class="footer-links">
                            <li><a href="/terms">이용약관</a></li>
                            <li><a href="/privacy">개인정보처리방침</a></li>
                        </ul>
                    </div>
                </div>

                <!-- 하단 영역 -->
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <p>&copy; <?= date('Y') ?> 탑마케팅. All rights reserved.</p>
                        <p class="company-info">
                            상호명: (주)윈카드 | 대표자: 이정현 | 사업자등록번호: 133-88-02437 | 
                            전화번호: <a href="tel:1577-9794">1577-9794</a> | 이메일: <a href="mailto:jh@wincard.kr">jh@wincard.kr</a> | 주소: 서울시 금천구 가산디지털1로 204, 반도 아이비밸리 6층
                        </p>
                    </div>

                </div>
            </div>
        </div>
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
</body>
</html> 