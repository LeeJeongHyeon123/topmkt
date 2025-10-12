<?php
/**
 * 통합 테스트 실행 스크립트
 * PHP 단위 테스트와 Playwright E2E 테스트를 실행합니다.
 */

class TestRunner {
    private $testResults = [];
    private $startTime;
    private $reportDir = __DIR__ . '/../reports';

    public function __construct() {
        $this->startTime = microtime(true);
        $this->ensureReportDir();
    }

    private function ensureReportDir() {
        if (!is_dir($this->reportDir)) {
            mkdir($this->reportDir, 0755, true);
        }
    }

    public function runAllTests() {
        echo "🧪 탑마케팅 통합 테스트 시작...\n";
        echo "=====================================\n\n";

        // 1. PHP 단위 테스트 실행
        $this->runPhpUnitTests();

        // 2. Playwright E2E 테스트 실행
        $this->runPlaywrightTests();

        // 3. 종합 리포트 생성
        $this->generateReport();

        $totalTime = microtime(true) - $this->startTime;
        echo "\n✅ 모든 테스트 완료! 총 소요시간: " . round($totalTime, 2) . "초\n";
    }

    private function runPhpUnitTests() {
        echo "🔧 PHP 단위 테스트 실행 중...\n";
        echo "------------------------------\n";

        $phpUnitPath = __DIR__ . '/../../vendor/bin/phpunit';
        if (!file_exists($phpUnitPath)) {
            echo "⚠️ PHPUnit이 설치되지 않았습니다.\n";
            return;
        }

        // PHPUnit 설정 파일 확인
        $phpunitConfig = __DIR__ . '/../../phpunit.xml';
        if (!file_exists($phpunitConfig)) {
            $phpunitConfig = __DIR__ . '/../../phpunit.xml.dist';
        }

        $command = "cd " . __DIR__ . "/../.. && php " . $phpUnitPath . " --configuration=" . $phpunitConfig . " 2>&1";
        $output = shell_exec($command);

        $this->testResults['phpunit'] = [
            'output' => $output,
            'success' => strpos($output, 'OK (') !== false || strpos($output, 'Tests: ') !== false
        ];

        echo $output;
        echo "✅ PHP 단위 테스트 완료\n\n";
    }

    private function runPlaywrightTests() {
        echo "🎭 Playwright E2E 테스트 실행 중...\n";
        echo "----------------------------------\n";

        $playwrightConfig = __DIR__ . '/../../playwright.config.js';
        if (!file_exists($playwrightConfig)) {
            echo "⚠️ Playwright 설정 파일이 없습니다.\n";
            return;
        }

        $command = "cd " . __DIR__ . "/../.. && npx playwright test --config=" . $playwrightConfig . " --reporter=line 2>&1";
        $output = shell_exec($command);

        $this->testResults['playwright'] = [
            'output' => $output,
            'success' => !preg_match('/failed|error/i', $output)
        ];

        echo $output;
        echo "✅ Playwright E2E 테스트 완료\n\n";
    }

    private function generateReport() {
        $reportFile = $this->reportDir . '/test-report-' . date('Y-m-d-H-i-s') . '.json';

        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'duration' => microtime(true) - $this->startTime,
            'results' => $this->testResults,
            'summary' => $this->generateSummary()
        ];

        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "📊 테스트 리포트 생성: " . basename($reportFile) . "\n";
    }

    private function generateSummary() {
        $summary = [
            'total_tests' => 0,
            'passed' => 0,
            'failed' => 0,
            'status' => 'success'
        ];

        foreach ($this->testResults as $testType => $result) {
            if ($result['success']) {
                $summary['passed']++;
            } else {
                $summary['failed']++;
                $summary['status'] = 'failed';
            }
            $summary['total_tests']++;
        }

        return $summary;
    }
}

// 메인 실행
if (php_sapi_name() === 'cli') {
    $runner = new TestRunner();
    $runner->runAllTests();
}
?>
