import 'package:flutter/material.dart';

/// Centralized application constants following standard Dart/Flutter naming conventions.
class AppConstants {
  // App metadata
  static const String appName = 'Binaro';
  static const String appSubtitle = 'SDN Kalitapen 1';
  static const String appVersion = '1.0.0';

  // Network configuration
  static const String baseUrl = 'http://192.168.18.9:8000/api';
  static const Duration timeoutDuration = Duration(seconds: 10);

  // Storage keys / Default fallback IDs
  static const int defaultSiswaId = 7;
  static const String defaultSiswaNama = 'Budi Santoso';
  static const String defaultSiswaKelas = 'Kelas 5 • SDN Kalitapen 01';
}

/// Centralized application colors palette following standard Flutter naming conventions.
class AppColors {
  // Primary brand palette
  static const Color primary = Color(0xFF1D5A86);
  static const Color primaryDark = Color(0xFF154566);
  static const Color primaryLight = Color(0xFFE8F1F8);

  // Secondary brand palette
  static const Color secondary = Color(0xFFFFA000);
  static const Color secondaryDark = Color(0xFFE68A00);
  static const Color secondaryLight = Color(0xFFFFF7ED);

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
}
