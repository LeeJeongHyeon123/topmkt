<?php
/**
 * 채팅 메인 페이지
 * Firebase Realtime Database 기반 실시간 채팅
 */
require_once SRC_PATH . '/helpers/ProfileImageHelper.php';

// 프로필 이미지 모달 리소스 로드
include SRC_PATH . '/views/components/profile-modal-resources.php';
?>

<!-- 브라우저 확장 프로그램 에러 억제 -->
<script src="/assets/js/error-suppressor.js"></script>

<!-- 채팅 페이지 스타일 include -->
<style>
<?php
// 채팅 페이지 스타일 파일 include
$styleFile = SRC_PATH . '/views/chat/components/chat-styles.css';
if (file_exists($styleFile)) {
    echo file_get_contents($styleFile);
}
?>
</style>
.chat-container {
</style>

<div class="chat-container">
    <!-- 채팅 헤더 -->
    <div class="chat-header">
        <div class="chat-header-content">
            <div class="chat-header-text">
                <h1>💬 실시간 채팅</h1>
                <p>다른 회원들과 실시간으로 소통하세요</p>
            </div>
            <!-- 모바일 채팅방 목록 토글 버튼 -->
            <button class="mobile-chat-toggle" onclick="toggleMobileChatSidebar()">
                <i class="fas fa-bars"></i>
                <span>채팅방</span>
            </button>
        </div>
    </div>
    
    <!-- 채팅 레이아웃 -->
    <div class="chat-layout">
        <!-- 사이드바 (채팅방 목록) -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">채팅방</div>
                <button class="new-chat-btn" onclick="openNewChatModal()">
                    <i class="fas fa-plus"></i> 새 채팅
                </button>
            </div>
            
            <div class="chat-rooms-list" id="chatRoomsList">
                <!-- 로딩 상태 -->
                <div class="loading" id="roomsLoading">
                    <div class="loading-spinner"></div>
                    채팅방을 불러오는 중...
                </div>
                
                <!-- 채팅방 목록이 여기에 동적으로 추가됩니다 -->
            </div>
        </div>
        
        <!-- 메인 채팅 영역 -->
        <div class="chat-main">
            <!-- 채팅 시작 안내 -->
            <div class="chat-welcome" id="chatWelcome">
                <div class="chat-welcome-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>채팅을 시작해보세요!</h3>
                <p>왼쪽에서 채팅방을 선택하거나 새로운 채팅을 시작하세요.</p>
            </div>
            
            <!-- 활성 채팅 영역 (처음엔 숨김) -->
            <div id="activeChatArea" style="display: none;">
                <!-- 채팅 헤더 -->
                <div class="chat-header-bar">
                    <!-- 모바일 뒤로가기 버튼 -->
                    <button class="mobile-back-btn" onclick="backToChatList()" style="display: none;">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="chat-partner-avatar" id="chatPartnerAvatar">
                        U
                    </div>
                    <div class="chat-partner-info">
                        <div class="chat-partner-name" id="chatPartnerName">사용자</div>
                    </div>
                    <div class="chat-options">
                        <button class="chat-option-btn" id="visitProfileBtn" title="프로필 방문" onclick="visitPartnerProfile()" style="display: none;">
                            <i class="fas fa-user"></i>
                        </button>
                        <button class="chat-option-btn" title="더보기" onclick="showChatOptionsMenu(event)">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                
                <!-- 메시지 영역 -->
                <div class="chat-messages" id="chatMessages">
                    <!-- 메시지들이 여기에 동적으로 추가됩니다 -->
                </div>
                
                <!-- 메시지 입력 영역 -->
                <div class="chat-input-area">
                    <form class="chat-input-form" id="chatInputForm">
                        <textarea 
                            class="chat-input" 
                            id="chatInput" 
                            placeholder="메시지를 입력하세요..." 
                            rows="1"
                            maxlength="1000"></textarea>
                        <button type="submit" class="chat-send-btn" id="chatSendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 새 채팅 모달 -->
<div class="new-chat-modal" id="newChatModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">새 채팅 시작</h3>
            <button class="modal-close" onclick="closeNewChatModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="search-section">
                <div class="search-input-container">
                    <input 
                        type="text" 
                        class="search-input" 
                        id="userSearchInput" 
                        placeholder="정확한 닉네임 입력 후 엔터 또는 돋보기 클릭..." 
                        autocomplete="off">
                    <button type="button" class="search-btn" id="userSearchBtn" onclick="performUserSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="users-list" id="usersList">
                <!-- 초기 상태 -->
                <div style="text-align: center; padding: 40px 20px; color: #718096;" id="usersInitial">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 0.9rem;">정확한 닉네임으로 검색하세요</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 기존 프로필 이미지 모달 HTML 제거됨 - profile-modal.js 통합 시스템 사용 -->

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>

<script>
// Firebase 설정 및 초기화
const firebaseConfig = <?= json_encode($firebase_config) ?>;

// Firebase 초기화
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}

const database = firebase.database();
const currentUserId = <?= json_encode($current_user_id) ?>;
const currentUser = <?= json_encode($current_user) ?>;

// 전역 변수
let activeRoomId = null;
let chatRooms = {};
let users = {};
let currentPartnerUserId = null;


/**
 * 전역 fetch 인터셉터 비활성화
 */
function disableFetchInterceptor() {
    // 원본 fetch가 이미 저장되어 있다면 복원
    if (window.originalFetch) {
        window.fetch = window.originalFetch;
    }
}

/**
 * 채팅용 커스텀 fetch (로딩 없음)
 */
function chatFetch(url, options = {}) {
    return window.originalFetch ? window.originalFetch(url, options) : fetch(url, options);
}

/**
 * 채팅 리스너 정리 함수
 */
function cleanupChatListeners() {
    
    // 채팅방 목록 리스너 제거
    if (window.chatRoomsListener) {
        const userRoomsRef = database.ref(`userRooms/${currentUserId}`);
        userRoomsRef.off('value', window.chatRoomsListener);
        window.chatRoomsListener = null;
    }
    
    // 개별 채팅방 리스너 제거
    if (window.roomListeners) {
        Object.keys(window.roomListeners).forEach(roomId => {
            const roomRef = database.ref(`chatRooms/${roomId}`);
            roomRef.off('value', window.roomListeners[roomId]);
        });
        window.roomListeners = {};
    }
    
    // 메시지 리스너 제거
    if (window.currentMessageListener && activeRoomId) {
        const messagesRef = database.ref(`messages/${activeRoomId}`);
        messagesRef.off('value', window.currentMessageListener);
        window.currentMessageListener = null;
    }
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 채팅 페이지 진입 시 읽지 않은 메시지 수 초기화
    if (typeof window.resetChatNotificationCount === 'function') {
        window.resetChatNotificationCount();
    }
    
    initializeChat();
    setupEventListeners();
    
    // 프로필 이미지 클릭 이벤트 위임 (동적으로 생성된 요소용)
    document.addEventListener('click', function(e) {
        const profileImage = e.target.closest('.profile-image-clickable');
        if (profileImage) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = profileImage.getAttribute('data-user-id');
            const userName = profileImage.getAttribute('data-user-name');
            
            if (userId && userName && typeof window.profileModal !== 'undefined') {
                window.profileModal.show(userId, userName, false);
            } else {
            }
        }
    });
    
    // 페이지 언로드 시 리스너 정리
    window.addEventListener('beforeunload', cleanupChatListeners);
    window.addEventListener('pagehide', cleanupChatListeners);
    
    // 🔍 전역 클릭 디버깅 - 모든 클릭 이벤트 감지
    document.addEventListener('click', function(e) {
        const target = e.target;
        const roomItem = target.closest('.chat-room-item');
        
        if (roomItem) {
            const roomId = roomItem.getAttribute('data-room-id');
        } else if (target.closest('#chatRoomsList')) {
        }
    }, true); // capture 단계에서 캐치
    
    // URL 해시로 특정 채팅방 열기 처리
    handleUrlHash();
});

/**
 * 채팅 초기화
 */
function initializeChat() {
    
    // 채팅 페이지에서는 전역 로딩 인터셉터 비활성화
    disableFetchInterceptor();
    
    // 사용자 온라인 상태 설정
    setUserOnlineStatus(true);
    
    // 채팅방 목록 로드
    loadChatRooms();
    
    // 페이지 언로드 시 오프라인 상태로 변경
    window.addEventListener('beforeunload', function() {
        setUserOnlineStatus(false);
    });
}

/**
 * 이벤트 리스너 설정
 */
function setupEventListeners() {
    // 새 채팅 모달 외부 클릭 시 닫기
    document.getElementById('newChatModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeNewChatModal();
        }
    });
    
    // 사용자 검색 - Enter 키 이벤트
    document.getElementById('userSearchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performUserSearch();
        }
    });
    
    // 메시지 입력 폼
    document.getElementById('chatInputForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // 메시지 입력창 엔터키 처리
    document.getElementById('chatInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // ESC 키로 모달 닫기 (프로필 이미지 모달은 profile-modal.js에서 자동 처리)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeNewChatModal();
        }
    });
    
    // 프로필 이미지 클릭 이벤트는 profile-modal.js의 통합 시스템에서 자동 처리됨
}

/**
 * 사용자 온라인 상태 설정
 */
function setUserOnlineStatus(isOnline) {
    const statusRef = database.ref(`users/${currentUserId}/status`);
    const lastSeenRef = database.ref(`users/${currentUserId}/lastSeen`);
    
    if (isOnline) {
        statusRef.set('online');
        // 연결이 끊어지면 자동으로 오프라인 상태로 변경
        statusRef.onDisconnect().set('offline');
        lastSeenRef.onDisconnect().set(firebase.database.ServerValue.TIMESTAMP);
    } else {
        statusRef.set('offline');
        lastSeenRef.set(firebase.database.ServerValue.TIMESTAMP);
    }
}

/**
 * 채팅방 목록 로드
 */
function loadChatRooms() {
    
    const userRoomsRef = database.ref(`userRooms/${currentUserId}`);
    
    // 기존 리스너가 있다면 제거 (중복 방지)
    if (window.chatRoomsListener) {
        userRoomsRef.off('value', window.chatRoomsListener);
    }
    
    // 새로운 리스너 생성 및 저장
    window.chatRoomsListener = function(snapshot) {
        const roomsListContainer = document.getElementById('chatRoomsList');
        const loadingElement = document.getElementById('roomsLoading');
        
        if (loadingElement) {
            loadingElement.remove();
        }
        
        const userRooms = snapshot.val() || {};
        const currentRoomIds = Object.keys(userRooms);
        
        // 중복 업데이트 방지: 이전 상태와 비교
        if (window.lastRoomIds && 
            window.lastRoomIds.length === currentRoomIds.length &&
            window.lastRoomIds.every(id => currentRoomIds.includes(id))) {
            return;
        }
        
        window.lastRoomIds = [...currentRoomIds]; // 현재 상태 저장
        
        if (currentRoomIds.length === 0) {
            roomsListContainer.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #718096;" id="noRoomsMessage">
                    <i class="fas fa-comments" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 0.9rem;">아직 채팅방이 없습니다.<br>새 채팅을 시작해보세요!</p>
                </div>
            `;
        } else {
            // 채팅방이 있으면 "채팅방이 없습니다" 메시지 제거
            const noRoomsMessage = document.getElementById('noRoomsMessage');
            if (noRoomsMessage) {
                noRoomsMessage.remove();
            }
        }
        
        // 각 채팅방의 정보를 가져와서 표시
        currentRoomIds.forEach(roomId => {
            loadChatRoomInfo(roomId);
        });
    };
    
    userRoomsRef.on('value', window.chatRoomsListener);
}

/**
 * 채팅방 정보 로드
 */
function loadChatRoomInfo(roomId) {
    
    // 이미 로드된 채팅방이면 렌더링만 수행
    if (chatRooms[roomId]) {
        renderChatRoomItem(roomId, chatRooms[roomId]);
        return;
    }
    
    const roomRef = database.ref(`chatRooms/${roomId}`);
    
    // 기존 리스너가 있다면 제거 (중복 방지)
    if (window.roomListeners && window.roomListeners[roomId]) {
        roomRef.off('value', window.roomListeners[roomId]);
    }
    
    // 리스너 저장용 객체 초기화
    if (!window.roomListeners) {
        window.roomListeners = {};
    }
    
    // 새로운 리스너 생성 및 저장
    window.roomListeners[roomId] = function(snapshot) {
        const roomData = snapshot.val();
        
        if (!roomData) {
            Toast.error('채팅방을 불러올 수 없습니다.\n페이지를 새로고침해주세요.');
            return;
        }
        
        chatRooms[roomId] = roomData;

        
        renderChatRoomItem(roomId, roomData);
    };
    
    roomRef.on('value', window.roomListeners[roomId], function(error) {
        Toast.error('채팅방 연결에 실패했습니다.\n잠시 후 다시 시도해주세요.');
    });
}

/**
 * 채팅방 아이템 렌더링
 */
function renderChatRoomItem(roomId, roomData) {
    const roomsListContainer = document.getElementById('chatRoomsList');
    
    // 기존 아이템이 있으면 업데이트, 없으면 생성
    // 특수문자가 포함된 roomId를 안전하게 선택하기 위해 모든 채팅방 아이템을 순회
    let roomItem = null;
    const allRoomItems = document.querySelectorAll('.chat-room-item');
    for (const item of allRoomItems) {
        if (item.getAttribute('data-room-id') === roomId) {
            roomItem = item;
            break;
        }
    }
    
    if (!roomItem) {
        roomItem = document.createElement('div');
        roomItem.className = 'chat-room-item';
        roomItem.setAttribute('data-room-id', roomId);
        
        // 클릭 이벤트 등록 with 디버깅 - 여러 이벤트로 시도
        const clickHandler = (e) => {

            
            // roomId 특수문자 검사
            if (roomId.includes('-') || roomId.includes('_')) {
            }
            
            // 채팅방 데이터 존재 여부 확인
            if (chatRooms[roomId]) {
            } else {

            }
            
            // 이벤트 전파 방지 (중복 실행 방지)
            e.stopPropagation();
            e.preventDefault();
            
            openChatRoom(roomId);
        };
        
        // 여러 이벤트 타입으로 등록
        roomItem.addEventListener('click', clickHandler);
        roomItem.addEventListener('mousedown', clickHandler);
        roomItem.addEventListener('touchstart', clickHandler, { passive: false });
        
        // 🔥 최신 메시지 순서로 삽입 위치 결정
        insertChatRoomAtCorrectPosition(roomsListContainer, roomItem, roomData);
        
        // 디버깅: 요소 클릭 가능 여부 확인
        
        // 추가 디버깅: 겹치는 요소 확인
        setTimeout(() => {
            const rect = roomItem.getBoundingClientRect();
            const centerX = rect.x + rect.width / 2;
            const centerY = rect.y + rect.height / 2;
            const topElement = document.elementFromPoint(centerX, centerY);
            
            
            if (topElement !== roomItem) {
                
                // 가리는 요소의 z-index를 낮춰보기
                if (topElement && topElement.style) {
                    const currentZIndex = window.getComputedStyle(topElement).zIndex;
                }
            }
        }, 1000);
    }
    
    // 채팅방 이름과 아바타 설정
    let roomName = roomData.name || '채팅방';
    let avatarText = roomName.substring(0, 1).toUpperCase();
    
    // 1:1 채팅인 경우 상대방 이름과 이미지 설정
    let partnerImage = null;
    if (roomData.type === 'private' && roomData.participants) {
        const otherUserId = Object.keys(roomData.participants).find(id => id != currentUserId);
        if (otherUserId) {
            const otherParticipant = roomData.participants[otherUserId];

            // 🔥 근본 원인 해결: 사용자 정보가 실제로 없는 경우에만 로드 (탈퇴한 회원 예외 처리)
            const shouldRefreshProfile = !users[otherUserId] ||
                                       (!users[otherUserId].nickname || !users[otherUserId].profile_image) &&
                                       !(users[otherUserId] && (users[otherUserId].is_deleted || users[otherUserId].status === 'deleted'));

            if (users[otherUserId] && !shouldRefreshProfile) {
                // 기존 사용자 정보 사용
                roomName = users[otherUserId].nickname || '사용자';
                avatarText = roomName.substring(0, 1).toUpperCase();
                partnerImage = users[otherUserId].profile_image || users[otherUserId].profile_image_thumb;
            } else {
                // 🔥 실제로 필요한 정보가 없는 경우에만 로드 (근본 원인 해결)
                // 탈퇴한 회원인 경우 추가 로드 방지
                const isDeletedUser = users[otherUserId] && (users[otherUserId].is_deleted || users[otherUserId].status === 'deleted');
                if (isDeletedUser) {
                }
                if (!roomItem.dataset.loadingUser && shouldRefreshProfile && !isDeletedUser) {
                    roomItem.dataset.loadingUser = 'true';

                    loadUserInfo(otherUserId).then(() => {
                        // 로딩 플래그 제거
                        delete roomItem.dataset.loadingUser;
                        // 새로운 정보로 채팅방 아이템 업데이트
                        renderChatRoomItem(roomId, roomData);
                    }).catch((error) => {
                        Toast.warning('사용자 프로필을 불러올 수 없습니다.');
                        delete roomItem.dataset.loadingUser;
                    });
                }

                // 임시로 기존 정보나 기본값 사용
                if (users[otherUserId]) {
                    roomName = users[otherUserId].nickname || '사용자';
                    partnerImage = users[otherUserId].profile_image || users[otherUserId].profile_image_thumb;
                } else {
                    roomName = '사용자';
                }
                avatarText = roomName.substring(0, 1).toUpperCase();
            }
        }
    }
    
    // 상대방의 참여 상태 확인
    const otherParticipantId = Object.keys(roomData.participants || {}).find(id => id != currentUserId);
    const otherParticipant = otherParticipantId ? roomData.participants[otherParticipantId] : null;
    const isOtherParticipantInactive = otherParticipant && otherParticipant.status === 'inactive';

    // 채팅방 상태에 따른 스타일 및 표시 결정
    const roomStatusClass = isOtherParticipantInactive ? 'inactive-room' : '';
    const roomStatusText = isOtherParticipantInactive ? ' (종료된 대화)' : '';
    const roomNameWithStatus = roomName + roomStatusText;

    // 비활성 채팅방의 경우 클릭 비활성화
    const clickDisabledClass = isOtherParticipantInactive ? 'disabled-room' : '';
    const clickHandler = isOtherParticipantInactive ?
        'onclick="showInactiveRoomModal()"' :
        `onclick="openChatRoom('${roomId}')"`;


    roomItem.innerHTML = `
        <div class="room-info ${roomStatusClass}">
            <div class="room-avatar">
                ${partnerImage ? `
                    <img src="${partnerImage}"
                         alt="${roomNameWithStatus}"
                         style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; ${isOtherParticipantInactive ? 'opacity: 0.6; filter: grayscale(50%);' : ''}"
                         class="profile-image-clickable ${clickDisabledClass}"
                         data-user-id="${otherParticipantId || ''}"
                         data-user-name="${roomName}"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                        ${avatarText}
                    </div>
                ` : `
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                        ${avatarText}
                    </div>
                `}
            </div>
            <div class="room-details">
                <div class="room-header">
                    <div class="room-name ${roomStatusClass}">${roomNameWithStatus}</div>
                    <div class="room-time" id="lastTime-${roomId}">
                        ${roomData.lastMessageTime ? formatTime(roomData.lastMessageTime) : ''}
                    </div>
                </div>
                <div class="room-last-message" id="lastMessage-${roomId}">
                    ${roomData.lastMessage || '메시지가 없습니다'}
                </div>
            </div>
        </div>
    `;

    // 비활성 채팅방의 경우 클릭 이벤트 추가
    if (isOtherParticipantInactive) {
        roomItem.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showInactiveRoomModal(roomId, roomName);
        });
    }

    // 🔥 room-name 디버깅 로그 추가
    setTimeout(() => {

        const roomNameElement = roomItem.querySelector('.room-name');
        if (roomNameElement) {

            const style = window.getComputedStyle(roomNameElement);
            const rect = roomNameElement.getBoundingClientRect();



            const isVisible = rect.width > 0 && rect.height > 0 &&
                             style.display !== 'none' &&
                             style.visibility !== 'hidden' &&
                             parseFloat(style.opacity) > 0;

            // 🔥 디버깅 완료 - 강제 스타일 제거됨
        } else {
        }
    }, 100);

    // 마지막 메시지 업데이트
    updateLastMessage(roomId);
    
    // 읽지 않은 메시지 수 업데이트
    updateRoomUnreadCount(roomId);

    // 🔥 기존 채팅방 업데이트 시에도 위치 재조정
    repositionExistingChatRoom(roomId, roomData);
}

/**
 * 🔥 채팅방을 최신 메시지 순서로 적절한 위치에 삽입
 */
function insertChatRoomAtCorrectPosition(container, roomItem, roomData) {
    const currentTime = roomData.lastMessageTime || 0;
    const existingItems = Array.from(container.querySelectorAll('.chat-room-item'));

    // 로딩 메시지가 있다면 제거
    const loadingElement = container.querySelector('.loading');
    if (loadingElement) {
        loadingElement.remove();
    }

    // 삽입할 위치 찾기 (최신 메시지가 위로 오도록)
    let insertIndex = 0;
    for (let i = 0; i < existingItems.length; i++) {
        const existingRoomId = existingItems[i].getAttribute('data-room-id');
        const existingRoomData = chatRooms[existingRoomId];
        const existingTime = existingRoomData?.lastMessageTime || 0;

        if (currentTime > existingTime) {
            insertIndex = i;
            break;
        }
        insertIndex = i + 1;
    }

    // 적절한 위치에 삽입
    if (insertIndex >= existingItems.length) {
        container.appendChild(roomItem);
    } else {
        container.insertBefore(roomItem, existingItems[insertIndex]);
    }

}

/**
 * 🔥 기존 채팅방 위치 재조정 (메시지 업데이트 시)
 */
function repositionExistingChatRoom(roomId, roomData) {
    const roomItem = document.querySelector(`[data-room-id="${roomId}"]`);
    const container = document.getElementById('chatRoomsList');

    if (!roomItem || !container) return;

    // 현재 위치에서 제거
    roomItem.remove();

    // 새로운 위치에 삽입
    insertChatRoomAtCorrectPosition(container, roomItem, roomData);
}

/**
 * 채팅방 열기
 */
function openChatRoom(roomId, retryCount = 0) {

    // 무한 루프 방지: 최대 3번까지만 재시도
    if (retryCount >= 3) {
        Toast.error('채팅방을 불러올 수 없습니다. 잠시 후 다시 시도해주세요.');
        return;
    }

    activeRoomId = roomId;
    const roomData = chatRooms[roomId];


    if (!roomData) {

        Toast.error('채팅방을 찾을 수 없습니다.\n목록에서 다시 선택해주세요.');

        // Firebase에서 직접 데이터 가져오기 시도
        const roomRef = database.ref(`chatRooms/${roomId}`);
        roomRef.once('value', function(snapshot) {
            const firebaseRoomData = snapshot.val();

            if (firebaseRoomData) {
                chatRooms[roomId] = firebaseRoomData;
                // 재귀 호출 시 retryCount 증가
                openChatRoom(roomId, retryCount + 1);
            } else {
                Toast.error('채팅방을 찾을 수 없습니다.');
            }
        });
        return;
    }
    
    // 활성 채팅방 표시 업데이트
    document.querySelectorAll('.chat-room-item').forEach(item => {
        item.classList.remove('active');
        // 특수문자가 포함된 roomId 안전한 매칭
        if (item.getAttribute('data-room-id') === roomId) {
            item.classList.add('active');
        }
    });
    
    // 채팅 UI 표시
    
    const chatWelcome = document.getElementById('chatWelcome');
    const activeChatArea = document.getElementById('activeChatArea');
    
    
    if (chatWelcome) {
        chatWelcome.style.display = 'none';
    } else {
    }
    
    if (activeChatArea) {
        activeChatArea.style.display = 'flex';
        activeChatArea.style.flexDirection = 'column';
        activeChatArea.style.height = '100%';
    } else {
    }
    
    // 채팅 상대 정보 설정
    updateChatHeader(roomData);
    
    // 메시지 로드
    loadMessages(roomId);
    
    // 읽음 상태 업데이트
    markRoomAsRead(roomId);

    // 🔥 모바일에서 채팅방 선택 시 채팅창으로 전환
    if (window.innerWidth <= 768) {
        const chatLayout = document.querySelector('.chat-layout');
        if (chatLayout) {
            chatLayout.classList.add('chat-active');
        }
    }
}

/**
 * 채팅 헤더 업데이트
 */
function updateChatHeader(roomData) {
    
    let partnerName = roomData.name || '채팅방';
    let partnerImage = null;
    
    // 1:1 채팅인 경우 상대방 정보로 설정
    if (roomData.type === 'private' && roomData.participants) {
        const otherUserId = Object.keys(roomData.participants).find(id => id != currentUserId);
        
        if (otherUserId) {
            currentPartnerUserId = otherUserId; // 현재 상대방 ID 저장
            
            // 사용자 정보가 없으면 비동기로 가져오기
            if (!users[otherUserId]) {
                loadUserInfo(otherUserId).then(() => {
                    // 무한 루프 방지: 사용자 정보가 실제로 로드된 경우에만 재호출
                    if (users[otherUserId]) {
                        updateChatHeader(roomData);
                    } else {
                        // 기본값으로 폴백
                        users[otherUserId] = {
                            id: otherUserId,
                            nickname: '사용자',
                            profile_image: null
                        };
                        updateChatHeader(roomData);
                    }
                }).catch(error => {
                    Toast.warning('사용자 정보를 불러올 수 없습니다.');
                    // 에러 시 기본값으로 폴백
                    users[otherUserId] = {
                        id: otherUserId,
                        nickname: '사용자',
                        profile_image: null
                    };
                    updateChatHeader(roomData);
                });
                return; // 비동기 로딩 중이므로 함수 종료
            }
            
            if (users[otherUserId]) {
                partnerName = users[otherUserId].nickname || '사용자';
                partnerImage = users[otherUserId].profile_image || users[otherUserId].profile_image_thumb;
            }
        }
        
        // 프로필 방문 버튼 표시
        document.getElementById('visitProfileBtn').style.display = 'block';
    } else {
        currentPartnerUserId = null;
        // 프로필 방문 버튼 숨김
        document.getElementById('visitProfileBtn').style.display = 'none';
    }
    
    // 🔍 닉네임 설정 디버깅 시작

    // 1. 기본 정보 확인

    // 2. HTML 요소 상태 확인 (설정 전)

    const nameElement = document.getElementById('chatPartnerName');
    if (nameElement) {

        // CSS 스타일 확인
        const rect = nameElement.getBoundingClientRect();
        const computedStyle = window.getComputedStyle(nameElement);
    } else {
    }

    // 3. 사용자 정보 확인
    if (currentPartnerUserId) {
        if (users[currentPartnerUserId]) {
        }
    }

    // 4. 채팅방 데이터 확인
    if (activeRoomId && chatRooms[activeRoomId]) {
        const participantIds = Object.keys(chatRooms[activeRoomId].participants || {});
        const otherUserId = participantIds.find(id => id != currentUserId);
    }

    // 5. 실제 DOM 설정
    document.getElementById('chatPartnerName').textContent = partnerName;

    // 6. 설정 후 상태 확인
    const afterSetText = document.getElementById('chatPartnerName').textContent;

    // 7. 0.5초 후 다시 확인 (다른 코드에 의한 덮어쓰기 감지)
    setTimeout(() => {
        const delayedText = document.getElementById('chatPartnerName').textContent;
        if (delayedText !== partnerName) {
        } else {
        }
    }, 500);
    
    // 프로필 이미지 설정
    const avatarElement = document.getElementById('chatPartnerAvatar');
    const avatarText = partnerName.substring(0, 1).toUpperCase();
    
    if (partnerImage) {
        const otherUserId = Object.keys(roomData.participants || {}).find(id => id != currentUserId);
        avatarElement.innerHTML = `
            <img src="${partnerImage}" 
                 alt="${partnerName}" 
                 style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                 class="profile-image-clickable"
                 data-user-id="${otherUserId || ''}"
                 data-user-name="${partnerName}"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                ${avatarText}
            </div>
        `;
    } else {
        avatarElement.textContent = avatarText;
        avatarElement.style.background = 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)';
    }
}

/**
 * 메시지 로드
 */
function loadMessages(roomId) {
    
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) {
        return;
    }
    
    messagesContainer.innerHTML = '<div class="loading"><div class="loading-spinner"></div>메시지를 불러오는 중...</div>';
    
    // 실용적인 메시지 로딩 수 (성능과 사용성의 균형)
    const messagesRef = database.ref(`messages/${roomId}`).limitToLast(50);
    
    
    // 기존 메시지 리스너 제거
    if (window.currentMessageListener) {
        const oldRef = database.ref(`messages/${activeRoomId || roomId}`);
        oldRef.off('value', window.currentMessageListener);
        window.currentMessageListener = null;
    }
    
    // 새로운 리스너 생성 및 저장
    window.currentMessageListener = function(snapshot) {

        const messages = snapshot.val() || {};
        
        renderMessages(messages);
        scrollToBottom();
    };
    
    messagesRef.on('value', window.currentMessageListener, function(error) {
        Toast.error('메시지를 불러올 수 없습니다.\n연결 상태를 확인해주세요.');
        messagesContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">메시지를 불러올 수 없습니다.</div>';
    });
}

/**
 * 메시지 렌더링
 */
function renderMessages(messages) {
    
    const messagesContainer = document.getElementById('chatMessages');
    
    if (!messagesContainer) {
        return;
    }
    
    messagesContainer.innerHTML = '';
    
    const messageCount = Object.keys(messages).length;
    
    if (messageCount === 0) {
        
        const emptyMessage = `
            <div style="text-align: center; padding: 40px 20px; color: #718096;">
                <i class="fas fa-comment-dots" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
                <p style="margin: 0; font-size: 0.9rem;">첫 메시지를 보내보세요!</p>
            </div>
        `;
        
        messagesContainer.innerHTML = emptyMessage;
        return;
    }
    
    const messageArray = Object.entries(messages).sort((a, b) => a[1].timestamp - b[1].timestamp);
    
    // DocumentFragment 사용으로 리플로우 최소화
    const fragment = document.createDocumentFragment();
    
    messageArray.forEach(([messageId, messageData]) => {
        const messageElement = createMessageElement(messageId, messageData);
        fragment.appendChild(messageElement);
    });
    
    // 한 번에 DOM에 추가
    messagesContainer.appendChild(fragment);
}

/**
 * 메시지 요소 생성 (성능 최적화)
 */
function createMessageElement(messageId, messageData) {
    const isOwn = messageData.senderId == currentUserId;
    
    const messageElement = document.createElement('div');
    messageElement.className = `message-item ${isOwn ? 'own' : ''}`;
    messageElement.setAttribute('data-message-id', messageId);
    
    const senderName = messageData.senderName || '사용자';
    const avatarText = senderName.substring(0, 1).toUpperCase();
    const formattedTime = formatTime(messageData.timestamp);
    
    // 메시지 보낸 사용자의 프로필 이미지 정보 확인
    const senderUser = users[messageData.senderId];
    const hasProfileImage = senderUser && senderUser.profile_image;
    
    messageElement.innerHTML = `
        ${!isOwn ? `
            <div class="message-avatar ${hasProfileImage ? 'profile-image-clickable' : ''}" 
                 ${hasProfileImage ? `data-user-id="${messageData.senderId}" data-user-name="${senderName}"` : ''}>
                ${hasProfileImage ? `
                    <img src="${senderUser.profile_image}" 
                         alt="${senderName}" 
                         style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.7rem;">
                        ${avatarText}
                    </div>
                ` : avatarText}
            </div>
        ` : ''}
        <div class="message-bubble">
            <p class="message-text">${escapeHtml(messageData.text || messageData.message)}</p>
            <div class="message-time">${formattedTime}</div>
        </div>
    `;
    
    return messageElement;
}

/**
 * 단일 메시지 렌더링 (기존 호환성)
 */
function renderSingleMessage(messageId, messageData) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageElement = createMessageElement(messageId, messageData);
    messagesContainer.appendChild(messageElement);
}

/**
 * 메시지 전송
 */
function sendMessage() {
    if (!activeRoomId) {
        Toast.info('채팅방을 선택해주세요.');
        return;
    }
    
    const input = document.getElementById('chatInput');
    const text = input.value.trim();
    
    if (!text) return;
    
    const sendBtn = document.getElementById('chatSendBtn');
    sendBtn.disabled = true;
    
    const messageData = {
        senderId: currentUserId,
        senderName: currentUser.nickname,
        text: text,
        timestamp: firebase.database.ServerValue.TIMESTAMP,
        type: 'text'
    };
    
    // Firebase에 메시지 저장
    const messagesRef = database.ref(`messages/${activeRoomId}`);
    const newMessageRef = messagesRef.push();
    
    newMessageRef.set(messageData)
        .then(() => {
            // 🔥 메시지 전송 시 비활성 참여자의 userRooms 복구
            const currentRoom = chatRooms[activeRoomId];
            if (currentRoom && currentRoom.participants) {
                Object.keys(currentRoom.participants).forEach(participantId => {
                    const participant = currentRoom.participants[participantId];

                    // 비활성 상태인 참여자가 있다면 userRooms에 다시 추가
                    if (participant.status === 'inactive' && participantId !== currentUserId) {

                        database.ref(`userRooms/${participantId}/${activeRoomId}`).set({
                            joinedAt: participant.joinedAt || firebase.database.ServerValue.TIMESTAMP,
                            lastReadTime: Date.now()
                        }).then(() => {
                        }).catch((error) => {
                            Toast.warning('채팅방 목록 동기화에 실패했습니다.');
                        });
                    }
                });
            }

            // 채팅방의 마지막 메시지 업데이트
            updateRoomLastMessage(activeRoomId, text);

            // 입력창 초기화
            input.value = '';
            input.style.height = 'auto';
            sendBtn.disabled = false;
            input.focus();

        })
        .catch((error) => {
            Toast.error('메시지 전송에 실패했습니다.');
            sendBtn.disabled = false;
        });
}

/**
 * 채팅방 마지막 메시지 업데이트
 */
function updateRoomLastMessage(roomId, message) {
    const roomRef = database.ref(`chatRooms/${roomId}`);
    roomRef.update({
        lastMessage: message,
        lastMessageTime: firebase.database.ServerValue.TIMESTAMP,
        lastSenderId: currentUserId
    });
}

/**
 * 채팅방을 읽음으로 표시
 */
function markRoomAsRead(roomId) {
    const readRef = database.ref(`userRooms/${currentUserId}/${roomId}/lastRead`);
    readRef.set(firebase.database.ServerValue.TIMESTAMP);
    
    // 해당 채팅방의 읽지 않은 메시지 배지 제거
    updateRoomBadge(roomId, 0);
}

/**
 * 새 채팅 모달 열기
 */
function openNewChatModal() {
    document.getElementById('newChatModal').style.display = 'block';
    document.getElementById('userSearchInput').focus();
}

/**
 * 새 채팅 모달 닫기
 */
function closeNewChatModal() {
    document.getElementById('newChatModal').style.display = 'none';
    document.getElementById('userSearchInput').value = '';
    document.getElementById('usersList').innerHTML = '<div style="text-align: center; padding: 40px 20px; color: #718096;" id="usersInitial"><i class="fas fa-search" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i><p style="margin: 0; font-size: 0.9rem;">정확한 닉네임으로 검색하세요</p></div>';
}

/**
 * 사용자 검색 실행
 */
function performUserSearch() {
    const query = document.getElementById('userSearchInput').value.trim();
    
    if (query.length < 2) {
        Toast.error('정확한 닉네임을 2글자 이상 입력해주세요.');
        return;
    }
    
    searchUsers(query);
}

/**
 * 사용자 검색
 */
function searchUsers(query = null) {
    if (!query) {
        query = document.getElementById('userSearchInput').value.trim();
    }
    
    const usersList = document.getElementById('usersList');
    
    if (query.length < 2) {
        usersList.innerHTML = '<div style="text-align: center; padding: 40px 20px; color: #718096;"><i class="fas fa-search" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i><p style="margin: 0; font-size: 0.9rem;">정확한 닉네임으로 검색하세요</p></div>';
        return;
    }
    
    usersList.innerHTML = '<div class="loading"><div class="loading-spinner"></div>검색 중...</div>';
    
    // API를 통해 사용자 검색
    chatFetch(`/chat/search-users?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderUsersList(data.data);
            } else {
                usersList.innerHTML = `<div style="text-align: center; padding: 20px; color: #e53e3e;">${data.message || '검색에 실패했습니다.'}</div>`;
            }
        })
        .catch(error => {
            Toast.error('사용자 검색에 실패했습니다.\n잠시 후 다시 시도해주세요.');
            usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #e53e3e;">검색 중 오류가 발생했습니다.</div>';
        });
}

/**
 * 사용자 목록 렌더링
 */
function renderUsersList(usersList) {
    const container = document.getElementById('usersList');

    if (!usersList || usersList.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 20px; color: #718096;">검색 결과가 없습니다.</div>';
        return;
    }
    
    container.innerHTML = '';
    
    usersList.forEach(user => {
        const userItem = document.createElement('div');
        userItem.className = 'user-item';
        userItem.addEventListener('click', () => startChatWithUser(user));
        
        const avatarText = (user.nickname || 'U').substring(0, 1).toUpperCase();
        
        // 자기소개에서 HTML 태그 제거 및 정리
        let cleanBio = '탑마케팅 회원';
        if (user.bio) {
            // HTML 태그 제거
            cleanBio = user.bio.replace(/<[^>]*>/g, '').trim();
            // 빈 내용이나 공백만 있으면 기본값 사용
            if (!cleanBio || cleanBio.length === 0) {
                cleanBio = '탑마케팅 회원';
            } else if (cleanBio.length > 50) {
                cleanBio = cleanBio.substring(0, 50) + '...';
            }
        }
        
        userItem.innerHTML = `
            <div class="user-avatar">
                ${user.profile_image ? `
                    <img src="${escapeHtml(user.profile_image)}" 
                         alt="${escapeHtml(user.nickname)}" 
                         style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                         class="profile-image-clickable"
                         data-user-id="${user.id}"
                         data-user-name="${escapeHtml(user.nickname)}"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                        ${avatarText}
                    </div>
                ` : `
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                        ${avatarText}
                    </div>
                `}
            </div>
            <div class="user-info">
                <div class="user-name">${escapeHtml(user.nickname)}</div>
                <div class="user-status">${escapeHtml(cleanBio)}</div>
            </div>
        `;
        
        container.appendChild(userItem);
        
        // 사용자 정보 저장
        users[user.id] = user;
    });
}

/**
 * 사용자와 채팅 시작
 */
function startChatWithUser(user) {

    // 탈퇴한 회원과는 채팅 시작 불가 (추가 안전성 검사)
    if (user.is_deleted || user.status === 'deleted') {
        Toast.error('탈퇴한 회원과는 채팅을 시작할 수 없습니다.');
        return;
    }

    // 기존 채팅방이 있는지 확인
    const existingRoomId = findExistingPrivateRoom(user.id);

    if (existingRoomId) {
        // 기존 채팅방 열기
        openChatRoom(existingRoomId);
        closeNewChatModal();
        return;
    }

    // 새 채팅방 생성
    createPrivateChatRoom(user);
}

/**
 * 기존 1:1 채팅방 찾기 (비활성 참여자 포함)
 */
function findExistingPrivateRoom(userId) {
    for (const roomId in chatRooms) {
        const room = chatRooms[roomId];
        if (room.type === 'private' && room.participants) {
            const participantIds = Object.keys(room.participants);
            if (participantIds.length === 2 &&
                participantIds.includes(currentUserId.toString()) &&
                participantIds.includes(userId.toString())) {


                // 🔥 비활성 참여자가 있어도 기존 채팅방으로 인식
                // 나간 사용자의 상태를 다시 활성화
                const myParticipant = room.participants[currentUserId];
                const otherParticipant = room.participants[userId];

                if (myParticipant && myParticipant.status === 'inactive') {
                    database.ref(`chatRooms/${roomId}/participants/${currentUserId}`).update({
                        status: 'active',
                        rejoinedAt: firebase.database.ServerValue.TIMESTAMP
                    });
                }

                if (otherParticipant && otherParticipant.status === 'inactive') {
                    database.ref(`chatRooms/${roomId}/participants/${userId}`).update({
                        status: 'active',
                        rejoinedAt: firebase.database.ServerValue.TIMESTAMP
                    });
                }

                return roomId;
            }
        }
    }
    return null;
}

/**
 * 1:1 채팅방 생성
 */
function createPrivateChatRoom(user) {
    const roomData = {
        type: 'private',
        name: `${currentUser.nickname}, ${user.nickname}`,
        createdBy: currentUserId,
        createdAt: firebase.database.ServerValue.TIMESTAMP,
        participants: {
            [currentUserId]: {
                joinedAt: firebase.database.ServerValue.TIMESTAMP,
                role: 'member',
                status: 'active' // 🔥 기본 상태를 활성으로 설정
            },
            [user.id]: {
                joinedAt: firebase.database.ServerValue.TIMESTAMP,
                role: 'member',
                status: 'active' // 🔥 기본 상태를 활성으로 설정
            }
        },
        lastMessage: '',
        lastMessageTime: firebase.database.ServerValue.TIMESTAMP
    };
    
    // 사용자 쌍별로 고유한 채팅방 ID 생성
    const participantIds = [currentUserId, user.id].sort((a, b) => a - b);
    const roomId = `room_${participantIds[0]}_${participantIds[1]}`;


    // Firebase에 채팅방 생성 (기존 방이 있으면 업데이트, 없으면 생성)
    const roomRef = database.ref(`chatRooms/${roomId}`);

    // 기존 채팅방 확인
    roomRef.once('value')
        .then((snapshot) => {
            const existingRoom = snapshot.val();

            if (existingRoom) {

                // 기존 방의 참여자 상태를 활성으로 업데이트
                const participantsRef = database.ref(`chatRooms/${roomId}/participants`);
                return Promise.all([
                    participantsRef.child(currentUserId).update({
                        status: 'active',
                        rejoinedAt: firebase.database.ServerValue.TIMESTAMP
                    }),
                    participantsRef.child(user.id).update({
                        status: 'active',
                        rejoinedAt: firebase.database.ServerValue.TIMESTAMP
                    })
                ]);
            } else {

                // 새 채팅방 생성
                return roomRef.set(roomData);
            }
        })
        .then(() => {
            // 사용자별 채팅방 목록에 추가 또는 업데이트
            const userRoomsRef = database.ref('userRooms');

            return Promise.all([
                // 현재 사용자
                userRoomsRef.child(`${currentUserId}/${roomId}`).set({
                    joinedAt: firebase.database.ServerValue.TIMESTAMP,
                    lastRead: firebase.database.ServerValue.TIMESTAMP,
                    lastMessage: '',
                    lastMessageTime: firebase.database.ServerValue.TIMESTAMP,
                    partnerId: user.id,
                    partnerName: user.nickname || '사용자'
                }),
                // 상대방 사용자
                userRoomsRef.child(`${user.id}/${roomId}`).set({
                    joinedAt: firebase.database.ServerValue.TIMESTAMP,
                    lastRead: firebase.database.ServerValue.TIMESTAMP,
                    lastMessage: '',
                    lastMessageTime: firebase.database.ServerValue.TIMESTAMP,
                    partnerId: currentUserId,
                    partnerName: currentUser.nickname || '사용자'
                })
            ]);
        })
        .then(() => {
            
            // 생성된 채팅방 열기
            setTimeout(() => {
                openChatRoom(roomId);
                closeNewChatModal();
            }, 500);
        })
        .catch((error) => {
            Toast.error('채팅방 생성에 실패했습니다.');
        });
}

// 유틸리티 함수들

/**
 * 마지막 메시지 업데이트
 */
function updateLastMessage(roomId) {
    const lastMessageRef = database.ref(`messages/${roomId}`).limitToLast(1);
    
    lastMessageRef.on('value', function(snapshot) {
        const messages = snapshot.val();
        if (messages) {
            const lastMessage = Object.values(messages)[0];
            const lastMessageElement = document.getElementById(`lastMessage-${roomId}`);
            const lastTimeElement = document.getElementById(`lastTime-${roomId}`);
            
            if (lastMessageElement) {
                const messageText = lastMessage.text || lastMessage.message || '';
                lastMessageElement.textContent = messageText.substring(0, 30) + (messageText.length > 30 ? '...' : '');
            }
            
            if (lastTimeElement) {
                lastTimeElement.textContent = formatTime(lastMessage.timestamp);
            }
            
            // 새 메시지가 있으면 읽지 않은 메시지 수 업데이트
            if (lastMessage.senderId != currentUserId) {
                updateRoomUnreadCount(roomId);
            }
        }
    });
}

/**
 * 시간 포맷팅 (채팅방 목록용 - 간소화된 표시)
 */
function formatTime(timestamp) {
    if (!timestamp) return '';

    const date = new Date(timestamp);
    const now = new Date();

    // 오늘인지 확인 (년, 월, 일이 모두 같은지)
    const isToday = date.getFullYear() === now.getFullYear() &&
                   date.getMonth() === now.getMonth() &&
                   date.getDate() === now.getDate();

    if (isToday) {
        // 오늘이면 시:분만 표시
        return date.toLocaleTimeString('ko-KR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // 같은 년도 확인
    const isSameYear = date.getFullYear() === now.getFullYear();

    if (isSameYear) {
        // 같은 년도면 x월 x일 표시
        const month = date.getMonth() + 1;
        const day = date.getDate();
        return `${month}월 ${day}일`;
    } else {
        // 다른 년도면 년.월.일 표시
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}.${month}.${day}`;
    }
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
 * v3.57.0: debounce 함수는 통합 utils.js.php 사용
 * (사용하지 않던 중복 코드 제거)
 */

/**
 * 채팅 메시지 영역 스크롤 하단으로
 */
function scrollToBottom() {
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

/**
 * 입력창 자동 리사이즈
 */
document.getElementById('chatInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

/**
 * 채팅 옵션 메뉴 표시
 */
function showChatOptionsMenu(event) {
    event.stopPropagation();
    
    // 기존 메뉴 제거
    const existingMenu = document.querySelector('.chat-options-menu');
    if (existingMenu) {
        existingMenu.remove();
        return;
    }
    
    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();
    
    const menu = document.createElement('div');
    menu.className = 'chat-options-menu';
    menu.style.cssText = `
        position: fixed;
        top: ${rect.bottom + 5}px;
        right: ${window.innerWidth - rect.right}px;
        z-index: 1000;
    `;
    
    menu.innerHTML = `
        <button class="chat-options-menu-item" onclick="closeChatRoom()">
            <i class="fas fa-times"></i>
            <span>채팅방 닫기</span>
        </button>
        <button class="chat-options-menu-item danger" onclick="leaveChatRoom()">
            <i class="fas fa-sign-out-alt"></i>
            <span>채팅방 나가기</span>
        </button>
    `;
    
    document.body.appendChild(menu);
    
    // 애니메이션을 위한 딜레이
    setTimeout(() => {
        menu.classList.add('show');
    }, 10);
    
    // 외부 클릭시 메뉴 닫기
    document.addEventListener('click', function closeMenu(e) {
        if (!menu.contains(e.target)) {
            menu.classList.remove('show');
            setTimeout(() => {
                if (menu.parentNode) {
                    menu.remove();
                }
            }, 200);
            document.removeEventListener('click', closeMenu);
        }
    });
}

/**
 * 상대방 프로필 방문
 */
function visitPartnerProfile() {
    if (!currentPartnerUserId) {
        Toast.error('프로필 정보를 찾을 수 없습니다.');
        return;
    }

    // 사용자 정보가 있으면 닉네임으로 프로필 페이지 이동
    if (users[currentPartnerUserId]) {
        // 탈퇴한 회원인지 확인
        if (users[currentPartnerUserId].is_deleted || users[currentPartnerUserId].status === 'deleted') {
            Toast.error('탈퇴한 회원의 프로필은 볼 수 없습니다.');
            return;
        }

        if (users[currentPartnerUserId].nickname) {
            const profileUrl = `/profile/${encodeURIComponent(users[currentPartnerUserId].nickname)}`;
            window.open(profileUrl, '_blank');
        } else {
            Toast.error('프로필 페이지를 찾을 수 없습니다.');
        }
    } else {
        // 사용자 정보가 없으면 로드 후 이동
        loadUserInfo(currentPartnerUserId).then(() => {
            if (users[currentPartnerUserId]) {
                // 탈퇴한 회원인지 확인
                if (users[currentPartnerUserId].is_deleted || users[currentPartnerUserId].status === 'deleted') {
                    Toast.error('탈퇴한 회원의 프로필은 볼 수 없습니다.');
                    return;
                }

                if (users[currentPartnerUserId].nickname) {
                    const profileUrl = `/profile/${encodeURIComponent(users[currentPartnerUserId].nickname)}`;
                    window.open(profileUrl, '_blank');
                } else {
                    Toast.error('프로필 페이지를 찾을 수 없습니다.');
                }
            } else {
                Toast.error('프로필 페이지를 찾을 수 없습니다.');
            }
        }).catch(() => {
            Toast.error('프로필 정보를 불러오는 중 오류가 발생했습니다.');
        });
    }
}

/**
 * 채팅방 닫기
 */
function closeChatRoom() {
    // 메뉴 닫기
    const menu = document.querySelector('.chat-options-menu');
    if (menu) menu.remove();
    
    // 채팅 영역 숨기고 환영 메시지 표시
    document.getElementById('activeChatArea').style.display = 'none';
    document.getElementById('chatWelcome').style.display = 'flex';
    
    // 활성 채팅방 초기화
    activeRoomId = null;
    currentPartnerUserId = null;
    
    // 프로필 방문 버튼 숨김
    document.getElementById('visitProfileBtn').style.display = 'none';
    
    // 사이드바 활성 상태 제거
    document.querySelectorAll('.chat-room-item').forEach(item => {
        item.classList.remove('active');
    });
}


/**
 * 비활성 채팅방 모달 표시
 */
function showInactiveRoomModal(roomId, roomName) {
    // 기존 모달이 있으면 제거
    const existingModal = document.querySelector('.inactive-room-modal');
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.className = 'inactive-room-modal';
    modal.innerHTML = `
        <div class="inactive-room-modal-content">
            <div class="inactive-room-modal-header">
                <h3>종료된 대화</h3>
                <button class="inactive-room-modal-close" onclick="closeInactiveRoomModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="inactive-room-modal-body">
                <div class="inactive-room-info">
                    <i class="fas fa-user-slash" style="font-size: 2rem; color: #718096; margin-bottom: 16px;"></i>
                    <h4>${roomName}</h4>
                    <p>상대방이 채팅방을 나가서 대화가 종료되었습니다.</p>
                    <p style="font-size: 0.9rem; color: #718096; margin-top: 8px;">
                        새로운 메시지를 보내면 대화가 다시 활성화됩니다.
                    </p>
                </div>
                <div class="inactive-room-actions">
                    <button class="btn btn-secondary" onclick="closeInactiveRoomModal()">
                        닫기
                    </button>
                    <button class="btn btn-primary" onclick="reactivateChatRoom('${roomId}')">
                        대화 다시 시작하기
                    </button>
                </div>
            </div>
        </div>
    `;

    // 배경 클릭시 닫기
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeInactiveRoomModal();
        }
    });

    document.body.appendChild(modal);

    // 애니메이션 효과
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

/**
 * 비활성 채팅방 모달 닫기
 */
function closeInactiveRoomModal() {
    const modal = document.querySelector('.inactive-room-modal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

/**
 * 채팅방 다시 활성화
 */
async function reactivateChatRoom(roomId) {
    try {

        // 현재 채팅방의 상대방 찾기
        const currentRoom = chatRooms[roomId];
        if (!currentRoom || !currentRoom.participants) {
            Toast.error('채팅방 정보를 찾을 수 없습니다.');
            return;
        }

        const participantIds = Object.keys(currentRoom.participants);
        const otherParticipantId = participantIds.find(id => id != currentUserId);

        if (!otherParticipantId) {
            Toast.error('채팅방 정보를 찾을 수 없습니다.');
            return;
        }

        // 상대방의 userRooms에서 방 제거 (다시 활성화)
        const otherUserRoomRef = database.ref(`userRooms/${otherParticipantId}/${roomId}`);
        await otherUserRoomRef.set({
            joinedAt: firebase.database.ServerValue.TIMESTAMP,
            lastRead: firebase.database.ServerValue.TIMESTAMP,
            lastMessage: '',
            lastMessageTime: firebase.database.ServerValue.TIMESTAMP,
            partnerId: currentUserId,
            partnerName: currentUser.nickname || '사용자'
        });

        // 채팅방의 참여자 상태를 활성으로 업데이트
        const participantsRef = database.ref(`chatRooms/${roomId}/participants`);
        await Promise.all([
            participantsRef.child(currentUserId).update({
                status: 'active',
                rejoinedAt: firebase.database.ServerValue.TIMESTAMP
            }),
            participantsRef.child(otherParticipantId).update({
                status: 'active',
                rejoinedAt: firebase.database.ServerValue.TIMESTAMP
            })
        ]);


        // 모달 닫기
        closeInactiveRoomModal();

        // 채팅방 열기
        openChatRoom(roomId);

        Toast.success('대화가 다시 활성화되었습니다!');

    } catch (error) {
        Toast.error('대화 활성화에 실패했습니다.');
    }
}

/**
 * 채팅방 나가기
 */
async function leaveChatRoom() {
    const menu = document.querySelector('.chat-options-menu');
    if (menu) menu.remove();

    if (!activeRoomId) return;

    if (await Modal.confirm('채팅방을 나가시겠습니까? 메시지를 보내면 대화가 다시 활성화됩니다.')) {
        try {

            // 현재 채팅방의 상대방 찾기
            const currentRoom = chatRooms[activeRoomId];
            if (!currentRoom || !currentRoom.participants) {
                Toast.error('채팅방 정보를 찾을 수 없습니다.');
                return;
            }

            const participantIds = Object.keys(currentRoom.participants);
            const otherParticipantId = participantIds.find(id => id != currentUserId);

            if (!otherParticipantId) {
                Toast.error('채팅방 정보를 찾을 수 없습니다.');
                return;
            }


            // 양쪽 모두의 userRooms에서 방 제거 및 참여자 상태 업데이트
            const userRoomRef = database.ref(`userRooms/${currentUserId}/${activeRoomId}`);
            const otherUserRoomRef = database.ref(`userRooms/${otherParticipantId}/${activeRoomId}`);
            const currentParticipantRef = database.ref(`chatRooms/${activeRoomId}/participants/${currentUserId}`);
            const otherParticipantRef = database.ref(`chatRooms/${activeRoomId}/participants/${otherParticipantId}`);

            Promise.all([
                userRoomRef.remove(), // 현재 사용자의 채팅방 목록에서 제거
                otherUserRoomRef.remove(), // 상대방의 채팅방 목록에서도 제거
                currentParticipantRef.update({
                    status: 'inactive',
                    leftAt: firebase.database.ServerValue.TIMESTAMP,
                    // 기존 정보는 보존 (joinedAt, role 등)
                }),
                otherParticipantRef.update({
                    status: 'inactive',
                    leftAt: firebase.database.ServerValue.TIMESTAMP,
                    // 기존 정보는 보존 (joinedAt, role 등)
                })
            ])
            .then(() => {
                
                // 채팅방 데이터 제거
                delete chatRooms[activeRoomId];
                
                // 사이드바에서 채팅방 제거 (특수문자 안전 처리)
                let roomItem = null;
                const allRoomItems = document.querySelectorAll('.chat-room-item');
                for (const item of allRoomItems) {
                    if (item.getAttribute('data-room-id') === activeRoomId) {
                        roomItem = item;
                        break;
                    }
                }
                if (roomItem) {
                    roomItem.remove();
                }
                
                // UI 초기화
                closeChatRoom();
                
                // 채팅방 목록이 비어있다면 안내 메시지 표시
                const roomsList = document.getElementById('chatRoomsList');
                const remainingRooms = roomsList.querySelectorAll('.chat-room-item');
                if (remainingRooms.length === 0) {
                    roomsList.innerHTML = `
                        <div style="text-align: center; padding: 40px 20px; color: #718096;">
                            <i class="fas fa-comments" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
                            <p style="margin: 0; font-size: 0.9rem;">아직 채팅방이 없습니다.<br>새 채팅을 시작해보세요!</p>
                        </div>
                    `;
                }
                
                Toast.success('채팅방을 나갔습니다.');
            })
            .catch((error) => {
                Toast.error('채팅방 나가기에 실패했습니다.');
            });
        } catch (error) {
            Toast.error('오류가 발생했습니다.');
        }
    }
}

/**
 * 채팅방별 읽지 않은 메시지 수 업데이트
 */
function updateRoomUnreadCount(roomId) {
    if (!roomId) return;
    
    // 사용자의 해당 채팅방 읽음 정보 가져오기
    const userRoomRef = database.ref(`userRooms/${currentUserId}/${roomId}/lastRead`);
    userRoomRef.once('value', (lastReadSnapshot) => {
        const lastRead = lastReadSnapshot.val() || 0;
        
        // 최근 100개 메시지만 확인 (성능 최적화)
        const messagesRef = database.ref(`messages/${roomId}`);
        messagesRef.limitToLast(100).once('value', (messagesSnapshot) => {
            const messages = messagesSnapshot.val() || {};
            
            // 읽지 않은 메시지 중 상대방이 보낸 메시지만 카운트
            let unreadCount = 0;
            Object.values(messages).forEach(message => {
                if (message.senderId && message.senderId != currentUserId && message.timestamp > lastRead) {
                    unreadCount++;
                }
            });
            
            // 배지 업데이트
            updateRoomBadge(roomId, unreadCount);
        });
    });
}

/**
 * 채팅방 배지 업데이트
 */
function updateRoomBadge(roomId, unreadCount) {
    const roomMeta = document.getElementById(`roomMeta-${roomId}`);
    if (!roomMeta) return;
    
    // 기존 배지 찾기
    let badge = roomMeta.querySelector('.unread-badge');
    
    if (unreadCount > 0) {
        // 배지가 없으면 생성
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'unread-badge';
            badge.style.cssText = `
                background: #e53e3e;
                color: white;
                border-radius: 10px;
                padding: 2px 6px;
                font-size: 0.7rem;
                font-weight: 600;
                min-width: 18px;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            roomMeta.appendChild(badge);
        }
        badge.textContent = unreadCount;
    } else {
        // 배지가 있으면 제거
        if (badge) {
            badge.remove();
        }
    }
}

/**
 * 사용자 정보 로드
 */
async function loadUserInfo(userId) {
    if (!userId || users[userId]) {
        return Promise.resolve();
    }
    
    try {
        const response = await chatFetch(`/api/users/${userId}/profile-image`);
        
        if (!response.ok) {
            const errorText = await response.text();
            Toast.error('사용자 정보를 불러올 수 없습니다.');
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const response_data = await response.json();
        
        // ResponseHelper의 구조에 맞게 data 필드에서 실제 데이터 추출
        const data = response_data.data || response_data;
        
        if (data.user_id) {
            const imageData = data.data || data;

            // 탈퇴한 회원 처리
            const isDeleted = imageData.is_deleted || imageData.status === 'deleted';

            users[userId] = {
                id: imageData.user_id,
                nickname: imageData.nickname || '사용자',
                profile_image: isDeleted ? null : imageData.original_image,
                is_deleted: isDeleted,
                status: imageData.status
            };

        } else {

            // 폴백: 기본 사용자 정보 생성하여 무한 루프 방지
            users[userId] = {
                id: userId,
                nickname: '사용자',
                profile_image: null,
                is_deleted: false
            };
        }
        
        return Promise.resolve();
    } catch (error) {
        Toast.warning('사용자 정보를 불러올 수 없습니다.');
        return Promise.reject(error);
    }
}


/**
 * URL 해시 처리 (알림에서 접근 시)
 */
function handleUrlHash() {
    const hash = window.location.hash;
    
    if (hash && hash.startsWith('#room-')) {
        const roomId = hash.substring(6); // #room- 제거
        
        // 채팅방 목록이 로드될 때까지 대기 후 해당 방 열기
        let tryCount = 0;
        const maxTries = 10; // 최대 10번 시도 (10초)
        const tryOpenRoom = () => {
            tryCount++;

            if (chatRooms[roomId]) {
                openChatRoom(roomId);
                // 해시 제거
                history.replaceState(null, null, '/chat');
            } else if (tryCount < maxTries) {
                // 1초 후 재시도 (최대 10번)
                setTimeout(tryOpenRoom, 1000);
            } else {
                Toast.error('채팅방을 찾을 수 없습니다. 채팅방 목록을 확인해주세요.');
                history.replaceState(null, null, '/chat');
            }
        };

        setTimeout(tryOpenRoom, 2000); // 2초 후 첫 시도
    } else if (hash && hash.startsWith('#user-')) {
        const userId = hash.substring(6); // #user- 제거
        
        // 사용자 정보 로드 후 채팅 시작
        const tryStartChatWithUser = () => {
            // 먼저 기존 채팅방이 있는지 확인
            const existingRoomId = findExistingPrivateRoom(userId);
            
            if (existingRoomId) {
                openChatRoom(existingRoomId);
                history.replaceState(null, null, '/chat');
            } else {
                // 사용자 정보를 가져와서 새 채팅방 생성
                loadUserInfo(userId).then(() => {
                    if (users[userId]) {
                        createPrivateChatRoom(users[userId]);
                        history.replaceState(null, null, '/chat');
                    } else {
                        // API를 통해 사용자 정보 직접 조회
                        chatFetch(`/api/users/${userId}/profile-image`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.user_id) {
                                    const imageData = data.data || data;
                                    const user = {
                                        id: imageData.user_id,
                                        nickname: imageData.nickname || '사용자',
                                        profile_image: imageData.original_image
                                    };
                                    users[userId] = user;
                                    createPrivateChatRoom(user);
                                    history.replaceState(null, null, '/chat');
                                } else {
                                    Toast.error('사용자 정보를 찾을 수 없습니다.');
                                    history.replaceState(null, null, '/chat');
                                }
                            })
                            .catch(error => {
                                Toast.error('사용자 정보를 불러오는 중 오류가 발생했습니다.');
                                history.replaceState(null, null, '/chat');
                            });
                    }
                }).catch(error => {
                    Toast.error('사용자 정보를 불러오는 중 오류가 발생했습니다.');
                    history.replaceState(null, null, '/chat');
                });
            }
        };
        
        // 채팅방 목록이 로드될 때까지 대기
        let waitCount = 0;
        const maxWaits = 20; // 최대 20번 대기 (10초)
        const waitForChatRoomsLoad = () => {
            waitCount++;

            // 채팅방 목록이 로드되었는지 확인 (빈 객체도 로드된 것으로 간주)
            if (typeof chatRooms === 'object') {
                tryStartChatWithUser();
            } else if (waitCount < maxWaits) {
                setTimeout(waitForChatRoomsLoad, 500);
            } else {
                Toast.error('채팅방 목록을 불러오는 중 시간이 초과되었습니다. 페이지를 새로고침해주세요.');
                history.replaceState(null, null, '/chat');
            }
        };

        setTimeout(waitForChatRoomsLoad, 1000); // 1초 후 시작
    }
}

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용

/**
 * 모바일 채팅방 사이드바 토글 함수
 */
function toggleMobileChatSidebar() {
    const sidebar = document.querySelector('.chat-sidebar');
    const overlay = document.getElementById('mobile-sidebar-overlay');

    if (!sidebar) {
        return;
    }

    // 사이드바가 현재 보이는 상태인지 확인
    const isVisible = sidebar.classList.contains('mobile-visible');

    if (isVisible) {
        // 사이드바 숨기기
        sidebar.classList.remove('mobile-visible');
        document.body.classList.remove('modal-open');

        // 오버레이 제거
        if (overlay) {
            overlay.remove();
        }
    } else {
        // 사이드바 표시
        sidebar.classList.add('mobile-visible');
        document.body.classList.add('modal-open');

        // 오버레이 생성 (백그라운드 클릭으로 닫기 위함)
        const newOverlay = document.createElement('div');
        newOverlay.id = 'mobile-sidebar-overlay';
        newOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 999;
            display: none;
        `;
        document.body.appendChild(newOverlay);

        // 사이드바 헤더에 닫기 버튼 추가
        const sidebarHeader = sidebar.querySelector('.sidebar-header');
        if (sidebarHeader && !sidebarHeader.querySelector('.mobile-close-btn')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'mobile-close-btn';
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.style.cssText = `
                position: absolute;
                top: 50%;
                right: 15px;
                transform: translateY(-50%);
                background: #e53e3e;
                color: white;
                border: none;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
            `;
            closeBtn.onclick = toggleMobileChatSidebar;
            sidebarHeader.appendChild(closeBtn);
        }

        // ESC 키로 닫기
        const handleEscKey = (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('mobile-visible')) {
                toggleMobileChatSidebar();
                document.removeEventListener('keydown', handleEscKey);
            }
        };
        document.addEventListener('keydown', handleEscKey);
    }

}

/**
 * 모바일 사이드바 외부 클릭 시 닫기
 */
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.chat-sidebar');
    const toggleBtn = document.querySelector('.mobile-chat-toggle');

    if (sidebar && sidebar.classList.contains('mobile-visible') &&
        !sidebar.contains(e.target) &&
        !toggleBtn.contains(e.target)) {
        toggleMobileChatSidebar();
    }
});

/**
 * 모바일에서 채팅방 목록으로 돌아가기
 */
function backToChatList() {

    // 모바일에서만 동작
    if (window.innerWidth <= 768) {
        const chatLayout = document.querySelector('.chat-layout');
        if (chatLayout) {
            chatLayout.classList.remove('chat-active');
        }

        // 활성 채팅방 해제
        activeRoomId = null;
        currentPartnerUserId = null;

        // 모든 채팅방 아이템에서 active 클래스 제거
        document.querySelectorAll('.chat-room-item').forEach(item => {
            item.classList.remove('active');
        });

    }
}

</script>