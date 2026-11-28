import 'package:flutter/material.dart';

/// Jenis animasi transisi perpindahan halaman.
enum TransitionType {
  /// Transisi Shared Axis (geser lembut dengan fade & micro-scale).
  sharedAxis,

  /// Transisi Fade Through (cross-fade + zoom mikro untuk Bottom Nav & Login).
  fadeThrough,

  /// Transisi Slide Up (naik lembut dari bawah dengan fade untuk modal).
  slideUp,
}

/// Custom PageRoute dengan animasi halus dan kurva perlambatan alami.
class SmoothPageRoute<T> extends PageRouteBuilder<T> {
  final Widget page;
  final TransitionType type;

  static const Duration _defaultDuration = Duration(milliseconds: 280);
  static const Duration _defaultReverseDuration = Duration(milliseconds: 250);

  SmoothPageRoute({
    required this.page,
    this.type = TransitionType.sharedAxis,
    Duration duration = _defaultDuration,
    Duration reverseDuration = _defaultReverseDuration,
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

const double _fadeThroughScaleBegin = 0.97;
const double _slideUpOffsetY = 0.16;
const double _sharedAxisSlideInX = 0.24;
const double _sharedAxisSlideOutX = -0.08;
const double _sharedAxisScaleIn = 0.975;
const double _sharedAxisScaleOut = 0.98;
const double _sharedAxisFadeOutEnd = 0.88;

/// Helper builder untuk transisi halus yang konsisten.
Widget buildSmoothTransition({
  required TransitionType type,
  required Animation<double> animation,
  required Animation<double> secondaryAnimation,
  required Widget child,
}) {
  return switch (type) {
    TransitionType.fadeThrough =>
      _buildFadeThrough(animation, secondaryAnimation, child),
    TransitionType.slideUp => _buildSlideUp(animation, child),
    TransitionType.sharedAxis =>
      _buildSharedAxis(animation, secondaryAnimation, child),
  };
}

Widget _buildFadeThrough(
  Animation<double> animation,
  Animation<double> secondaryAnimation,
  Widget child,
) {
  final curvedAnimation = CurvedAnimation(
    parent: animation,
    curve: Curves.easeOutCubic,
    reverseCurve: Curves.easeOutCubic,
  );
  final fadeIn = Tween<double>(begin: 0.0, end: 1.0).animate(CurvedAnimation(
    parent: animation,
    curve: const Interval(0.15, 1.0, curve: Curves.easeOutCubic),
    reverseCurve: const Interval(0.0, 0.85, curve: Curves.easeInCubic),
  ));
  final scaleIn = Tween<double>(begin: _fadeThroughScaleBegin, end: 1.0)
      .animate(curvedAnimation);
  final fadeOut = Tween<double>(begin: 1.0, end: 0.0).animate(CurvedAnimation(
    parent: secondaryAnimation,
    curve: const Interval(0.0, 0.45, curve: Curves.easeOutCubic),
    reverseCurve: const Interval(0.55, 1.0, curve: Curves.easeInCubic),
  ));

  return FadeTransition(
    opacity: fadeOut,
    child: ScaleTransition(
      scale: scaleIn,
      child: FadeTransition(opacity: fadeIn, child: child),
    ),
  );
}

Widget _buildSlideUp(Animation<double> animation, Widget child) {
  final curvedAnimation = CurvedAnimation(
    parent: animation,
    curve: Curves.easeOutCubic,
    reverseCurve: Curves.easeOutCubic,
  );
  final slideIn = Tween<Offset>(
    begin: const Offset(0.0, _slideUpOffsetY),
    end: Offset.zero,
  ).animate(curvedAnimation);
  final fadeIn = Tween<double>(begin: 0.0, end: 1.0).animate(CurvedAnimation(
    parent: animation,
    curve: const Interval(0.0, 0.85, curve: Curves.easeOut),
  ));
  final scaleIn = Tween<double>(begin: _fadeThroughScaleBegin, end: 1.0)
      .animate(curvedAnimation);

  return SlideTransition(
    position: slideIn,
    child: ScaleTransition(
      scale: scaleIn,
      child: FadeTransition(opacity: fadeIn, child: child),
    ),
  );
}

Widget _buildSharedAxis(
  Animation<double> animation,
  Animation<double> secondaryAnimation,
  Widget child,
) {
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

  final slideIn = Tween<Offset>(
    begin: const Offset(_sharedAxisSlideInX, 0.0),
    end: Offset.zero,
  ).animate(inCurve);
  final fadeIn = Tween<double>(begin: 0.0, end: 1.0).animate(CurvedAnimation(
    parent: animation,
    curve: const Interval(0.0, 0.85, curve: Curves.easeOut),
    reverseCurve: const Interval(0.15, 1.0, curve: Curves.easeIn),
  ));
  final scaleIn =
      Tween<double>(begin: _sharedAxisScaleIn, end: 1.0).animate(inCurve);

  final slideOut = Tween<Offset>(
    begin: Offset.zero,
    end: const Offset(_sharedAxisSlideOutX, 0.0),
  ).animate(outCurve);
  final fadeOut = Tween<double>(begin: 1.0, end: _sharedAxisFadeOutEnd)
      .animate(outCurve);
  final scaleOut =
      Tween<double>(begin: 1.0, end: _sharedAxisScaleOut).animate(outCurve);

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
            child: FadeTransition(opacity: fadeIn, child: child),
          ),
        ),
      ),
    ),
  );
}

/// Global PageTransitionsBuilder untuk tema aplikasi.
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
