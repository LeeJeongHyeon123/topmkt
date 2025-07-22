<?php
/**
 * 기업 정보 수정 페이지
 */
?>

<style>
/* 기업 정보 수정 페이지 스타일 */
.corp-edit-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 40px 20px;
}

.corp-edit-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 30px;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border-radius: 16px;
    margin-top: 60px;
}

.corp-edit-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.corp-edit-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    line-height: 1.6;
}

.edit-notice {
    background: #ebf8ff;
    border: 1px solid #90cdf4;
    color: #2b6cb0;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    font-weight: 500;
}

.form-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.form-header {
    background: #f8fafc;
    padding: 30px;
    border-bottom: 1px solid #e2e8f0;
}

.form-header h2 {
    font-size: 1.5rem;
    color: #2d3748;
    margin-bottom: 10px;
    font-weight: 600;
}

.form-header p {
    color: #4a5568;
    line-height: 1.6;
}

.form-body {
    padding: 40px;
}

.form-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 1.3rem;
    color: #2d3748;
    margin-bottom: 20px;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.required {
    color: #e53e3e;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #48bb78;
    box-shadow: 0 0 0 3px rgba(72, 187, 120, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.form-help {
    font-size: 0.875rem;
    color: #718096;
    margin-top: 5px;
    line-height: 1.4;
}

.readonly-field {
    background: #f7fafc;
    border-color: #cbd5e0;
    color: #4a5568;
    cursor: not-allowed;
}

.readonly-notice {
    background: #fff5f5;
    border: 1px solid #fed7d7;
    color: #c53030;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 0.9rem;
}

/* 버튼 */
.form-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
}

.btn-submit {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 15px 40px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-cancel {
    background: #e2e8f0;
    color: #4a5568;
    padding: 15px 40px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-cancel:hover {
    background: #cbd5e0;
    text-decoration: none;
    color: #4a5568;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .corp-edit-container {
        padding: 20px 15px;
    }
    
    .corp-edit-header {
        padding: 30px 20px;
        margin-top: 20px;
    }
    
    .corp-edit-header h1 {
        font-size: 2rem;
    }
    
    .form-body {
        padding: 30px 20px;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-submit,
    .btn-cancel {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

/* 로딩 스타일 */
.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="corp-edit-container">
    <!-- 헤더 -->
    <div class="corp-edit-header">
        <h1>✏️ 기업 정보 수정</h1>
        <p>승인된 기업 정보를 수정할 수 있습니다.</p>
    </div>

    <div class="edit-notice">
        <strong>📝 수정 가능 항목 안내</strong><br>
        회사명, 대표자명, 대표자 연락처, 회사 주소는 수정 가능합니다.<br>
        사업자등록번호나 사업자등록증 변경이 필요한 경우 새로 신청해주세요.
    </div>

    <!-- 수정 폼 -->
    <form id="corpEditForm" method="POST" class="form-container">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-header">
            <h2>기업 정보 수정</h2>
            <p>변경할 정보를 입력해주세요. 승인된 기업회원만 정보를 수정할 수 있습니다.</p>
        </div>

        <div class="form-body">
            <!-- 기본 정보 섹션 -->
            <div class="form-section">
                <h3 class="section-title">📋 기본 정보</h3>
                
                <div class="form-group">
                    <label for="company_name" class="form-label">
                        회사명 <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="company_name" 
                           name="company_name" 
                           class="form-input" 
                           value="<?= htmlspecialchars($profile['company_name']) ?>"
                           placeholder="(주)탑마케팅" 
                           required maxlength="255">
                    <div class="form-help">정확한 회사명을 입력해주세요.</div>
                </div>

                <div class="form-group">
                    <label for="business_number" class="form-label">
                        사업자등록번호
                    </label>
                    <input type="text" 
                           id="business_number" 
                           name="business_number" 
                           class="form-input readonly-field" 
                           value="<?= htmlspecialchars($profile['business_number']) ?>"
                           readonly>
                    <div class="readonly-notice">
                        사업자등록번호는 수정할 수 없습니다. 변경이 필요한 경우 새로 신청해주세요.
                    </div>
                </div>

                <?php if ($profile['is_overseas']): ?>
                <div class="form-group">
                    <label class="form-label">기업 유형</label>
                    <div style="padding: 12px 16px; background: #ebf8ff; border: 2px solid #90cdf4; border-radius: 8px; color: #2b6cb0; font-weight: 500;">
                        🌍 해외 기업
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 대표자 정보 섹션 -->
            <div class="form-section">
                <h3 class="section-title">👤 대표자 정보</h3>
                
                <div class="form-group">
                    <label for="representative_name" class="form-label">
                        대표자명 <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="representative_name" 
                           name="representative_name" 
                           class="form-input" 
                           value="<?= htmlspecialchars($profile['representative_name']) ?>"
                           placeholder="홍길동" 
                           required maxlength="100">
                    <div class="form-help">정확한 대표자명을 입력해주세요.</div>
                </div>

                <div class="form-group">
                    <label for="representative_phone" class="form-label">
                        대표자 연락처 <span class="required">*</span>
                    </label>
                    <input type="tel" 
                           id="representative_phone" 
                           name="representative_phone" 
                           class="form-input" 
                           value="<?= htmlspecialchars($profile['representative_phone']) ?>"
                           placeholder="010-1234-5678" 
                           required maxlength="20">
                    <div class="form-help">연락 가능한 대표자의 휴대폰 번호를 입력해주세요.</div>
                </div>
            </div>

            <!-- 회사 주소 섹션 -->
            <div class="form-section">
                <h3 class="section-title">📍 회사 주소</h3>
                
                <div class="form-group">
                    <label for="company_address" class="form-label">
                        회사 주소 <span class="required">*</span>
                    </label>
                    <textarea id="company_address" 
                              name="company_address" 
                              class="form-input form-textarea" 
                              placeholder="서울특별시 강남구 테헤란로 123, 4층 (역삼동, ABC빌딩)" 
                              required><?= htmlspecialchars($profile['company_address']) ?></textarea>
                    <div class="form-help">실제 사업장 주소를 입력해주세요.</div>
                </div>
            </div>

            <!-- 수정 불가 항목 안내 -->
            <div class="form-section">
                <h3 class="section-title">🚫 수정 불가 항목</h3>
                
                <div class="form-group">
                    <label class="form-label">사업자등록증 파일</label>
                    <div style="padding: 15px; background: #f7fafc; border: 1px solid #cbd5e0; border-radius: 8px; color: #4a5568;">
                        📄 <?= htmlspecialchars($profile['business_registration_file']) ?>
                    </div>
                    <div class="readonly-notice">
                        사업자등록증은 수정할 수 없습니다. 새로운 서류가 필요한 경우 새로 신청해주세요.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">승인 정보</label>
                    <div style="padding: 15px; background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 8px; color: #22543d;">
                        ✅ 승인일: <?= date('Y-m-d H:i', strtotime($profile['processed_at'])) ?><br>
                        👤 처리자: <?= htmlspecialchars($profile['processed_by_name']) ?>
                    </div>
                </div>
            </div>

            <!-- 제출 버튼 -->
            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>💾</span> 정보 수정하기
                </button>
                <a href="/corp/status" class="btn-cancel">
                    <span>↩️</span> 취소
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('corpEditForm');
    const submitBtn = document.getElementById('submitBtn');

    // 전화번호 자동 하이픈 추가
    document.getElementById('representative_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length <= 11) {
            if (value.length > 7) {
                value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, '$1-$2-$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{0,4})/, '$1-$2');
            }
        }
        e.target.value = value;
    });

    // 폼 제출 처리
    form.addEventListener('submit', function(e) {
        // 기본 검증
        if (!validateForm()) {
            e.preventDefault();
            return;
        }

        // 제출 버튼 비활성화
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<span>⏳</span> 수정 중...';
    });

    function validateForm() {
        const requiredFields = [
            { id: 'company_name', name: '회사명' },
            { id: 'representative_name', name: '대표자명' },
            { id: 'representative_phone', name: '대표자 연락처' },
            { id: 'company_address', name: '회사 주소' }
        ];

        for (let field of requiredFields) {
            const element = document.getElementById(field.id);
            if (!element.value.trim()) {
                alert(field.name + '을(를) 입력해주세요.');
                element.focus();
                return false;
            }
        }

        // 전화번호 형식 검증
        const phoneRegex = /^010-\d{4}-\d{4}$/;
        const phone = document.getElementById('representative_phone').value;
        if (!phoneRegex.test(phone)) {
            alert('올바른 휴대폰 번호 형식을 입력해주세요. (예: 010-1234-5678)');
            document.getElementById('representative_phone').focus();
            return false;
        }

        return true;
    }
});
</script>

