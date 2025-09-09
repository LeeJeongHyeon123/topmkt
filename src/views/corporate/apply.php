<?php
/**
 * 기업 인증 신청 페이지
 */
?>

<style>
/* 기업 인증 신청 페이지 스타일 */
.corp-apply-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
}

.corp-apply-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 30px;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border-radius: 16px;
    margin-top: 60px;
}

.corp-apply-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.corp-apply-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    line-height: 1.6;
}

.reapply-notice {
    background: #fef3cd;
    border: 1px solid #fceecf;
    color: #856404;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    text-align: center;
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

/* 파일 업로드 */
.file-upload-area {
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    transition: all 0.3s ease;
    background: #f8fafc;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: #48bb78;
    background: #f0fff4;
}

.file-upload-area.dragover {
    border-color: #48bb78;
    background: #f0fff4;
    transform: scale(1.02);
}

.file-upload-icon {
    font-size: 3rem;
    color: #a0aec0;
    margin-bottom: 15px;
}

.file-upload-text {
    font-size: 1.1rem;
    color: #4a5568;
    margin-bottom: 10px;
    font-weight: 500;
}

.file-upload-hint {
    font-size: 0.875rem;
    color: #718096;
}

.file-input {
    display: none;
}

.selected-file {
    margin-top: 15px;
    padding: 12px 16px;
    background: #e6fffa;
    border: 1px solid #81e6d9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: between;
    gap: 10px;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    color: #234e52;
}

.file-size {
    font-size: 0.875rem;
    color: #4c8085;
}

.file-remove {
    background: none;
    border: none;
    color: #e53e3e;
    cursor: pointer;
    font-size: 1.2rem;
    padding: 5px;
}

/* 체크박스 */
.checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 20px;
}

.checkbox-input {
    margin-top: 3px;
}

.checkbox-label {
    font-size: 0.95rem;
    color: #4a5568;
    line-height: 1.5;
    cursor: pointer;
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

/* 해외 기업 안내 */
.overseas-info {
    background: #ebf8ff;
    border: 1px solid #90cdf4;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #2b6cb0;
    line-height: 1.5;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .corp-apply-container {
        padding: 20px 15px;
    }
    
    .corp-apply-header {
        padding: 30px 20px;
        margin-top: 20px;
    }
    
    .corp-apply-header h1 {
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
    
    .file-upload-area {
        padding: 30px 15px;
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

<!-- 공통 업로드 설정 (validateFileSize 함수 사용 전에 로드) -->
<?php include '/var/www/html/topmkt/src/views/includes/upload-config.js.php'; ?>

<div class="corp-apply-container">
    <!-- 헤더 -->
    <div class="corp-apply-header">
        <h1><?= $isReapply ? '🔄 기업 인증 재신청' : '📝 기업 인증 신청' ?></h1>
        <p><?= $isReapply ? '거절 사유를 보완하여 다시 신청해주세요.' : '강의와 행사를 등록하기 위해 기업 인증을 신청하세요.' ?></p>
    </div>

    <?php if ($isReapply): ?>
    <div class="reapply-notice">
        <strong>재신청 안내</strong><br>
        이전 신청에서 거절된 사유를 확인하고 필요한 서류를 보완하여 다시 신청해주세요.
    </div>
    <?php endif; ?>

    <!-- 오류 메시지 표시 -->
    <?php if (isset($_SESSION['error_message'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 20px; border-radius: 12px; margin-bottom: 30px; text-align: center; font-weight: 500;">
        <strong>❌ 오류 발생</strong><br>
        <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php 
        unset($_SESSION['error_message']); // 메시지 표시 후 삭제
    endif; ?>

    <!-- 신청 폼 -->
    <form id="corpApplyForm" method="POST" enctype="multipart/form-data" class="form-container">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-header">
            <h2>기업 정보 입력</h2>
            <p>정확한 정보를 입력해주세요. 입력하신 정보는 인증 심사에 사용됩니다.</p>
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
                           value="<?= htmlspecialchars($existingData['company_name'] ?? '') ?>"
                           placeholder="(주)탑마케팅" 
                           required maxlength="255">
                    <div class="form-help">사업자등록증에 표시된 정확한 회사명을 입력해주세요.</div>
                </div>

                <div class="form-group" id="business_number_group">
                    <label for="business_number" class="form-label">
                        사업자등록번호 <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="business_number" 
                           name="business_number" 
                           class="form-input" 
                           value="<?= htmlspecialchars($existingData['business_number'] ?? '') ?>"
                           placeholder="123-45-67890" 
                           required maxlength="100">
                    <div class="form-help">하이픈(-)을 포함하여 입력해주세요.</div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" 
                           id="is_overseas" 
                           name="is_overseas" 
                           class="checkbox-input"
                           <?= ($existingData['is_overseas'] ?? 0) ? 'checked' : '' ?>>
                    <label for="is_overseas" class="checkbox-label">
                        해외 기업입니다 (한국 사업자등록증이 없는 경우)
                    </label>
                </div>

                <div id="overseas_info" class="overseas-info" style="display: none;">
                    해외 기업의 경우 사업자등록증 대신 <strong>법인등기부등본, 상업등기부, 사업허가증</strong> 등 
                    동등한 서류를 업로드해주세요. 언어는 제한이 없습니다.
                </div>
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
                           value="<?= htmlspecialchars($existingData['representative_name'] ?? '') ?>"
                           placeholder="홍길동" 
                           required maxlength="100">
                    <div class="form-help">사업자등록증에 표시된 대표자명과 일치해야 합니다.</div>
                </div>

                <div class="form-group">
                    <label for="representative_phone" class="form-label">
                        대표자 연락처 <span class="required">*</span>
                    </label>
                    <input type="tel" 
                           id="representative_phone" 
                           name="representative_phone" 
                           class="form-input" 
                           value="<?= htmlspecialchars($existingData['representative_phone'] ?? '') ?>"
                           placeholder="010-1234-5678" 
                           required maxlength="13">
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
                              required><?= htmlspecialchars($existingData['company_address'] ?? '') ?></textarea>
                    <div class="form-help">사업자등록증에 표시된 주소 또는 실제 사업장 주소를 입력해주세요.</div>
                </div>
            </div>

            <!-- 사업자등록증 업로드 섹션 -->
            <div class="form-section">
                <h3 class="section-title">📎 사업자등록증 업로드</h3>
                
                <div class="form-group">
                    <label for="business_registration_file" class="form-label">
                        사업자등록증 파일 <span class="required">*</span>
                    </label>
                    
                    <div class="file-upload-area" onclick="document.getElementById('business_registration_file').click()">
                        <div class="file-upload-icon">📄</div>
                        <div class="file-upload-text">클릭하거나 파일을 드래그하여 업로드</div>
                        <div class="file-upload-hint">JPG, PNG, WebP, PDF 파일 (최대 30MB)</div>
                    </div>
                    
                    <input type="file" 
                           id="business_registration_file" 
                           name="business_registration_file" 
                           class="file-input" 
                           accept=".jpg,.jpeg,.png,.webp,.pdf"
                           required>
                    
                    <div id="selected_file" class="selected-file" style="display: none;">
                        <div class="file-info">
                            <div class="file-name"></div>
                            <div class="file-size"></div>
                        </div>
                        <button type="button" class="file-remove" onclick="removeFile()">✕</button>
                    </div>
                    
                    <div class="form-help">
                        선명하고 읽기 쉬운 사업자등록증 사본을 업로드해주세요. 
                        해외 기업은 동등한 사업자 등록 서류를 업로드하시면 됩니다.
                    </div>
                </div>
            </div>

            <!-- 제출 버튼 -->
            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>📤</span> <?= $isReapply ? '재신청하기' : '신청하기' ?>
                </button>
                <a href="/corp/info" class="btn-cancel">
                    <span>↩️</span> 취소
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('corpApplyForm');
    const fileInput = document.getElementById('business_registration_file');
    const fileUploadArea = document.querySelector('.file-upload-area');
    const selectedFileDiv = document.getElementById('selected_file');
    const overseasCheckbox = document.getElementById('is_overseas');
    const overseasInfo = document.getElementById('overseas_info');
    const submitBtn = document.getElementById('submitBtn');

    // 해외 기업 체크박스 처리
    const businessNumberGroup = document.getElementById('business_number_group');
    const businessNumberInput = document.getElementById('business_number');
    
    function toggleOverseasMode(isOverseas) {
        if (isOverseas) {
            // 해외 기업 모드
            overseasInfo.style.display = 'block';
            businessNumberGroup.style.display = 'none';
            businessNumberInput.required = false;
            businessNumberInput.value = ''; // 값 초기화
        } else {
            // 국내 기업 모드
            overseasInfo.style.display = 'none';
            businessNumberGroup.style.display = 'block';
            businessNumberInput.required = true;
        }
    }
    
    overseasCheckbox.addEventListener('change', function() {
        toggleOverseasMode(this.checked);
    });

    // 페이지 로드시 해외 기업 체크 상태 확인
    toggleOverseasMode(overseasCheckbox.checked);

    // 사업자번호 자동 하이픈 추가
    document.getElementById('business_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length <= 10) {
            if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{2})(\d{0,5})/, '$1-$2-$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{0,2})/, '$1-$2');
            }
        }
        e.target.value = value;
    });

    // 전화번호 자동 하이픈 추가 (11자리 제한)
    document.getElementById('representative_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        
        // 11자리 숫자로 제한
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        // 하이픈 형태로 포맷팅
        if (value.length > 7) {
            value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, '$1-$2-$3');
        } else if (value.length > 3) {
            value = value.replace(/(\d{3})(\d{0,4})/, '$1-$2');
        }
        
        e.target.value = value;
    });

    // 파일 업로드 처리
    fileInput.addEventListener('change', handleFileSelect);

    // 드래그 앤 드롭 처리
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });

    function handleFileSelect() {
        const file = fileInput.files[0];
        if (!file) return;

        // 파일 크기 검증 (공통 설정 사용: 30MB)
        if (!window.validateFileSize || !window.validateFileSize(file.size)) {
            alert(window.getFileSizeErrorMessage ? window.getFileSizeErrorMessage() : '파일 크기가 너무 큽니다.');
            fileInput.value = '';
            return;
        }

        // 파일 타입 검증
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('JPG, PNG, WebP, PDF 파일만 업로드 가능합니다.');
            fileInput.value = '';
            return;
        }

        // 선택된 파일 정보 표시
        selectedFileDiv.querySelector('.file-name').textContent = file.name;
        selectedFileDiv.querySelector('.file-size').textContent = formatFileSize(file.size);
        selectedFileDiv.style.display = 'flex';
        fileUploadArea.style.display = 'none';
    }

    // 파일 제거
    window.removeFile = function() {
        fileInput.value = '';
        selectedFileDiv.style.display = 'none';
        fileUploadArea.style.display = 'block';
    };

    // 파일 크기 포맷팅
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

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
        submitBtn.innerHTML = '<span>⏳</span> ' + (<?= $isReapply ? 'true' : 'false' ?> ? '재신청 중...' : '신청 중...');
        
        // Ajax로 폼 제출 후 리다이렉트 처리
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('/corp/apply', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('[CORP_APPLY] Response 수신:', {
                status: response.status,
                statusText: response.statusText,
                redirected: response.redirected,
                url: response.url,
                ok: response.ok
            });
            
            if (response.redirected) {
                console.log('[CORP_APPLY] 서버에서 리다이렉트됨:', response.url);
                window.location.href = response.url;
            } else if (response.ok) {
                console.log('[CORP_APPLY] 성공 응답, /corp/status로 이동');
                window.location.href = '/corp/status';
            } else {
                console.error('[CORP_APPLY] 서버 오류 응답:', response.status, response.statusText);
                throw new Error('서버 오류가 발생했습니다: ' + response.status);
            }
        })
        .catch(error => {
            console.error('[CORP_APPLY] Fetch 오류:', error);
            alert('신청 처리 중 오류가 발생했습니다. 다시 시도해주세요.\n오류: ' + error.message);
            
            // 버튼 원상복구
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = '<span>📤</span> ' + (<?= $isReapply ? 'true' : 'false' ?> ? '재신청하기' : '신청하기');
        });
    });

    function validateForm() {
        const requiredFields = [
            { id: 'company_name', name: '회사명' },
            { id: 'representative_name', name: '대표자명' },
            { id: 'representative_phone', name: '대표자 연락처' },
            { id: 'company_address', name: '회사 주소' }
        ];

        // 해외 기업이 아닌 경우에만 사업자등록번호 검증
        if (!overseasCheckbox.checked) {
            requiredFields.splice(1, 0, { id: 'business_number', name: '사업자등록번호' });
        }

        for (let field of requiredFields) {
            const element = document.getElementById(field.id);
            if (!element.value.trim()) {
                alert(field.name + '을(를) 입력해주세요.');
                element.focus();
                return false;
            }
        }

        // 파일 업로드 확인
        if (!fileInput.files[0]) {
            alert('사업자등록증 파일을 업로드해주세요.');
            return false;
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


