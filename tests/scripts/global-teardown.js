/**
 * Playwright 글로벌 티어다운 스크립트
 * 테스트 실행 후에 정리 작업을 수행합니다.
 */

module.exports = async (config) => {
  console.log('🧹 글로벌 티어다운 시작...');

  // 데이터베이스 정리
  try {
    const mysql = require('mysql2/promise');

    const connection = await mysql.createConnection({
      host: '127.0.0.1',
      user: 'root',
      password: 'Dnlszkem1!',
      database: 'TOPMKT'
    });

    console.log('✅ 데이터베이스 연결 성공');

    // 테스트 데이터 정리
    await connection.execute('DELETE FROM user_sessions WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
    await connection.execute('DELETE FROM user_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    await connection.execute('DELETE FROM verification_codes WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');

    await connection.end();
    console.log('✅ 데이터베이스 정리 완료');

  } catch (error) {
    console.log('⚠️ 데이터베이스 정리 실패:', error.message);
  }

  // 캐시 정리
  try {
    const fs = require('fs');

    const cacheDir = '/tmp/topmkt_cache';
    if (fs.existsSync(cacheDir)) {
      fs.rmSync(cacheDir, { recursive: true, force: true });
      console.log('✅ 캐시 디렉토리 정리 완료');
    }
  } catch (error) {
    console.log('⚠️ 캐시 정리 실패:', error.message);
  }

  // 테스트 스크린샷 정리 (오래된 것들)
  try {
    const fs = require('fs');
    const path = require('path');

    const screenshotsDir = path.join(__dirname, '../../tests/reports/screenshots');
    if (fs.existsSync(screenshotsDir)) {
      const files = fs.readdirSync(screenshotsDir);
      const oldFiles = files.filter(file => {
        const filePath = path.join(screenshotsDir, file);
        const stats = fs.statSync(filePath);
        const age = Date.now() - stats.mtime.getTime();
        return age > 24 * 60 * 60 * 1000; // 24시간 이상 된 파일
      });

      oldFiles.forEach(file => {
        fs.unlinkSync(path.join(screenshotsDir, file));
      });

      if (oldFiles.length > 0) {
        console.log('✅ 오래된 스크린샷 ' + oldFiles.length + '개 정리 완료');
      }
    }
  } catch (error) {
    console.log('⚠️ 스크린샷 정리 실패:', error.message);
  }

  console.log('🎯 글로벌 티어다운 완료');
};


