import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 }
  });
  const page = await context.newPage();

  try {
    console.log('Navigating to homepage...');
    await page.goto('https://www.topmktx.com/', { waitUntil: 'domcontentloaded' });
    
    // Wait for feature cards to be visible
    console.log('Waiting for feature cards...');
    await page.waitForSelector('.feature-card', { timeout: 10000 });

    // Check for feature cards and their borders
    const featureCards = await page.$$('.feature-card');
    console.log(`Found ${featureCards.length} feature cards`);

    // Get styles for each feature card
    for (let i = 0; i < featureCards.length; i++) {
      const card = featureCards[i];
      const borderStyle = await card.evaluate(el => {
        const styles = window.getComputedStyle(el);
        return {
          border: styles.border,
          borderWidth: styles.borderWidth,
          borderColor: styles.borderColor,
          borderStyle: styles.borderStyle
        };
      });
      console.log(`Card ${i + 1} border:`, borderStyle);
    }

    // Test hover behavior
    console.log('Testing hover behavior...');
    const firstCard = featureCards[0];
    
    // Get initial transform and box-shadow
    const initialStyles = await firstCard.evaluate(el => {
      const styles = window.getComputedStyle(el);
      return {
        transform: styles.transform,
        boxShadow: styles.boxShadow
      };
    });
    console.log('Initial styles:', initialStyles);

    // Hover over the first card
    await firstCard.hover();
    await page.waitForTimeout(500); // Wait for any transitions

    // Get styles after hover
    const hoverStyles = await firstCard.evaluate(el => {
      const styles = window.getComputedStyle(el);
      return {
        transform: styles.transform,
        boxShadow: styles.boxShadow
      };
    });
    console.log('Hover styles:', hoverStyles);

    // Check if there's any change
    const hasMovement = initialStyles.transform !== hoverStyles.transform;
    const hasShadowChange = initialStyles.boxShadow !== hoverStyles.boxShadow;
    
    console.log(`Movement detected: ${hasMovement}`);
    console.log(`Shadow change detected: ${hasShadowChange}`);

    // Take screenshot
    await page.screenshot({ 
      path: 'feature-card-borders-test.png',
      fullPage: true
    });
    
    console.log('Screenshot saved: feature-card-borders-test.png');

  } catch (error) {
    console.error('Error:', error);
  } finally {
    await browser.close();
  }
})();