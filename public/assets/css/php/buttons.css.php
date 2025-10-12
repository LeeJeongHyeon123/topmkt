<?php
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "/* 탑마케팅 버튼 컴포넌트 */

";
echo "/* 버튼 스타일 */
.btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    text-align: center;
    border: none;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #1e88e5;
    color: #fff;
}

.btn-primary:hover {
    background-color: #1976d2;
    text-decoration: none;
}

.btn-secondary {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background-color: #e0e0e0;
    text-decoration: none;
}

.btn-danger {
    background-color: #e53935;
    color: #fff;
}

.btn-danger:hover {
    background-color: #d32f2f;
    text-decoration: none;
}

.btn-small {
    padding: 5px 10px;
    font-size: 12px;
}

.btn-large {
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 500;
}

/* 통합 버튼 시스템 (v3.38.0) */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    min-height: 44px;
    min-width: 44px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

/* 기본 버튼 스타일 */
.btn {
    background: #667eea;
    color: white;
    border: none;
}

.btn:hover {
    background: #5a67d8;
}

/* 기본 버튼 */
.btn {
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    text-decoration: none;
}

/* 버튼 크기 */
.btn-small {
    padding: 8px 16px;
    font-size: 13px;
    min-height: 36px;
}

.btn-large {
    padding: 16px 32px;
    font-size: 16px;
    min-height: 52px;
}

/* 버튼 색상 */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
    border: 1px solid #cbd5e0;
}

.btn-secondary:hover {
    background: #cbd5e0;
    border-color: #a0aec0;
    transform: translateY(-1px);
}

.btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 101, 101, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(237, 137, 54, 0.4);
}

.btn-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.btn-info:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2b6cb0 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(66, 153, 225, 0.4);
}

/* 아웃라인 버튼 */
.btn-outline {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-outline-primary {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline-primary:hover {
    background: #667eea;
    color: white;
}

.btn-outline-secondary {
    background: transparent;
    border: 2px solid #718096;
    color: #718096;
}

.btn-outline-secondary:hover {
    background: #718096;
    color: white;
}

/* 비활성 버튼 */
.btn:disabled,
.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

/* 로딩 버튼 */
.btn.loading {
    position: relative;
    color: transparent;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: btn-loading 1s linear infinite;
}

@keyframes btn-loading {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* 반응형 버튼 */
@media (max-width: 768px) {
    .btn {
        padding: 14px 16px;
        font-size: 15px;
        min-height: 48px;
    }

    .btn-small {
        padding: 10px 14px;
        font-size: 14px;
        min-height: 40px;
    }

    .btn-large {
        padding: 18px 28px;
        font-size: 17px;
        min-height: 56px;
    }
}

/* 접근성 개선 */
.btn:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}

.btn:focus:not(:focus-visible) {
    outline: none;
}

.btn:focus-visible {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}
";
?>
