#!/usr/bin/env node

/**
 * Playwright MCP Server for Claude Code
 * 헤드리스 브라우저 자동화를 위한 MCP 서버
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ErrorCode,
  ListResourcesRequestSchema,
  ListToolsRequestSchema,
  McpError,
  ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import { chromium, firefox, webkit } from 'playwright';

class PlaywrightMCPServer {
  constructor() {
    this.server = new Server(
      {
        name: 'playwright-mcp-server',
        version: '0.1.0',
      },
      {
        capabilities: {
          resources: {},
          tools: {},
        },
      }
    );

    this.browsers = new Map();
    this.pages = new Map();
    
    this.setupToolHandlers();
    this.setupResourceHandlers();
    
    // 프로세스 종료시 브라우저 정리
    process.on('SIGINT', () => this.cleanup());
    process.on('SIGTERM', () => this.cleanup());
    process.on('exit', () => this.cleanup());
  }

  setupToolHandlers() {
    this.server.setRequestHandler(ListToolsRequestSchema, async () => ({
      tools: [
        {
          name: 'launch_browser',
          description: '헤드리스 브라우저를 실행합니다',
          inputSchema: {
            type: 'object',
            properties: {
              browser: {
                type: 'string',
                enum: ['chromium', 'firefox', 'webkit'],
                default: 'chromium',
                description: '사용할 브라우저 유형'
              },
              headless: {
                type: 'boolean',
                default: true,
                description: '헤드리스 모드 여부'
              },
              viewport: {
                type: 'object',
                properties: {
                  width: { type: 'number', default: 1920 },
                  height: { type: 'number', default: 1080 }
                },
                description: '뷰포트 크기'
              }
            }
          }
        },
        {
          name: 'navigate',
          description: '지정된 URL로 이동합니다',
          inputSchema: {
            type: 'object',
            properties: {
              url: {
                type: 'string',
                description: '이동할 URL'
              },
              waitUntil: {
                type: 'string',
                enum: ['load', 'domcontentloaded', 'networkidle'],
                default: 'load',
                description: '대기 조건'
              }
            },
            required: ['url']
          }
        },
        {
          name: 'screenshot',
          description: '현재 페이지의 스크린샷을 촬영합니다',
          inputSchema: {
            type: 'object',
            properties: {
              path: {
                type: 'string',
                description: '스크린샷 저장 경로'
              },
              fullPage: {
                type: 'boolean',
                default: false,
                description: '전체 페이지 스크린샷 여부'
              },
              format: {
                type: 'string',
                enum: ['png', 'jpeg'],
                default: 'png',
                description: '이미지 형식'
              }
            }
          }
        },
        {
          name: 'click',
          description: '지정된 요소를 클릭합니다',
          inputSchema: {
            type: 'object',
            properties: {
              selector: {
                type: 'string',
                description: 'CSS 선택자'
              },
              timeout: {
                type: 'number',
                default: 5000,
                description: '대기 시간 (밀리초)'
              }
            },
            required: ['selector']
          }
        },
        {
          name: 'type',
          description: '지정된 요소에 텍스트를 입력합니다',
          inputSchema: {
            type: 'object',
            properties: {
              selector: {
                type: 'string',
                description: 'CSS 선택자'
              },
              text: {
                type: 'string',
                description: '입력할 텍스트'
              },
              timeout: {
                type: 'number',
                default: 5000,
                description: '대기 시간 (밀리초)'
              }
            },
            required: ['selector', 'text']
          }
        },
        {
          name: 'wait_for_selector',
          description: '지정된 선택자가 나타날 때까지 대기합니다',
          inputSchema: {
            type: 'object',
            properties: {
              selector: {
                type: 'string',
                description: 'CSS 선택자'
              },
              timeout: {
                type: 'number',
                default: 5000,
                description: '대기 시간 (밀리초)'
              },
              state: {
                type: 'string',
                enum: ['visible', 'hidden', 'attached', 'detached'],
                default: 'visible',
                description: '대기할 상태'
              }
            },
            required: ['selector']
          }
        },
        {
          name: 'evaluate',
          description: '페이지에서 JavaScript 코드를 실행합니다',
          inputSchema: {
            type: 'object',
            properties: {
              script: {
                type: 'string',
                description: '실행할 JavaScript 코드'
              }
            },
            required: ['script']
          }
        },
        {
          name: 'get_page_content',
          description: '현재 페이지의 HTML 내용을 가져옵니다',
          inputSchema: {
            type: 'object',
            properties: {}
          }
        },
        {
          name: 'close_browser',
          description: '브라우저를 닫습니다',
          inputSchema: {
            type: 'object',
            properties: {}
          }
        }
      ]
    }));

    this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
      const { name, arguments: args } = request.params;

      try {
        switch (name) {
          case 'launch_browser':
            return await this.launchBrowser(args);
          case 'navigate':
            return await this.navigate(args);
          case 'screenshot':
            return await this.screenshot(args);
          case 'click':
            return await this.click(args);
          case 'type':
            return await this.type(args);
          case 'wait_for_selector':
            return await this.waitForSelector(args);
          case 'evaluate':
            return await this.evaluate(args);
          case 'get_page_content':
            return await this.getPageContent(args);
          case 'close_browser':
            return await this.closeBrowser(args);
          default:
            throw new McpError(
              ErrorCode.MethodNotFound,
              `알 수 없는 도구: ${name}`
            );
        }
      } catch (error) {
        return {
          content: [
            {
              type: 'text',
              text: `오류 발생: ${error.message}`
            }
          ],
          isError: true
        };
      }
    });
  }

  setupResourceHandlers() {
    this.server.setRequestHandler(ListResourcesRequestSchema, async () => ({
      resources: []
    }));

    this.server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
      throw new McpError(
        ErrorCode.InvalidRequest,
        `리소스를 찾을 수 없습니다: ${request.params.uri}`
      );
    });
  }

  async launchBrowser(args) {
    const { browser = 'chromium', headless = true, viewport = { width: 1920, height: 1080 } } = args;
    
    try {
      let browserInstance;
      switch (browser) {
        case 'chromium':
          browserInstance = await chromium.launch({ 
            headless,
            args: ['--no-sandbox', '--disable-dev-shm-usage']
          });
          break;
        case 'firefox':
          browserInstance = await firefox.launch({ headless });
          break;
        case 'webkit':
          browserInstance = await webkit.launch({ headless });
          break;
        default:
          throw new Error(`지원하지 않는 브라우저: ${browser}`);
      }

      const context = await browserInstance.newContext({
        viewport
      });
      
      const page = await context.newPage();
      
      this.browsers.set('default', browserInstance);
      this.pages.set('default', page);

      return {
        content: [
          {
            type: 'text',
            text: `✅ ${browser} 브라우저가 성공적으로 시작되었습니다 (헤드리스: ${headless})`
          }
        ]
      };
    } catch (error) {
      throw new Error(`브라우저 실행 실패: ${error.message}`);
    }
  }

  async navigate(args) {
    const { url, waitUntil = 'load' } = args;
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다. 먼저 launch_browser를 실행하세요.');
    }

    try {
      await page.goto(url, { waitUntil });
      const title = await page.title();
      
      return {
        content: [
          {
            type: 'text',
            text: `✅ ${url}로 이동 완료\n제목: ${title}`
          }
        ]
      };
    } catch (error) {
      throw new Error(`페이지 이동 실패: ${error.message}`);
    }
  }

  async screenshot(args) {
    const { path, fullPage = false, format = 'png' } = args;
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다.');
    }

    try {
      const screenshotOptions = {
        fullPage,
        type: format
      };

      if (path) {
        screenshotOptions.path = path;
        await page.screenshot(screenshotOptions);
        return {
          content: [
            {
              type: 'text',
              text: `✅ 스크린샷이 ${path}에 저장되었습니다`
            }
          ]
        };
      } else {
        const buffer = await page.screenshot(screenshotOptions);
        return {
          content: [
            {
              type: 'text',
              text: `✅ 스크린샷이 생성되었습니다 (${buffer.length} bytes)`
            }
          ]
        };
      }
    } catch (error) {
      throw new Error(`스크린샷 실패: ${error.message}`);
    }
  }

  async click(args) {
    const { selector, timeout = 5000 } = args;
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다.');
    }

    try {
      await page.click(selector, { timeout });
      return {
        content: [
          {
            type: 'text',
            text: `✅ 요소 클릭 완료: ${selector}`
          }
        ]
      };
    } catch (error) {
      throw new Error(`클릭 실패: ${error.message}`);
    }
  }

  async type(args) {
    const { selector, text, timeout = 5000 } = args;
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다.');
    }

    try {
      await page.fill(selector, text, { timeout });
      return {
        content: [
          {
            type: 'text',
            text: `✅ 텍스트 입력 완료: ${selector} = "${text}"`
          }
        ]
      };
    } catch (error) {
      throw new Error(`텍스트 입력 실패: ${error.message}`);
    }
  }

  async waitForSelector(args) {
    const { selector, timeout = 5000, state = 'visible' } = args;
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다.');
    }

    try {
      await page.waitForSelector(selector, { timeout, state });
      return {
        content: [
          {
            type: 'text',
            text: `✅ 요소 대기 완료: ${selector} (${state})`
          }
        ]
      };
    } catch (error) {
      throw new Error(`요소 대기 실패: ${error.message}`);
    }
  }

  async evaluate(args) {
    const { script } = args;
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다.');
    }

    try {
      const result = await page.evaluate(() => eval(script));
      return {
        content: [
          {
            type: 'text',
            text: `✅ JavaScript 실행 완료\n결과: ${JSON.stringify(result, null, 2)}`
          }
        ]
      };
    } catch (error) {
      throw new Error(`JavaScript 실행 실패: ${error.message}`);
    }
  }

  async getPageContent(args) {
    const page = this.pages.get('default');
    
    if (!page) {
      throw new Error('브라우저가 실행되지 않았습니다.');
    }

    try {
      const content = await page.content();
      return {
        content: [
          {
            type: 'text',
            text: content
          }
        ]
      };
    } catch (error) {
      throw new Error(`페이지 내용 가져오기 실패: ${error.message}`);
    }
  }

  async closeBrowser(args) {
    try {
      const browser = this.browsers.get('default');
      if (browser) {
        await browser.close();
        this.browsers.delete('default');
        this.pages.delete('default');
      }

      return {
        content: [
          {
            type: 'text',
            text: '✅ 브라우저가 성공적으로 닫혔습니다'
          }
        ]
      };
    } catch (error) {
      throw new Error(`브라우저 닫기 실패: ${error.message}`);
    }
  }

  async cleanup() {
    for (const [key, browser] of this.browsers) {
      try {
        await browser.close();
      } catch (error) {
        console.error(`브라우저 정리 중 오류 (${key}):`, error);
      }
    }
    this.browsers.clear();
    this.pages.clear();
  }

  async run() {
    const transport = new StdioServerTransport();
    await this.server.connect(transport);
    console.error('🎭 Playwright MCP Server 시작됨 (헤드리스 모드)');
  }
}

const server = new PlaywrightMCPServer();
server.run().catch(console.error);