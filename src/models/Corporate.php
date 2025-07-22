<?php
/**
 * Corporate 모델 클래스
 * 기업회원 인증 및 관리 기능
 */

require_once SRC_PATH . '/config/database.php';

class Corporate {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 기업 인증 신청
     */
    public function submitApplication($userId, $companyData) {
        try {
            $this->db->beginTransaction();
            
            // 이미 신청한 내역이 있는지 확인
            $existingApplication = $this->getCompanyProfile($userId);
            if ($existingApplication) {
                throw new Exception('이미 기업 인증을 신청하셨습니다.');
            }
            
            // 사업자번호 중복 체크
            if ($this->checkBusinessNumberExists($companyData['business_number'], $userId)) {
                throw new Exception('이미 등록된 사업자번호입니다.');
            }
            
            // 기업 프로필 생성
            $sql = "INSERT INTO company_profiles (
                user_id, company_name, business_number, representative_name, 
                representative_phone, company_address, business_registration_file, 
                is_overseas, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $result = $this->db->execute($sql, [
                $userId,
                $companyData['company_name'],
                $companyData['business_number'],
                $companyData['representative_name'],
                $companyData['representative_phone'],
                $companyData['company_address'],
                $companyData['business_registration_file'],
                $companyData['is_overseas'] ?? 0
            ]);
            
            if (!$result) {
                throw new Exception('기업 정보 저장에 실패했습니다.');
            }
            
            // 사용자 상태 업데이트
            $this->updateUserCorpStatus($userId, 'pending');
            
            // 이력 기록
            $this->addApplicationHistory($userId, 'apply', null, $companyData, null, $userId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 기업 정보 수정
     */
    public function updateCompanyInfo($userId, $data) {
        try {
            $this->db->beginTransaction();
            
            // 기존 정보 조회
            $oldData = $this->getCompanyProfile($userId);
            if (!$oldData) {
                throw new Exception('기업 정보를 찾을 수 없습니다.');
            }
            
            // 수정 가능한 필드만 업데이트
            $sql = "UPDATE company_profiles SET 
                company_name = ?,
                representative_name = ?,
                representative_phone = ?,
                company_address = ?,
                updated_at = NOW()
                WHERE user_id = ?";
            
            $result = $this->db->execute($sql, [
                $data['company_name'],
                $data['representative_name'],
                $data['representative_phone'],
                $data['company_address'],
                $userId
            ]);
            
            if (!$result) {
                throw new Exception('기업 정보 수정에 실패했습니다.');
            }
            
            // 이력 기록
            $this->addApplicationHistory($userId, 'modify', $oldData, $data, null, $userId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 재신청 처리
     */
    public function reapply($userId, $companyData) {
        try {
            $this->db->beginTransaction();
            
            // 기존 정보 조회
            $existingProfile = $this->getCompanyProfile($userId);
            if (!$existingProfile) {
                throw new Exception('기존 신청 정보를 찾을 수 없습니다.');
            }
            
            if ($existingProfile['status'] !== 'rejected') {
                throw new Exception('거절된 신청만 재신청할 수 있습니다.');
            }
            
            // 사업자번호 중복 체크 (본인 제외)
            if ($this->checkBusinessNumberExists($companyData['business_number'], $userId)) {
                throw new Exception('이미 등록된 사업자번호입니다.');
            }
            
            // 기업 프로필 업데이트
            $sql = "UPDATE company_profiles SET 
                company_name = ?,
                business_number = ?,
                representative_name = ?,
                representative_phone = ?,
                company_address = ?,
                business_registration_file = ?,
                is_overseas = ?,
                status = 'pending',
                admin_notes = NULL,
                processed_by = NULL,
                processed_at = NULL,
                updated_at = NOW()
                WHERE user_id = ?";
            
            $result = $this->db->execute($sql, [
                $companyData['company_name'],
                $companyData['business_number'],
                $companyData['representative_name'],
                $companyData['representative_phone'],
                $companyData['company_address'],
                $companyData['business_registration_file'],
                $companyData['is_overseas'] ?? 0,
                $userId
            ]);
            
            if (!$result) {
                throw new Exception('재신청 처리에 실패했습니다.');
            }
            
            // 사용자 상태 업데이트
            $this->updateUserCorpStatus($userId, 'pending');
            
            // 이력 기록
            $this->addApplicationHistory($userId, 'reapply', $existingProfile, $companyData, null, $userId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 기업 프로필 조회
     */
    public function getCompanyProfile($userId) {
        $sql = "SELECT cp.*, u.nickname as user_nickname,
                       admin.nickname as processed_by_name
                FROM company_profiles cp
                JOIN users u ON cp.user_id = u.id
                LEFT JOIN users admin ON cp.processed_by = admin.id
                WHERE cp.user_id = ?";
        
        return $this->db->fetch($sql, [$userId]);
    }
    
    /**
     * 신청 상태 조회
     */
    public function getApplicationStatus($userId) {
        $profile = $this->getCompanyProfile($userId);
        if (!$profile) {
            return ['status' => 'none', 'profile' => null];
        }
        
        return [
            'status' => $profile['status'],
            'profile' => $profile
        ];
    }
    
    /**
     * 기업회원 권한 확인
     */
    public function hasCorpPermission($userId) {
        $sql = "SELECT corp_status FROM users WHERE id = ? AND corp_status = 'approved'";
        $result = $this->db->fetch($sql, [$userId]);
        return $result !== false;
    }
    
    /**
     * 사업자번호 중복 체크
     */
    public function checkBusinessNumberExists($businessNumber, $excludeUserId = null) {
        $sql = "SELECT id FROM company_profiles WHERE business_number = ?";
        $params = [$businessNumber];
        
        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result !== false;
    }
    
    /**
     * 사용자 기업 상태 업데이트
     */
    private function updateUserCorpStatus($userId, $status) {
        $approvedAt = ($status === 'approved') ? 'NOW()' : 'NULL';
        
        $sql = "UPDATE users SET 
                corp_status = ?, 
                corp_approved_at = {$approvedAt}
                WHERE id = ?";
        
        return $this->db->execute($sql, [$status, $userId]) > 0;
    }
    
    /**
     * 신청 이력 기록
     */
    private function addApplicationHistory($userId, $actionType, $oldData, $newData, $adminNotes, $createdBy) {
        $sql = "INSERT INTO company_application_history (
            user_id, action_type, old_data, new_data, admin_notes, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        return $this->db->execute($sql, [
            $userId,
            $actionType,
            $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            $adminNotes,
            $createdBy
        ]);
    }
    
    /**
     * 관리자용: 승인 처리
     */
    public function approveApplication($userId, $adminId, $adminNotes = null) {
        try {
            $this->db->beginTransaction();
            
            // 기업 프로필 업데이트
            $sql = "UPDATE company_profiles SET 
                status = 'approved',
                admin_notes = ?,
                processed_by = ?,
                processed_at = NOW()
                WHERE user_id = ?";
            
            $result = $this->db->execute($sql, [$adminNotes, $adminId, $userId]);
            
            if (!$result) {
                throw new Exception('승인 처리에 실패했습니다.');
            }
            
            // 사용자 상태 업데이트 및 권한 부여
            $this->updateUserCorpStatus($userId, 'approved');
            $this->updateUserRole($userId, 'ROLE_CORP');
            
            // 이력 기록
            $this->addApplicationHistory($userId, 'approve', null, null, $adminNotes, $adminId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 관리자용: 거절 처리
     */
    public function rejectApplication($userId, $adminId, $adminNotes) {
        try {
            $this->db->beginTransaction();
            
            // 기업 프로필 업데이트
            $sql = "UPDATE company_profiles SET 
                status = 'rejected',
                admin_notes = ?,
                processed_by = ?,
                processed_at = NOW()
                WHERE user_id = ?";
            
            $result = $this->db->execute($sql, [$adminNotes, $adminId, $userId]);
            
            if (!$result) {
                throw new Exception('거절 처리에 실패했습니다.');
            }
            
            // 사용자 상태 업데이트
            $this->updateUserCorpStatus($userId, 'rejected');
            
            // 이력 기록
            $this->addApplicationHistory($userId, 'reject', null, null, $adminNotes, $adminId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 사용자 역할 업데이트
     */
    private function updateUserRole($userId, $role) {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        return $this->db->execute($sql, [$role, $userId]) > 0;
    }
    
    /**
     * 관리자용: 신청 목록 조회
     */
    public function getApplicationList($status = null, $limit = 20, $offset = 0) {
        $sql = "SELECT cp.*, u.nickname, u.email, u.created_at as user_created_at
                FROM company_profiles cp
                JOIN users u ON cp.user_id = u.id";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE cp.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY cp.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * 신청 이력 조회
     */
    public function getApplicationHistory($userId, $limit = 10) {
        $sql = "SELECT h.*, u.nickname as created_by_name
                FROM company_application_history h
                JOIN users u ON h.created_by = u.id
                WHERE h.user_id = ?
                ORDER BY h.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
}