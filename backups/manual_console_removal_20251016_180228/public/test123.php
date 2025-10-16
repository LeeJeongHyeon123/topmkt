<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테트리스 게임</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #1a1a1a;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .game-container {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        
        .game-board {
            position: relative;
        }
        
        canvas {
            border: 2px solid #fff;
            background: #000;
        }
        
        .game-info {
            min-width: 200px;
        }
        
        .score {
            background: #333;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .controls {
            background: #333;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .next-piece {
            background: #333;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        
        .next-canvas {
            border: 1px solid #666;
            background: #000;
            margin-top: 10px;
        }
        
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        
        button:hover {
            background: #45a049;
        }
        
        .game-over {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.9);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            display: none;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #4CAF50;
        }
        
        .controls p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-board">
            <canvas id="gameCanvas" width="300" height="600"></canvas>
            <div id="gameOverScreen" class="game-over">
                <h2>게임 오버!</h2>
                <p id="finalScore">점수: 0</p>
                <button onclick="restartGame()">다시 시작</button>
            </div>
        </div>
        
        <div class="game-info">
            <h1>테트리스</h1>
            
            <div class="score">
                <h3>점수</h3>
                <p id="score">0</p>
                <h3>레벨</h3>
                <p id="level">1</p>
                <h3>라인</h3>
                <p id="lines">0</p>
            </div>
            
            <div class="controls">
                <h3>조작법</h3>
                <p>← → : 좌우 이동</p>
                <p>↓ : 빠른 낙하</p>
                <p>↑ : 회전</p>
                <p>스페이스 : 즉시 낙하</p>
                <p>P : 일시정지</p>
            </div>
            
            <div class="next-piece">
                <h3>다음 블록</h3>
                <canvas id="nextCanvas" class="next-canvas" width="120" height="120"></canvas>
            </div>
            
            <button onclick="togglePause()">일시정지</button>
            <button onclick="restartGame()" style="margin-top: 10px; background: #f44336;">새 게임</button>
        </div>
    </div>

    <script>
        // 게임 설정
        const BOARD_WIDTH = 10;
        const BOARD_HEIGHT = 20;
        const BLOCK_SIZE = 30;
        
        // 캔버스 설정
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const nextCanvas = document.getElementById('nextCanvas');
        const nextCtx = nextCanvas.getContext('2d');
        
        // 게임 상태
        let board = [];
        let currentPiece = null;
        let nextPiece = null;
        let score = 0;
        let level = 1;
        let lines = 0;
        let gameRunning = false;
        let paused = false;
        let dropTimer = 0;
        let dropInterval = 1000;
        
        // 테트리스 블록 정의
        const PIECES = [
            // I 블록
            {
                shape: [
                    [1, 1, 1, 1]
                ],
                color: '#00f0f0'
            },
            // O 블록
            {
                shape: [
                    [1, 1],
                    [1, 1]
                ],
                color: '#f0f000'
            },
            // T 블록
            {
                shape: [
                    [0, 1, 0],
                    [1, 1, 1]
                ],
                color: '#a000f0'
            },
            // S 블록
            {
                shape: [
                    [0, 1, 1],
                    [1, 1, 0]
                ],
                color: '#00f000'
            },
            // Z 블록
            {
                shape: [
                    [1, 1, 0],
                    [0, 1, 1]
                ],
                color: '#f00000'
            },
            // J 블록
            {
                shape: [
                    [1, 0, 0],
                    [1, 1, 1]
                ],
                color: '#0000f0'
            },
            // L 블록
            {
                shape: [
                    [0, 0, 1],
                    [1, 1, 1]
                ],
                color: '#f0a000'
            }
        ];
        
        // 게임 초기화
        function initGame() {
            // 게임 보드 초기화
            board = [];
            for (let y = 0; y < BOARD_HEIGHT; y++) {
                board[y] = [];
                for (let x = 0; x < BOARD_WIDTH; x++) {
                    board[y][x] = 0;
                }
            }
            
            // 게임 상태 초기화
            score = 0;
            level = 1;
            lines = 0;
            gameRunning = true;
            paused = false;
            dropTimer = 0;
            dropInterval = 1000;
            
            // 첫 블록 생성
            nextPiece = getRandomPiece();
            spawnNewPiece();
            
            updateDisplay();
            hideGameOver();
        }
        
        // 랜덤 블록 생성
        function getRandomPiece() {
            const pieceTemplate = PIECES[Math.floor(Math.random() * PIECES.length)];
            return {
                shape: pieceTemplate.shape.map(row => [...row]),
                color: pieceTemplate.color,
                x: Math.floor(BOARD_WIDTH / 2) - Math.floor(pieceTemplate.shape[0].length / 2),
                y: 0
            };
        }
        
        // 새 블록 생성
        function spawnNewPiece() {
            currentPiece = nextPiece;
            nextPiece = getRandomPiece();
            
            // 게임 오버 체크
            if (checkCollision(currentPiece.x, currentPiece.y, currentPiece.shape)) {
                gameOver();
                return;
            }
            
            drawNextPiece();
        }
        
        // 충돌 체크
        function checkCollision(x, y, shape) {
            for (let py = 0; py < shape.length; py++) {
                for (let px = 0; px < shape[py].length; px++) {
                    if (shape[py][px]) {
                        const newX = x + px;
                        const newY = y + py;
                        
                        if (newX < 0 || newX >= BOARD_WIDTH || 
                            newY >= BOARD_HEIGHT || 
                            (newY >= 0 && board[newY][newX])) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }
        
        // 블록 이동
        function movePiece(dx, dy) {
            if (!currentPiece || !gameRunning || paused) return false;
            
            const newX = currentPiece.x + dx;
            const newY = currentPiece.y + dy;
            
            if (!checkCollision(newX, newY, currentPiece.shape)) {
                currentPiece.x = newX;
                currentPiece.y = newY;
                return true;
            }
            return false;
        }
        
        // 블록 회전
        function rotatePiece() {
            if (!currentPiece || !gameRunning || paused) return;
            
            const rotated = rotateMatrix(currentPiece.shape);
            
            if (!checkCollision(currentPiece.x, currentPiece.y, rotated)) {
                currentPiece.shape = rotated;
            }
        }
        
        // 매트릭스 회전
        function rotateMatrix(matrix) {
            const rows = matrix.length;
            const cols = matrix[0].length;
            const rotated = [];
            
            for (let i = 0; i < cols; i++) {
                rotated[i] = [];
                for (let j = 0; j < rows; j++) {
                    rotated[i][j] = matrix[rows - 1 - j][i];
                }
            }
            
            return rotated;
        }
        
        // 즉시 낙하
        function hardDrop() {
            if (!currentPiece || !gameRunning || paused) return;
            
            while (movePiece(0, 1)) {
                score += 2; // 하드 드롭 보너스
            }
            
            placePiece();
        }
        
        // 블록 고정
        function placePiece() {
            if (!currentPiece) return;
            
            // 보드에 블록 추가
            for (let py = 0; py < currentPiece.shape.length; py++) {
                for (let px = 0; px < currentPiece.shape[py].length; px++) {
                    if (currentPiece.shape[py][px]) {
                        const x = currentPiece.x + px;
                        const y = currentPiece.y + py;
                        if (y >= 0) {
                            board[y][x] = currentPiece.color;
                        }
                    }
                }
            }
            
            // 라인 클리어 체크
            clearLines();
            
            // 다음 블록 생성
            spawnNewPiece();
        }
        
        // 라인 클리어
        function clearLines() {
            let linesCleared = 0;
            
            for (let y = BOARD_HEIGHT - 1; y >= 0; y--) {
                if (board[y].every(cell => cell !== 0)) {
                    board.splice(y, 1);
                    board.unshift(new Array(BOARD_WIDTH).fill(0));
                    linesCleared++;
                    y++; // 같은 라인 다시 체크
                }
            }
            
            if (linesCleared > 0) {
                // 점수 계산
                const lineScore = [0, 100, 300, 500, 800][linesCleared];
                score += lineScore * level;
                lines += linesCleared;
                
                // 레벨 업
                level = Math.floor(lines / 10) + 1;
                dropInterval = Math.max(50, 1000 - (level - 1) * 50);
                
                updateDisplay();
            }
        }
        
        // 화면 그리기
        function draw() {
            // 캔버스 클리어
            ctx.fillStyle = '#000';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // 보드 그리기
            for (let y = 0; y < BOARD_HEIGHT; y++) {
                for (let x = 0; x < BOARD_WIDTH; x++) {
                    if (board[y][x]) {
                        ctx.fillStyle = board[y][x];
                        ctx.fillRect(x * BLOCK_SIZE, y * BLOCK_SIZE, BLOCK_SIZE - 1, BLOCK_SIZE - 1);
                    }
                }
            }
            
            // 현재 블록 그리기
            if (currentPiece) {
                ctx.fillStyle = currentPiece.color;
                for (let py = 0; py < currentPiece.shape.length; py++) {
                    for (let px = 0; px < currentPiece.shape[py].length; px++) {
                        if (currentPiece.shape[py][px]) {
                            const x = (currentPiece.x + px) * BLOCK_SIZE;
                            const y = (currentPiece.y + py) * BLOCK_SIZE;
                            ctx.fillRect(x, y, BLOCK_SIZE - 1, BLOCK_SIZE - 1);
                        }
                    }
                }
            }
        }
        
        // 다음 블록 그리기
        function drawNextPiece() {
            nextCtx.fillStyle = '#000';
            nextCtx.fillRect(0, 0, nextCanvas.width, nextCanvas.height);
            
            if (nextPiece) {
                nextCtx.fillStyle = nextPiece.color;
                const offsetX = (nextCanvas.width - nextPiece.shape[0].length * 20) / 2;
                const offsetY = (nextCanvas.height - nextPiece.shape.length * 20) / 2;
                
                for (let py = 0; py < nextPiece.shape.length; py++) {
                    for (let px = 0; px < nextPiece.shape[py].length; px++) {
                        if (nextPiece.shape[py][px]) {
                            nextCtx.fillRect(
                                offsetX + px * 20,
                                offsetY + py * 20,
                                19, 19
                            );
                        }
                    }
                }
            }
        }
        
        // 게임 루프
        function gameLoop() {
            if (!gameRunning) return;
            
            if (!paused) {
                dropTimer += 16;
                
                if (dropTimer >= dropInterval) {
                    if (!movePiece(0, 1)) {
                        placePiece();
                        score += level;
                        updateDisplay();
                    }
                    dropTimer = 0;
                }
                
                draw();
            }
            
            requestAnimationFrame(gameLoop);
        }
        
        // 화면 업데이트
        function updateDisplay() {
            document.getElementById('score').textContent = score;
            document.getElementById('level').textContent = level;
            document.getElementById('lines').textContent = lines;
        }
        
        // 게임 오버
        function gameOver() {
            gameRunning = false;
            document.getElementById('finalScore').textContent = `점수: ${score}`;
            document.getElementById('gameOverScreen').style.display = 'block';
        }
        
        // 게임 오버 화면 숨기기
        function hideGameOver() {
            document.getElementById('gameOverScreen').style.display = 'none';
        }
        
        // 일시정지 토글
        function togglePause() {
            if (!gameRunning) return;
            paused = !paused;
        }
        
        // 게임 재시작
        function restartGame() {
            initGame();
            gameLoop();
        }
        
        // 키보드 이벤트
        document.addEventListener('keydown', (e) => {
            if (!gameRunning) return;
            
            switch(e.code) {
                case 'ArrowLeft':
                    movePiece(-1, 0);
                    break;
                case 'ArrowRight':
                    movePiece(1, 0);
                    break;
                case 'ArrowDown':
                    if (movePiece(0, 1)) {
                        score += 1;
                        updateDisplay();
                    }
                    break;
                case 'ArrowUp':
                    rotatePiece();
                    break;
                case 'Space':
                    e.preventDefault();
                    hardDrop();
                    updateDisplay();
                    break;
                case 'KeyP':
                    togglePause();
                    break;
            }
        });
        
        // 게임 시작
        initGame();
        gameLoop();
    </script>
</body>
</html>