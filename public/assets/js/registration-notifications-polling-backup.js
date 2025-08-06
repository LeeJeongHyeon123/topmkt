/**
 * 신청 대기 알림 시스템
 * 기업 유저가 등록한 강의/행사의 대기 중인 신청을 알림
 */

const RegistrationNotifications = {
    currentUserId: null,
    userRole: null,
    pendingCount: 0,
    checkInterval: null,
    intervalTime: 30000, // 30초마다 확인
    alertElement: null
};

/**
 * 신청 대기 알림 시스템 초기화
 */
function initializeRegistrationNotifications() {
    // 로그인한 사용자만 알림 활성화
    const userElement = document.querySelector('meta[name="user-id"]');
    const roleElement = document.querySelector('meta[name="user-role"]');
    
    if (!userElement || !roleElement) {
        return;
    }
    
    RegistrationNotifications.currentUserId = userElement.getAttribute('content');
    RegistrationNotifications.userRole = roleElement.getAttribute('content');
    
    if (!RegistrationNotifications.currentUserId || RegistrationNotifications.userRole !== 'ROLE_CORP') {
        return;
    }
    
    console.log('🔔 신청 대기 알림 시스템 초기화 - 사용자 ID:', RegistrationNotifications.currentUserId);
    
    // 초기 확인
    checkPendingRegistrations();
    
    // 정기적 확인 설정
    RegistrationNotifications.checkInterval = setInterval(checkPendingRegistrations, RegistrationNotifications.intervalTime);
    
    // 페이지 언로드 시 인터벌 정리
    window.addEventListener('beforeunload', function() {
        if (RegistrationNotifications.checkInterval) {
            clearInterval(RegistrationNotifications.checkInterval);
        }
    });
}

/**
 * 대기 중인 신청 확인
 */
async function checkPendingRegistrations() {
    try {
        const response = await fetch('/api/registrations/pending-count', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status === 'success' && data.data) {
            const newCount = data.data.count || 0;
            
            // 새로운 신청이 있는 경우에만 알림 표시
            if (newCount > 0 && newCount !== RegistrationNotifications.pendingCount) {
                showPendingAlert(newCount, data.data.message);
            } else if (newCount === 0 && RegistrationNotifications.pendingCount > 0) {
                hidePendingAlert();
            }
            
            RegistrationNotifications.pendingCount = newCount;
        }
        
    } catch (error) {
        console.error('대기 신청 확인 오류:', error);
    }
}

/**
 * 대기 알림 표시
 */
function showPendingAlert(count, message) {
    // 기존 알림 제거
    hidePendingAlert();
    
    // 알림 엘리먼트 생성
    const alertDiv = document.createElement('div');
    alertDiv.id = 'pending-registration-alert';
    alertDiv.className = 'pending-alert';
    alertDiv.innerHTML = `
        <div class="pending-alert-content">
            <div class="pending-alert-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="pending-alert-text">
                <div class="pending-alert-title">신청 대기 알림</div>
                <div class="pending-alert-message">${message}</div>
            </div>
            <div class="pending-alert-actions">
                <button class="pending-alert-btn" onclick="goToRegistrationDashboard()">확인하기</button>
                <button class="pending-alert-close" onclick="hidePendingAlert()">×</button>
            </div>
        </div>
    `;
    
    // 스타일 추가
    if (!document.getElementById('pending-alert-styles')) {
        const style = document.createElement('style');
        style.id = 'pending-alert-styles';
        style.textContent = `
            .pending-alert {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                max-width: 400px;
                animation: slideInFromRight 0.3s ease-out;
            }
            
            .pending-alert-content {
                display: flex;
                align-items: center;
                padding: 15px;
                gap: 12px;
            }
            
            .pending-alert-icon {
                font-size: 1.5rem;
                color: #ffd700;
            }
            
            .pending-alert-text {
                flex: 1;
            }
            
            .pending-alert-title {
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 4px;
            }
            
            .pending-alert-message {
                font-size: 0.9rem;
                opacity: 0.9;
                line-height: 1.3;
            }
            
            .pending-alert-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .pending-alert-btn {
                background: rgba(255, 255, 255, 0.2);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .pending-alert-btn:hover {
                background: rgba(255, 255, 255, 0.3);
                border-color: rgba(255, 255, 255, 0.5);
            }
            
            .pending-alert-close {
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
            
            .pending-alert-close:hover {
                opacity: 1;
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
            
            @media (max-width: 768px) {
                .pending-alert {
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
    
    RegistrationNotifications.alertElement = alertDiv;
    
    // 15초 후 자동 숨김
    setTimeout(() => {
        if (RegistrationNotifications.alertElement === alertDiv) {
            hidePendingAlert();
        }
    }, 15000);
}

/**
 * 대기 알림 숨김
 */
function hidePendingAlert() {
    if (RegistrationNotifications.alertElement) {
        RegistrationNotifications.alertElement.remove();
        RegistrationNotifications.alertElement = null;
    }
}

/**
 * 신청 관리 대시보드로 이동
 */
function goToRegistrationDashboard() {
    window.location.href = '/registrations';
}

/**
 * 페이지 로드 시 초기화
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeRegistrationNotifications();
});