/**
 * Playwright 글로벌 셋업 스크립트
 * 테스트 실행 전에 필요한 환경 설정을 수행합니다.
 */

const { chromium } = require('playwright');

module.exports = async (config) => {
  console.log('🔧 글로벌 셋업 시작...');

  // 데이터베이스 초기화
  try {
    const mysql = require('mysql2/promise');

    const connection = await mysql.createConnection({
      host: '127.0.0.1',
      user: 'root',
      password: 'Dnlszkem1!',
      database: 'TOPMKT'
    });

    console.log('✅ 데이터베이스 연결 성공');

    // 테스트용 데이터 정리
    await connection.execute('DELETE FROM user_sessions WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
    await connection.execute('DELETE FROM user_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');

    await connection.end();
    console.log('✅ 데이터베이스 정리 완료');

  } catch (error) {
    console.log('⚠️ 데이터베이스 연결 실패:', error.message);
  }

  // 캐시 정리
  try {
    const fs = require('fs');
    const path = require('path');

    const cacheDir = '/tmp/topmkt_cache';
    if (fs.existsSync(cacheDir)) {
      fs.rmSync(cacheDir, { recursive: true, force: true });
      console.log('✅ 캐시 디렉토리 정리 완료');
    }
  } catch (error) {
    console.log('⚠️ 캐시 정리 실패:', error.message);
  }

  console.log('🎯 글로벌 셋업 완료');
};


