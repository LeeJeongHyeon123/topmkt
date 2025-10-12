<?php
// CSS 파일을 PHP 파일로 변환하는 스크립트

$cssFiles = [
    'public/assets/css/components/layout.css',
    'public/assets/css/components/buttons.css'
];

foreach ($cssFiles as $cssFile) {
    if (!file_exists($cssFile)) {
        echo "파일 없음: $cssFile\n";
        continue;
    }
    
    $phpFile = str_replace('.css', '.css.php', $cssFile);
    $phpDir = dirname($phpFile);
    
    if (!is_dir($phpDir)) {
        mkdir($phpDir, 0755, true);
    }
    
    $content = file_get_contents($cssFile);
    
    $phpContent = "<?php\n";
    $phpContent .= "header('Content-Type: text/css; charset=utf-8');\n";
    $phpContent .= "header('Cache-Control: no-cache, no-store, must-revalidate');\n";
    $phpContent .= "header('Pragma: no-cache');\n";
    $phpContent .= "header('Expires: 0');\n\n";
    $phpContent .= "echo \"" . addslashes($content) . "\";\n";
    $phpContent .= "?>";
    
    file_put_contents($phpFile, $phpContent);
    echo "변환 완료: $cssFile -> $phpFile\n";
}

echo "모든 CSS 파일 변환 완료!\n";
