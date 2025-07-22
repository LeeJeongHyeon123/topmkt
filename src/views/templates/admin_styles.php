<?php
/**
 * 관리자 페이지 공통 스타일
 */
?>

<style>
/* ===== 관리자 페이지 공통 스타일 ===== */

/* 기본 설정 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body.admin-page {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: #1a202c;
}

/* 관리자 컨테이너 */
.admin-container {
    width: 1920px;
    min-width: 1920px;
    margin: 0 auto;
    display: flex;
    min-height: 100vh;
}

/* 사이드바 */
.admin-sidebar {
    width: 280px;
    background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    position: fixed;
    height: 100vh;
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-logo {
    color: white;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sidebar-subtitle {
    color: #a0aec0;
    font-size: 14px;
    font-weight: 500;
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-section {
    margin-bottom: 30px;
}

.nav-section-title {
    color: #718096;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 25px 12px;
}

.nav-item {
    display: block;
    color: #e2e8f0;
    text-decoration: none;
    padding: 12px 25px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    position: relative;
}

.nav-item:hover,
.nav-item.active {
    background: rgba(255, 255, 255, 0.1);
    border-left-color: #667eea;
    color: white;
    text-decoration: none;
}

.nav-item i {
    margin-right: 10px;
    width: 20px;
}

.nav-badge {
    background: #e53e3e;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: auto;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: bold;
    min-width: 16px;
    text-align: center;
}

/* 메인 콘텐츠 영역 */
.admin-main {
    margin-left: 280px;
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* 헤더 */
.main-header {
    background: white !important;
    padding: 20px 40px !important;
    border-bottom: 1px solid #e2e8f0;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    min-height: 100px !important;
    max-height: 100px !important;
    height: 100px !important;
    line-height: 1.4 !important;
}

/* 헤더 내부 요소들 */
.main-header * {
    line-height: 1.4 !important;
}

.main-header h1,
.main-header p {
    margin: 0 !important;
    padding: 0 !important;
}

.header-left h1 {
    font-size: 24px !important;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 4px 0 !important;
    line-height: 1.3 !important;
}

.header-left p {
    color: #718096;
    font-size: 14px !important;
    font-weight: 500;
    margin: 0 !important;
    line-height: 1.3 !important;
}


.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.admin-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 20px;
    background: #f7fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    height: 56px;
    min-width: 180px;
    box-sizing: border-box;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #1a202c;
    font-size: 14px;
}

.user-role {
    color: #718096;
    font-size: 12px;
}

/* 메인페이지 버튼 스타일 */
.main-site-btn {
    padding: 0 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 56px;
    min-width: 140px;
    white-space: nowrap;
    box-sizing: border-box;
}

.main-site-btn:hover {
    background: linear-gradient(135deg, #5a67d8, #6b46c1);
    text-decoration: none;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.logout-btn {
    padding: 0 20px;
    background: #e53e3e;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    height: 56px;
    min-width: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
    box-sizing: border-box;
}

.logout-btn:hover {
    background: #c53030;
    text-decoration: none;
    color: white;
    transform: translateY(-1px);
}

/* 페이지 콘텐츠 */
.page-content {
    flex: 1;
    background: #f7fafc;
    padding: 40px;
}
</style>