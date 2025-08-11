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

<style>
/* 채팅 페이지 전용 스타일 */
.chat-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    height: calc(100vh - 120px);
    background: #f8fafc;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    text-align: center;
    border-radius: 12px;
    flex-shrink: 0;
    margin-top: 60px;
    margin-bottom: 30px;
}

.chat-header h1 {
    font-size: 2rem;
    margin-bottom: 8px;
    font-weight: 700;
}

.chat-header p {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.chat-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 20px;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* 사이드바 (채팅방 목록) */
.chat-sidebar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
}

.new-chat-btn {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.new-chat-btn:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.chat-rooms-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.chat-room-item {
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 4px;
    position: relative;
    z-index: 10; /* 디버깅: 클릭 우선순위 높임 */
    pointer-events: auto; /* 디버깅: 클릭 가능 명시 */
    background: #fff; /* 디버깅: 배경색 명시 */
}

/* 채팅방 아이템 내부 요소들이 클릭을 차단하지 않도록 */
.chat-room-item * {
    pointer-events: none;
}

.chat-room-item {
    pointer-events: auto;
}

.chat-room-item:hover {
    background: #f7fafc;
}

.chat-room-item.active {
    background: #edf2f7;
    border-left: 4px solid #667eea;
}

.room-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.room-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.room-details {
    flex: 1;
    min-width: 0;
}

.room-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.room-last-message {
    font-size: 0.8rem;
    color: #718096;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.room-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}

.room-time {
    font-size: 0.7rem;
    color: #a0aec0;
}

.unread-badge {
    background: #e53e3e;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 0.7rem;
    font-weight: 600;
    min-width: 18px;
    text-align: center;
}

/* 메인 채팅 영역 */
.chat-main {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.chat-welcome {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    color: #718096;
}

.chat-welcome-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    color: #cbd5e0;
}

.chat-welcome h3 {
    font-size: 1.3rem;
    color: #4a5568;
    margin-bottom: 8px;
}

.chat-welcome p {
    font-size: 0.9rem;
    margin: 0;
}

/* 채팅 헤더 */
.chat-header-bar {
    padding: 15px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.chat-partner-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
}

.chat-partner-info {
    flex: 1;
}

.chat-partner-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 2px;
}


.chat-options {
    display: flex;
    gap: 8px;
}

.chat-option-btn {
    background: none;
    border: none;
    color: #718096;
    font-size: 1rem;
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.chat-option-btn:hover {
    background: #f7fafc;
    color: #4a5568;
}

/* 메시지 영역 */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8fafc;
    min-height: 200px; /* 최소 높이 보장 */
    border: 2px solid #e2e8f0; /* 디버깅용 테두리 */
}

.message-group {
    margin-bottom: 16px;
}

.message-item {
    display: flex;
    margin-bottom: 8px;
}

.message-item.own {
    justify-content: flex-end;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.7rem;
    flex-shrink: 0;
    margin-right: 8px;
}

.message-item.own .message-avatar {
    display: none;
}

.message-bubble {
    max-width: 70%;
    padding: 10px 14px;
    border-radius: 12px;
    position: relative;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
}

.message-item:not(.own) .message-bubble {
    background: white;
    color: #2d3748;
    border-bottom-left-radius: 4px;
}

.message-item.own .message-bubble {
    background: #667eea;
    color: white;
    border-bottom-right-radius: 4px;
}

.message-text {
    font-size: 0.9rem;
    line-height: 1.4;
    margin: 0;
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
}

.message-item:not(.own) .message-time {
    text-align: left;
}

/* 메시지 입력 영역 */
.chat-input-area {
    padding: 15px 20px;
    border-top: 1px solid #e2e8f0;
    background: white;
    flex-shrink: 0;
}

.chat-input-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.chat-input {
    flex: 1;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 10px 16px;
    font-size: 0.9rem;
    resize: none;
    min-height: 40px;
    max-height: 120px;
    outline: none;
    transition: border-color 0.2s ease;
}

.chat-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.chat-send-btn {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.chat-send-btn:hover:not(:disabled) {
    background: #5a67d8;
    transform: translateY(-1px);
}

.chat-send-btn:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
    transform: none;
}

/* 새 채팅 모달 */
.new-chat-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    backdrop-filter: blur(3px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #718096;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.modal-close:hover {
    background: #f7fafc;
}

.modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.search-section {
    margin-bottom: 20px;
}

.search-input-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.2s ease;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-btn {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
}

.search-btn:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.search-btn:active {
    transform: translateY(0);
}

.search-btn i {
    font-size: 0.9rem;
}

.users-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.user-item:hover {
    background: #f7fafc;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.user-status {
    font-size: 0.8rem;
    color: #718096;
}

/* 채팅 옵션 메뉴 */
.chat-options-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
    min-width: 150px;
    z-index: 1000;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
}

.chat-options-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.chat-options-menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 0.9rem;
    color: #374151;
}

.chat-options-menu-item:hover {
    background-color: #f9fafb;
}

.chat-options-menu-item.danger {
    color: #dc2626;
}

.chat-options-menu-item.danger:hover {
    background-color: #fef2f2;
}

.chat-options-menu-item i {
    width: 16px;
    color: currentColor;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .chat-container {
        padding: 10px;
        height: calc(100vh - 80px);
    }
    
    .chat-header {
        padding: 30px 20px;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    
    .chat-header h1 {
        font-size: 1.5rem;
    }
    
    .chat-layout {
        grid-template-columns: 1fr;
        flex: 1;
        min-height: 0;
    }
    
    .chat-sidebar {
        display: none;
    }
    
    .chat-sidebar.mobile-show {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 999;
        border-radius: 0;
    }
    
    .message-bubble {
        max-width: 85%;
    }
    
    .modal-content {
        width: 95%;
        margin: 20px;
    }
}

/* 로딩 상태 */
.loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    color: #718096;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #e2e8f0;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


.user-avatar, .room-avatar, .chat-partner-avatar, .message-avatar {
    position: relative;
}

/* 프로필 이미지 클릭 가능 스타일 */
.profile-image-clickable {
    cursor: pointer;
    transition: all 0.2s ease;
}

.profile-image-clickable:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* 기존 프로필 이미지 모달 CSS 제거됨 - profile-modal.css 통합 시스템 사용 */
</style>

<div class="chat-container">
    <!-- 채팅 헤더 -->
    <div class="chat-header">
        <h1>💬 실시간 채팅</h1>
        <p>다른 회원들과 실시간으로 소통하세요</p>
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

console.log('🔥 Firebase 채팅 초기화됨');
console.log('👤 현재 사용자:', currentUser);

/**
 * 전역 fetch 인터셉터 비활성화
 */
function disableFetchInterceptor() {
    // 원본 fetch가 이미 저장되어 있다면 복원
    if (window.originalFetch) {
        window.fetch = window.originalFetch;
        console.log('🔇 채팅 페이지: 전역 fetch 로딩 인터셉터 비활성화');
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
    console.log('🧹 채팅 리스너 정리 중...');
    
    // 채팅방 목록 리스너 제거
    if (window.chatRoomsListener) {
        const userRoomsRef = database.ref(`userRooms/${currentUserId}`);
        userRoomsRef.off('value', window.chatRoomsListener);
        window.chatRoomsListener = null;
        console.log('✅ 채팅방 목록 리스너 제거됨');
    }
    
    // 개별 채팅방 리스너 제거
    if (window.roomListeners) {
        Object.keys(window.roomListeners).forEach(roomId => {
            const roomRef = database.ref(`chatRooms/${roomId}`);
            roomRef.off('value', window.roomListeners[roomId]);
            console.log(`✅ 채팅방 ${roomId} 리스너 제거됨`);
        });
        window.roomListeners = {};
    }
    
    // 메시지 리스너 제거
    if (window.currentMessageListener && activeRoomId) {
        const messagesRef = database.ref(`messages/${activeRoomId}`);
        messagesRef.off('value', window.currentMessageListener);
        window.currentMessageListener = null;
        console.log('✅ 메시지 리스너 제거됨');
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
                console.log('🖼️ 채팅 페이지 프로필 이미지 클릭:', { userId, userName });
                window.profileModal.show(userId, userName, false);
            } else {
                console.warn('⚠️ 프로필 이미지 클릭 실패:', { userId, userName, profileModal: typeof window.profileModal });
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
            console.log(`🎯 채팅방 영역 클릭 감지: ${roomId}`, {
                target: target,
                roomItem: roomItem,
                targetTag: target.tagName,
                targetClass: target.className,
                targetId: target.id
            });
        } else if (target.closest('#chatRoomsList')) {
            console.log(`🎯 채팅방 목록 영역 클릭 (채팅방 아이템 아님):`, {
                target: target,
                targetTag: target.tagName,
                targetClass: target.className,
                targetId: target.id
            });
        }
    }, true); // capture 단계에서 캐치
    
    // URL 해시로 특정 채팅방 열기 처리
    handleUrlHash();
});

/**
 * 채팅 초기화
 */
function initializeChat() {
    console.log('📱 채팅 초기화 시작');
    
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
    console.log('📂 채팅방 목록 로드 중...');
    
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
            console.log('📂 채팅방 목록 변경 없음 - 업데이트 건너뜀');
            return;
        }
        
        console.log('📂 채팅방 목록 업데이트됨:', currentRoomIds);
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
    console.log(`🔄 채팅방 정보 로드 시작: ${roomId}`);
    
    // 이미 로드된 채팅방이면 렌더링만 수행
    if (chatRooms[roomId]) {
        console.log(`📂 채팅방 ${roomId} 캐시에서 로드됨`);
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
        console.log(`📊 채팅방 ${roomId} Firebase 데이터:`, roomData);
        
        if (!roomData) {
            console.error(`❌ 채팅방 ${roomId}의 데이터가 Firebase에 존재하지 않음`);
            return;
        }
        
        chatRooms[roomId] = roomData;
        console.log(`✅ 채팅방 ${roomId} 로컬 캐시에 저장됨`);
        console.log(`📋 현재 전체 chatRooms:`, Object.keys(chatRooms));
        
        renderChatRoomItem(roomId, roomData);
    };
    
    roomRef.on('value', window.roomListeners[roomId], function(error) {
        console.error(`❌ 채팅방 ${roomId} 로드 실패:`, error);
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
            console.log(`🖱️ 채팅방 클릭됨: ${roomId}`);
            console.log('🖱️ 클릭된 요소:', e.target);
            console.log('🖱️ 현재 요소:', e.currentTarget);
            console.log('🖱️ 요소 클래스:', e.currentTarget.className);
            console.log('🖱️ data-room-id:', e.currentTarget.getAttribute('data-room-id'));
            
            // roomId 특수문자 검사
            if (roomId.includes('-') || roomId.includes('_')) {
                console.log(`⚠️ 특수문자 포함 roomId 감지: ${roomId}`);
            }
            
            // 채팅방 데이터 존재 여부 확인
            if (chatRooms[roomId]) {
                console.log(`✅ 채팅방 데이터 존재: ${roomId}`, chatRooms[roomId]);
            } else {
                console.log(`❌ 채팅방 데이터 없음: ${roomId}`);
                console.log('📋 현재 chatRooms:', Object.keys(chatRooms));
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
        
        roomsListContainer.appendChild(roomItem);
        console.log(`✅ 채팅방 아이템 생성 및 클릭 이벤트 등록: ${roomId}`);
        
        // 디버깅: 요소 클릭 가능 여부 확인
        console.log(`🔍 채팅방 ${roomId} 요소 상태:`, {
            display: roomItem.style.display,
            visibility: roomItem.style.visibility,
            pointerEvents: window.getComputedStyle(roomItem).pointerEvents,
            zIndex: window.getComputedStyle(roomItem).zIndex,
            position: window.getComputedStyle(roomItem).position
        });
        
        // 추가 디버깅: 겹치는 요소 확인
        setTimeout(() => {
            const rect = roomItem.getBoundingClientRect();
            const centerX = rect.x + rect.width / 2;
            const centerY = rect.y + rect.height / 2;
            const topElement = document.elementFromPoint(centerX, centerY);
            
            console.log(`🎯 채팅방 ${roomId} 중앙 좌표 (${Math.round(centerX)}, ${Math.round(centerY)})에서 감지된 최상위 요소:`, {
                expected: roomItem,
                actual: topElement,
                isExpected: topElement === roomItem,
                topElementTag: topElement?.tagName,
                topElementClass: topElement?.className,
                topElementId: topElement?.id
            });
            
            if (topElement !== roomItem) {
                console.warn(`⚠️ 채팅방 ${roomId}이 다른 요소에 가려져 있습니다!`, topElement);
                
                // 가리는 요소의 z-index를 낮춰보기
                if (topElement && topElement.style) {
                    const currentZIndex = window.getComputedStyle(topElement).zIndex;
                    console.log(`🔧 가리는 요소의 z-index: ${currentZIndex}`);
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
            // 사용자 정보가 이미 있으면 사용
            if (users[otherUserId]) {
                roomName = users[otherUserId].nickname || '사용자';
                avatarText = roomName.substring(0, 1).toUpperCase();
                partnerImage = users[otherUserId].profile_image || users[otherUserId].profile_image_thumb;
            } else {
                // 사용자 정보가 없으면 비동기로 가져오기 (재귀 호출 방지)
                if (!roomItem.dataset.loadingUser) {
                    roomItem.dataset.loadingUser = 'true';
                    loadUserInfo(otherUserId).then(() => {
                        // 로딩 플래그 제거
                        delete roomItem.dataset.loadingUser;
                        // 사용자 정보 로드 완료 후 채팅방 아이템 다시 렌더링
                        renderChatRoomItem(roomId, roomData);
                    }).catch(() => {
                        delete roomItem.dataset.loadingUser;
                    });
                }
            }
        }
    }
    
    roomItem.innerHTML = `
        <div class="room-info">
            <div class="room-avatar">
                ${partnerImage ? `
                    <img src="${partnerImage}" 
                         alt="${roomName}" 
                         style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                         class="profile-image-clickable"
                         data-user-id="${Object.keys(roomData.participants || {}).find(id => id != currentUserId) || ''}"
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
                <div class="room-name">${roomName}</div>
                <div class="room-last-message" id="lastMessage-${roomId}">
                    ${roomData.lastMessage || '메시지가 없습니다'}
                </div>
            </div>
            <div class="room-meta" id="roomMeta-${roomId}">
                <div class="room-time" id="lastTime-${roomId}">
                    ${roomData.lastMessageTime ? formatTime(roomData.lastMessageTime) : ''}
                </div>
            </div>
        </div>
    `;
    
    // 마지막 메시지 업데이트
    updateLastMessage(roomId);
    
    // 읽지 않은 메시지 수 업데이트
    updateRoomUnreadCount(roomId);
}

/**
 * 채팅방 열기
 */
function openChatRoom(roomId) {
    console.log(`💬 채팅방 열기: ${roomId}`);
    console.log(`📊 현재 chatRooms 데이터:`, chatRooms);
    console.log(`🔍 요청된 채팅방 ID: ${roomId}`);
    
    activeRoomId = roomId;
    const roomData = chatRooms[roomId];
    
    console.log(`📂 해당 채팅방 데이터:`, roomData);
    
    if (!roomData) {
        console.error('❌ 채팅방 데이터를 찾을 수 없습니다:', roomId);
        console.error('📋 사용 가능한 채팅방 목록:', Object.keys(chatRooms));
        
        // Firebase에서 직접 데이터 가져오기 시도
        console.log('🔄 Firebase에서 직접 채팅방 데이터 로드 시도...');
        const roomRef = database.ref(`chatRooms/${roomId}`);
        roomRef.once('value', function(snapshot) {
            const firebaseRoomData = snapshot.val();
            console.log('🔥 Firebase에서 가져온 데이터:', firebaseRoomData);
            
            if (firebaseRoomData) {
                console.log('✅ Firebase에 데이터가 존재함. 로컬 캐시 업데이트 중...');
                chatRooms[roomId] = firebaseRoomData;
                openChatRoom(roomId); // 재귀 호출로 다시 시도
            } else {
                console.error('❌ Firebase에도 채팅방이 존재하지 않음');
                alert('채팅방을 찾을 수 없습니다.');
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
    console.log('🖥️ UI 요소 상태 변경 중...');
    
    const chatWelcome = document.getElementById('chatWelcome');
    const activeChatArea = document.getElementById('activeChatArea');
    
    console.log('📋 chatWelcome 요소:', chatWelcome);
    console.log('📋 activeChatArea 요소:', activeChatArea);
    
    if (chatWelcome) {
        chatWelcome.style.display = 'none';
        console.log('✅ chatWelcome 숨김 처리됨');
    } else {
        console.error('❌ chatWelcome 요소를 찾을 수 없음');
    }
    
    if (activeChatArea) {
        activeChatArea.style.display = 'flex';
        activeChatArea.style.flexDirection = 'column';
        activeChatArea.style.height = '100%';
        console.log('✅ activeChatArea 표시 처리됨');
    } else {
        console.error('❌ activeChatArea 요소를 찾을 수 없음');
    }
    
    // 채팅 상대 정보 설정
    updateChatHeader(roomData);
    
    // 메시지 로드
    loadMessages(roomId);
    
    // 읽음 상태 업데이트
    markRoomAsRead(roomId);
}

/**
 * 채팅 헤더 업데이트
 */
function updateChatHeader(roomData) {
    console.log('🎨 채팅 헤더 업데이트:', roomData);
    
    let partnerName = roomData.name || '채팅방';
    let partnerImage = null;
    
    // 1:1 채팅인 경우 상대방 정보로 설정
    if (roomData.type === 'private' && roomData.participants) {
        const otherUserId = Object.keys(roomData.participants).find(id => id != currentUserId);
        console.log(`👤 상대방 사용자 ID: ${otherUserId}`);
        console.log(`👤 현재 사용자 ID: ${currentUserId}`);
        console.log(`📋 현재 users 객체:`, users);
        
        if (otherUserId) {
            currentPartnerUserId = otherUserId; // 현재 상대방 ID 저장
            
            // 사용자 정보가 없으면 비동기로 가져오기
            if (!users[otherUserId]) {
                console.log(`🔄 사용자 ${otherUserId} 정보 로드 필요`);
                loadUserInfo(otherUserId).then(() => {
                    console.log(`✅ 사용자 ${otherUserId} 정보 로드 완료:`, users[otherUserId]);
                    // 무한 루프 방지: 사용자 정보가 실제로 로드된 경우에만 재호출
                    if (users[otherUserId]) {
                        updateChatHeader(roomData);
                    } else {
                        console.warn(`⚠️ 사용자 ${otherUserId} 정보 로드 실패 - 기본값 사용`);
                        // 기본값으로 폴백
                        users[otherUserId] = {
                            id: otherUserId,
                            nickname: '사용자',
                            profile_image: null
                        };
                        updateChatHeader(roomData);
                    }
                }).catch(error => {
                    console.error(`❌ 사용자 ${otherUserId} 정보 로드 오류:`, error);
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
                console.log(`👤 사용자 ${otherUserId} 정보 사용:`, users[otherUserId]);
                partnerName = users[otherUserId].nickname || '사용자';
                partnerImage = users[otherUserId].profile_image || users[otherUserId].profile_image_thumb;
                console.log(`👤 설정된 상대방 이름: ${partnerName}`);
                console.log(`🖼️ 설정된 상대방 이미지: ${partnerImage || '없음'}`);
            }
        }
        
        // 프로필 방문 버튼 표시
        document.getElementById('visitProfileBtn').style.display = 'block';
    } else {
        currentPartnerUserId = null;
        // 프로필 방문 버튼 숨김
        document.getElementById('visitProfileBtn').style.display = 'none';
    }
    
    document.getElementById('chatPartnerName').textContent = partnerName;
    
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
    console.log(`📨 메시지 로드 시작: ${roomId}`);
    
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) {
        console.error('❌ 메시지 컨테이너를 찾을 수 없음');
        return;
    }
    
    messagesContainer.innerHTML = '<div class="loading"><div class="loading-spinner"></div>메시지를 불러오는 중...</div>';
    
    // 실용적인 메시지 로딩 수 (성능과 사용성의 균형)
    const messagesRef = database.ref(`messages/${roomId}`).limitToLast(50);
    
    console.log(`🔗 Firebase 리스너 설정: messages/${roomId}`);
    
    // 기존 메시지 리스너 제거
    if (window.currentMessageListener) {
        const oldRef = database.ref(`messages/${activeRoomId || roomId}`);
        oldRef.off('value', window.currentMessageListener);
        window.currentMessageListener = null;
    }
    
    // 새로운 리스너 생성 및 저장
    window.currentMessageListener = function(snapshot) {
        console.log(`📥 메시지 데이터 수신:`, snapshot.val());
        const messages = snapshot.val() || {};
        console.log(`📊 메시지 개수: ${Object.keys(messages).length}`);
        
        renderMessages(messages);
        scrollToBottom();
    };
    
    messagesRef.on('value', window.currentMessageListener, function(error) {
        console.error('❌ 메시지 로드 실패:', error);
        messagesContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">메시지를 불러올 수 없습니다.</div>';
    });
}

/**
 * 메시지 렌더링
 */
function renderMessages(messages) {
    console.log('🎨 메시지 렌더링 시작:', messages);
    
    const messagesContainer = document.getElementById('chatMessages');
    console.log('📋 메시지 컨테이너:', messagesContainer);
    
    if (!messagesContainer) {
        console.error('❌ 메시지 컨테이너를 찾을 수 없음');
        return;
    }
    
    messagesContainer.innerHTML = '';
    
    const messageCount = Object.keys(messages).length;
    console.log(`📊 렌더링할 메시지 개수: ${messageCount}`);
    
    if (messageCount === 0) {
        console.log('💬 빈 채팅방 - 안내 메시지 표시');
        
        const emptyMessage = `
            <div style="text-align: center; padding: 40px 20px; color: #718096;">
                <i class="fas fa-comment-dots" style="font-size: 2rem; margin-bottom: 12px; opacity: 0.5;"></i>
                <p style="margin: 0; font-size: 0.9rem;">첫 메시지를 보내보세요!</p>
            </div>
        `;
        
        messagesContainer.innerHTML = emptyMessage;
        console.log('✅ 빈 채팅방 안내 메시지 설정 완료');
        console.log('📝 설정된 HTML:', emptyMessage);
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
        alert('채팅방을 선택해주세요.');
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
            // 채팅방의 마지막 메시지 업데이트
            updateRoomLastMessage(activeRoomId, text);
            
            // 입력창 초기화
            input.value = '';
            input.style.height = 'auto';
            sendBtn.disabled = false;
            input.focus();
            
            console.log('✅ 메시지 전송 완료');
        })
        .catch((error) => {
            console.error('❌ 메시지 전송 실패:', error);
            alert('메시지 전송에 실패했습니다.');
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
        alert('정확한 닉네임을 2글자 이상 입력해주세요.');
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
            if (data.success) {
                renderUsersList(data.data);
            } else {
                usersList.innerHTML = `<div style="text-align: center; padding: 20px; color: #e53e3e;">${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('사용자 검색 오류:', error);
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
    console.log('💬 채팅 시작:', user);
    
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
 * 기존 1:1 채팅방 찾기
 */
function findExistingPrivateRoom(userId) {
    for (const roomId in chatRooms) {
        const room = chatRooms[roomId];
        if (room.type === 'private' && room.participants) {
            const participantIds = Object.keys(room.participants);
            if (participantIds.length === 2 && 
                participantIds.includes(currentUserId.toString()) && 
                participantIds.includes(userId.toString())) {
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
                role: 'member'
            },
            [user.id]: {
                joinedAt: firebase.database.ServerValue.TIMESTAMP,
                role: 'member'
            }
        },
        lastMessage: '',
        lastMessageTime: firebase.database.ServerValue.TIMESTAMP
    };
    
    // Firebase에 채팅방 생성
    const roomsRef = database.ref('chatRooms');
    const newRoomRef = roomsRef.push();
    const roomId = newRoomRef.key;
    
    newRoomRef.set(roomData)
        .then(() => {
            // 사용자별 채팅방 목록에 추가
            const userRoomsRef = database.ref('userRooms');
            return Promise.all([
                userRoomsRef.child(`${currentUserId}/${roomId}`).set({
                    joinedAt: firebase.database.ServerValue.TIMESTAMP,
                    lastRead: firebase.database.ServerValue.TIMESTAMP
                }),
                userRoomsRef.child(`${user.id}/${roomId}`).set({
                    joinedAt: firebase.database.ServerValue.TIMESTAMP,
                    lastRead: firebase.database.ServerValue.TIMESTAMP
                })
            ]);
        })
        .then(() => {
            console.log('✅ 채팅방 생성 완료:', roomId);
            
            // 생성된 채팅방 열기
            setTimeout(() => {
                openChatRoom(roomId);
                closeNewChatModal();
            }, 500);
        })
        .catch((error) => {
            console.error('❌ 채팅방 생성 실패:', error);
            alert('채팅방 생성에 실패했습니다.');
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
 * 시간 포맷팅 (메시지 시간용)
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
        // 오늘이면 시간만 표시
        return date.toLocaleTimeString('ko-KR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    } else {
        // 오늘이 아니면 년월일 시분초 요일 모두 표시
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        
        // 요일 구하기
        const weekdays = ['일', '월', '화', '수', '목', '금', '토'];
        const weekday = weekdays[date.getDay()];
        
        return `${year}.${month}.${day}(${weekday}) ${hours}:${minutes}:${seconds}`;
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
 * 디바운스 함수
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

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
        alert('프로필 정보를 찾을 수 없습니다.');
        return;
    }
    
    // 사용자 정보가 있으면 닉네임으로 프로필 페이지 이동
    if (users[currentPartnerUserId] && users[currentPartnerUserId].nickname) {
        const profileUrl = `/profile/${encodeURIComponent(users[currentPartnerUserId].nickname)}`;
        window.open(profileUrl, '_blank');
    } else {
        // 사용자 정보가 없으면 로드 후 이동
        loadUserInfo(currentPartnerUserId).then(() => {
            if (users[currentPartnerUserId] && users[currentPartnerUserId].nickname) {
                const profileUrl = `/profile/${encodeURIComponent(users[currentPartnerUserId].nickname)}`;
                window.open(profileUrl, '_blank');
            } else {
                alert('프로필 페이지를 찾을 수 없습니다.');
            }
        }).catch(() => {
            alert('프로필 정보를 불러오는 중 오류가 발생했습니다.');
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
 * 채팅방 나가기
 */
function leaveChatRoom() {
    const menu = document.querySelector('.chat-options-menu');
    if (menu) menu.remove();
    
    if (!activeRoomId) return;
    
    if (confirm('채팅방을 나가시겠습니까? 나간 후에는 다시 초대받아야 참여할 수 있습니다.')) {
        try {
            console.log(`🚪 채팅방 나가기 시작: 사용자 ${currentUserId}, 채팅방 ${activeRoomId}`);
            
            // Firebase에서 사용자 참여 정보 제거
            const userRoomRef = database.ref(`userRooms/${currentUserId}/${activeRoomId}`);
            const participantRef = database.ref(`chatRooms/${activeRoomId}/participants/${currentUserId}`);
            
            Promise.all([
                userRoomRef.remove(),
                participantRef.remove()
            ])
            .then(() => {
                console.log('✅ 채팅방을 나갔습니다.');
                
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
                    console.log('✅ 사이드바에서 채팅방 아이템 제거됨');
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
                
                alert('채팅방을 나갔습니다.');
            })
            .catch((error) => {
                console.error('❌ 채팅방 나가기 실패:', error);
                alert('채팅방 나가기에 실패했습니다.');
            });
        } catch (error) {
            console.error('❌ 채팅방 나가기 오류:', error);
            alert('오류가 발생했습니다.');
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
        console.log(`🌐 API 호출 시작: /api/users/${userId}/profile-image`);
        const response = await chatFetch(`/api/users/${userId}/profile-image`);
        console.log(`📊 API 응답 상태: ${response.status}`);
        
        if (!response.ok) {
            console.error(`❌ API 응답 오류: ${response.status} ${response.statusText}`);
            const errorText = await response.text();
            console.error(`❌ 오류 내용: ${errorText}`);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const response_data = await response.json();
        console.log(`📋 API 응답 데이터:`, response_data);
        
        // ResponseHelper의 구조에 맞게 data 필드에서 실제 데이터 추출
        const data = response_data.data || response_data;
        
        if (data.user_id) {
            const imageData = data.data || data;
            users[userId] = {
                id: imageData.user_id,
                nickname: imageData.nickname,
                profile_image: imageData.original_image
            };
            
            console.log('✅ 사용자 정보 로드됨:', users[userId]);
        } else {
            console.warn(`⚠️ API 응답에 user_id가 없음:`, data);
            console.warn(`🔍 전체 응답 구조:`, response_data);
            
            // 폴백: 기본 사용자 정보 생성하여 무한 루프 방지
            users[userId] = {
                id: userId,
                nickname: '사용자',
                profile_image: null
            };
            console.warn(`🔄 기본 사용자 정보로 폴백 처리됨:`, users[userId]);
        }
        
        return Promise.resolve();
    } catch (error) {
        console.error('❌ 사용자 정보 로드 실패:', error);
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
        console.log(`🔗 URL 해시로 채팅방 열기 요청: ${roomId}`);
        
        // 채팅방 목록이 로드될 때까지 대기 후 해당 방 열기
        const tryOpenRoom = () => {
            if (chatRooms[roomId]) {
                openChatRoom(roomId);
                // 해시 제거
                history.replaceState(null, null, '/chat');
            } else {
                // 1초 후 재시도 (최대 10초)
                setTimeout(tryOpenRoom, 1000);
            }
        };
        
        setTimeout(tryOpenRoom, 2000); // 2초 후 첫 시도
    } else if (hash && hash.startsWith('#user-')) {
        const userId = hash.substring(6); // #user- 제거
        console.log(`🔗 URL 해시로 사용자와 채팅 시작 요청: ${userId}`);
        
        // 사용자 정보 로드 후 채팅 시작
        const tryStartChatWithUser = () => {
            // 먼저 기존 채팅방이 있는지 확인
            const existingRoomId = findExistingPrivateRoom(userId);
            
            if (existingRoomId) {
                console.log(`🔗 기존 채팅방 발견: ${existingRoomId}`);
                openChatRoom(existingRoomId);
                history.replaceState(null, null, '/chat');
            } else {
                // 사용자 정보를 가져와서 새 채팅방 생성
                console.log(`🔗 사용자 정보 조회 중: ${userId}`);
                loadUserInfo(userId).then(() => {
                    if (users[userId]) {
                        console.log(`🔗 사용자 정보 로드 완료, 채팅방 생성 중:`, users[userId]);
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
                                    console.log(`🔗 API로 사용자 정보 로드 완료, 채팅방 생성 중:`, user);
                                    createPrivateChatRoom(user);
                                    history.replaceState(null, null, '/chat');
                                } else {
                                    console.error('🔗 사용자 정보를 찾을 수 없습니다.');
                                    alert('사용자 정보를 찾을 수 없습니다.');
                                    history.replaceState(null, null, '/chat');
                                }
                            })
                            .catch(error => {
                                console.error('🔗 사용자 정보 조회 실패:', error);
                                alert('사용자 정보를 불러오는 중 오류가 발생했습니다.');
                                history.replaceState(null, null, '/chat');
                            });
                    }
                }).catch(error => {
                    console.error('🔗 사용자 정보 로드 실패:', error);
                    alert('사용자 정보를 불러오는 중 오류가 발생했습니다.');
                    history.replaceState(null, null, '/chat');
                });
            }
        };
        
        // 채팅방 목록이 로드될 때까지 대기
        const waitForChatRoomsLoad = () => {
            // 채팅방 목록이 로드되었는지 확인 (빈 객체도 로드된 것으로 간주)
            if (typeof chatRooms === 'object') {
                tryStartChatWithUser();
            } else {
                setTimeout(waitForChatRoomsLoad, 500);
            }
        };
        
        setTimeout(waitForChatRoomsLoad, 1000); // 1초 후 시작
    }
}

// 기존 프로필 이미지 모달 JavaScript 함수들 제거됨 - profile-modal.js 통합 시스템 사용
</script>