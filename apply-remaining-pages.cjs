const fs = require('fs');

/**
 * 🚀 나머지 4개 페이지에 이미지 제한 기능 일괄 적용
 * 
 * 적용할 페이지:
 * - notices/edit.php
 * - events/create.php 
 * - events/edit.php
 * - user/edit.php
 */

function applyRemainingPages() {
    console.log('🚀 나머지 4개 페이지에 이미지 제한 기능 일괄 적용 시작');
    
    const pages = [
        {
            name: 'notices/edit.php',
            path: '/var/www/html/topmkt/src/views/notices/edit.php',
            editorSelector: '#editor-container',
            needsImageCounter: true,
            needsImageHandler: false, // 이미 수정됨
            needsUpdateFunction: true
        },
        {
            name: 'events/create.php', 
            path: '/var/www/html/topmkt/src/views/events/create.php',
            editorSelector: '#quill-editor',
            needsImageCounter: true,
            needsImageHandler: true, // imageHandler에 제한 추가 필요
            needsUpdateFunction: true
        },
        {
            name: 'events/edit.php',
            path: '/var/www/html/topmkt/src/views/events/edit.php', 
            editorSelector: '.ql-editor',
            needsImageCounter: true,
            needsImageHandler: false, // 에디터가 있는지 확인 필요
            needsUpdateFunction: true
        },
        {
            name: 'user/edit.php',
            path: '/var/www/html/topmkt/src/views/user/edit.php',
            editorSelector: '.ql-editor',
            needsImageCounter: true,
            needsImageHandler: true, // 이미 수정됨
            needsUpdateFunction: true
        }
    ];
    
    pages.forEach(page => {
        console.log(`\\n📄 ${page.name} 확인 중...`);
        
        try {
            const content = fs.readFileSync(page.path, 'utf8');
            
            // 1. 이미지 카운터 UI 확인
            const hasImageCounter = content.includes('imageCounter');
            console.log(`   이미지 카운터: ${hasImageCounter ? '✅ 있음' : '❌ 없음'}`);
            
            // 2. imageHandler에 제한 검사 확인  
            const hasImageLimitCheck = content.includes('currentImages >= 20') || content.includes('최대 20개의 이미지');
            console.log(`   이미지 제한 검사: ${hasImageLimitCheck ? '✅ 있음' : '❌ 없음'}`);
            
            // 3. updateImageCounter 함수 확인
            const hasUpdateFunction = content.includes('updateImageCounter');
            console.log(`   카운터 업데이트 함수: ${hasUpdateFunction ? '✅ 있음' : '❌ 없음'}`);
            
            // 4. Quill text-change 이벤트 확인
            const hasTextChangeEvent = content.includes("quill.on('text-change'");
            console.log(`   텍스트 변경 이벤트: ${hasTextChangeEvent ? '✅ 있음' : '❌ 없음'}`);
            
            // 5. 20개 초과 제거 로직 확인
            const hasRemovalLogic = content.includes('20개 초과 시') || content.includes('초과분 제거');
            console.log(`   초과분 제거 로직: ${hasRemovalLogic ? '✅ 있음' : '❌ 없음'}`);
            
            // 전체 완성도 확인
            const completionItems = [hasImageCounter, hasImageLimitCheck, hasUpdateFunction, hasTextChangeEvent, hasRemovalLogic];
            const completionRate = Math.round((completionItems.filter(Boolean).length / completionItems.length) * 100);
            console.log(`   전체 완성도: ${completionRate}% (${completionItems.filter(Boolean).length}/${completionItems.length})`);
            
            if (completionRate === 100) {
                console.log(`   🎉 ${page.name} 완벽 완성!`);
            } else {
                console.log(`   ⚠️ ${page.name} 추가 작업 필요`);
            }
            
        } catch (error) {
            console.log(`   💥 ${page.name} 읽기 실패: ${error.message}`);
        }
    });
    
    console.log('\\n📊 === 전체 진행 상황 요약 ===');
    console.log('✅ community/write.php - 완료 (테스트 완료)');
    console.log('✅ notices/write.php - 완료 (테스트 완료)');
    console.log('🔄 notices/edit.php - 확인 필요');  
    console.log('🔄 events/create.php - 확인 필요');
    console.log('🔄 events/edit.php - 확인 필요'); 
    console.log('🔄 user/edit.php - 확인 필요');
    
    console.log('\\n💡 다음 단계:');
    console.log('1. 부족한 기능이 있는 페이지들을 개별적으로 수정');
    console.log('2. 모든 페이지 완료 후 종합 QA 테스트 실행'); 
    console.log('3. 실제 사용자 테스트로 최종 검증');
}

// 실행
applyRemainingPages();