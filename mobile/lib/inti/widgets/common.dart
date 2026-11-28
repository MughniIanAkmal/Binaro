import 'package:flutter/material.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';
import 'package:binaro_flutter/fitur/notifikasi/widgets/lonceng_notifikasi.dart';

/// Jenis visual untuk [showAppSnackbar].
enum SnackbarType { info, success, error }

/// App bar baku Binaro: logo resmi Binaro + judul halaman.
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
          Image.asset(
            AppConstants.logoAsset,
            height: 40,
            fit: BoxFit.contain,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
          ),
        ],
      ),
      actions: [...actions, const LoncengNotifikasi()],
    );
  }
}

/// Snackbar baku Binaro — satu helper untuk semua layar.
void showAppSnackbar(
  BuildContext context,
  String message, {
  SnackbarType type = SnackbarType.info,
  Duration duration = const Duration(seconds: 3),
}) {
  final (Color background, IconData icon) = switch (type) {
    SnackbarType.success => (AppColors.success, Icons.check_circle_rounded),
    SnackbarType.error => (AppColors.error, Icons.error_rounded),
    SnackbarType.info => (AppColors.primary, Icons.info_rounded),
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
