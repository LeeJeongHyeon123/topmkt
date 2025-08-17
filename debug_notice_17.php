<?php
/**
 * Ultra Think v3.13.0: 공지사항 #17 중복 업로드 버그 진단
 */

require_once 'src/config/database.php';

try {
    $db = Database::getInstance();
    $notice = $db->fetch("SELECT id, title, content, image_path, created_at, updated_at FROM notices WHERE id = 17");
    
    if ($notice) {
        echo "🔍 공지사항 #17 분석\n";
        echo "========================\n";
        echo "제목: " . $notice['title'] . "\n";
        echo "생성: " . $notice['created_at'] . "\n";
        echo "수정: " . $notice['updated_at'] . "\n";
        echo "이미지 경로: " . ($notice['image_path'] ?: 'NULL') . "\n\n";
        
        // 본문 내 img 태그 분석
        preg_match_all('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $notice['content'], $contentImages);
        echo "📝 본문 내 이미지 개수: " . count($contentImages[0]) . "\n";
        
        if (!empty($contentImages[0])) {
            echo "본문 이미지들:\n";
            foreach ($contentImages[1] as $i => $imgSrc) {
                echo "  " . ($i+1) . ". " . $imgSrc . "\n";
            }
        }
        
        // Notice 모델을 통한 첨부 이미지 확인
        require_once 'src/models/Notice.php';
        $noticeModel = new Notice();
        $images = $noticeModel->getNoticeImages(17, $notice);
        
        echo "\n📎 첨부 이미지 개수: " . count($images) . "\n";
        if (!empty($images)) {
            echo "첨부 이미지들:\n";
            foreach ($images as $i => $image) {
                echo "  " . ($i+1) . ". " . $image['file_path'] . "\n";
            }
        }
        
        // 중복 검사
        if (!empty($contentImages[1]) && !empty($images)) {
            echo "\n⚠️ 중복 분석:\n";
            foreach ($contentImages[1] as $contentImg) {
                foreach ($images as $attachedImg) {
                    if (strpos($contentImg, basename($attachedImg['file_path'])) !== false) {
                        echo "❌ 중복 발견: " . basename($attachedImg['file_path']) . "\n";
                        echo "   - 본문: " . $contentImg . "\n";
                        echo "   - 첨부: " . $attachedImg['file_path'] . "\n";
                    }
                }
            }
        }
        
    } else {
        echo "❌ 공지사항 #17을 찾을 수 없습니다.\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>