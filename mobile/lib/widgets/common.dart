import 'package:flutter/material.dart';
import '../utils/constants.dart';

/// App bar baku Binaro: logo kotak amber + kicker "SAHABAT BELAJAR" + judul.
/// Satu-satunya app bar untuk halaman tab utama (Beranda, Ujian, Pembelajaran,
/// Profil). Halaman sub dengan tombol kembali memakai pola AppBar sendiri
/// hingga T10 lanjutan.
class BinaroAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final List<Widget> actions;
  final bool showBack;

  const BinaroAppBar({
    super.key,
    required this.title,
    this.actions = const [],
    this.showBack = false,
  });

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: AppColors.primary,
      elevation: 0,
      automaticallyImplyLeading: false,
      leading: showBack
          ? IconButton(
              icon: const Icon(Icons.arrow_back_rounded,
                  color: Colors.white, size: 24),
              onPressed: () => Navigator.maybePop(context),
            )
          : null,
      titleSpacing: showBack ? 0 : 16,
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.accent,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.school_rounded,
              color: Colors.white,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'SAHABAT BELAJAR',
                style: TextStyle(
                  fontSize: 10,
                  letterSpacing: 0.8,
                  fontWeight: FontWeight.w800,
                  color: AppColors.accentBright,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
      actions: actions,
    );
  }
}

/// Snackbar baku Binaro — satu helper untuk semua layar.
/// [type]: success (hijau), error (merah), atau info (primary).
void showAppSnackbar(
  BuildContext context,
  String message, {
  String type = 'info',
  Duration duration = const Duration(seconds: 3),
}) {
  final Color background = switch (type) {
    'success' => AppColors.success,
    'error' => AppColors.error,
    _ => AppColors.primary,
  };
  final IconData icon = switch (type) {
    'success' => Icons.check_circle_rounded,
    'error' => Icons.error_rounded,
    _ => Icons.info_rounded,
  };

  ScaffoldMessenger.of(context).hideCurrentSnackBar();
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Row(
        children: [
          Icon(icon, color: Colors.white, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
      backgroundColor: background,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      duration: duration,
    ),
  );
}
