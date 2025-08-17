/**
 * 🚀 Ultra Think Mode: 공지사항 작성 vs 편집 페이지 비교 분석
 * 
 * 목적: 편집 페이지의 데이터 표시 문제를 정확히 진단하기 위한 포괄적 비교
 */

import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

class NoticePageComparator {
    constructor() {
        this.browser = null;
        this.context = null;
        this.timestamp = new Date().toISOString().slice(0, 19).replace(/[-:]/g, '');
        this.outputDir = `/var/www/html/topmkt/tests/playwright/comparison-${this.timestamp}`;
        
        // 출력 디렉토리 생성
        if (!fs.existsSync(this.outputDir)) {
            fs.mkdirSync(this.outputDir, { recursive: true });
        }
        
        console.log(`🚀 Ultra Think 모드: 공지사항 페이지 비교 분석 시작`);
        console.log(`📁 결과 저장 경로: ${this.outputDir}`);
    }
    
    async init() {
        this.browser = await chromium.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-dev-shm-usage']
        });
        
        this.context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'UltraThink-NoticeComparator/1.0'
        });
        
        console.log('🔧 브라우저 초기화 완료');
    }
    
    async login() {
        const page = await this.context.newPage();
        
        console.log('🔑 로그인 진행 중...');
        
        try {
            await page.goto('https://www.topmktx.com/auth/login', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 로그인 폼 입력
            await page.fill('#email', 'wincardkorea@gmail.com');
            await page.fill('#password', 'Dnlszkem1!');
            
            // 로그인 버튼 클릭
            await page.click('button[type="submit"]');
            await page.waitForURL(/\/(?!auth)/, { timeout: 15000 });
            
            console.log('✅ 로그인 성공');
            return page;
            
        } catch (error) {
            console.error('❌ 로그인 실패:', error.message);
            throw error;
        }
    }
    
    async analyzeWritePage() {
        console.log('\n📝 1. 작성 페이지 분석 시작');
        
        const page = await this.login();
        
        try {
            await page.goto('https://www.topmktx.com/notices/write', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            console.log('   페이지 로딩 완료');
            
            // 전체 페이지 스크린샷
            await page.screenshot({ 
                path: `${this.outputDir}/write-page-full.png`,
                fullPage: true 
            });
            
            // 폼 영역만 스크린샷
            const formSelector = 'form, .write-form, .form-container';
            try {
                const formElement = await page.$(formSelector);
                if (formElement) {
                    await formElement.screenshot({ 
                        path: `${this.outputDir}/write-form-area.png` 
                    });
                }
            } catch (e) {
                console.log('   폼 영역 스크린샷 실패, 전체 페이지로 대체');
            }
            
            // HTML 구조 분석
            const htmlContent = await page.content();
            fs.writeFileSync(`${this.outputDir}/write-page.html`, htmlContent);
            
            // 폼 필드 정보 수집
            const formData = await page.evaluate(() => {
                const data = {
                    title: { 
                        element: document.querySelector('#title, input[name="title"]'),
                        value: '',
                        placeholder: ''
                    },
                    content: { 
                        element: document.querySelector('#content, textarea[name="content"]'),
                        value: '',
                        placeholder: ''
                    },
                    images: {
                        element: document.querySelector('#images, input[name="images"], input[type="file"]'),
                        multiple: false
                    },
                    formElement: document.querySelector('form')
                };
                
                // 실제 값 추출
                if (data.title.element) {
                    data.title.value = data.title.element.value;
                    data.title.placeholder = data.title.element.placeholder;
                }
                
                if (data.content.element) {
                    data.content.value = data.content.element.value;
                    data.content.placeholder = data.content.element.placeholder;
                }
                
                if (data.images.element) {
                    data.images.multiple = data.images.element.multiple;
                }
                
                return {
                    title: { value: data.title.value, placeholder: data.title.placeholder },
                    content: { value: data.content.value, placeholder: data.content.placeholder },
                    images: { multiple: data.images.multiple },
                    hasForm: !!data.formElement
                };
            });
            
            console.log('✅ 작성 페이지 분석 완료');
            console.log(`   - 제목 필드: ${formData.hasForm ? '✅' : '❌'}`);
            console.log(`   - 내용 필드: ${formData.content.placeholder ? '✅' : '❌'}`);
            console.log(`   - 이미지 업로드: ${formData.images.multiple ? '✅ (다중)' : '❌'}`);
            
            return { page, formData };
            
        } catch (error) {
            console.error('❌ 작성 페이지 분석 실패:', error.message);
            throw error;
        }
    }
    
    async analyzeEditPage() {
        console.log('\n✏️ 2. 편집 페이지 분석 시작');
        
        const page = await this.context.newPage();
        
        try {
            await page.goto('https://www.topmktx.com/notices/10/edit', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            console.log('   페이지 로딩 완료');
            
            // 전체 페이지 스크린샷
            await page.screenshot({ 
                path: `${this.outputDir}/edit-page-full.png`,
                fullPage: true 
            });
            
            // 폼 영역만 스크린샷
            const formSelector = 'form, .edit-form, .form-container';
            try {
                const formElement = await page.$(formSelector);
                if (formElement) {
                    await formElement.screenshot({ 
                        path: `${this.outputDir}/edit-form-area.png` 
                    });
                }
            } catch (e) {
                console.log('   폼 영역 스크린샷 실패, 전체 페이지로 대체');
            }
            
            // HTML 구조 분석
            const htmlContent = await page.content();
            fs.writeFileSync(`${this.outputDir}/edit-page.html`, htmlContent);
            
            // 폼 필드 정보 및 기존 데이터 수집
            const formData = await page.evaluate(() => {
                const data = {
                    title: { 
                        element: document.querySelector('#title, input[name="title"]'),
                        value: '',
                        placeholder: ''
                    },
                    content: { 
                        element: document.querySelector('#content, textarea[name="content"]'),
                        value: '',
                        placeholder: ''
                    },
                    images: {
                        element: document.querySelector('#images, input[name="images"], input[type="file"]'),
                        existingImages: document.querySelectorAll('.existing-image-item, .image-preview, img'),
                        multiple: false,
                        count: 0
                    },
                    formElement: document.querySelector('form'),
                    errors: document.querySelectorAll('.error-message, .alert-danger')
                };
                
                // 실제 값 추출
                if (data.title.element) {
                    data.title.value = data.title.element.value;
                    data.title.placeholder = data.title.element.placeholder;
                }
                
                if (data.content.element) {
                    data.content.value = data.content.element.value;
                    data.content.placeholder = data.content.element.placeholder;
                }
                
                if (data.images.element) {
                    data.images.multiple = data.images.element.multiple;
                }
                
                data.images.count = data.images.existingImages.length;
                
                return {
                    title: { 
                        value: data.title.value, 
                        placeholder: data.title.placeholder,
                        length: data.title.value.length 
                    },
                    content: { 
                        value: data.content.value, 
                        placeholder: data.content.placeholder,
                        length: data.content.value.length 
                    },
                    images: { 
                        multiple: data.images.multiple,
                        existingCount: data.images.count
                    },
                    hasForm: !!data.formElement,
                    errorCount: data.errors.length
                };
            });
            
            console.log('✅ 편집 페이지 분석 완료');
            console.log(`   - 기존 제목: "${formData.title.value}" (${formData.title.length}자)`);
            console.log(`   - 기존 내용: ${formData.content.length}자`);
            console.log(`   - 기존 이미지: ${formData.images.existingCount}개`);
            console.log(`   - 에러 메시지: ${formData.errorCount}개`);
            
            return { page, formData };
            
        } catch (error) {
            console.error('❌ 편집 페이지 분석 실패:', error.message);
            
            // 에러 페이지 스크린샷
            try {
                await page.screenshot({ 
                    path: `${this.outputDir}/edit-page-error.png`,
                    fullPage: true 
                });
            } catch (e) {}
            
            throw error;
        }
    }
    
    async generateComparison(writeData, editData) {
        console.log('\n📊 3. 비교 분석 보고서 생성');
        
        const comparison = {
            timestamp: this.timestamp,
            analysis: {
                write: writeData.formData,
                edit: editData.formData
            },
            issues: [],
            recommendations: []
        };
        
        // 문제점 분석
        if (!editData.formData.title.value) {
            comparison.issues.push('❌ 편집 페이지에 기존 제목이 로드되지 않음');
        }
        
        if (!editData.formData.content.value) {
            comparison.issues.push('❌ 편집 페이지에 기존 내용이 로드되지 않음');
        }
        
        if (editData.formData.images.existingCount === 0) {
            comparison.issues.push('❌ 편집 페이지에 기존 이미지가 표시되지 않음');
        }
        
        if (editData.formData.errorCount > 0) {
            comparison.issues.push(`❌ 편집 페이지에 ${editData.formData.errorCount}개의 에러 메시지 존재`);
        }
        
        // 권장사항
        if (comparison.issues.length > 0) {
            comparison.recommendations.push('🔧 NoticeController::showEdit 메서드의 데이터 로딩 로직 점검 필요');
            comparison.recommendations.push('🔧 edit.php 뷰 파일의 데이터 바인딩 확인 필요');
            comparison.recommendations.push('🔧 이미지 표시 로직 점검 필요');
        }
        
        // 보고서 저장
        const reportContent = `
# 🚀 Ultra Think 모드: 공지사항 페이지 비교 분석 보고서

## 📅 분석 시각
${new Date().toLocaleString('ko-KR')}

## 📊 비교 결과

### ✅ 작성 페이지 (/notices/write)
- 폼 존재: ${writeData.formData.hasForm ? '✅' : '❌'}
- 제목 필드: ${writeData.formData.title.placeholder ? '✅' : '❌'}
- 내용 필드: ${writeData.formData.content.placeholder ? '✅' : '❌'}
- 이미지 업로드: ${writeData.formData.images.multiple ? '✅ (다중 지원)' : '❌'}

### ✏️ 편집 페이지 (/notices/10/edit)
- 폼 존재: ${editData.formData.hasForm ? '✅' : '❌'}
- 기존 제목 로드: ${editData.formData.title.value ? '✅' : '❌'} (${editData.formData.title.length}자)
- 기존 내용 로드: ${editData.formData.content.value ? '✅' : '❌'} (${editData.formData.content.length}자)
- 기존 이미지 표시: ${editData.formData.images.existingCount > 0 ? '✅' : '❌'} (${editData.formData.images.existingCount}개)
- 에러 메시지: ${editData.formData.errorCount > 0 ? '❌' : '✅'} (${editData.formData.errorCount}개)

## 🚨 발견된 문제점

${comparison.issues.map(issue => `- ${issue}`).join('\n')}

## 💡 해결 권장사항

${comparison.recommendations.map(rec => `- ${rec}`).join('\n')}

## 📁 생성된 파일들

- write-page-full.png: 작성 페이지 전체 스크린샷
- edit-page-full.png: 편집 페이지 전체 스크린샷
- write-form-area.png: 작성 페이지 폼 영역
- edit-form-area.png: 편집 페이지 폼 영역
- write-page.html: 작성 페이지 HTML 소스
- edit-page.html: 편집 페이지 HTML 소스

## 🔍 다음 단계

1. 데이터 로딩 로직 점검
2. 뷰 파일 데이터 바인딩 수정
3. 이미지 표시 시스템 개선
4. 재테스트 및 검증
        `.trim();
        
        fs.writeFileSync(`${this.outputDir}/comparison-report.md`, reportContent);
        
        console.log('✅ 비교 분석 보고서 생성 완료');
        console.log(`📄 보고서: ${this.outputDir}/comparison-report.md`);
        
        return comparison;
    }
    
    async run() {
        try {
            await this.init();
            
            const writeResult = await this.analyzeWritePage();
            const editResult = await this.analyzeEditPage();
            
            const comparison = await this.generateComparison(writeResult, editResult);
            
            console.log('\n🎯 Ultra Think 분석 완료!');
            console.log('📁 모든 결과 파일이 저장되었습니다.');
            
            if (comparison.issues.length > 0) {
                console.log('\n🚨 발견된 문제점:');
                comparison.issues.forEach(issue => console.log(`   ${issue}`));
            } else {
                console.log('\n✅ 문제점이 발견되지 않았습니다.');
            }
            
            return comparison;
            
        } catch (error) {
            console.error('❌ 분석 중 오류:', error);
            throw error;
        } finally {
            if (this.browser) {
                await this.browser.close();
            }
        }
    }
}

// 실행
const comparator = new NoticePageComparator();
comparator.run().then(result => {
    console.log('\n🎉 Ultra Think 모드 비교 분석 완료!');
    process.exit(0);
}).catch(error => {
    console.error('❌ 치명적 오류:', error);
    process.exit(1);
});