import 'package:flutter/material.dart';

/// Pemetaan terpusat dari string `tipe_icon` (backend) ke [IconData] Material.
class IconHelper {
  IconHelper._();

  static const Map<String, IconData> _iconMap = {
    'math': Icons.calculate_rounded,
    'science': Icons.biotech_rounded,
    'book': Icons.menu_book_rounded,
    'star': Icons.star_rounded,
    'palette': Icons.palette_rounded,
    'chat': Icons.chat_bubble_rounded,
    'shield': Icons.shield_outlined,
  };

  /// Kembalikan [IconData] sesuai [tipe], atau [fallback] bila tidak dikenali.
  static IconData fromTipe(
    String? tipe, {
    IconData fallback = Icons.school_rounded,
  }) {
    if (tipe == null || tipe.isEmpty) return fallback;
    return _iconMap[tipe] ?? fallback;
  }
}
