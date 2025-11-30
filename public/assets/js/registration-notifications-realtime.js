/**
 * Firebase 실시간 신청 대기 알림 시스템
 * 기존 폴링 시스템을 Firebase Realtime Database 리스너로 교체
 */

const RegistrationRealtimeNotifications = {
    firebaseApp: null,
    database: null,
    currentUserId: null,
    userRole: null,
    pendingRef: null,
    alertElement: null,
    initialized: false
};

/**
 * Firebase 실시간 신청 대기 알림 시스템 초기화
 */
function initializeRegistrationRealtimeNotifications() {
    // 로그인한 기업 사용자만 알림 활성화
    const userElement = document.querySelector('meta[name="user-id"]');
    const roleElement = document.querySelector('meta[name="user-role"]');
    
    if (!userElement || !roleElement) {
        return;
    }
    
    RegistrationRealtimeNotifications.currentUserId = userElement.getAttribute('content');
    RegistrationRealtimeNotifications.userRole = roleElement.getAttribute('content');
    
    // 기업 회원만 알림 활성화
    if (!RegistrationRealtimeNotifications.currentUserId || 
        RegistrationRealtimeNotifications.userRole !== 'ROLE_CORP') {
        return;
    }
    
    
    // Firebase 초기화
    initializeFirebaseForRegistrations();
}

/**
 * Firebase 초기화 및 리스너 설정
 */
function initializeFirebaseForRegistrations() {
    // 기존 Firebase 앱 재사용 (채팅 시스템에서 초기화된 것)
    if (typeof firebase !== 'undefined' && firebase.apps.length > 0) {
        RegistrationRealtimeNotifications.firebaseApp = firebase.apps[0];
        RegistrationRealtimeNotifications.database = firebase.database();
        
        setupRegistrationRealtimeListeners();
        
    } else {
        // Firebase가 아직 초기화되지 않았으면 설정을 가져와서 초기화
        fetch('/chat/firebase-token', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': getCsrfTokenForRegistration()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.firebase_config) {
                // Firebase 초기화
                if (!firebase.apps.length) {
                    RegistrationRealtimeNotifications.firebaseApp = firebase.initializeApp(data.firebase_config);
                } else {
                    RegistrationRealtimeNotifications.firebaseApp = firebase.apps[0];
                }
                RegistrationRealtimeNotifications.database = firebase.database();
                
                setupRegistrationRealtimeListeners();
                
            } else {
            }
        })
        .catch(error => {
        });
    }
}

/**
 * Firebase 실시간 리스너 설정
 */
function setupRegistrationRealtimeListeners() {
    if (!RegistrationRealtimeNotifications.database || 
        !RegistrationRealtimeNotifications.currentUserId) {
        return;
    }
    
    const userId = RegistrationRealtimeNotifications.currentUserId;
    const pendingRef = RegistrationRealtimeNotifications.database.ref(`pendingRegistrations/${userId}`);
    
    
    // 실시간 리스너 설정
    pendingRef.on('value', (snapshot) => {
        const data = snapshot.val();
        
        
        if (data && data.count > 0) {
            showRealtimePendingAlert(data.count, data.message, data.details);
        } else {
            hideRealtimePendingAlert();
        }
    }, (error) => {
    });
    
    RegistrationRealtimeNotifications.pendingRef = pendingRef;
    RegistrationRealtimeNotifications.initialized = true;
    
}

/**
 * 실시간 대기 알림 표시
 */
function showRealtimePendingAlert(count, message, details) {
    // 기존 알림 제거
    hideRealtimePendingAlert();
    
    
    // 상세 메시지 구성
    let detailMessage = '';
    if (details && (details.lectures > 0 || details.events > 0)) {
        const parts = [];
        if (details.lectures > 0) parts.push(`강의 ${details.lectures}개`);
        if (details.events > 0) parts.push(`행사 ${details.events}개`);
        detailMessage = `(${parts.join(', ')})`;
    }
    
    // 알림 엘리먼트 생성
    const alertDiv = document.createElement('div');
    alertDiv.id = 'realtime-registration-alert';
    alertDiv.className = 'realtime-pending-alert';
    alertDiv.innerHTML = `
        <div class="realtime-alert-content">
            <div class="realtime-alert-icon">
                <i data-lucide="bell" style="width:24px;height:24px"></i>
            </div>
            <div class="realtime-alert-text">
                <div class="realtime-alert-title">실시간 신청 알림</div>
                <div class="realtime-alert-message">${message} ${detailMessage}</div>
            </div>
            <div class="realtime-alert-actions">
                <button class="realtime-alert-btn" onclick="goToRegistrationDashboard()">확인하기</button>
                <button class="realtime-alert-close" onclick="hideRealtimePendingAlert()">×</button>
            </div>
        </div>
    `;
    
    // 스타일 추가 (한 번만)
    if (!document.getElementById('realtime-alert-styles')) {
        const style = document.createElement('style');
        style.id = 'realtime-alert-styles';
        style.textContent = `
            .realtime-pending-alert {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
                max-width: 400px;
                animation: slideInFromRight 0.3s ease-out;
                border-left: 4px solid #34d399;
            }
            
            .realtime-alert-content {
                display: flex;
                align-items: center;
                padding: 15px;
                gap: 12px;
            }
            
            .realtime-alert-icon {
                font-size: 1.5rem;
                color: #fde047;
                animation: pulse 2s infinite;
            }
            
            .realtime-alert-text {
                flex: 1;
            }
            
            .realtime-alert-title {
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 4px;
            }
            
            .realtime-alert-message {
                font-size: 0.9rem;
                opacity: 0.95;
                line-height: 1.3;
            }
            
            .realtime-alert-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .realtime-alert-btn {
                background: rgba(255, 255, 255, 0.2);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .realtime-alert-btn:hover {
                background: rgba(255, 255, 255, 0.3);
                border-color: rgba(255, 255, 255, 0.5);
                transform: translateY(-1px);
            }
            
            .realtime-alert-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                opacity: 0.7;
                transition: opacity 0.2s ease;
            }
            
            .realtime-alert-close:hover {
                opacity: 1;
                background: rgba(255, 255, 255, 0.1);
            }
            
            @keyframes slideInFromRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.1);
                }
            }
            
            @media (max-width: 768px) {
                .realtime-pending-alert {
                    left: 20px;
                    right: 20px;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // 페이지에 알림 추가
    document.body.appendChild(alertDiv);

    // Lucide 아이콘 렌더링
    if (window.lucide) {
        window.lucide.createIcons();
    }

    RegistrationRealtimeNotifications.alertElement = alertDiv;
    
    // 20초 후 자동 숨김
    setTimeout(() => {
        if (RegistrationRealtimeNotifications.alertElement === alertDiv) {
            hideRealtimePendingAlert();
        }
    }, 20000);
}

/**
 * 실시간 대기 알림 숨김
 */
function hideRealtimePendingAlert() {
    if (RegistrationRealtimeNotifications.alertElement) {
        RegistrationRealtimeNotifications.alertElement.remove();
        RegistrationRealtimeNotifications.alertElement = null;
    }
}

/**
 * 신청 관리 대시보드로 이동
 */
function goToRegistrationDashboard() {
    window.location.href = '/registrations';
}

/**
 * CSRF 토큰 가져오기
 */
function getCsrfTokenForRegistration() {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : '';
}

/**
 * 정리 함수
 */
function cleanupRegistrationRealtimeNotifications() {
    if (RegistrationRealtimeNotifications.pendingRef) {
        RegistrationRealtimeNotifications.pendingRef.off();
        RegistrationRealtimeNotifications.pendingRef = null;
    }
    
    hideRealtimePendingAlert();
    RegistrationRealtimeNotifications.initialized = false;
    
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // Firebase SDK가 로드된 후 초기화
    if (typeof firebase !== 'undefined') {
        initializeRegistrationRealtimeNotifications();
    } else {
        // Firebase SDK 로드 대기
        const checkFirebase = setInterval(() => {
            if (typeof firebase !== 'undefined') {
                clearInterval(checkFirebase);
                initializeRegistrationRealtimeNotifications();
            }
        }, 500);
        
        // 15초 후 포기
        setTimeout(() => {
            clearInterval(checkFirebase);
        }, 15000);
    }
});

// 페이지 언로드 시 정리
window.addEventListener('beforeunload', cleanupRegistrationRealtimeNotifications);

// 전역 함수로 노출
window.hideRealtimePendingAlert = hideRealtimePendingAlert;
window.goToRegistrationDashboard = goToRegistrationDashboard;