/**
 * 채팅 알림 시스템
 * Firebase Realtime Database를 사용한 효율적인 실시간 알림
 */

// Firebase 설정 - 전역 변수 충돌 방지를 위해 네임스페이스 사용
const ChatNotifications = {
    firebaseApp: null,
    database: null,
    currentUserId: null,
    userRoomsListeners: [],
    roomMessageListeners: {},
    unreadCount: 0
};

/**
 * 채팅 알림 시스템 초기화
 */
function initializeChatNotifications() {
    
    // 채팅 페이지에서는 실행하지 않음 (중복 방지)
    if (window.location.pathname === '/chat') {
        return;
    }
    
    // 로그인한 사용자만 알림 활성화
    const userElement = document.querySelector('meta[name="user-id"]');
    if (!userElement) {
        return;
    }
    
    ChatNotifications.currentUserId = userElement.getAttribute('content');
    if (!ChatNotifications.currentUserId) {
        return;
    }
    
    
    // Firebase 초기화
    initializeFirebase();
}

/**
 * Firebase 초기화
 */
function initializeFirebase() {
    
    // Firebase 설정 가져오기
    fetch('/chat/firebase-token', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': getCsrfToken()
        }
    })
        .then(response => {
            return response.json();
        })
        .then(data => {
            
            if (data.success && data.firebase_config) {
                // Firebase 초기화 - 기존 앱이 있으면 재사용
                if (!firebase.apps.length) {
                    ChatNotifications.firebaseApp = firebase.initializeApp(data.firebase_config);
                } else {
                    ChatNotifications.firebaseApp = firebase.apps[0];
                }
                ChatNotifications.database = firebase.database();
                
                
                // 초기 배지 상태 설정 (배지 없음)
                ChatNotifications.unreadCount = 0;
                
                // 알림 리스너 설정
                setupNotificationListeners();
            } else {
            }
        })
        .catch(error => {
        });
}

/**
 * 알림 리스너 설정
 */
function setupNotificationListeners() {
    
    if (!ChatNotifications.database || !ChatNotifications.currentUserId) {
        return;
    }
    
    // 사용자의 채팅방 목록 모니터링
    const userRoomsRef = ChatNotifications.database.ref(`userRooms/${ChatNotifications.currentUserId}`);
    
    userRoomsRef.on('child_added', (snapshot) => {
        const roomId = snapshot.key;
        const roomData = snapshot.val();
        
        
        // 각 채팅방의 마지막 메시지 모니터링
        setupRoomMessageListener(roomId, roomData.lastRead || 0);
    });
    
    userRoomsRef.on('child_changed', (snapshot) => {
        const roomId = snapshot.key;
        const roomData = snapshot.val();
        
        // 읽음 시간이 업데이트된 경우 리스너 재설정
        if (roomData.lastRead) {
            removeRoomMessageListener(roomId);
            setupRoomMessageListener(roomId, roomData.lastRead);
        }
    });
    
    userRoomsRef.on('child_removed', (snapshot) => {
        const roomId = snapshot.key;
        
        // 해당 채팅방 리스너 제거
        removeRoomMessageListener(roomId);
    });
    
    // 정리를 위해 리스너 참조 저장
    ChatNotifications.userRoomsListeners.push(userRoomsRef);
    
    // 초기 읽지 않은 메시지 수 계산
    calculateInitialUnreadCount();
}

/**
 * 개별 채팅방 메시지 리스너 설정
 */
function setupRoomMessageListener(roomId, lastReadTime) {
    
    if (!ChatNotifications.database || !roomId) {
        return;
    }
    
    // 채팅방의 마지막 메시지 시간과 발신자 모니터링
    const roomRef = ChatNotifications.database.ref(`chatRooms/${roomId}`);
    
    const messageListener = roomRef.on('value', (snapshot) => {
        const roomData = snapshot.val();
        
        if (!roomData) return;
        
        const lastMessageTime = roomData.lastMessageTime;
        const lastSenderId = roomData.lastSenderId;
        const lastMessage = roomData.lastMessage;
        
        // 새로운 메시지가 있고, 내가 보낸 메시지가 아닌 경우
        if (lastMessageTime && 
            lastMessageTime > lastReadTime && 
            lastSenderId && 
            lastSenderId != ChatNotifications.currentUserId) {
            
            // 상대방 정보 가져오기
            getRoomPartnerInfo(roomId, roomData)
                .then(partnerInfo => {
                    showChatNotification(partnerInfo.name, lastMessage, roomId);
                    updateUnreadCount();
                })
                .catch(error => {
                    showChatNotification('알 수 없음', lastMessage, roomId);
                    updateUnreadCount();
                });
        }
    });
    
    // 리스너 참조 저장
    ChatNotifications.roomMessageListeners[roomId] = {
        ref: roomRef,
        listener: messageListener
    };
}

/**
 * 채팅방 메시지 리스너 제거
 */
function removeRoomMessageListener(roomId) {
    if (ChatNotifications.roomMessageListeners[roomId]) {
        const { ref, listener } = ChatNotifications.roomMessageListeners[roomId];
        ref.off('value', listener);
        delete ChatNotifications.roomMessageListeners[roomId];
    }
}

/**
 * 채팅방 상대방 정보 가져오기
 */
async function getRoomPartnerInfo(roomId, roomData) {
    if (!roomData.participants) {
        return { name: '알 수 없음', userId: null };
    }
    
    // 1:1 채팅인 경우 상대방 찾기
    const participantIds = Object.keys(roomData.participants);
    const partnerId = participantIds.find(id => id != ChatNotifications.currentUserId);
    
    if (!partnerId) {
        return { name: '알 수 없음', userId: null };
    }
    
    try {
        // 상대방 정보 API 호출 - 원본 fetch 사용 (로딩 표시 안 함)
        const fetchFn = window.originalFetch || fetch;
        const response = await fetchFn(`/api/users/${partnerId}/profile-image`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': getCsrfToken()
            }
        });
        const result = await response.json();
        
        // ResponseHelper 구조에 맞게 데이터 추출
        const data = result.data || result;
        
        // 디버깅용 로그 (개발 시에만)
        if (!data.nickname) {
            console.warn('채팅 알림: 닉네임을 찾을 수 없음', { result, data, partnerId });
        }
        
        return {
            name: data.nickname || '알 수 없음',
            userId: partnerId
        };
    } catch (error) {
        console.warn('채팅 알림: API 호출 실패', error, partnerId);
        return { name: '알 수 없음', userId: partnerId };
    }
}

/**
 * 채팅 알림 표시
 */
function showChatNotification(senderName, message, roomId) {
    // 기존 채팅 알림 제거
    const existingAlert = document.querySelector('.alert.chat-notification');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // 메시지 내용 정리 (최대 50자)
    const cleanMessage = message ? message.substring(0, 50) + (message.length > 50 ? '...' : '') : '새 메시지';
    
    // 알림 HTML 생성
    const alertHtml = `
        <div class="alert alert-info chat-notification" style="cursor: pointer;" onclick="handleChatNotificationClick('${roomId}')">
            <div class="alert-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="alert-content">
                <div>
                    <strong>${escapeHtml(senderName)}</strong><br>
                    <span style="font-size: 0.9em; opacity: 0.9;">${escapeHtml(cleanMessage)}</span>
                </div>
                <button class="alert-close" onclick="event.stopPropagation(); this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // 알림을 body에 추가
    const alertElement = document.createElement('div');
    alertElement.innerHTML = alertHtml;
    document.body.appendChild(alertElement.firstElementChild);
    
    // 5초 후 자동 제거
    setTimeout(() => {
        const alert = document.querySelector('.alert.chat-notification');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }, 5000);
    
}

/**
 * 채팅 알림 클릭 처리
 */
function handleChatNotificationClick(roomId) {
    // 채팅 페이지로 이동하면서 해당 채팅방 열기
    window.location.href = `/chat#room-${roomId}`;
}

/**
 * HTML 이스케이프
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * 읽지 않은 메시지 수 업데이트
 */
function updateUnreadCount() {
    ChatNotifications.unreadCount++;
    updateBadgeDisplay();
}

/**
 * 읽지 않은 메시지 수 초기화
 */
function resetUnreadCount() {
    ChatNotifications.unreadCount = 0;
    updateBadgeDisplay();
}

/**
 * 배지 표시 업데이트
 */
function updateBadgeDisplay() {
    const shouldShow = ChatNotifications.unreadCount > 0;
    
    // 헤더의 메인 배지 업데이트 (동적 생성/제거)
    const chatMenuItem = document.querySelector('a[href="/chat"].dropdown-item');
    if (chatMenuItem) {
        let badge = document.getElementById('chatNotificationBadge');
        
        if (shouldShow) {
            // 배지가 없으면 생성
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'chatNotificationBadge';
                badge.className = 'notification-badge';
                badge.style.cssText = 'background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: auto; font-weight: bold; min-width: 16px; text-align: center; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;';
                chatMenuItem.appendChild(badge);
            }
            badge.textContent = ChatNotifications.unreadCount;
        } else {
            // 배지가 있으면 제거
            if (badge) {
                badge.remove();
            }
        }
    }
    
    // 드롭다운의 배지 업데이트 (드롭다운이 열려있을 때만)
    const floatingDropdown = document.getElementById('floating-user-dropdown');
    if (floatingDropdown) {
        const dropdownChatItem = floatingDropdown.querySelector('a[href="/chat"]');
        if (dropdownChatItem) {
            let dropdownBadge = dropdownChatItem.querySelector('.dropdown-chat-badge');
            
            if (shouldShow) {
                // 배지가 없으면 생성
                if (!dropdownBadge) {
                    dropdownBadge = document.createElement('span');
                    dropdownBadge.className = 'notification-badge dropdown-chat-badge';
                    dropdownBadge.style.cssText = 'background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: auto; font-weight: bold; min-width: 16px; text-align: center;';
                    dropdownChatItem.appendChild(dropdownBadge);
                }
                dropdownBadge.textContent = ChatNotifications.unreadCount;
            } else {
                // 배지가 있으면 제거
                if (dropdownBadge) {
                    dropdownBadge.remove();
                }
            }
        }
    }
    
}

/**
 * 전역 함수로 노출 (채팅 페이지에서 사용)
 */
window.resetChatNotificationCount = resetUnreadCount;

/**
 * CSRF 토큰 가져오기
 */
function getCsrfToken() {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : '';
}

/**
 * 초기 읽지 않은 메시지 수 계산
 */
function calculateInitialUnreadCount() {
    if (!ChatNotifications.database || !ChatNotifications.currentUserId) {
        return;
    }
    
    
    const userRoomsRef = ChatNotifications.database.ref(`userRooms/${ChatNotifications.currentUserId}`);
    userRoomsRef.once('value', (snapshot) => {
        const userRooms = snapshot.val();
        if (!userRooms) {
            ChatNotifications.unreadCount = 0;
            updateBadgeDisplay();
            return;
        }
        
        let totalUnread = 0;
        const roomIds = Object.keys(userRooms);
        let processedRooms = 0;
        
        if (roomIds.length === 0) {
            ChatNotifications.unreadCount = 0;
            updateBadgeDisplay();
            return;
        }
        
        roomIds.forEach(roomId => {
            const roomInfo = userRooms[roomId];
            const lastRead = roomInfo.lastRead || 0;
            
            // 각 채팅방의 마지막 메시지 시간 확인
            const roomRef = ChatNotifications.database.ref(`chatRooms/${roomId}`);
            roomRef.once('value', (roomSnapshot) => {
                const roomData = roomSnapshot.val();
                if (roomData && roomData.lastMessageTime && roomData.lastMessageTime > lastRead) {
                    // 내가 보낸 메시지가 아닌 경우만 카운트
                    if (roomData.lastSenderId && roomData.lastSenderId != ChatNotifications.currentUserId) {
                        totalUnread++;
                    }
                }
                
                processedRooms++;
                if (processedRooms === roomIds.length) {
                    // 모든 채팅방 처리 완료
                    ChatNotifications.unreadCount = totalUnread;
                    updateBadgeDisplay();
                }
            });
        });
    });
}

/**
 * 페이지 언로드 시 정리
 */
function cleanupChatNotifications() {
    // 모든 리스너 제거
    ChatNotifications.userRoomsListeners.forEach(ref => {
        ref.off();
    });
    
    Object.values(ChatNotifications.roomMessageListeners).forEach(({ ref, listener }) => {
        ref.off('value', listener);
    });
    
    ChatNotifications.userRoomsListeners = [];
    ChatNotifications.roomMessageListeners = {};
    
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // Firebase SDK가 로드된 후 초기화
    if (typeof firebase !== 'undefined') {
        initializeChatNotifications();
    } else {
        // Firebase SDK 로드 대기
        const checkFirebase = setInterval(() => {
            if (typeof firebase !== 'undefined') {
                clearInterval(checkFirebase);
                initializeChatNotifications();
            }
        }, 100);
        
        // 10초 후 포기
        setTimeout(() => {
            clearInterval(checkFirebase);
        }, 10000);
    }
});

// 페이지 언로드 시 정리
window.addEventListener('beforeunload', cleanupChatNotifications);