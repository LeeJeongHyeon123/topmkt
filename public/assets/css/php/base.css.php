<?php
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "/* 탑마케팅 기본 스타일 컴포넌트 */\n\n";

/* 기본 스타일 리셋 */
echo "* {\n    margin: 0;\n    padding: 0;\n    box-sizing: border-box;\n}\n\n";

/* 기본 커서 스타일 */
echo "input, textarea, [contenteditable] {\n    cursor: text !important;\n}\n\n";

/* 클릭 가능한 요소만 포인터 커서 */
echo "a, button, [role=\"button\"], .btn, [onclick],\n";
echo ".user-menu, .dropdown-toggle,\n";
echo ".nav-menu a, .feature-link, .rocket-launch-btn {\n    cursor: pointer !important;\n}\n\n";

/* Quill 에디터 텍스트 선택 강제 활성화 */
echo "html body #quill-editor .ql-editor,\n";
echo "html body #quill-editor .ql-editor *,\n";
echo "html body #quill-editor .ql-editor p,\n";
echo "html body #quill-editor .ql-editor span,\n";
echo "html body #quill-editor .ql-editor div,\n";
echo "html body #quill-editor .ql-editor strong,\n";
echo "html body #quill-editor .ql-editor em,\n";
echo "html body #quill-editor .ql-editor u,\n";
echo "html body .ql-editor,\n";
echo "html body .ql-editor *,\n";
echo "html body .ql-container,\n";
echo "html body .ql-container *,\n";
echo "body #quill-editor .ql-editor,\n";
echo "body #quill-editor .ql-editor *,\n";
echo "body .ql-editor,\n";
echo "body .ql-editor *,\n";
echo "body .ql-container,\n";
echo "body .ql-container * {\n    cursor: text !important;\n    user-select: text !important;\n    -webkit-user-select: text !important;\n    -moz-user-select: text !important;\n    -ms-user-select: text !important;\n    pointer-events: auto !important;\n}\n\n";

/* 기본 커서 복원 */
echo "input, textarea, select, [contenteditable] {\n    cursor: text !important;\n}\n\n";

/* 텍스트 요소들은 기본 커서 */
echo "p, span, div, h1, h2, h3, h4, h5, h6, label, td, th {\n    cursor: default !important;\n}\n";
?>


