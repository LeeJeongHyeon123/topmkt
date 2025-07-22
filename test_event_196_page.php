<?php
/**
 * 이벤트 ID 196 상세 페이지 기능 테스트
 */

require_once 'src/config/database.php';
require_once 'src/middlewares/AuthMiddleware.php';

echo "<!DOCTYPE html>";
echo "<html lang='ko'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>이벤트 196 상세 페이지 테스트</title>";
echo "<link rel='stylesheet' href='/assets/css/main.css'>";
echo "<script src='https://openapi.map.naver.com/openapi/v3/maps.js?ncpClientId=YOUR_CLIENT_ID'></script>";
echo "</head>";
echo "<body>";

try {
    $db = Database::getInstance();
    $event = $db->fetch('SELECT * FROM lectures WHERE id = ?', [196]);
    
    if (!$event) {
        echo "<h1>이벤트를 찾을 수 없습니다.</h1>";
        exit;
    }
    
    $images = $db->fetchAll('SELECT * FROM event_images WHERE event_id = ? ORDER BY sort_order', [196]);
    
    echo "<div style='max-width: 1200px; margin: 0 auto; padding: 20px;'>";
    
    // 이벤트 헤더
    echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 12px; margin-bottom: 20px;'>";
    echo "<h1 style='font-size: 2.5rem; margin-bottom: 10px;'>" . htmlspecialchars($event['title']) . "</h1>";
    echo "<p style='font-size: 1.1rem; margin-bottom: 20px;'>카테고리: " . ucfirst($event['category']) . "</p>";
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;'>";
    echo "<div><i class='fas fa-calendar'></i> " . $event['start_date'] . " ~ " . $event['end_date'] . "</div>";
    echo "<div><i class='fas fa-clock'></i> " . $event['start_time'] . " ~ " . $event['end_time'] . "</div>";
    echo "<div><i class='fas fa-map-marker-alt'></i> " . htmlspecialchars($event['venue_name']) . "</div>";
    echo "<div><i class='fas fa-users'></i> " . $event['current_participants'] . "/" . $event['max_participants'] . "명</div>";
    echo "<div><i class='fas fa-won'></i> " . number_format($event['registration_fee']) . "원</div>";
    echo "</div>";
    echo "</div>";
    
    // 메인 컨텐츠
    echo "<div style='display: grid; grid-template-columns: 2fr 1fr; gap: 30px;'>";
    
    // 왼쪽 컨텐츠
    echo "<div>";
    
    // 상세 설명
    echo "<div style='background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin-bottom: 20px;'>상세 설명</h2>";
    echo "<div>" . $event['description'] . "</div>";
    echo "</div>";
    
    // 이미지 갤러리
    if (count($images) > 0) {
        echo "<div style='background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<h2 style='margin-bottom: 20px;'>이미지 갤러리</h2>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>";
        
        foreach ($images as $image) {
            echo "<div style='position: relative; cursor: pointer;' onclick='openImageModal(\"" . $image['image_path'] . "\")'>";
            echo "<img src='" . $image['image_path'] . "' alt='" . htmlspecialchars($image['alt_text']) . "' ";
            echo "style='width: 100%; height: 150px; object-fit: cover; border-radius: 8px; transition: transform 0.3s;' ";
            echo "onmouseover='this.style.transform=\"scale(1.05)\"' onmouseout='this.style.transform=\"scale(1)\"'>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }
    
    // 강사 정보
    if (!empty($event['instructor_name'])) {
        echo "<div style='background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<h2 style='margin-bottom: 20px;'>강사 정보</h2>";
        echo "<div style='display: flex; align-items: center; gap: 20px;'>";
        
        if (!empty($event['instructor_image'])) {
            echo "<img src='" . $event['instructor_image'] . "' alt='강사 사진' ";
            echo "style='width: 80px; height: 80px; border-radius: 50%; object-fit: cover;'>";
        }
        
        echo "<div>";
        echo "<h3 style='margin-bottom: 10px;'>" . htmlspecialchars($event['instructor_name']) . "</h3>";
        echo "<p>" . htmlspecialchars($event['instructor_info']) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    
    // YouTube 비디오
    if (!empty($event['youtube_video'])) {
        $videoId = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/', $event['youtube_video'], $matches)) {
            $videoId = $matches[1];
        }
        
        if ($videoId) {
            echo "<div style='background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
            echo "<h2 style='margin-bottom: 20px;'>관련 영상</h2>";
            echo "<div style='position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;'>";
            echo "<iframe src='https://www.youtube.com/embed/{$videoId}' ";
            echo "style='position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 8px;' ";
            echo "allowfullscreen></iframe>";
            echo "</div>";
            echo "</div>";
        }
    }
    
    echo "</div>";
    
    // 오른쪽 사이드바
    echo "<div>";
    
    // 신청 박스
    echo "<div style='background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: sticky; top: 20px;'>";
    echo "<h3 style='margin-bottom: 20px;'>이벤트 신청</h3>";
    
    $now = new DateTime();
    $deadline = new DateTime($event['registration_deadline']);
    $canRegister = $now < $deadline && $event['current_participants'] < $event['max_participants'];
    
    if ($canRegister) {
        echo "<button onclick='registerEvent()' style='width: 100%; padding: 15px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-bottom: 15px;'>";
        echo "신청하기";
        echo "</button>";
    } else {
        echo "<button disabled style='width: 100%; padding: 15px; background: #9ca3af; color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; margin-bottom: 15px;'>";
        echo $now >= $deadline ? "신청 마감" : "정원 마감";
        echo "</button>";
    }
    
    echo "<div style='font-size: 0.9rem; color: #6b7280;'>";
    echo "<p>신청 마감: " . $deadline->format('Y-m-d H:i') . "</p>";
    echo "<p>참가비: " . number_format($event['registration_fee']) . "원</p>";
    echo "<p>정원: " . $event['current_participants'] . "/" . $event['max_participants'] . "명</p>";
    echo "</div>";
    echo "</div>";
    
    // 위치 정보
    if (!empty($event['venue_name']) && $event['location_type'] === 'offline') {
        echo "<div style='background: white; border-radius: 12px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<h3 style='margin-bottom: 20px;'>위치 정보</h3>";
        echo "<p style='font-weight: 600; margin-bottom: 10px;'>" . htmlspecialchars($event['venue_name']) . "</p>";
        echo "<p style='color: #6b7280; margin-bottom: 15px;'>" . htmlspecialchars($event['venue_address']) . "</p>";
        
        // 지도 표시 영역
        echo "<div id='map' style='width: 100%; height: 200px; border-radius: 8px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb;'>";
        echo "<p style='color: #6b7280;'>지도 로딩 중...</p>";
        echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // 이미지 모달
    echo "<div id='imageModal' style='display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8);' onclick='closeImageModal()'>";
    echo "<div style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 90%; max-height: 90%;'>";
    echo "<img id='modalImage' style='width: 100%; height: auto; border-radius: 8px;'>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h1>오류가 발생했습니다</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "</body>";

// JavaScript 추가
echo "<script>";
echo "
function openImageModal(imagePath) {
    document.getElementById('imageModal').style.display = 'block';
    document.getElementById('modalImage').src = imagePath;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

function registerEvent() {
    if (confirm('이벤트에 신청하시겠습니까?')) {
        // 실제 신청 API 호출
        fetch('/api/lectures/196/registration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': document.querySelector('meta[name=\"csrf-token\"]')?.content || ''
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('신청이 완료되었습니다.');
                location.reload();
            } else {
                alert('신청 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('신청 오류:', error);
            alert('신청 중 오류가 발생했습니다.');
        });
    }
}

// 지도 초기화 (좌표가 있는 경우)
";

if (!empty($event['venue_latitude']) && !empty($event['venue_longitude'])) {
    echo "
    function initMap() {
        try {
            var mapOptions = {
                center: new naver.maps.LatLng(" . $event['venue_latitude'] . ", " . $event['venue_longitude'] . "),
                zoom: 16
            };
            
            var map = new naver.maps.Map('map', mapOptions);
            
            var marker = new naver.maps.Marker({
                position: new naver.maps.LatLng(" . $event['venue_latitude'] . ", " . $event['venue_longitude'] . "),
                map: map
            });
            
            var infoWindow = new naver.maps.InfoWindow({
                content: '<div style=\"padding: 10px; font-size: 14px;\"><strong>" . addslashes($event['venue_name']) . "</strong><br>" . addslashes($event['venue_address']) . "</div>'
            });
            
            naver.maps.Event.addListener(marker, 'click', function() {
                if (infoWindow.getMap()) {
                    infoWindow.close();
                } else {
                    infoWindow.open(map, marker);
                }
            });
        } catch (e) {
            document.getElementById('map').innerHTML = '<p style=\"color: #6b7280;\">지도를 불러올 수 없습니다.</p>';
        }
    }
    
    // 페이지 로드 후 지도 초기화
    if (typeof naver !== 'undefined' && naver.maps) {
        initMap();
    } else {
        document.getElementById('map').innerHTML = '<p style=\"color: #6b7280;\">지도 API를 불러올 수 없습니다.</p>';
    }
    ";
}

echo "</script>";
echo "</html>";
?>