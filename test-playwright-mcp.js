#!/usr/bin/env node

/**
 * Playwright MCP 서버 테스트 스크립트
 */

import { spawn } from 'child_process';
import readline from 'readline';

async function testPlaywrightMCP() {
  console.log('🧪 Playwright MCP 서버 테스트 시작...');
  
  // MCP 서버 시작
  const server = spawn('node', ['playwright-mcp-server.js'], {
    stdio: ['pipe', 'pipe', 'inherit']
  });
  
  const rl = readline.createInterface({
    input: server.stdout,
    output: process.stdout
  });
  
  // 서버 응답 처리
  server.stdout.on('data', (data) => {
    console.log('서버 응답:', data.toString());
  });
  
  server.on('error', (error) => {
    console.error('서버 오류:', error);
  });
  
  // 테스트 메시지들
  const testMessages = [
    // 도구 목록 요청
    {
      jsonrpc: "2.0",
      id: 1,
      method: "tools/list"
    },
    // 브라우저 실행
    {
      jsonrpc: "2.0",
      id: 2,
      method: "tools/call",
      params: {
        name: "launch_browser",
        arguments: {
          browser: "chromium",
          headless: true,
          viewport: { width: 1920, height: 1080 }
        }
      }
    },
    // 페이지 이동
    {
      jsonrpc: "2.0",
      id: 3,
      method: "tools/call",
      params: {
        name: "navigate",
        arguments: {
          url: "https://www.topmktx.com"
        }
      }
    },
    // 스크린샷
    {
      jsonrpc: "2.0",
      id: 4,
      method: "tools/call",
      params: {
        name: "screenshot",
        arguments: {
          path: "/var/www/html/topmkt/test-screenshot.png",
          fullPage: false
        }
      }
    },
    // 브라우저 닫기
    {
      jsonrpc: "2.0",
      id: 5,
      method: "tools/call",
      params: {
        name: "close_browser",
        arguments: {}
      }
    }
  ];
  
  // 1초 후 첫 번째 메시지 전송
  setTimeout(() => {
    console.log('📤 도구 목록 요청...');
    server.stdin.write(JSON.stringify(testMessages[0]) + '\n');
  }, 1000);
  
  // 3초 후 브라우저 실행
  setTimeout(() => {
    console.log('📤 브라우저 실행...');
    server.stdin.write(JSON.stringify(testMessages[1]) + '\n');
  }, 3000);
  
  // 5초 후 페이지 이동
  setTimeout(() => {
    console.log('📤 페이지 이동...');
    server.stdin.write(JSON.stringify(testMessages[2]) + '\n');
  }, 5000);
  
  // 8초 후 스크린샷
  setTimeout(() => {
    console.log('📤 스크린샷 촬영...');
    server.stdin.write(JSON.stringify(testMessages[3]) + '\n');
  }, 8000);
  
  // 10초 후 브라우저 닫기
  setTimeout(() => {
    console.log('📤 브라우저 닫기...');
    server.stdin.write(JSON.stringify(testMessages[4]) + '\n');
  }, 10000);
  
  // 12초 후 테스트 종료
  setTimeout(() => {
    console.log('✅ 테스트 완료!');
    server.kill();
    process.exit(0);
  }, 12000);
}

testPlaywrightMCP().catch(console.error);