import 'package:flutter/material.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Base layout bersama untuk tampilan status (error maupun kosong) agar tidak
/// ada duplikasi struktur Center -> Padding -> Column -> IconBadge -> Text.
class _StateViewBase extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final Color iconBgColor;
  final String title;
  final String message;
  final EdgeInsetsGeometry padding;
  final Widget? action;

  const _StateViewBase({
    required this.icon,
    required this.iconColor,
    required this.iconBgColor,
    required this.title,
    required this.message,
    this.padding = const EdgeInsets.symmetric(horizontal: 32),
    this.action,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: padding,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: iconBgColor,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(icon, color: iconColor, size: 28),
            ),
            const SizedBox(height: 14),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 12.5,
                height: 1.5,
                color: AppColors.textSecondary,
              ),
            ),
            if (action != null) ...[
              const SizedBox(height: 18),
              action!,
            ],
          ],
        ),
      ),
    );
  }
}

/// Tampilan saat gagal memuat data dari server.
/// Wajib menyediakan [onRetry] agar pengguna bisa mencoba lagi.
class ErrorState extends StatelessWidget {
  final String title;
  final String message;
  final VoidCallback? onRetry;

  const ErrorState({
    super.key,
    this.title = 'Gagal Memuat Data',
    this.message = 'Terjadi kendala saat menghubungi server.',
    this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return _StateViewBase(
      icon: Icons.cloud_off_rounded,
      iconColor: AppColors.error,
      iconBgColor: AppColors.errorLight,
      title: title,
      message: message,
      action: onRetry == null
          ? null
          : ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded, size: 19),
              label: const Text(
                'Coba Lagi',
                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                elevation: 0,
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
    );
  }
}

/// Tampilan saat data kosong.
class EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;

  const EmptyState({
    super.key,
    required this.icon,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return _StateViewBase(
      icon: icon,
      iconColor: AppColors.textMuted,
      iconBgColor: AppColors.surfaceAlt,
      title: title,
      message: message,
      padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
      action: (actionLabel != null && onAction != null)
          ? TextButton(
              onPressed: onAction,
              style: TextButton.styleFrom(
                foregroundColor: AppColors.primary,
                textStyle:
                    const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
              ),
              child: Text(actionLabel!),
            )
          : null,
    );
  }
}
