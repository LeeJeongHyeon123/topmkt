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

    console.log('Manually loading chat-notifications.js script...');

    // Manually inject the chat-notifications script
    await page.addScriptTag({
      url: 'https://www.topmktx.com/assets/js/chat-notifications.js'
    });

    console.log('Waiting for script to load...');
    await page.waitForTimeout(2000);

    // Check if the function exists now
    const functionExists = await page.evaluate(() => {
      return typeof window.testChatNotification === 'function';
    });

    console.log('testChatNotification function exists after manual load:', functionExists);

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
        const alerts = document.querySelectorAll('.alert.chat-notification');
        return Array.from(alerts).map(el => ({
          tagName: el.tagName,
          className: el.className,
          textContent: el.textContent.trim(),
          innerHTML: el.innerHTML,
          visible: el.offsetParent !== null,
          display: window.getComputedStyle(el).display,
          opacity: window.getComputedStyle(el).opacity,
          position: window.getComputedStyle(el).position,
          top: window.getComputedStyle(el).top,
          right: window.getComputedStyle(el).right,
          zIndex: window.getComputedStyle(el).zIndex
        }));
      });

      console.log('Chat notification elements found:', notificationElements);

      // Check for all alert elements
      const allAlerts = await page.evaluate(() => {
        const alerts = document.querySelectorAll('.alert');
        return Array.from(alerts).map(el => ({
          tagName: el.tagName,
          className: el.className,
          textContent: el.textContent.trim(),
          visible: el.offsetParent !== null
        }));
      });

      console.log('All alert elements found:', allAlerts);

      // Check if the DOM structure looks correct
      const bodyChildren = await page.evaluate(() => {
        const children = Array.from(document.body.children);
        return children.map(el => ({
          tagName: el.tagName,
          className: el.className,
          id: el.id
        }));
      });

      console.log('Body children (looking for alert):', bodyChildren.filter(child =>
        child.className.includes('alert') || child.tagName === 'DIV'
      ));

    } else {
      console.log('Function still not available after manual script load');

      // Check if there are any script errors
      const scriptErrors = await page.evaluate(() => {
        return window.errors || [];
      });

      console.log('Script errors:', scriptErrors);
    }

    // Take screenshot after test
    console.log('Taking screenshot after test...');
    await page.screenshot({
      path: '/var/www/html/topmkt/chat-notification-test-with-script.png',
      fullPage: true
    });

    console.log('Screenshot saved as chat-notification-test-with-script.png');

  } catch (error) {
    console.error('Error during testing:', error.message);
  } finally {
    await browser.close();
  }
})();