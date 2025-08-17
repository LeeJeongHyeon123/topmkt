<?php
/**
 * 🚨 CRITICAL: 공지사항 #18 잘못된 이미지 표시 버그 긴급 진단
 */

// 경로 상수 정의
define('ROOT_PATH', dirname(__FILE__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once 'src/config/database.php';

try {
    $db = Database::getInstance();
    
    echo "🚨 CRITICAL BUG 분석: 공지사항 #18 잘못된 이미지 표시\n";
    echo "=".str_repeat("=", 70)."\n\n";
    
    // 공지사항 #17, #18 정보 조회
    $notices = $db->fetchAll("SELECT id, title, content, image_path, created_at, updated_at FROM notices WHERE id IN (17, 18) ORDER BY id");
    
    foreach ($notices as $notice) {
        echo "📋 공지사항 #{$notice['id']}\n";
        echo "   제목: " . $notice['title'] . "\n";
        echo "   생성: " . $notice['created_at'] . "\n";
        echo "   수정: " . $notice['updated_at'] . "\n";
        echo "   이미지 경로: " . ($notice['image_path'] ?: 'NULL') . "\n";
        
        // 본문 내 이미지 분석
        preg_match_all('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $notice['content'], $contentImages);
        echo "   본문 이미지: " . count($contentImages[0]) . "개\n";
        
        if (!empty($contentImages[1])) {
            foreach ($contentImages[1] as $i => $imgSrc) {
                $filename = basename($imgSrc);
                echo "     " . ($i+1) . ". " . $filename . "\n";
                echo "        전체 경로: " . $imgSrc . "\n";
            }
        }
        
        // Notice 모델을 통한 첨부 이미지 검색
        require_once 'src/models/Notice.php';
        $noticeModel = new Notice();
        $images = $noticeModel->getNoticeImages($notice['id'], $notice);
        
        echo "   첨부 이미지: " . count($images) . "개\n";
        if (!empty($images)) {
            foreach ($images as $i => $image) {
                echo "     " . ($i+1) . ". " . basename($image['file_path']) . "\n";
                echo "        전체 경로: " . $image['file_path'] . "\n";
            }
        }
        
        echo "\n";
    }
    
    // 🚨 Critical: 업로드 디렉토리 파일 목록 확인
    echo "📁 업로드 디렉토리 파일 분석\n";
    echo "-".str_repeat("-", 50)."\n";
    
    $uploadDirs = [
        'notices' => '/var/www/html/topmkt/public/assets/uploads/notices/2025/08',
        'notices-content' => '/var/www/html/topmkt/public/assets/uploads/notices-content/2025/08'
    ];
    
    foreach ($uploadDirs as $type => $dir) {
        echo "📂 $type 디렉토리: $dir\n";
        
        if (is_dir($dir)) {
            $files = scandir($dir);
            $imageFiles = array_filter($files, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            
            echo "   파일 개수: " . count($imageFiles) . "\n";
            
            foreach ($imageFiles as $file) {
                $filePath = $dir . '/' . $file;
                $fileTime = date('Y-m-d H:i:s', filemtime($filePath));
                $fileSize = number_format(filesize($filePath));
                
                // 타임스탬프 추출
                if (preg_match('/^(\d{14})_/', $file, $matches)) {
                    $timestamp = $matches[1];
                    $formattedTime = DateTime::createFromFormat('YmdHis', $timestamp)->format('Y-m-d H:i:s');
                    echo "   • $file\n";
                    echo "     생성시간: $formattedTime (파일: $fileTime)\n";
                    echo "     크기: {$fileSize} bytes\n";
                }
            }
        } else {
            echo "   ❌ 디렉토리 없음\n";
        }
        echo "\n";
    }
    
    // 🚨 Critical: Notice 모델의 getNoticeImages 로직 디버깅
    echo "🔍 Notice 모델 getNoticeImages 로직 분석\n";
    echo "-".str_repeat("-", 50)."\n";
    
    // #18 공지사항에 대해 상세 분석
    $notice18 = $db->fetch("SELECT * FROM notices WHERE id = 18");
    if ($notice18) {
        echo "🎯 공지사항 #18 상세 분석:\n";
        
        $createdTime = new DateTime($notice18['created_at']);
        $updatedTime = new DateTime($notice18['updated_at']);
        
        echo "   생성시간: " . $createdTime->format('Y-m-d H:i:s') . "\n";
        echo "   수정시간: " . $updatedTime->format('Y-m-d H:i:s') . "\n";
        
        // 시간 범위 계산
        $minTime = clone $createdTime;
        $minTime->modify('-5 minutes');
        $maxTime = clone $updatedTime;
        $maxTime->modify('+30 minutes');
        
        echo "   검색 범위: " . $minTime->format('Y-m-d H:i:s') . " ~ " . $maxTime->format('Y-m-d H:i:s') . "\n";
        
        // 해당 범위의 모든 파일 확인
        $dateDir = $createdTime->format('Y/m');
        $uploadPath = '/var/www/html/topmkt/public/assets/uploads/notices/' . $dateDir;
        
        if (is_dir($uploadPath)) {
            $files = scandir($uploadPath);
            echo "\n   시간 범위 내 파일들:\n";
            
            foreach ($files as $file) {
                if (preg_match('/^(\d{14})_/', $file, $matches)) {
                    $fileTimestamp = $matches[1];
                    $fileTime = new DateTime($fileTimestamp);
                    
                    if ($fileTime >= $minTime && $fileTime <= $maxTime) {
                        echo "     ✅ $file (" . $fileTime->format('Y-m-d H:i:s') . ")\n";
                    } else {
                        echo "     ❌ $file (" . $fileTime->format('Y-m-d H:i:s') . ") - 범위 밖\n";
                    }
                }
            }
        }
    }
    
    echo "\n🚨 결론: 공지사항 #18이 #17의 이미지를 잘못 가져오는 원인 분석 필요\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>