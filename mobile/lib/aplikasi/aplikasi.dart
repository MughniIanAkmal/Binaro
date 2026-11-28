import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:binaro_flutter/fitur/autentikasi/screens/login_page.dart';
import 'package:binaro_flutter/fitur/beranda/screens/dashboard_page.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';
import 'package:binaro_flutter/inti/layanan/page_transitions.dart';

class BinaroApp extends StatelessWidget {
  final Map<String, dynamic>? sesiAwal;

  const BinaroApp({super.key, this.sesiAwal});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: '${AppConstants.appName} - ${AppConstants.appSubtitle}',
      debugShowCheckedModeBanner: false,
      scrollBehavior: const MaterialScrollBehavior().copyWith(
        dragDevices: {
          PointerDeviceKind.touch,
          PointerDeviceKind.mouse,
          PointerDeviceKind.trackpad,
          PointerDeviceKind.stylus,
        },
      ),
      // Batasi skala teks sistem agar layout padat tidak overflow (T17)
      builder: (context, child) => MediaQuery(
        data: MediaQuery.of(context).copyWith(
          textScaler: MediaQuery.textScalerOf(context)
              .clamp(maxScaleFactor: 1.3),
        ),
        child: child!,
      ),
      theme: ThemeData(
        fontFamily: 'Plus Jakarta Sans',
        colorScheme: ColorScheme.fromSeed(seedColor: AppColors.primary),
        useMaterial3: true,
        textTheme: const TextTheme(
          // Skala design-system.md §2.1
          displaySmall: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w800,
              height: 32 / 24,
              color: AppColors.textPrimary),
          headlineSmall: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              height: 1.4,
              color: AppColors.textPrimary),
          titleLarge: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              height: 1.4,
              color: AppColors.textPrimary),
          titleMedium: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              height: 1.5,
              color: AppColors.textPrimary),
          bodyLarge: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w400,
              height: 1.55,
              color: AppColors.textPrimary),
          bodyMedium: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w400,
              height: 1.5,
              color: AppColors.textSecondary),
          bodySmall: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w400,
              height: 1.45,
              color: AppColors.textSecondary),
          labelLarge: TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
          labelMedium: TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
          labelSmall: TextStyle(fontSize: 10, fontWeight: FontWeight.w500),
        ),
        pageTransitionsTheme: const PageTransitionsTheme(
          builders: {
            TargetPlatform.android: SmoothPageTransitionsBuilder(),
            TargetPlatform.iOS: SmoothPageTransitionsBuilder(),
            TargetPlatform.windows: SmoothPageTransitionsBuilder(),
          },
        ),
      ),
      home: sesiAwal != null
          ? DashboardPage(user: sesiAwal)
          : const LoginPage(),
    );
  }
}
