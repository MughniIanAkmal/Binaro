import 'package:flutter/material.dart';
import 'package:binaro_flutter/inti/konstanta/app_decorations.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Kartu pengaturan preferensi aplikasi (berbasis SharedPreferences).
class PreferensiCard extends StatelessWidget {
  final bool simpanDraft;
  final bool cacheJadwal;
  final bool autoPetunjuk;
  final ValueChanged<bool> onSimpanDraftChanged;
  final ValueChanged<bool> onCacheJadwalChanged;
  final ValueChanged<bool> onAutoPetunjukChanged;

  const PreferensiCard({
    super.key,
    required this.simpanDraft,
    required this.cacheJadwal,
    required this.autoPetunjuk,
    required this.onSimpanDraftChanged,
    required this.onCacheJadwalChanged,
    required this.onAutoPetunjukChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: AppDecorations.card(borderRadius: 18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.tune_rounded, color: AppColors.primary, size: 21),
              SizedBox(width: 8),
              Text(
                'Preferensi Aplikasi',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: AppColors.textPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          _PreferensiSwitchRow(
            title: 'Simpan Draft Jawaban Ujian',
            subtitle: 'Simpan otomatis jawaban ujian ke SQLite lokal.',
            value: simpanDraft,
            onChanged: onSimpanDraftChanged,
          ),
          const Divider(height: 14, color: AppColors.border),
          _PreferensiSwitchRow(
            title: 'Cache Jadwal Offline',
            subtitle: 'Tampilkan jadwal tersimpan saat koneksi terputus.',
            value: cacheJadwal,
            onChanged: onCacheJadwalChanged,
          ),
          const Divider(height: 14, color: AppColors.border),
          _PreferensiSwitchRow(
            title: 'Otomatis Siap Ujian',
            subtitle: 'Centang konfirmasi petunjuk ujian secara otomatis.',
            value: autoPetunjuk,
            onChanged: onAutoPetunjukChanged,
          ),
        ],
      ),
    );
  }
}

class _PreferensiSwitchRow extends StatelessWidget {
  final String title;
  final String subtitle;
  final bool value;
  final ValueChanged<bool> onChanged;

  const _PreferensiSwitchRow({
    required this.title,
    required this.subtitle,
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: const TextStyle(
                  fontSize: 11.5,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
        Switch.adaptive(
          value: value,
          activeTrackColor: AppColors.primary,
          onChanged: onChanged,
        ),
      ],
    );
  }
}
