
import { chromium } from "playwright";

console.log("👤 프로필 페이지 로켓 애니메이션 테스트 시작...");

const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"] 
});

try {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1920, height: 1080 });
    
    console.log("📍 프로필 페이지 방문 중...");
    await page.goto("https://www.topmktx.com/profile", { 
        waitUntil: "networkidle", 
        timeout: 30000 
    });
    
    console.log("⏰ 2초 대기 중...");
    await page.waitForTimeout(2000);
    
    console.log("📸 스크린샷 캡처 중...");
    await page.screenshot({ 
        path: "profile-page-rocket-static.png", 
        fullPage: false 
    });
    
    console.log("✅ profile-page-rocket-static.png 저장 완료");
    
} catch (error) {
    console.error("❌ 오류 발생:", error.message);
} finally {
    await browser.close();
    console.log("🔚 브라우저 종료");
}

