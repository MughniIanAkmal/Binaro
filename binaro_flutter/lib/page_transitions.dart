import 'package:flutter/material.dart';

/// Jenis animasi transisi perpindahan halaman
enum TransitionType {
  /// Transisi Slide Halus (Geser penuh kanan-kiri dengan efek Parallax & bayangan kedalaman)
  sharedAxis,

  /// Transisi Fade Through (Memudar halus + zoom mikro, ideal untuk Login -> Dashboard)
  fadeThrough,

  /// Transisi Slide Up (Naik dari bawah dengan kurva lembut)
  slideUp,
}

/// Custom PageRoute dengan animasi Parallax dan kurva perlambatan alami
class SmoothPageRoute<T> extends PageRouteBuilder<T> {
  final Widget page;
  final TransitionType type;

  SmoothPageRoute({
    required this.page,
    this.type = TransitionType.sharedAxis,
    Duration duration = const Duration(milliseconds: 320),
    Duration reverseDuration = const Duration(milliseconds: 300),
  }) : super(
          pageBuilder: (context, animation, secondaryAnimation) => page,
          transitionDuration: duration,
          reverseTransitionDuration: reverseDuration,
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            switch (type) {
              case TransitionType.fadeThrough:
                final curved = CurvedAnimation(
                  parent: animation,
                  curve: const Cubic(0.2, 0.0, 0.0, 1.0),
                  reverseCurve: Curves.easeOutCubic,
                );
                final secondaryCurved = CurvedAnimation(
                  parent: secondaryAnimation,
                  curve: const Cubic(0.05, 0.7, 0.1, 1.0),
                  reverseCurve: Curves.easeOutCubic,
                );

                // Ketika ada halaman baru dibuka di atas halaman ini (cth: Profil di atas Dashboard)
                final slideBack = Tween<Offset>(
                  begin: Offset.zero,
                  end: const Offset(-0.22, 0.0),
                ).animate(secondaryCurved);

                return SlideTransition(
                  position: slideBack,
                  child: FadeTransition(
                    opacity: Tween<double>(begin: 0.0, end: 1.0).animate(curved),
                    child: ScaleTransition(
                      scale: Tween<double>(begin: 0.94, end: 1.0).animate(curved),
                      child: child,
                    ),
                  ),
                );

              case TransitionType.slideUp:
                final curved = CurvedAnimation(
                  parent: animation,
                  curve: Curves.easeOutCubic,
                  reverseCurve: Curves.easeOutCubic,
                );
                return SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0.0, 1.0),
                    end: Offset.zero,
                  ).animate(curved),
                  child: child,
                );

              case TransitionType.sharedAxis:
                // Kurva perlambatan alami yang halus (masuk dan kembali sama-sama lembut)
                final inCurve = CurvedAnimation(
                  parent: animation,
                  curve: const Cubic(0.05, 0.7, 0.1, 1.0),
                  reverseCurve: Curves.easeOutCubic,
                );
                final outCurve = CurvedAnimation(
                  parent: secondaryAnimation,
                  curve: const Cubic(0.05, 0.7, 0.1, 1.0),
                  reverseCurve: Curves.easeOutCubic,
                );

                // Halaman baru masuk dari kanan penuh (1.0 -> 0.0 saat buka, 0.0 -> 1.0 saat pop/kembali)
                final primarySlide = Tween<Offset>(
                  begin: const Offset(1.0, 0.0),
                  end: Offset.zero,
                ).animate(inCurve);

                // Halaman bawah mundur perlahan dengan efek parallax (-0.22 -> 0.0)
                final secondarySlide = Tween<Offset>(
                  begin: Offset.zero,
                  end: const Offset(-0.22, 0.0),
                ).animate(outCurve);

                return SlideTransition(
                  position: secondarySlide,
                  child: SlideTransition(
                    position: primarySlide,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.08),
                            blurRadius: 18,
                            offset: const Offset(-5, 0),
                          ),
                        ],
                      ),
                      child: child,
                    ),
                  ),
                );
            }
          },
        );
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
    final inCurve = CurvedAnimation(
      parent: animation,
      curve: const Cubic(0.05, 0.7, 0.1, 1.0),
      reverseCurve: Curves.easeOutCubic,
    );
    final outCurve = CurvedAnimation(
      parent: secondaryAnimation,
      curve: const Cubic(0.05, 0.7, 0.1, 1.0),
      reverseCurve: Curves.easeOutCubic,
    );

    final primarySlide = Tween<Offset>(
      begin: const Offset(1.0, 0.0),
      end: Offset.zero,
    ).animate(inCurve);

    final secondarySlide = Tween<Offset>(
      begin: Offset.zero,
      end: const Offset(-0.22, 0.0),
    ).animate(outCurve);

    return SlideTransition(
      position: secondarySlide,
      child: SlideTransition(
        position: primarySlide,
        child: DecoratedBox(
          decoration: BoxDecoration(
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.08),
                blurRadius: 18,
                offset: const Offset(-5, 0),
              ),
            ],
          ),
          child: child,
        ),
      ),
    );
  }
}
