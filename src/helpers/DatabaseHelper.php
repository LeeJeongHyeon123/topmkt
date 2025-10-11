<?php
/**
 * DatabaseHelper 클래스
 * 데이터베이스 관련 공통 기능을 제공합니다.
 */

require_once SRC_PATH . '/config/database.php';

class DatabaseHelper {

    /**
     * 데이터베이스 인스턴스 반환
     */
    public static function getDB() {
        return Database::getInstance();
    }

    /**
     * 안전한 쿼리 실행 (Prepared Statement)
     */
    public static function query($sql, $params = []) {
        $db = self::getDB();
        return $db->query($sql, $params);
    }

    /**
     * 단일 행 조회
     */
    public static function fetch($sql, $params = []) {
        $db = self::getDB();
        return $db->fetch($sql, $params);
    }

    /**
     * 다중 행 조회
     */
    public static function fetchAll($sql, $params = []) {
        $db = self::getDB();
        return $db->fetchAll($sql, $params);
    }

    /**
     * INSERT 실행 후 마지막 ID 반환
     */
    public static function insert($sql, $params = []) {
        $db = self::getDB();
        return $db->insert($sql, $params);
    }

    /**
     * UPDATE/DELETE 실행 후 영향받은 행 수 반환
     */
    public static function execute($sql, $params = []) {
        $db = self::getDB();
        return $db->execute($sql, $params);
    }

    /**
     * 트랜잭션 시작
     */
    public static function beginTransaction() {
        $db = self::getDB();
        return $db->beginTransaction();
    }

    /**
     * 트랜잭션 커밋
     */
    public static function commit() {
        $db = self::getDB();
        return $db->commit();
    }

    /**
     * 트랜잭션 롤백
     */
    public static function rollback() {
        $db = self::getDB();
        return $db->rollback();
    }
}
?>
