import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:binaro_flutter/inti/layanan/api_client.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Pop up konfirmasi sebelum siswa masuk mengerjakan kuis.
///
/// Ditampilkan di atas halaman yang sedang terbuka (tanpa pindah halaman),
/// berisi judul kuis, jumlah soal, durasi, dan peringatan bahwa selama
/// pengerjaan siswa tidak bisa kembali. Mengembalikan `true` bila siswa
/// menekan "Ya, Mulai Kuis".
class KonfirmasiKuisDialog extends StatefulWidget {
  final int? quizId;

  const KonfirmasiKuisDialog({super.key, this.quizId});

  static Future<bool> tampilkan(BuildContext context, {int? quizId}) async {
    final bool? hasil = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (_) => KonfirmasiKuisDialog(quizId: quizId),
    );
    return hasil ?? false;
  }

  @override
  State<KonfirmasiKuisDialog> createState() => _KonfirmasiKuisDialogState();
}

class _KonfirmasiKuisDialogState extends State<KonfirmasiKuisDialog> {
  bool _memuat = true;
  String _judul = 'Kuis Siswa';
  int _totalSoal = 0;
  int _durasiDetik = 900;

  @override
  void initState() {
    super.initState();
    _muatInfoKuis();
  }

  Future<void> _muatInfoKuis() async {
    final String path = widget.quizId != null
        ? '/siswa/quiz/${widget.quizId}'
        : '/siswa/quiz';
    try {
      final response = await ApiClient.get(
        path,
        timeout: const Duration(seconds: 8),
      );
      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          final data = decoded['data'];
          final List<dynamic> soals = data['soal'] ?? [];
          if (mounted) {
            setState(() {
              _judul = data['judul_quiz'] ?? _judul;
              _totalSoal = data['total_soal'] ?? soals.length;
              _durasiDetik = data['durasi_detik'] ?? 900;
              _memuat = false;
            });
            return;
          }
        }
      }
    } catch (_) {}
    if (mounted) {
      setState(() => _memuat = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool belumTersedia = !_memuat && _totalSoal == 0;
    final int durasiMenit = (_durasiDetik / 60).ceil();

    return Dialog(
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 26),
        child: _memuat
            ? const Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: AppColors.primary),
                  SizedBox(height: 16),
                  Text(
                    'Memuat info kuis...',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                  ),
                ],
              )
            : Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 68,
                    height: 68,
                    decoration: const BoxDecoration(
                      color: AppColors.warningLight,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      belumTersedia ? Icons.quiz_outlined : Icons.quiz_rounded,
                      size: 34,
                      color: AppColors.accent,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    belumTersedia ? 'Kuis Belum Tersedia' : 'Siap Mengerjakan Kuis?',
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    belumTersedia
                        ? 'Guru belum mengunggah butir soal kuis untuk materi ini.'
                        : _judul,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                    textAlign: TextAlign.center,
                  ),
                  if (!belumTersedia) ...[
                    const SizedBox(height: 14),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: AppColors.primaryLight,
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            '$_totalSoal Soal',
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.primary),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: AppColors.primaryLight,
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            '$durasiMenit Menit',
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.primary),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppColors.warningLight,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.warning_amber_rounded, size: 18, color: AppColors.accent),
                          SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Setelah kuis dimulai, kamu tidak bisa kembali atau keluar sampai kuis selesai dikerjakan.',
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textPrimary, height: 1.4),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  const SizedBox(height: 20),
                  if (belumTersedia)
                    SizedBox(
                      width: double.infinity,
                      height: 46,
                      child: ElevatedButton(
                        onPressed: () => Navigator.pop(context, false),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Kembali', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                      ),
                    )
                  else
                    Row(
                      children: [
                        Expanded(
                          child: SizedBox(
                            height: 46,
                            child: OutlinedButton(
                              onPressed: () => Navigator.pop(context, false),
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: AppColors.primary, width: 1.4),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: const Text(
                                'Belum, Kembali',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.primary),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: SizedBox(
                            height: 46,
                            child: ElevatedButton(
                              onPressed: () => Navigator.pop(context, true),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.accent,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: const Text(
                                'Ya, Mulai Kuis',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                ],
              ),
      ),
    );
  }
}
