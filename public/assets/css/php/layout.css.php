<?php
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "/* 탑마케팅 레이아웃 컴포넌트 */

";
echo "/* 레이아웃 */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* 개선된 헤더 스타일 */
.main-header {
    background: linear-gradient(to right, #1E3A8A, #3949ab);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.main-header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
}

.header-left {
    display: flex;
    align-items: center;
}

.logo {
    margin: 0;
    padding: 0;
}

.logo a {
    display: flex;
    align-items: center;
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    transition: all 0.3s ease;
}

.logo a:hover {
    text-decoration: none;
    transform: scale(1.05);
}

.logo img {
    height: 36px;
    margin-right: 10px;
}

.main-nav {
    margin-left: 30px;
}

.main-nav ul {
    display: flex;
    gap: 5px;
}

.main-nav li {
    position: relative;
}

.main-nav a,
.main-nav button {
    display: block;
    padding: 10px 15px;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    font-size: 16px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.main-nav a:hover,
.main-nav button:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff;
    text-decoration: none;
}

.main-nav a.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff;
}

/* 특별 메뉴 스타일링 (신청 관리 등) */
.main-nav .special-menu {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important;
    color: #fff !important;
    font-weight: 600;
    box-shadow: 0 2px 20px rgba(67, 233, 123, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.main-nav .special-menu::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.main-nav .special-menu:hover::before {
    left: 100%;
}

/* 모바일에서 특별 메뉴 스타일 */
@media (max-width: 768px) {
    .main-nav .special-menu {
        padding: 8px 12px;
        font-size: 14px;
        margin: 2px;
    }
}
";
?>
