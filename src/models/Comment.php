<?php
/**
 * 댓글 모델 클래스
 */

require_once SRC_PATH . '/config/database.php';

class Comment {
    private $db;
    
    /**
     * 생성자
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 게시글의 댓글 목록 조회 (페이지네이션 적용)
     *
     * @param int $postId 게시글 ID
     * @param int $page 페이지 번호 (기본값: 1)
     * @param int $limit 페이지당 댓글 수 (기본값: 20)
     * @return array 댓글 목록
     */
    public function getByPostId($postId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $comments = $this->db->fetchAll("
            SELECT c.*, 
                   u.nickname as author_name, 
                   COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ", [$postId, $limit, $offset]);
        
        // 댓글 내용 정규화
        foreach ($comments as &$comment) {
            $comment['content'] = $this->normalizeContent($comment['content']);
        }
        
        // 최신순이므로 평면 배열로 반환 (view에서 트리 구성)
        return $comments;
    }
    
    /**
     * 게시글의 전체 댓글 목록 조회 (기존 메서드 - 소량 데이터용)
     *
     * @param int $postId 게시글 ID
     * @return array 댓글 목록
     */
    public function getAllByPostId($postId) {
        $sql = "
            SELECT c.*, 
                   u.nickname as author_name, 
                   COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
        ";
        
        $comments = $this->db->fetchAll($sql, [$postId]);
        
        // 댓글 내용 정규화
        foreach ($comments as &$comment) {
            $comment['content'] = $this->normalizeContent($comment['content']);
        }
        
        return $comments;
    }
    
    /**
     * 댓글을 계층 구조로 구성
     *
     * @param array $comments 평면 댓글 배열
     * @param int|null $parentId 부모 댓글 ID
     * @return array 계층화된 댓글 배열
     */
    private function buildCommentTree($comments, $parentId = null) {
        $tree = [];
        
        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parentId) {
                $comment['replies'] = $this->buildCommentTree($comments, $comment['id']);
                $tree[] = $comment;
            }
        }
        
        return $tree;
    }
    
    
    /**
     * 댓글 생성
     *
     * @param array $data 댓글 데이터
     * @return int 생성된 댓글 ID
     */
    public function create($data) {
        $sql = "
            INSERT INTO comments (post_id, user_id, parent_id, content, status, created_at)
            VALUES (?, ?, ?, ?, 'active', NOW())
        ";
        
        $this->db->execute($sql, [
            $data['post_id'],
            $data['user_id'],
            $data['parent_id'] ?? null,
            $data['content']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 댓글 수정
     *
     * @param int $id 댓글 ID
     * @param array $data 수정할 데이터
     * @return bool 성공 여부
     */
    public function update($id, $data) {
        $sql = "
            UPDATE comments 
            SET content = ?, updated_at = NOW()
            WHERE id = ? AND status = 'active'
        ";
        
        return $this->db->execute($sql, [
            $data['content'],
            $id
        ]) > 0;
    }
    
    /**
     * 댓글 삭제 (soft delete)
     *
     * @param int $id 댓글 ID
     * @return bool 성공 여부
     */
    public function delete($id) {
        $sql = "
            UPDATE comments 
            SET status = 'deleted', updated_at = NOW()
            WHERE id = ?
        ";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    /**
     * 댓글 완전 삭제
     *
     * @param int $id 댓글 ID
     * @return bool 성공 여부
     */
    public function hardDelete($id) {
        $sql = "DELETE FROM comments WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    /**
     * 게시글별 댓글 수 조회
     *
     * @param int $postId 게시글 ID
     * @return int 댓글 수
     */
    public function getCountByPostId($postId) {
        $sql = "
            SELECT COUNT(*) as count FROM comments
            WHERE post_id = ? AND status = 'active'
        ";
        
        $result = $this->db->fetch($sql, [$postId]);
        return $result ? array_values($result)[0] : 0;
    }
    
    /**
     * 특정 댓글의 대댓글 조회
     *
     * @param int $parentId 부모 댓글 ID
     * @return array 대댓글 목록
     */
    public function getReplies($parentId) {
        $sql = "
            SELECT c.*, u.nickname as author_name
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.parent_id = ? AND c.status = 'active'
            ORDER BY c.created_at ASC
        ";
        
        $replies = $this->db->fetchAll($sql, [$parentId]);
        
        // 댓글 내용 정규화
        foreach ($replies as &$reply) {
            $reply['content'] = $this->normalizeContent($reply['content']);
        }
        
        return $replies;
    }
    
    /**
     * ID로 댓글 조회
     *
     * @param int $id 댓글 ID
     * @return array|false 댓글 정보 또는 false
     */
    public function getById($id) {
        $sql = "
            SELECT c.*, u.nickname as author_name
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ? AND c.status = 'active'
        ";
        
        $comment = $this->db->fetch($sql, [$id]);
        
        if ($comment) {
            $comment['content'] = $this->normalizeContent($comment['content']);
        }
        
        return $comment;
    }
    
    /**
     * 사용자의 댓글인지 확인
     *
     * @param int $commentId 댓글 ID
     * @param int $userId 사용자 ID
     * @return bool
     */
    public function isOwner($commentId, $userId) {
        $sql = "
            SELECT COUNT(*) as count FROM comments
            WHERE id = ? AND user_id = ? AND status = 'active'
        ";
        
        $result = $this->db->fetch($sql, [$commentId, $userId]);
        return $result && array_values($result)[0] > 0;
    }
    
    /**
     * 댓글 내용 정규화
     *
     * @param string $content 원본 내용
     * @return string 정규화된 내용
     */
    private function normalizeContent($content) {
        // 제어 문자 제거 (단, 탭과 개행은 유지)
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
        
        // 윈도우/맥 줄바꿈을 유닉스 스타일로 통일
        $content = preg_replace('/\r\n|\r/', "\n", $content);
        
        // 연속된 줄바꿈을 최대 2개로 제한
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // 앞뒤 공백 제거
        $content = trim($content);
        
        return $content;
    }
} 