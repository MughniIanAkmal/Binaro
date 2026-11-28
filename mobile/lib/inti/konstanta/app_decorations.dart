import 'package:flutter/material.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Dekorasi standar kartu dan container Binaro agar tidak terjadi duplikasi
/// `BoxDecoration` di berbagai layar.
class AppDecorations {
  AppDecorations._();

  /// Dekorasi kartu putih standar dengan border halus dan bayangan lembut.
  static BoxDecoration card({
    Color color = AppColors.surface,
    Color borderColor = AppColors.border,
    double borderRadius = 16,
    double borderWidth = 1,
    double shadowAlpha = 0.02,
    double blurRadius = 8,
  }) {
    return BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(borderRadius),
      border: Border.all(color: borderColor, width: borderWidth),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withValues(alpha: shadowAlpha),
          blurRadius: blurRadius,
          offset: const Offset(0, 3),
        ),
      ],
    );
  }
}
