<!-- 알림 설정 페이지 -->
<style>
/* ============================================
   알림 설정 페이지 스타일
   ============================================ */

/* 컨테이너 */
.notification-settings-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px);
}

/* 헤더 */
.notification-settings-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 30px;
    border-radius: 16px;
    margin-top: 60px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.notification-settings-header h1 {
    margin: 0 0 10px 0;
    font-size: 32px;
    font-weight: 700;
}

.notification-settings-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
}

/* 폼 컨테이너 */
.notification-settings-form {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
    padding: 40px;
}

/* 섹션 */
.settings-section {
    margin-bottom: 40px;
}

.settings-section:last-of-type {
    margin-bottom: 0;
}

.settings-section-title {
    font-size: 20px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}

/* 알림 설정 아이템 */
.notification-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #f0f0f0;
}

.notification-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.notification-item.master-item {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    padding: 25px 20px;
    border-radius: 12px;
    border: 2px solid #667eea;
    margin-bottom: 30px;
}

.notification-info {
    flex: 1;
    margin-right: 20px;
}

.notification-label {
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.notification-label i {
    color: #667eea;
    font-size: 18px;
}

.notification-desc {
    font-size: 14px;
    color: #718096;
    line-height: 1.5;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 56px;
    height: 28px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e0;
    transition: all 0.3s ease;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: all 0.3s ease;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(28px);
}

.toggle-switch input:disabled + .toggle-slider {
    opacity: 0.5;
    cursor: not-allowed;
}

/* 버튼 그룹 */
.notification-button-group {
    display: flex;
    gap: 15px;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e2e8f0;
}

.notification-button-group button {
    flex: 1;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-save:active {
    transform: translateY(0);
}

.btn-cancel {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.btn-cancel:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
}

/* 안내 메시지 */
.notification-guide {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-left: 4px solid #667eea;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.notification-guide h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 600;
    color: #667eea;
}

.notification-guide p {
    margin: 0;
    font-size: 14px;
    color: #4a5568;
    line-height: 1.6;
}

.notification-guide ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.notification-guide li {
    font-size: 14px;
    color: #4a5568;
    line-height: 1.8;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .notification-settings-container {
        padding: 15px;
    }

    .notification-settings-header {
        padding: 30px 20px;
        margin-bottom: 20px;
    }

    .notification-settings-header h1 {
        font-size: 26px;
    }

    .notification-settings-header p {
        font-size: 14px;
    }

    .notification-settings-form {
        padding: 25px 20px;
    }

    .notification-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .notification-info {
        margin-right: 0;
    }

    .notification-button-group {
        flex-direction: column;
    }

    .notification-button-group button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .notification-settings-container {
        padding: 12px;
    }

    .notification-settings-header h1 {
        font-size: 22px;
    }

    .notification-settings-header p {
        font-size: 13px;
    }

    .notification-settings-form {
        padding: 20px 15px;
    }

    .settings-section-title {
        font-size: 18px;
    }

    .notification-label {
        font-size: 15px;
    }

    .notification-desc {
        font-size: 13px;
    }
}
</style>

<div class="notification-settings-container">
    <!-- 헤더 -->
    <div class="notification-settings-header">
        <h1>
            <i data-lucide="bell" width="24" height="24"></i>
            알림 설정
        </h1>
        <p>FCM 앱 푸시 알림을 관리하세요. 원하는 알림만 받아볼 수 있습니다.</p>
    </div>

    <!-- 폼 -->
    <div class="notification-settings-form">
        <!-- 안내 메시지 -->
        <div class="notification-guide">
            <h3>
                <i data-lucide="info" width="20" height="20"></i>
                알림 설정 안내
            </h3>
            <p>
                탑마케팅 앱에서 받을 알림을 선택할 수 있습니다.
            </p>
            <ul>
                <li><strong>전체 알림</strong>이 꺼져 있으면 개별 설정과 관계없이 모든 알림이 비활성화됩니다.</li>
                <li>개별 알림은 원하는 항목만 선택하여 받을 수 있습니다.</li>
                <li>설정 변경 후 반드시 하단의 <strong>저장</strong> 버튼을 클릭해주세요.</li>
            </ul>
        </div>

        <form id="notificationSettingsForm">
            <!-- 전체 알림 ON/OFF -->
            <div class="settings-section">
                <div class="notification-item master-item">
                    <div class="notification-info">
                        <div class="notification-label">
                            <i data-lucide="toggle-right" width="20" height="20"></i>
                            전체 알림
                        </div>
                        <div class="notification-desc">
                            모든 알림을 한 번에 켜거나 끌 수 있습니다. 이 설정이 꺼져 있으면 아래의 모든 개별 알림이 비활성화됩니다.
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="allNotifications" name="all_notifications" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- 개별 알림 설정 -->
            <div class="settings-section">
                <h2 class="settings-section-title">개별 알림 설정</h2>

                <!-- 댓글, 대댓글 알림 -->
                <div class="notification-item">
                    <div class="notification-info">
                        <div class="notification-label">
                            <i data-lucide="message-square" width="20" height="20"></i>
                            댓글, 대댓글 알림
                        </div>
                        <div class="notification-desc">
                            내 게시글에 새로운 댓글이나 대댓글이 달렸을 때 알림을 받습니다.
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="commentsEnabled" name="comments_enabled" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- 좋아요 알림 -->
                <div class="notification-item">
                    <div class="notification-info">
                        <div class="notification-label">
                            <i data-lucide="heart" width="20" height="20"></i>
                            좋아요 알림
                        </div>
                        <div class="notification-desc">
                            내 게시글에 좋아요가 눌렸을 때 알림을 받습니다.
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="likesEnabled" name="likes_enabled" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- 신규 강의, 행사 알림 -->
                <div class="notification-item">
                    <div class="notification-info">
                        <div class="notification-label">
                            <i data-lucide="calendar-plus" width="20" height="20"></i>
                            신규 강의, 행사 알림
                        </div>
                        <div class="notification-desc">
                            새로운 강의나 행사가 등록되었을 때 알림을 받습니다.
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="lecturesEventsEnabled" name="lectures_events_enabled" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- 신청 승인/거절 알림 -->
                <div class="notification-item">
                    <div class="notification-info">
                        <div class="notification-label">
                            <i data-lucide="check-circle" width="20" height="20"></i>
                            신청 승인, 거절 알림
                        </div>
                        <div class="notification-desc">
                            내가 신청한 강의나 행사의 승인/거절 결과를 알림으로 받습니다.
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="registrationEnabled" name="registration_enabled" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- 공지사항 알림 -->
                <div class="notification-item">
                    <div class="notification-info">
                        <div class="notification-label">
                            <i data-lucide="megaphone" width="20" height="20"></i>
                            공지사항 알림
                        </div>
                        <div class="notification-desc">
                            새로운 공지사항이 등록되었을 때 알림을 받습니다.
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="noticesEnabled" name="notices_enabled" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- 버튼 그룹 -->
            <div class="notification-button-group">
                <button type="button" class="btn-cancel" onclick="window.location.href='/'">
                    <i data-lucide="x" width="20" height="20"></i>
                    취소
                </button>
                <button type="submit" class="btn-save">
                    <i data-lucide="save" width="20" height="20"></i>
                    저장
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================
// 알림 설정 페이지 JavaScript
// ============================================

// CSRF 토큰 가져오기
const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

/**
 * 컴포넌트 로드 대기 함수 (개선 버전)
 */
function waitForComponents() {
    return new Promise((resolve) => {
        let attempts = 0;
        const maxAttempts = 100; // 5초 최대 대기 (50ms * 100)

        const checkComponents = () => {
            attempts++;

            if (typeof window.Loading !== 'undefined' &&
                typeof window.Toast !== 'undefined' &&
                typeof window.ApiClient !== 'undefined') {
                resolve(true);
            } else if (attempts >= maxAttempts) {
                console.error('컴포넌트 로드 타임아웃');
                resolve(false);
            } else {
                setTimeout(checkComponents, 50);
            }
        };
        checkComponents();
    });
}

// 초기화 - window.onload 사용 (모든 리소스 로드 완료 후)
window.addEventListener('load', async () => {
    // 컴포넌트 로드 대기
    const componentsReady = await waitForComponents();

    if (!componentsReady) {
        console.error('필수 컴포넌트를 로드할 수 없습니다.');
        alert('페이지 로딩 중 오류가 발생했습니다. 페이지를 새로고침해주세요.');
        return;
    }

    // 현재 설정 로드
    await loadSettings();

    // 전체 알림 토글 이벤트
    const allNotificationsToggle = document.getElementById('allNotifications');
    if (allNotificationsToggle) {
        allNotificationsToggle.addEventListener('change', handleMasterToggle);
    }

    // 폼 제출 이벤트
    const form = document.getElementById('notificationSettingsForm');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
});

/**
 * 현재 설정 로드
 */
async function loadSettings() {
    try {
        // Loading 컴포넌트가 있을 때만 사용
        if (typeof window.Loading !== 'undefined' && window.Loading.show) {
            window.Loading.show('설정을 불러오는 중...');
        }

        const response = await window.ApiClient.get('/api/notifications/settings');

        if (response.success && response.data) {
            // 폼에 값 채우기
            const allNotifications = document.getElementById('allNotifications');
            const commentsEnabled = document.getElementById('commentsEnabled');
            const likesEnabled = document.getElementById('likesEnabled');
            const lecturesEventsEnabled = document.getElementById('lecturesEventsEnabled');
            const registrationEnabled = document.getElementById('registrationEnabled');
            const noticesEnabled = document.getElementById('noticesEnabled');

            if (allNotifications) allNotifications.checked = Boolean(response.data.all_notifications);
            if (commentsEnabled) commentsEnabled.checked = Boolean(response.data.comments_enabled);
            if (likesEnabled) likesEnabled.checked = Boolean(response.data.likes_enabled);
            if (lecturesEventsEnabled) lecturesEventsEnabled.checked = Boolean(response.data.lectures_events_enabled);
            if (registrationEnabled) registrationEnabled.checked = Boolean(response.data.registration_enabled);
            if (noticesEnabled) noticesEnabled.checked = Boolean(response.data.notices_enabled);

            // 전체 알림이 꺼져 있으면 개별 토글 비활성화
            if (allNotifications) {
                handleMasterToggle({ target: allNotifications });
            }
        }

        // Loading 컴포넌트가 있을 때만 사용
        if (typeof window.Loading !== 'undefined' && window.Loading.hide) {
            window.Loading.hide();
        }
    } catch (error) {
        console.error('설정 로드 오류:', error);

        // Loading 컴포넌트가 있을 때만 사용
        if (typeof window.Loading !== 'undefined' && window.Loading.hide) {
            window.Loading.hide();
        }

        // Toast 컴포넌트가 있을 때만 사용
        if (typeof window.Toast !== 'undefined' && window.Toast.error) {
            window.Toast.error('설정을 불러오는데 실패했습니다.');
        } else {
            alert('설정을 불러오는데 실패했습니다.');
        }
    }
}

/**
 * 전체 알림 토글 처리
 */
function handleMasterToggle(event) {
    const isEnabled = event.target.checked;

    // 개별 토글 활성화/비활성화
    const individualToggles = [
        'commentsEnabled',
        'likesEnabled',
        'lecturesEventsEnabled',
        'registrationEnabled',
        'noticesEnabled'
    ];

    individualToggles.forEach(id => {
        const toggle = document.getElementById(id);

        if (isEnabled) {
            // 전체 알림 ON: 모든 개별 알림도 ON + 활성화
            toggle.checked = true;
            toggle.disabled = false;
        } else {
            // 전체 알림 OFF: 개별 알림은 비활성화만 (checked 값 유지)
            toggle.disabled = true;
        }

        // 시각적 피드백
        const item = toggle.closest('.notification-item');
        if (isEnabled) {
            item.style.opacity = '1';
        } else {
            item.style.opacity = '0.5';
        }
    });
}

/**
 * 폼 제출 처리
 */
async function handleSubmit(event) {
    event.preventDefault();

    try {
        // Loading 컴포넌트가 있을 때만 사용
        if (typeof window.Loading !== 'undefined' && window.Loading.show) {
            window.Loading.show('저장 중...');
        }

        // 폼 데이터 수집
        const formData = {
            csrf_token: csrfToken,
            all_notifications: document.getElementById('allNotifications').checked,
            comments_enabled: document.getElementById('commentsEnabled').checked,
            likes_enabled: document.getElementById('likesEnabled').checked,
            lectures_events_enabled: document.getElementById('lecturesEventsEnabled').checked,
            registration_enabled: document.getElementById('registrationEnabled').checked,
            notices_enabled: document.getElementById('noticesEnabled').checked
        };

        const response = await window.ApiClient.put('/api/notifications/settings', formData);

        // Loading 컴포넌트가 있을 때만 사용
        if (typeof window.Loading !== 'undefined' && window.Loading.hide) {
            window.Loading.hide();
        }

        if (response.success) {
            // Toast 컴포넌트가 있을 때만 사용
            if (typeof window.Toast !== 'undefined' && window.Toast.success) {
                window.Toast.success('알림 설정이 저장되었습니다.');
            } else {
                alert('알림 설정이 저장되었습니다.');
            }

            // 1초 후 새로고침
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Toast 컴포넌트가 있을 때만 사용
            if (typeof window.Toast !== 'undefined' && window.Toast.error) {
                window.Toast.error(response.message || '저장에 실패했습니다.');
            } else {
                alert(response.message || '저장에 실패했습니다.');
            }
        }
    } catch (error) {
        console.error('설정 저장 오류:', error);

        // Loading 컴포넌트가 있을 때만 사용
        if (typeof window.Loading !== 'undefined' && window.Loading.hide) {
            window.Loading.hide();
        }

        // Toast 컴포넌트가 있을 때만 사용
        if (typeof window.Toast !== 'undefined' && window.Toast.error) {
            window.Toast.error('저장 중 오류가 발생했습니다.');
        } else {
            alert('저장 중 오류가 발생했습니다.');
        }
    }
}
</script>
