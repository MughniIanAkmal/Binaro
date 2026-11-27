import 'dart:convert';

import 'package:flutter/material.dart';

import '../utils/api_client.dart';
import '../utils/constants.dart';
import '../utils/session.dart';

/// Panel notifikasi PR berbentuk dropdown yang menggantung tepat di
/// bawah header (tanpa pindah halaman), berisi pemberitahuan PR yang
/// dikirim guru ke siswa.
class NotifikasiPanel extends StatefulWidget {
  const NotifikasiPanel({super.key});

  /// Tampilkan panel notifikasi sebagai dropdown di bawah app bar.
  static Future<void> tampilkan(BuildContext context) {
    return showGeneralDialog<void>(
      context: context,
      barrierDismissible: true,
      barrierLabel: 'Notifikasi',
      barrierColor: Colors.black.withValues(alpha: 0.45),
      transitionDuration: const Duration(milliseconds: 220),
      pageBuilder: (dialogContext, _, __) {
        final size = MediaQuery.of(dialogContext).size;
        return Align(
          alignment: Alignment.topCenter,
          child: Padding(
            padding: EdgeInsets.only(
              top: MediaQuery.of(dialogContext).padding.top + kToolbarHeight + 8,
              left: 12,
              right: 12,
            ),
            child: Material(
              color: Colors.white,
              elevation: 14,
              shadowColor: Colors.black.withValues(alpha: 0.3),
              borderRadius: BorderRadius.circular(20),
              clipBehavior: Clip.antiAlias,
              child: ConstrainedBox(
                constraints: BoxConstraints(maxHeight: size.height * 0.72),
                child: const NotifikasiPanel(),
              ),
            ),
          ),
        );
      },
      transitionBuilder: (_, animation, __, child) {
        final geser = Tween<Offset>(
          begin: const Offset(0, -0.06),
          end: Offset.zero,
        ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOut));
        return FadeTransition(
          opacity: animation,
          child: SlideTransition(position: geser, child: child),
        );
      },
    );
  }

  @override
  State<NotifikasiPanel> createState() => _NotifikasiPanelState();
}

class _NotifikasiPanelState extends State<NotifikasiPanel> {
  bool _memuat = true;
  bool _gagal = false;
  List<dynamic> _notifikasi = [];

  @override
  void initState() {
    super.initState();
    _muatNotifikasi();
  }

  Future<void> _muatNotifikasi() async {
    setState(() {
      _memuat = true;
      _gagal = false;
    });

    try {
      final response = await ApiClient.get('/siswa/notifikasi');

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true) {
          final data = result['data'] as Map<String, dynamic>;
          if (!mounted) return;
          setState(() {
            _notifikasi = data['notifikasi'] ?? [];
            _memuat = false;
          });

          // Panel sudah dibuka: tandai semua sebagai dibaca di server.
          final belumDibaca = data['belum_dibaca'] ?? 0;
          if (belumDibaca is int && belumDibaca > 0) {
            ApiClient.post('/siswa/notifikasi/baca')
                .catchError((_) => response);
          }
          return;
        }
      }
      throw Exception('Gagal memuat notifikasi');
    } catch (_) {
      if (mounted) {
        setState(() {
          _memuat = false;
          _gagal = true;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 14, 10, 10),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.warningLight,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.notifications_rounded,
                        color: AppColors.accentDeep, size: 21),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Notifikasi',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        SizedBox(height: 1),
                        Text(
                          'Pemberitahuan PR dari gurumu',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded,
                        color: AppColors.textSecondary),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),
            const Divider(height: 1, color: AppColors.border),
            Flexible(child: _buildIsi()),
      ],
    );
  }

  Widget _buildIsi() {
    if (_memuat) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 48),
        child: Center(child: CircularProgressIndicator()),
      );
    }

    if (_gagal) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_rounded,
                size: 40, color: AppColors.textSecondary),
            const SizedBox(height: 10),
            const Text(
              'Notifikasi belum bisa dimuat. Periksa koneksi lalu coba lagi.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 14),
            OutlinedButton(
              onPressed: _muatNotifikasi,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (_notifikasi.isEmpty) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 44, horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.notifications_off_rounded,
                size: 42, color: AppColors.textSecondary),
            SizedBox(height: 10),
            Text(
              'Belum ada notifikasi',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
            ),
            SizedBox(height: 4),
            Text(
              'Kalau guru memberi PR, pemberitahuannya akan muncul di sini.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.separated(
      shrinkWrap: true,
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 18),
      itemCount: _notifikasi.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final item = _notifikasi[index] as Map<String, dynamic>;
        return _KartuNotifikasi(item: item);
      },
    );
  }
}

class _KartuNotifikasi extends StatelessWidget {
  final Map<String, dynamic> item;

  const _KartuNotifikasi({required this.item});

  /// Format tenggat dari string ISO ke bahasa Indonesia,
  /// mis. "Minggu, 22 Nov 2026 • 23:59".
  /// Komponen tanggal/jam diambil apa adanya dari string server
  /// (zona waktu server) agar tidak bergeser mengikuti zona alat.
  String _formatTenggat(Map<String, dynamic> pr) {
    final iso = pr['tgl_tenggat']?.toString() ?? '';
    final cocok = RegExp(r'^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})').firstMatch(iso);
    if (cocok == null) return pr['tenggat_format'] ?? '';

    final tahun = int.parse(cocok.group(1)!);
    final bulanKe = int.parse(cocok.group(2)!);
    final tanggal = int.parse(cocok.group(3)!);
    final jam = '${cocok.group(4)}:${cocok.group(5)}';
    final weekday = DateTime(tahun, bulanKe, tanggal).weekday;

    const hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const bulan = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];
    return '${hari[weekday - 1]}, $tanggal ${bulan[bulanKe - 1]} $tahun • $jam';
  }

  @override
  Widget build(BuildContext context) {
    final pr = item['pr'] as Map<String, dynamic>?;
    final belumDibaca = item['status_baca'] != true;
    final sisaHari = (pr?['sisa_hari'] as num?)?.toInt();
    final terlewat = sisaHari != null && sisaHari < 0;
    final mendesak = pr?['mendesak'] == true;

    final Color chipBg = terlewat
        ? AppColors.errorLight
        : mendesak
            ? AppColors.warningLight
            : AppColors.primaryLight;
    final Color chipFg = terlewat
        ? AppColors.error
        : mendesak
            ? AppColors.accentDeep
            : AppColors.primary;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: belumDibaca
            ? AppColors.primaryLight.withValues(alpha: 0.4)
            : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border, width: 1.2),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: chipBg,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(Icons.assignment_rounded, color: chipFg, size: 20),
          ),
          const SizedBox(width: 11),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        pr?['nama_pr'] ?? 'Pemberitahuan PR',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ),
                    if (belumDibaca)
                      Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: AppColors.error,
                          shape: BoxShape.circle,
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  item['pesan'] ?? '',
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 12,
                    height: 1.45,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  ),
                ),
                if (pr != null) ...[
                  const SizedBox(height: 7),
                  Text(
                    [
                      if (pr['nama_mapel'] != null) pr['nama_mapel'],
                      if (pr['nama_guru'] != null) pr['nama_guru'],
                    ].join(' • '),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      const Icon(Icons.calendar_month_rounded,
                          size: 13, color: AppColors.textSecondary),
                      Text(
                        _formatTenggat(pr),
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textSecondary,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 7, vertical: 2.5),
                        decoration: BoxDecoration(
                          color: chipBg,
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          pr['label_tenggat'] ?? '',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: chipFg,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
