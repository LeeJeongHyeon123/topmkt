<?php
/**
 * 게시글 관련 컨트롤러
 */
namespace App\Controllers;

class PostController {
    /**
     * 게시글 목록 페이지 (메인 페이지)
     */
    public function index() {
        // 게시글 목록 가져오기 (실제 구현에서는 DB 조회)
        $posts = [
            [
                'id' => 1,
                'title' => '탑마케팅 공식 출범',
                'content' => '탑마케팅이 정식 서비스를 시작합니다.',
                'author' => '관리자',
                'created_at' => '2025-06-01 09:00:00'
            ],
            [
                'id' => 2,
                'title' => '마케팅 노하우 공유',
                'content' => '효과적인 마케팅 전략을 소개합니다.',
                'author' => '김마케터',
                'created_at' => '2025-06-01 10:30:00'
            ]
        ];
        
        // 뷰 표시
        include SRC_PATH . '/views/post/list.php';
    }
    
    /**
     * 게시글 상세 페이지
     */
    public function show() {
        // URL에서 ID 추출
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        preg_match('/\/posts\/(\d+)/', $uri, $matches);
        $id = $matches[1] ?? null;
        
        if (!$id) {
            header('HTTP/1.1 404 Not Found');
            include SRC_PATH . '/views/templates/404.php';
            return;
        }
        
        // 게시글 정보 가져오기 (실제 구현에서는 DB 조회)
        $post = [
            'id' => $id,
            'title' => '게시글 ' . $id,
            'content' => '게시글 ' . $id . '의 내용입니다.',
            'author' => '작성자 ' . $id,
            'created_at' => '2025-06-01 09:00:00'
        ];
        
        // 댓글 목록 가져오기 (실제 구현에서는 DB 조회)
        $comments = [
            [
                'id' => 1,
                'content' => '좋은 게시글입니다.',
                'author' => '댓글 작성자 1',
                'created_at' => '2025-06-01 10:00:00'
            ],
            [
                'id' => 2,
                'content' => '감사합니다.',
                'author' => '댓글 작성자 2',
                'created_at' => '2025-06-01 11:00:00'
            ]
        ];
        
        // 뷰 표시
        include SRC_PATH . '/views/post/detail.php';
    }
    
    /**
     * 게시글 작성 처리
     */
    public function create() {
        // 로그인 확인
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            return;
        }
        
        // POST 요청 확인
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            return;
        }
        
        // 입력 데이터 검증
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        
        if (empty($title) || empty($content)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '제목과 내용을 모두 입력해주세요.']);
            return;
        }
        
        // 게시글 저장 (실제 구현에서는 DB에 저장)
        // ...
        
        // 응답 처리
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'message' => '게시글이 작성되었습니다.',
                'post_id' => 3 // 실제 구현에서는 DB에서 생성된 ID 반환
            ]);
        } else {
            header('Location: /posts/3'); // 실제 구현에서는 생성된 게시글로 이동
        }
    }
    
    /**
     * 게시글 수정 처리
     */
    public function update() {
        // 로그인 확인
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            return;
        }
        
        // URL에서 ID 추출
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        preg_match('/\/posts\/(\d+)/', $uri, $matches);
        $id = $matches[1] ?? null;
        
        if (!$id) {
            header('HTTP/1.1 404 Not Found');
            return;
        }
        
        // 게시글 작성자 확인 (실제 구현에서는 DB에서 확인)
        // 임시로 항상 본인 게시글로 가정
        
        // PUT 요청 데이터 파싱
        parse_str(file_get_contents("php://input"), $putData);
        
        // 입력 데이터 검증
        $title = filter_var($putData['title'] ?? '', FILTER_SANITIZE_STRING);
        $content = filter_var($putData['content'] ?? '', FILTER_SANITIZE_STRING);
        
        if (empty($title) || empty($content)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '제목과 내용을 모두 입력해주세요.']);
            return;
        }
        
        // 게시글 업데이트 (실제 구현에서는 DB 업데이트)
        // ...
        
        // 응답 처리
        header('Content-Type: application/json');
        echo json_encode(['message' => '게시글이 수정되었습니다.']);
    }
    
    /**
     * 게시글 삭제 처리
     */
    public function delete() {
        // 로그인 확인
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            return;
        }
        
        // URL에서 ID 추출
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        preg_match('/\/posts\/(\d+)/', $uri, $matches);
        $id = $matches[1] ?? null;
        
        if (!$id) {
            header('HTTP/1.1 404 Not Found');
            return;
        }
        
        // 게시글 작성자 확인 (실제 구현에서는 DB에서 확인)
        // 임시로 항상 본인 게시글로 가정
        
        // 게시글 삭제 (실제 구현에서는 DB에서 삭제)
        // ...
        
        // 응답 처리
        header('HTTP/1.1 204 No Content');
    }
} 