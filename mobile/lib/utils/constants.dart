import 'package:flutter/material.dart';

/// Centralized application constants following standard Dart/Flutter naming conventions.
class AppConstants {
  // App metadata
  static const String appName = 'Binaro';
  static const String appSubtitle = 'SDN Kalitapen 1';
  static const String appVersion = '1.0.0';

  // Network configuration
  static const String baseUrl = 'http://192.168.18.187:8000/api';
  static const Duration timeoutDuration = Duration(seconds: 10);

  // Storage keys / Default fallback ID (0 = belum ada sesi valid)
  static const int defaultSiswaId = 0;
  static const String defaultSiswaNama = 'Siswa';
  static const String defaultSiswaKelas = 'SDN Kalitapen 01';
}

/// Token warna Binaro — satu-satunya tempat nilai hex boleh berada.
/// Rujukan: docs/design-system.md §1.2 (keputusan D1: primary #13527D).
class AppColors {
  // Primary brand palette
  static const Color primary = Color(0xFF13527D);
  static const Color primaryDark = Color(0xFF0E3D5D);
  static const Color primarySoft = Color(0xFF1E5D88);
  static const Color primaryLight = Color(0xFFEFF6FF);

  // Accent palette
  static const Color accent = Color(0xFFF59E0B);
  static const Color accentBright = Color(0xFFFBBF24);
  static const Color accentDeep = Color(0xFFD97706);

  // Background and surface
  static const Color background = Color(0xFFF8FAFC);
  static const Color surface = Colors.white;
  static const Color surfaceAlt = Color(0xFFF1F5F9);

  // Borders and dividers
  static const Color border = Color(0xFFE2E8F0);
  static const Color borderSubtle = Color(0xFFF1F5F9);

  // Typography / Text colors
  static const Color textPrimary = Color(0xFF1E293B);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textMuted = Color(0xFF94A3B8);

  // Status colors
  static const Color success = Color(0xFF16A34A);
  static const Color successLight = Color(0xFFDCFCE7);
  static const Color warning = Color(0xFFF59E0B);
  static const Color warningLight = Color(0xFFFEF3C7);
  static const Color error = Color(0xFFDC2626);
  static const Color errorLight = Color(0xFFFEE2E2);
  static const Color info = Color(0xFF0284C7);
  static const Color infoLight = Color(0xFFE0F2FE);

  // Warna gelap khusus (latar login / gradien overlay)
  static const Color navy950 = Color(0xFF0B2A44);
}
