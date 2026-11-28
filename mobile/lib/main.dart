import 'package:flutter/material.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_platform_interface/webview_flutter_platform_interface.dart';
import 'package:binaro_flutter/aplikasi/aplikasi.dart';
import 'package:binaro_flutter/inti/layanan/session.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi platform WebView untuk Android
  try {
    AndroidWebViewPlatform.registerWith();
  } catch (_) {
    try {
      WebViewPlatform.instance ??= AndroidWebViewPlatform();
    } catch (_) {}
  }

  // Muat sesi tersimpan: bila ada, langsung ke Dashboard tanpa login ulang.
  final sesiAwal = await AppSession.load();

  runApp(BinaroApp(sesiAwal: sesiAwal));
}
