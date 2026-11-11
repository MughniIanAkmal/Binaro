import 'package:flutter/material.dart';

/// Jenis animasi transisi perpindahan halaman
enum TransitionType {
  /// Transisi Shared Axis (Geser lembut 24% dengan Fade & Micro-Scale, kedalaman alami)
  sharedAxis,

  /// Transisi Fade Through (Cross-fade murni + zoom mikro tanpa geser, ideal untuk Bottom Nav & Login)
  fadeThrough,

  /// Transisi Slide Up (Naik lembut dari bawah dengan fade, ideal untuk modal/petunjuk)
  slideUp,
}

/// Custom PageRoute dengan animasi ultra-halus dan kurva perlambatan alami
class SmoothPageRoute<T> extends PageRouteBuilder<T> {
  final Widget page;
  final TransitionType type;

  SmoothPageRoute({
    required this.page,
    this.type = TransitionType.sharedAxis,
    Duration duration = const Duration(milliseconds: 280),
    Duration reverseDuration = const Duration(milliseconds: 250),
  }) : super(
          pageBuilder: (context, animation, secondaryAnimation) => page,
          transitionDuration: duration,
          reverseTransitionDuration: reverseDuration,
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            return buildSmoothTransition(
              type: type,
              animation: animation,
              secondaryAnimation: secondaryAnimation,
              child: child,
            );
          },
        );
}

/// Helper builder untuk transisi halus yang konsisten
Widget buildSmoothTransition({
  required TransitionType type,
  required Animation<double> animation,
  required Animation<double> secondaryAnimation,
  required Widget child,
}) {
  switch (type) {
    case TransitionType.fadeThrough:
      final curvedAnimation = CurvedAnimation(
        parent: animation,
        curve: Curves.easeOutCubic,
        reverseCurve: Curves.easeOutCubic,
      );

      // Halaman baru memudar masuk + sedikit micro-zoom untuk dinamisme
      final fadeIn = Tween<double>(
        begin: 0.0,
        end: 1.0,
      ).animate(CurvedAnimation(
        parent: animation,
        curve: const Interval(0.15, 1.0, curve: Curves.easeOutCubic),
        reverseCurve: const Interval(0.0, 0.85, curve: Curves.easeInCubic),
      ));

      final scaleIn = Tween<double>(
        begin: 0.97,
        end: 1.0,
      ).animate(curvedAnimation);

      // Halaman sebelumnya memudar keluar dengan tenang tanpa geseran liar
      final fadeOut = Tween<double>(
        begin: 1.0,
        end: 0.0,
      ).animate(CurvedAnimation(
        parent: secondaryAnimation,
        curve: const Interval(0.0, 0.45, curve: Curves.easeOutCubic),
        reverseCurve: const Interval(0.55, 1.0, curve: Curves.easeInCubic),
      ));

      return FadeTransition(
        opacity: fadeOut,
        child: ScaleTransition(
          scale: scaleIn,
          child: FadeTransition(
            opacity: fadeIn,
            child: child,
          ),
        ),
      );

    case TransitionType.slideUp:
      final curvedAnimation = CurvedAnimation(
        parent: animation,
        curve: Curves.easeOutCubic,
        reverseCurve: Curves.easeOutCubic,
      );

      final slideIn = Tween<Offset>(
        begin: const Offset(0.0, 0.16),
        end: Offset.zero,
      ).animate(curvedAnimation);

      final fadeIn = Tween<double>(
        begin: 0.0,
        end: 1.0,
      ).animate(CurvedAnimation(
        parent: animation,
        curve: const Interval(0.0, 0.85, curve: Curves.easeOut),
      ));

      final scaleIn = Tween<double>(
        begin: 0.97,
        end: 1.0,
      ).animate(curvedAnimation);

      return SlideTransition(
        position: slideIn,
        child: ScaleTransition(
          scale: scaleIn,
          child: FadeTransition(
            opacity: fadeIn,
            child: child,
          ),
        ),
      );

    case TransitionType.sharedAxis:
      // Kurva perlambatan alami yang super halus
      final inCurve = CurvedAnimation(
        parent: animation,
        curve: Curves.easeOutCubic,
        reverseCurve: Curves.easeOutCubic,
      );
      final outCurve = CurvedAnimation(
        parent: secondaryAnimation,
        curve: Curves.easeOutCubic,
        reverseCurve: Curves.easeOutCubic,
      );

      // Halaman baru masuk dengan geser lembut 24% dari kanan + fade in halus
      final slideIn = Tween<Offset>(
        begin: const Offset(0.24, 0.0),
        end: Offset.zero,
      ).animate(inCurve);

      final fadeIn = Tween<double>(
        begin: 0.0,
        end: 1.0,
      ).animate(CurvedAnimation(
        parent: animation,
        curve: const Interval(0.0, 0.85, curve: Curves.easeOut),
        reverseCurve: const Interval(0.15, 1.0, curve: Curves.easeIn),
      ));

      final scaleIn = Tween<double>(
        begin: 0.975,
        end: 1.0,
      ).animate(inCurve);

      // Halaman bawah yang ditinggalkan mundur perlahan 8% (parallax halus) & meredup lembut
      final slideOut = Tween<Offset>(
        begin: Offset.zero,
        end: const Offset(-0.08, 0.0),
      ).animate(outCurve);

      final fadeOut = Tween<double>(
        begin: 1.0,
        end: 0.88,
      ).animate(outCurve);

      final scaleOut = Tween<double>(
        begin: 1.0,
        end: 0.98,
      ).animate(outCurve);

      return SlideTransition(
        position: slideOut,
        child: ScaleTransition(
          scale: scaleOut,
          child: FadeTransition(
            opacity: fadeOut,
            child: SlideTransition(
              position: slideIn,
              child: ScaleTransition(
                scale: scaleIn,
                child: FadeTransition(
                  opacity: fadeIn,
                  child: child,
                ),
              ),
            ),
          ),
        ),
      );
  }
}

/// Global PageTransitionsBuilder untuk tema aplikasi di main.dart
class SmoothPageTransitionsBuilder extends PageTransitionsBuilder {
  const SmoothPageTransitionsBuilder();

  @override
  Widget buildTransitions<T>(
    PageRoute<T> route,
    BuildContext context,
    Animation<double> animation,
    Animation<double> secondaryAnimation,
    Widget child,
  ) {
    return buildSmoothTransition(
      type: TransitionType.sharedAxis,
      animation: animation,
      secondaryAnimation: secondaryAnimation,
      child: child,
    );
  }
}
