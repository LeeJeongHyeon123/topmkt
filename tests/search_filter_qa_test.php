<?php
/**
 * SearchFilter Component QA Test
 * v3.37.0 - 검색/필터 시스템 완전 컴포넌트화
 *
 * Ultra Think 모드 QA 테스트
 */

// 테스트 환경 설정
define('SRC_PATH', dirname(__DIR__) . '/src');
require_once SRC_PATH . '/components/ui/SearchFilter.php';

class SearchFilterQATest
{
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;

    public function run(): void
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║         SearchFilter Component QA Test Suite v3.37.0          ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";

        // 1. Component Creation Tests
        $this->testComponentCreation();

        // 2. HTML Output Tests
        $this->testHTMLOutput();

        // 3. Layout Tests
        $this->testLayouts();

        // 4. Filter Types Tests
        $this->testFilterTypes();

        // 5. JavaScript Mode Tests
        $this->testJavaScriptMode();

        // 6. Collapsible Tests
        $this->testCollapsible();

        // 7. Parameter Preservation Tests
        $this->testParameterPreservation();

        // 8. Edge Cases Tests
        $this->testEdgeCases();

        // 9. Security Tests
        $this->testSecurity();

        // 10. Performance Tests
        $this->testPerformance();

        // Print Results
        $this->printResults();
    }

    private function testComponentCreation(): void
    {
        echo "🧪 Test 1: Component Creation\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 1.1: Basic Creation
        try {
            $html = SearchFilter::create(['action' => '/test']);
            $this->pass("1.1: Basic component creation");
        } catch (Exception $e) {
            $this->fail("1.1: Basic component creation - " . $e->getMessage());
        }

        // Test 1.2: With Filters
        try {
            $html = SearchFilter::create([
                'action' => '/test',
                'filters' => [
                    ['type' => 'select', 'name' => 'filter', 'options' => ['all' => '전체']]
                ]
            ]);
            $this->pass("1.2: Component with filters");
        } catch (Exception $e) {
            $this->fail("1.2: Component with filters - " . $e->getMessage());
        }

        echo "\n";
    }

    private function testHTMLOutput(): void
    {
        echo "🧪 Test 2: HTML Output Validation\n";
        echo "─────────────────────────────────────────────────────────\n";

        $html = SearchFilter::create([
            'action' => '/community',
            'method' => 'GET',
            'filters' => [
                ['type' => 'select', 'name' => 'filter', 'options' => ['all' => '전체', 'title' => '제목만']]
            ],
            'searchInput' => true
        ]);

        // Test 2.1: Form Tag
        if (strpos($html, '<form method="GET"') !== false) {
            $this->pass("2.1: Form tag present");
        } else {
            $this->fail("2.1: Form tag missing");
        }

        // Test 2.2: Action Attribute
        if (strpos($html, 'action="/community"') !== false) {
            $this->pass("2.2: Action attribute correct");
        } else {
            $this->fail("2.2: Action attribute incorrect");
        }

        // Test 2.3: Select Filter
        if (strpos($html, '<select') !== false && strpos($html, 'name="filter"') !== false) {
            $this->pass("2.3: Select filter rendered");
        } else {
            $this->fail("2.3: Select filter missing");
        }

        // Test 2.4: Search Input
        if (strpos($html, 'class="search-filter-search-input"') !== false) {
            $this->pass("2.4: Search input rendered");
        } else {
            $this->fail("2.4: Search input missing");
        }

        // Test 2.5: Submit Button
        if (strpos($html, 'type="submit"') !== false) {
            $this->pass("2.5: Submit button present");
        } else {
            $this->fail("2.5: Submit button missing");
        }

        echo "\n";
    }

    private function testLayouts(): void
    {
        echo "🧪 Test 3: Layout Variations\n";
        echo "─────────────────────────────────────────────────────────\n";

        $testFilter = ['type' => 'select', 'name' => 'test', 'options' => ['a' => 'A']];

        // Test 3.1: Inline Layout
        $html = SearchFilter::create([
            'layout' => 'inline',
            'filters' => [$testFilter]
        ]);
        if (strpos($html, 'layout-inline') !== false) {
            $this->pass("3.1: Inline layout");
        } else {
            $this->fail("3.1: Inline layout");
        }

        // Test 3.2: Grid-2 Layout
        $html = SearchFilter::create([
            'layout' => 'grid-2',
            'filters' => [$testFilter]
        ]);
        if (strpos($html, 'layout-grid-2') !== false) {
            $this->pass("3.2: Grid-2 layout");
        } else {
            $this->fail("3.2: Grid-2 layout");
        }

        // Test 3.3: Grid-3 Layout
        $html = SearchFilter::create([
            'layout' => 'grid-3',
            'filters' => [$testFilter]
        ]);
        if (strpos($html, 'layout-grid-3') !== false) {
            $this->pass("3.3: Grid-3 layout");
        } else {
            $this->fail("3.3: Grid-3 layout");
        }

        // Test 3.4: Grid-4 Layout
        $html = SearchFilter::create([
            'layout' => 'grid-4',
            'filters' => [$testFilter]
        ]);
        if (strpos($html, 'layout-grid-4') !== false) {
            $this->pass("3.4: Grid-4 layout");
        } else {
            $this->fail("3.4: Grid-4 layout");
        }

        echo "\n";
    }

    private function testFilterTypes(): void
    {
        echo "🧪 Test 4: Filter Input Types\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 4.1: Text Input
        $html = SearchFilter::create([
            'filters' => [
                ['type' => 'text', 'name' => 'company', 'placeholder' => 'Enter company']
            ]
        ]);
        if (strpos($html, 'type="text"') !== false && strpos($html, 'name="company"') !== false) {
            $this->pass("4.1: Text input filter");
        } else {
            $this->fail("4.1: Text input filter");
        }

        // Test 4.2: Select Dropdown
        $html = SearchFilter::create([
            'filters' => [
                ['type' => 'select', 'name' => 'status', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']]
            ]
        ]);
        if (strpos($html, '<select') !== false && strpos($html, 'name="status"') !== false) {
            $this->pass("4.2: Select dropdown filter");
        } else {
            $this->fail("4.2: Select dropdown filter");
        }

        // Test 4.3: Date Input
        $html = SearchFilter::create([
            'filters' => [
                ['type' => 'date', 'name' => 'start_date']
            ]
        ]);
        if (strpos($html, 'type="date"') !== false && strpos($html, 'name="start_date"') !== false) {
            $this->pass("4.3: Date input filter");
        } else {
            $this->fail("4.3: Date input filter");
        }

        // Test 4.4: Number Input
        $html = SearchFilter::create([
            'filters' => [
                ['type' => 'number', 'name' => 'age', 'min' => 18, 'max' => 100]
            ]
        ]);
        if (strpos($html, 'type="number"') !== false && strpos($html, 'min="18"') !== false) {
            $this->pass("4.4: Number input filter");
        } else {
            $this->fail("4.4: Number input filter");
        }

        echo "\n";
    }

    private function testJavaScriptMode(): void
    {
        echo "🧪 Test 5: JavaScript Mode\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 5.1: JavaScript Method
        $html = SearchFilter::create([
            'method' => 'JS',
            'onSubmit' => 'applyFilters()'
        ]);
        if (strpos($html, '<form') === false && strpos($html, 'onclick="applyFilters()"') !== false) {
            $this->pass("5.1: JavaScript mode (no form tag)");
        } else {
            $this->fail("5.1: JavaScript mode");
        }

        // Test 5.2: JavaScript Callback
        $html = SearchFilter::create([
            'method' => 'JS',
            'onReset' => 'resetFilters()'
        ]);
        if (strpos($html, 'onclick="resetFilters()"') !== false) {
            $this->pass("5.2: JavaScript reset callback");
        } else {
            $this->fail("5.2: JavaScript reset callback");
        }

        echo "\n";
    }

    private function testCollapsible(): void
    {
        echo "🧪 Test 6: Collapsible Functionality\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 6.1: Collapsible Header
        $html = SearchFilter::create([
            'collapsible' => true,
            'title' => '🔍 필터 및 검색'
        ]);
        if (strpos($html, 'search-filter-header') !== false && strpos($html, '🔍 필터 및 검색') !== false) {
            $this->pass("6.1: Collapsible header rendered");
        } else {
            $this->fail("6.1: Collapsible header missing");
        }

        // Test 6.2: Toggle Button
        if (strpos($html, 'search-filter-toggle') !== false && strpos($html, 'SearchFilter.toggle') !== false) {
            $this->pass("6.2: Toggle button present");
        } else {
            $this->fail("6.2: Toggle button missing");
        }

        // Test 6.3: Collapsed State
        $html = SearchFilter::create([
            'collapsible' => true,
            'collapsed' => true
        ]);
        if (strpos($html, 'display: none;') !== false) {
            $this->pass("6.3: Initial collapsed state");
        } else {
            $this->fail("6.3: Initial collapsed state");
        }

        echo "\n";
    }

    private function testParameterPreservation(): void
    {
        echo "🧪 Test 7: Parameter Preservation\n";
        echo "─────────────────────────────────────────────────────────\n";

        $_GET['page'] = '2';
        $_GET['limit'] = '20';

        $html = SearchFilter::create([
            'preserveParams' => ['page', 'limit']
        ]);

        // Test 7.1: Hidden Input for Page
        if (strpos($html, 'name="page"') !== false && strpos($html, 'value="2"') !== false) {
            $this->pass("7.1: Page parameter preserved");
        } else {
            $this->fail("7.1: Page parameter not preserved");
        }

        // Test 7.2: Hidden Input for Limit
        if (strpos($html, 'name="limit"') !== false && strpos($html, 'value="20"') !== false) {
            $this->pass("7.2: Limit parameter preserved");
        } else {
            $this->fail("7.2: Limit parameter not preserved");
        }

        unset($_GET['page'], $_GET['limit']);

        echo "\n";
    }

    private function testEdgeCases(): void
    {
        echo "🧪 Test 8: Edge Cases\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 8.1: Empty Filters Array
        try {
            $html = SearchFilter::create(['filters' => []]);
            $this->pass("8.1: Empty filters array handled");
        } catch (Exception $e) {
            $this->fail("8.1: Empty filters array error - " . $e->getMessage());
        }

        // Test 8.2: No Search Input
        $html = SearchFilter::create(['searchInput' => false]);
        if (strpos($html, 'search-filter-search-input') === false) {
            $this->pass("8.2: Search input disabled");
        } else {
            $this->fail("8.2: Search input still present");
        }

        // Test 8.3: No Buttons
        $html = SearchFilter::create([
            'submitButton' => false,
            'resetButton' => false
        ]);
        if (strpos($html, 'search-filter-submit') === false && strpos($html, 'search-filter-reset') === false) {
            $this->pass("8.3: Buttons disabled");
        } else {
            $this->fail("8.3: Buttons still present");
        }

        echo "\n";
    }

    private function testSecurity(): void
    {
        echo "🧪 Test 9: Security (XSS Prevention)\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 9.1: XSS in Search Value
        $_GET['search'] = '<script>alert("XSS")</script>';
        $html = SearchFilter::create([
            'searchValue' => $_GET['search']
        ]);
        // Check that user input is properly escaped in value attribute
        preg_match('/class="search-filter-search-input"[^>]*value="([^"]*)"/', $html, $matches);
        $escapedValue = $matches[1] ?? '';
        // The value should contain &lt;script&gt; (escaped) not <script> (unescaped)
        if (strpos($escapedValue, '&lt;script&gt;') !== false && strpos($escapedValue, '<script>') === false) {
            $this->pass("9.1: XSS in search value escaped");
        } else {
            $this->fail("9.1: XSS vulnerability in search value");
        }

        // Test 9.2: XSS in Filter Value
        $_GET['filter'] = '"><script>alert("XSS")</script>';
        $html = SearchFilter::create([
            'filters' => [
                ['type' => 'text', 'name' => 'filter', 'value' => $_GET['filter']]
            ]
        ]);
        // Check that user input is properly escaped in value attribute
        preg_match('/name="filter"[^>]*value="([^"]*)"/', $html, $matches);
        $escapedValue = $matches[1] ?? '';
        // The value should not contain unescaped quotes or script tags
        if ((strpos($escapedValue, '&quot;') !== false || strpos($escapedValue, '&#34;') !== false) &&
            strpos($escapedValue, '&lt;script&gt;') !== false &&
            strpos($escapedValue, '<script>') === false) {
            $this->pass("9.2: XSS in filter value escaped");
        } else {
            $this->fail("9.2: XSS vulnerability in filter value");
        }

        unset($_GET['search'], $_GET['filter']);

        echo "\n";
    }

    private function testPerformance(): void
    {
        echo "🧪 Test 10: Performance\n";
        echo "─────────────────────────────────────────────────────────\n";

        // Test 10.1: Simple Component Generation Time
        $startTime = microtime(true);
        SearchFilter::create(['action' => '/test']);
        $simpleTime = (microtime(true) - $startTime) * 1000;

        if ($simpleTime < 10) {
            $this->pass("10.1: Simple component < 10ms (" . number_format($simpleTime, 2) . "ms)");
        } else {
            $this->fail("10.1: Simple component too slow (" . number_format($simpleTime, 2) . "ms)");
        }

        // Test 10.2: Complex Component Generation Time
        $startTime = microtime(true);
        SearchFilter::create([
            'layout' => 'grid-4',
            'collapsible' => true,
            'filters' => [
                ['type' => 'select', 'name' => 'f1', 'options' => ['a' => 'A', 'b' => 'B']],
                ['type' => 'select', 'name' => 'f2', 'options' => ['c' => 'C', 'd' => 'D']],
                ['type' => 'date', 'name' => 'f3'],
                ['type' => 'date', 'name' => 'f4'],
                ['type' => 'text', 'name' => 'f5'],
                ['type' => 'number', 'name' => 'f6'],
            ],
            'searchInput' => true,
            'submitButton' => true,
            'resetButton' => true
        ]);
        $complexTime = (microtime(true) - $startTime) * 1000;

        if ($complexTime < 20) {
            $this->pass("10.2: Complex component < 20ms (" . number_format($complexTime, 2) . "ms)");
        } else {
            $this->fail("10.2: Complex component too slow (" . number_format($complexTime, 2) . "ms)");
        }

        echo "\n";
    }

    private function pass(string $test): void
    {
        $this->totalTests++;
        $this->passedTests++;
        $this->results[] = ['test' => $test, 'status' => 'PASS'];
        echo "  ✅ PASS: $test\n";
    }

    private function fail(string $test): void
    {
        $this->totalTests++;
        $this->results[] = ['test' => $test, 'status' => 'FAIL'];
        echo "  ❌ FAIL: $test\n";
    }

    private function printResults(): void
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                          FINAL RESULTS                         ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";

        $passRate = ($this->passedTests / $this->totalTests) * 100;

        echo "Total Tests: {$this->totalTests}\n";
        echo "Passed: {$this->passedTests}\n";
        echo "Failed: " . ($this->totalTests - $this->passedTests) . "\n";
        echo "Pass Rate: " . number_format($passRate, 1) . "%\n\n";

        if ($passRate >= 95) {
            echo "🎉 EXCELLENT! Component is production-ready.\n";
        } elseif ($passRate >= 80) {
            echo "👍 GOOD! Minor improvements needed.\n";
        } elseif ($passRate >= 60) {
            echo "⚠️  NEEDS WORK! Several issues to fix.\n";
        } else {
            echo "❌ CRITICAL! Major issues detected.\n";
        }

        echo "\n";
    }
}

// Run Tests
$test = new SearchFilterQATest();
$test->run();
