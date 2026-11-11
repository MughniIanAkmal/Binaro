import 'package:flutter/material.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_platform_interface/webview_flutter_platform_interface.dart';
import 'screens/login_page.dart';
import 'utils/constants.dart';
import 'utils/page_transitions.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi platform WebView untuk Android
  try {
    AndroidWebViewPlatform.registerWith();
  } catch (_) {
    try {
      WebViewPlatform.instance ??= AndroidWebViewPlatform();
    } catch (_) {}
  }

  runApp(const BinaroApp());
}

class BinaroApp extends StatelessWidget {
  const BinaroApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: '${AppConstants.appName} - ${AppConstants.appSubtitle}',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        fontFamily: 'Roboto',
        colorScheme: ColorScheme.fromSeed(seedColor: AppColors.primary),
        useMaterial3: true,
        pageTransitionsTheme: const PageTransitionsTheme(
          builders: {
            TargetPlatform.android: SmoothPageTransitionsBuilder(),
            TargetPlatform.iOS: SmoothPageTransitionsBuilder(),
            TargetPlatform.windows: SmoothPageTransitionsBuilder(),
          },
        ),
      ),
      home: const LoginPage(),
    );
  }
}
