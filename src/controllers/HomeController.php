<?php
/**
 * 탑마케팅 홈 컨트롤러
 * 메인 페이지의 데이터를 처리하고 뷰를 렌더링합니다.
 */

class HomeController
{
    /**
     * 메인 페이지를 렌더링합니다.
     */
    public function index()
    {
        // 메인 페이지 데이터 준비 (향후 DB에서 가져올 데이터)
        $page_data = [
            'recent_posts' => [],
            'upcoming_events' => [],
            'upcoming_lectures' => [],
            'active_members' => [],
            'community_stats' => [
                'total_members' => 10247,
                'total_posts' => 15893,
                'monthly_events' => 24,
                'success_stories' => 156
            ]
        ];
        
        // 메인 페이지 템플릿 로드
        include SRC_PATH . '/views/home/index.php';
    }
} 