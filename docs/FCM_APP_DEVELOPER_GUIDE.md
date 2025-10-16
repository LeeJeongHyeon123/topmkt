# 탑마케팅 FCM 푸시 알림 - 앱 개발자 가이드

**버전**: v3.88.0
**작성일**: 2025-10-16
**대상**: Android / iOS / Flutter 앱 개발자

---

## 📋 목차

1. [개요](#개요)
2. [구현 요약](#구현-요약)
3. [Android 구현](#android-구현)
4. [iOS 구현](#ios-구현)
5. [Flutter 구현](#flutter-구현)
6. [테스트 방법](#테스트-방법)
7. [문제 해결](#문제-해결)

---

## 개요

### 목적

탑마케팅 앱에서 **FCM(Firebase Cloud Messaging) 푸시 알림**을 받을 수 있도록 설정합니다.

### 동작 흐름

```
┌──────────────────────────────────────────────────────────┐
│                     FCM 토큰 등록 플로우                   │
└──────────────────────────────────────────────────────────┘

1️⃣ 앱 실행 → Firebase FCM 토큰 생성
2️⃣ 메인 페이지(https://www.topmktx.com) 로딩
3️⃣ 앱이 JavaScript 함수 호출 (토큰 전달)
4️⃣ 웹이 백엔드 API로 토큰 저장
5️⃣ 완료! (이후 푸시 알림 수신 가능)
```

### 중요 포인트 ⭐

- ✅ **메인 페이지 진입할 때마다** JavaScript 함수 호출
- ✅ **DB 부하 최소화**: 웹에서 자동으로 중복 확인 (변경 시에만 저장)
- ✅ **로그인 필수**: 로그인하지 않은 사용자는 토큰 등록 불가
- ✅ **간단한 구현**: 앱에서는 JavaScript 함수만 호출하면 됨

---

## 구현 요약

### 앱에서 해야 할 일 (매우 간단!)

```
1. Firebase FCM 토큰 가져오기
2. WebView 메인 페이지 로딩 완료 감지
3. JavaScript 함수 호출 (토큰 전달)
```

### 호출할 JavaScript 함수

```javascript
registerFCMTokenFromApp(fcmToken, deviceType, deviceName, appVersion)
```

| 파라미터 | 타입 | 설명 | 예시 |
|----------|------|------|------|
| `fcmToken` | string | Firebase FCM 토큰 (필수) | `'eXAMPLE_token_123...'` |
| `deviceType` | string | 디바이스 타입 (필수) | `'android'` / `'ios'` / `'web'` |
| `deviceName` | string | 디바이스 이름 (필수) | `'Samsung Galaxy S23'` |
| `appVersion` | string | 앱 버전 (필수) | `'1.0.0'` |

### 반환값

```javascript
{
  status: 'success' | 'error',
  message: '메시지',
  action: 'skipped' | 'inserted' | 'updated',  // 성공 시
  changed: true | false                         // DB 변경 여부
}
```

---

## Android 구현

### 1️⃣ Firebase 설정

#### build.gradle (프로젝트 레벨)

```gradle
buildscript {
    dependencies {
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

#### build.gradle (앱 레벨)

```gradle
plugins {
    id 'com.android.application'
    id 'com.google.gms.google-services'
}

dependencies {
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-messaging-ktx'
}
```

#### google-services.json

Firebase Console에서 다운로드하여 `app/` 폴더에 추가

---

### 2️⃣ MainActivity.kt 구현

```kotlin
package com.topmkt.app

import android.os.Build
import android.os.Bundle
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.messaging.FirebaseMessaging

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private var fcmToken: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        // 1. FCM 토큰 미리 가져오기
        getFCMToken()

        // 2. WebView 설정
        setupWebView()

        // 3. 웹 로딩
        webView.loadUrl("https://www.topmktx.com/")
    }

    /**
     * FCM 토큰 가져오기
     */
    private fun getFCMToken() {
        FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
            if (task.isSuccessful) {
                fcmToken = task.result
                android.util.Log.d("FCM", "토큰 획득: ${fcmToken?.substring(0, 20)}...")
            } else {
                android.util.Log.e("FCM", "토큰 가져오기 실패", task.exception)
            }
        }
    }

    /**
     * WebView 설정
     */
    private fun setupWebView() {
        webView = findViewById(R.id.webView)

        // JavaScript 활성화
        webView.settings.javaScriptEnabled = true
        webView.settings.domStorageEnabled = true

        // WebViewClient 설정
        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)

                // 메인 페이지 진입 시 FCM 토큰 전송
                if (isMainPage(url)) {
                    sendFCMTokenToWeb()
                }
            }
        }
    }

    /**
     * 메인 페이지 확인
     */
    private fun isMainPage(url: String?): Boolean {
        return url?.let {
            it == "https://www.topmktx.com/" ||
            it == "https://www.topmktx.com/index.php" ||
            it.matches(Regex("https://www\\.topmktx\\.com/?$"))
        } ?: false
    }

    /**
     * FCM 토큰을 웹으로 전송 (JavaScript 함수 호출)
     */
    private fun sendFCMTokenToWeb() {
        val token = fcmToken ?: run {
            android.util.Log.w("FCM", "토큰이 아직 준비되지 않음")
            return
        }

        // 디바이스 정보
        val deviceName = "${Build.MANUFACTURER} ${Build.MODEL}"
        val appVersion = BuildConfig.VERSION_NAME

        // JavaScript 함수 호출
        val script = """
            (function() {
                if (typeof registerFCMTokenFromApp === 'function') {
                    registerFCMTokenFromApp(
                        '$token',
                        'android',
                        '$deviceName',
                        '$appVersion'
                    ).then(function(result) {
                        console.log('✅ 앱→웹 호출 결과:', result);
                    }).catch(function(error) {
                        console.error('❌ 앱→웹 호출 실패:', error);
                    });
                } else {
                    console.error('❌ registerFCMTokenFromApp 함수를 찾을 수 없습니다');
                }
            })();
        """.trimIndent()

        webView.evaluateJavascript(script, null)
        android.util.Log.d("FCM", "웹으로 토큰 전송 완료")
    }
}
```

---

### 3️⃣ AndroidManifest.xml

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">

    <!-- 인터넷 권한 -->
    <uses-permission android:name="android.permission.INTERNET" />

    <!-- 알림 권한 (Android 13+) -->
    <uses-permission android:name="android.permission.POST_NOTIFICATIONS" />

    <application
        android:name=".MyApplication"
        android:usesCleartextTraffic="false"
        ... >

        <activity android:name=".MainActivity" ... />

        <!-- Firebase Messaging Service -->
        <service
            android:name=".MyFirebaseMessagingService"
            android:exported="false">
            <intent-filter>
                <action android:name="com.google.firebase.MESSAGING_EVENT" />
            </intent-filter>
        </service>
    </application>
</manifest>
```

---

### 4️⃣ MyFirebaseMessagingService.kt

```kotlin
package com.topmkt.app

import android.util.Log
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class MyFirebaseMessagingService : FirebaseMessagingService() {

    /**
     * FCM 토큰 갱신 시 호출
     */
    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d("FCM", "새 토큰 생성: ${token.substring(0, 20)}...")

        // 토큰이 변경되었으므로 다음 메인 페이지 진입 시 자동으로 업데이트됨
    }

    /**
     * 푸시 메시지 수신 시 호출
     */
    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)

        Log.d("FCM", "푸시 수신: ${message.notification?.title}")

        // 알림 표시 로직
        // ...
    }
}
```

---

## iOS 구현

### 1️⃣ Firebase 설정

#### Podfile

```ruby
platform :ios, '13.0'

target 'TopMKT' do
  use_frameworks!

  pod 'Firebase/Messaging'
end
```

```bash
pod install
```

#### GoogleService-Info.plist

Firebase Console에서 다운로드하여 프로젝트에 추가

---

### 2️⃣ AppDelegate.swift

```swift
import UIKit
import Firebase
import UserNotifications

@main
class AppDelegate: UIResponder, UIApplicationDelegate, UNUserNotificationCenterDelegate {

    func application(_ application: UIApplication,
                     didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {

        // Firebase 초기화
        FirebaseApp.configure()

        // 알림 권한 요청
        UNUserNotificationCenter.current().delegate = self
        UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .badge, .sound]) { granted, error in
            if granted {
                DispatchQueue.main.async {
                    application.registerForRemoteNotifications()
                }
            }
        }

        return true
    }

    // APNS 토큰 등록 성공
    func application(_ application: UIApplication,
                     didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data) {
        Messaging.messaging().apnsToken = deviceToken
    }
}
```

---

### 3️⃣ ViewController.swift

```swift
import UIKit
import WebKit
import FirebaseMessaging

class ViewController: UIViewController, WKNavigationDelegate {

    var webView: WKWebView!
    var fcmToken: String?

    override func viewDidLoad() {
        super.viewDidLoad()

        // 1. FCM 토큰 미리 가져오기
        getFCMToken()

        // 2. WebView 설정
        setupWebView()

        // 3. 웹 로딩
        let url = URL(string: "https://www.topmktx.com/")!
        webView.load(URLRequest(url: url))
    }

    /// FCM 토큰 가져오기
    private func getFCMToken() {
        Messaging.messaging().token { token, error in
            if let token = token {
                self.fcmToken = token
                print("토큰 획득: \(token.prefix(20))...")
            } else if let error = error {
                print("토큰 가져오기 실패: \(error)")
            }
        }
    }

    /// WebView 설정
    private func setupWebView() {
        let config = WKWebViewConfiguration()
        config.preferences.javaScriptEnabled = true

        webView = WKWebView(frame: view.bounds, configuration: config)
        webView.navigationDelegate = self
        webView.autoresizingMask = [.flexibleWidth, .flexibleHeight]
        view.addSubview(webView)
    }

    /// 페이지 로딩 완료
    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        // 메인 페이지 진입 시 FCM 토큰 전송
        if let url = webView.url?.absoluteString, isMainPage(url: url) {
            sendFCMTokenToWeb()
        }
    }

    /// 메인 페이지 확인
    private func isMainPage(url: String) -> Bool {
        return url == "https://www.topmktx.com/" ||
               url == "https://www.topmktx.com/index.php" ||
               url.range(of: #"https://www\.topmktx\.com/?$"#, options: .regularExpression) != nil
    }

    /// FCM 토큰을 웹으로 전송 (JavaScript 함수 호출)
    private func sendFCMTokenToWeb() {
        guard let token = fcmToken else {
            print("토큰이 아직 준비되지 않음")
            return
        }

        let deviceName = UIDevice.current.model
        let appVersion = Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0.0"

        let script = """
        (function() {
            if (typeof registerFCMTokenFromApp === 'function') {
                registerFCMTokenFromApp(
                    '\(token)',
                    'ios',
                    '\(deviceName)',
                    '\(appVersion)'
                ).then(function(result) {
                    console.log('✅ 앱→웹 호출 결과:', result);
                }).catch(function(error) {
                    console.error('❌ 앱→웹 호출 실패:', error);
                });
            } else {
                console.error('❌ registerFCMTokenFromApp 함수를 찾을 수 없습니다');
            }
        })();
        """

        webView.evaluateJavaScript(script) { result, error in
            if let error = error {
                print("JavaScript 실행 실패: \(error)")
            } else {
                print("✅ 웹으로 토큰 전송 완료")
            }
        }
    }
}
```

---

## Flutter 구현

### 1️⃣ pubspec.yaml

```yaml
dependencies:
  flutter:
    sdk: flutter
  firebase_core: ^2.24.0
  firebase_messaging: ^14.7.0
  webview_flutter: ^4.4.0
```

```bash
flutter pub get
```

---

### 2️⃣ main.dart

```dart
import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:webview_flutter/webview_flutter.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: '탑마케팅',
      home: MainPage(),
    );
  }
}

class MainPage extends StatefulWidget {
  @override
  _MainPageState createState() => _MainPageState();
}

class _MainPageState extends State<MainPage> {
  late WebViewController _controller;
  String? fcmToken;

  @override
  void initState() {
    super.initState();
    initFCM();
  }

  /// FCM 초기화
  Future<void> initFCM() async {
    // 알림 권한 요청
    await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // FCM 토큰 가져오기
    fcmToken = await FirebaseMessaging.instance.getToken();
    print('토큰 획득: ${fcmToken?.substring(0, 20)}...');

    // 토큰 갱신 리스너
    FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
      fcmToken = newToken;
      print('새 토큰: ${newToken.substring(0, 20)}...');
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('탑마케팅'),
      ),
      body: WebView(
        initialUrl: 'https://www.topmktx.com/',
        javascriptMode: JavascriptMode.unrestricted,
        onWebViewCreated: (WebViewController controller) {
          _controller = controller;
        },
        onPageFinished: (String url) {
          // 메인 페이지 진입 시 FCM 토큰 전송
          if (isMainPage(url)) {
            sendFCMTokenToWeb();
          }
        },
      ),
    );
  }

  /// 메인 페이지 확인
  bool isMainPage(String url) {
    return url == 'https://www.topmktx.com/' ||
           url == 'https://www.topmktx.com/index.php' ||
           RegExp(r'https://www\.topmktx\.com/?$').hasMatch(url);
  }

  /// FCM 토큰을 웹으로 전송 (JavaScript 함수 호출)
  Future<void> sendFCMTokenToWeb() async {
    if (fcmToken == null) {
      print('토큰이 아직 준비되지 않음');
      return;
    }

    // 디바이스 정보
    final deviceType = Theme.of(context).platform == TargetPlatform.android
        ? 'android'
        : 'ios';
    final deviceName = Theme.of(context).platform == TargetPlatform.android
        ? 'Android Device'
        : 'iOS Device';
    const appVersion = '1.0.0'; // pubspec.yaml의 version과 동기화

    final script = '''
      (function() {
          if (typeof registerFCMTokenFromApp === 'function') {
              registerFCMTokenFromApp(
                  '$fcmToken',
                  '$deviceType',
                  '$deviceName',
                  '$appVersion'
              ).then(function(result) {
                  console.log('✅ 앱→웹 호출 결과:', result);
              }).catch(function(error) {
                  console.error('❌ 앱→웹 호출 실패:', error);
              });
          } else {
              console.error('❌ registerFCMTokenFromApp 함수를 찾을 수 없습니다');
          }
      })();
    ''';

    await _controller.runJavascript(script);
    print('✅ 웹으로 토큰 전송 완료');
  }
}
```

---

## 테스트 방법

### 1️⃣ Chrome DevTools (Android)

```bash
# Android 디바이스를 USB로 연결한 상태에서
chrome://inspect/#devices
```

1. **Devices** 탭에서 앱의 WebView 선택
2. **Console** 탭에서 로그 확인:
   ```
   📱 앱이 registerFCMTokenFromApp 호출
      토큰: eXAMPLE_token_123...
      디바이스: android Samsung Galaxy S23
      버전: 1.0.0
   ✅ FCM 토큰 신규 등록 완료
   ```

---

### 2️⃣ Safari Web Inspector (iOS)

1. **Mac Safari**: 개발자 메뉴 > iPhone 선택
2. **Console** 탭에서 로그 확인

---

### 3️⃣ 데이터베이스 확인

```sql
-- 토큰이 저장되었는지 확인
SELECT id, user_id, device_type, device_name, app_version, is_active, created_at
FROM fcm_tokens
WHERE user_id = YOUR_USER_ID
ORDER BY created_at DESC;
```

**예상 결과**:
```
| id | user_id | device_type | device_name          | app_version | is_active | created_at          |
|----|---------|-------------|----------------------|-------------|-----------|---------------------|
| 1  | 3       | android     | Samsung Galaxy S23   | 1.0.0       | 1         | 2025-10-16 20:00:00 |
```

---

### 4️⃣ 웹 API 직접 테스트 (Postman)

```bash
# 로그인 후 세션 쿠키 획득하여 테스트
POST https://www.topmktx.com/api/fcm/tokens

Headers:
  Content-Type: application/json
  Cookie: PHPSESSID=your_session_id

Body:
{
  "csrf_token": "your_csrf_token",
  "fcm_token": "test_token_12345",
  "device_type": "android",
  "device_name": "Test Device",
  "app_version": "1.0.0"
}

# 예상 응답
{
  "status": "success",
  "message": "FCM 토큰이 등록되었습니다.",
  "data": {
    "user_id": 3,
    "device_type": "android",
    "action": "inserted",
    "changed": true
  }
}
```

---

## 문제 해결

### ❌ "registerFCMTokenFromApp 함수를 찾을 수 없습니다"

**원인**: JavaScript 파일이 로드되기 전에 함수를 호출함

**해결**:
```javascript
// 리트라이 로직 추가
function sendFCMTokenWithRetry(token, deviceType, deviceName, appVersion, maxRetry = 5) {
    let attempt = 0;
    const interval = setInterval(() => {
        if (typeof registerFCMTokenFromApp === 'function') {
            clearInterval(interval);
            registerFCMTokenFromApp(token, deviceType, deviceName, appVersion);
        } else if (++attempt >= maxRetry) {
            clearInterval(interval);
            console.error('함수를 찾을 수 없음 (최대 재시도 초과)');
        }
    }, 200); // 0.2초마다 확인
}
```

---

### ❌ "로그인이 필요합니다"

**원인**: 사용자가 로그인하지 않음

**해결**: 로그인 후에만 토큰 전송하도록 확인
```javascript
// 웹에서 자동으로 처리됨 (401 에러 반환)
```

---

### ❌ FCM 토큰이 null

**원인**: Firebase 초기화가 완료되지 않음

**해결**:
```kotlin
// Android: 토큰 준비될 때까지 대기
private fun sendFCMTokenToWeb() {
    val token = fcmToken ?: run {
        // 토큰이 없으면 스킵 (다음 메인 페이지 진입 시 재시도)
        return
    }
    // ...
}
```

---

### ❌ 중복 호출 우려

**원인**: 메인 페이지를 여러 번 방문할 때마다 API 호출

**해결**: 웹에서 자동으로 처리
- DB 조회 후 동일한 토큰이면 SKIP
- 24시간 이내 재등록 방지 (클라이언트 측 - 선택사항)

---

## 연락처

**문제 발생 시**:
- 백엔드 개발팀: jh@wincard.kr
- Firebase Console: https://console.firebase.google.com/project/topmkt-832f2

---

**문서 버전**: 1.0
**최종 업데이트**: 2025-10-16
**작성자**: 탑마케팅 백엔드 팀
