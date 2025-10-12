const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  // Enable console logging
  page.on('console', msg => console.log('Browser console:', msg.text()));
  page.on('pageerror', error => console.log('Page error:', error.message));

  try {
    console.log('Navigating to TopMKT website...');
    await page.goto('https://www.topmktx.com', { waitUntil: 'networkidle' });

    console.log('Checking if testChatNotification function exists...');

    // Check if the function exists first
    const functionExists = await page.evaluate(() => {
      return typeof window.testChatNotification === 'function';
    });

    console.log('testChatNotification function exists:', functionExists);

    if (functionExists) {
      console.log('Executing testChatNotification()...');

      // Execute the test function
      const result = await page.evaluate(() => {
        try {
          window.testChatNotification();
          return { success: true, message: 'Function executed successfully' };
        } catch (error) {
          return { success: false, error: error.message };
        }
      });

      console.log('Function execution result:', result);

      // Wait a moment for the notification to appear
      await page.waitForTimeout(3000);

      // Check if notification element exists in DOM
      const notificationElements = await page.evaluate(() => {
        const alerts = document.querySelectorAll('.alert, .notification, [class*="notification"], [class*="alert"], .toast, [class*="toast"]');
        return Array.from(alerts).map(el => ({
          tagName: el.tagName,
          className: el.className,
          textContent: el.textContent.trim(),
          style: el.style.cssText,
          visible: el.offsetParent !== null,
          display: window.getComputedStyle(el).display,
          opacity: window.getComputedStyle(el).opacity
        }));
      });

      console.log('Notification elements found:', notificationElements);

      // Also check for any elements that might be notification containers
      const possibleNotifications = await page.evaluate(() => {
        const elements = document.querySelectorAll('[id*="notification"], [id*="alert"], [class*="glassmorphism"]');
        return Array.from(elements).map(el => ({
          tagName: el.tagName,
          id: el.id,
          className: el.className,
          textContent: el.textContent.trim(),
          visible: el.offsetParent !== null
        }));
      });

      console.log('Possible notification elements:', possibleNotifications);

    } else {
      console.log('testChatNotification function not found. Checking available functions...');

      // Check for any chat or notification related functions
      const availableFunctions = await page.evaluate(() => {
        const functions = [];
        for (let prop in window) {
          if (typeof window[prop] === 'function') {
            const propLower = prop.toLowerCase();
            if (propLower.includes('chat') || propLower.includes('notification') || propLower.includes('alert')) {
              functions.push(prop);
            }
          }
        }
        return functions;
      });

      console.log('Available chat/notification related functions:', availableFunctions);

      // Check if there are any script elements that might contain the function
      const scripts = await page.evaluate(() => {
        const scriptElements = document.querySelectorAll('script');
        return Array.from(scriptElements).map(script => ({
          src: script.src,
          hasContent: script.textContent.length > 0,
          containsTestFunction: script.textContent.includes('testChatNotification')
        }));
      });

      console.log('Script elements found:', scripts.length);
      console.log('Scripts with testChatNotification:', scripts.filter(s => s.containsTestFunction));
    }

    // Take screenshot
    console.log('Taking screenshot...');
    await page.screenshot({
      path: '/var/www/html/topmkt/chat-notification-test.png',
      fullPage: true
    });

    console.log('Screenshot saved as chat-notification-test.png');

  } catch (error) {
    console.error('Error during testing:', error.message);
  } finally {
    await browser.close();
  }
})();