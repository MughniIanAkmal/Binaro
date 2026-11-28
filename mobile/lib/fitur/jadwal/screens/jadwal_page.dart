import 'package:flutter/material.dart';
import 'package:binaro_flutter/fitur/jadwal/services/jadwal_service.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';
import 'package:binaro_flutter/inti/layanan/icon_helper.dart';
import 'package:binaro_flutter/inti/layanan/responsive.dart';
import 'package:binaro_flutter/inti/widgets/common.dart';
import 'package:binaro_flutter/inti/widgets/dynamic_bottom_nav.dart';
import 'package:binaro_flutter/inti/widgets/state_views.dart';

/// Jadwal Pelajaran — jadwal mingguan kelas siswa dari tabel
/// jadwal_mata_pelajaran (endpoint GET /api/siswa/jadwal).
///
/// Tampilan mengikuti desain referensi: pill pemilih hari, header
/// "Jadwal Hari X" + lencana jumlah pelajaran, lalu kartu pelajaran
/// dengan border tebal bergantian amber/navy dan chip ikon solid.
class JadwalPage extends StatefulWidget {
  final Map<String, dynamic>? user;

  const JadwalPage({super.key, this.user});

  @override
  State<JadwalPage> createState() => _JadwalPageState();
}

class _JadwalPageState extends State<JadwalPage> {
  static const List<String> _hariList = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
  ];

  bool _isLoading = true;
  bool _hasError = false;
  String _pesanError = '';
  List<dynamic> _jadwal = [];
  late String _hariTerpilih;
  final ScrollController _pillController = ScrollController();

  @override
  void initState() {
    super.initState();
    final weekday = DateTime.now().weekday; // 1 = Senin ... 7 = Minggu
    _hariTerpilih =
        (weekday >= 1 && weekday <= 6) ? _hariList[weekday - 1] : 'Senin';
    _fetchJadwal();
  }

  /// Geser baris pill supaya hari terpilih langsung terlihat.
  /// Dipanggil setelah data termuat (baris pill belum terbangun
  /// selama status loading tampil).
  void _scrollKeHariTerpilih() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_pillController.hasClients) {
        final target = _hariList.indexOf(_hariTerpilih) * 100.0;
        _pillController.jumpTo(
          target.clamp(0, _pillController.position.maxScrollExtent),
        );
      }
    });
  }

  @override
  void dispose() {
    _pillController.dispose();
    super.dispose();
  }

  Future<void> _fetchJadwal() async {
    setState(() {
      _isLoading = true;
      _hasError = false;
    });

    final int siswaId =
        widget.user?['id_siswa'] ?? AppConstants.defaultSiswaId;
    if (siswaId <= 0) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _pesanError = 'Sesi tidak valid. Silakan keluar lalu login kembali.';
        });
      }
      return;
    }

    final result = await JadwalService.fetchJadwal(siswaId);
    if (!mounted) return;

    if (result.isSuccess) {
      setState(() {
        _jadwal = result.jadwal!;
        _isLoading = false;
        _hasError = false;
      });
      _scrollKeHariTerpilih();
    } else {
      setState(() {
        _isLoading = false;
        _hasError = true;
        _pesanError =
            result.errorMessage ?? 'Gagal memuat jadwal pelajaran.';
      });
    }
  }

  IconData _getIcon(String? tipe) => IconHelper.fromTipe(tipe);

  String _jamTampil(String? jam) {
    if (jam == null || jam.isEmpty) return '-';
    return jam.replaceAll('-', ' - ');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const BinaroAppBar(title: 'Jadwal Mapel'),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.primary),
            )
          : _hasError
              ? ErrorState(message: _pesanError, onRetry: _fetchJadwal)
              : RefreshIndicator(
                  color: AppColors.primary,
                  onRefresh: _fetchJadwal,
                  child: KontenResponsif(child: _buildKonten()),
                ),
      bottomNavigationBar: DynamicBottomNav(currentIndex: 3, user: widget.user),
    );
  }

  Widget _buildKonten() {
    final pelajaranHariIni = _jadwal
        .where((j) => j['hari'] == _hariTerpilih)
        .toList()
      ..sort((a, b) => (a['jam'] ?? '').compareTo(b['jam'] ?? ''));

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      children: [
        _buildPemilihHari(),
        const SizedBox(height: 22),
        Row(
          children: [
            Container(
              width: 10,
              height: 10,
              decoration: const BoxDecoration(
                color: AppColors.accent,
                shape: BoxShape.circle,
              ),
            ),
            const SizedBox(width: 8),
            Text(
              'Jadwal Hari $_hariTerpilih',
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
              ),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.surfaceAlt,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                '${pelajaranHariIni.length} Pelajaran',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textSecondary,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        if (pelajaranHariIni.isEmpty)
          _buildKosong()
        else
          ...List.generate(
            pelajaranHariIni.length,
            (i) => _buildKartuPelajaran(pelajaranHariIni[i], i),
          ),
      ],
    );
  }

  Widget _buildPemilihHari() {
    return SingleChildScrollView(
      controller: _pillController,
      scrollDirection: Axis.horizontal,
      child: Row(
        children: _hariList.map((hari) {
          final aktif = hari == _hariTerpilih;
          return Padding(
            padding: const EdgeInsets.only(right: 10),
            child: GestureDetector(
              onTap: () => setState(() => _hariTerpilih = hari),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  color: aktif ? AppColors.primary : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: aktif ? AppColors.primary : AppColors.border,
                    width: 1.2,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (aktif) ...[
                      const Icon(
                        Icons.check_circle_rounded,
                        size: 16,
                        color: Colors.white,
                      ),
                      const SizedBox(width: 6),
                    ],
                    Text(
                      hari,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color:
                            aktif ? Colors.white : AppColors.textPrimary,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildKartuPelajaran(Map<String, dynamic> j, int index) {
    final warna = index.isEven ? AppColors.accent : AppColors.primary;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: warna, width: 3),
      ),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: warna,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(_getIcon(j['tipe_icon']), color: Colors.white, size: 27),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  j['nama_mapel'] ?? 'Mata Pelajaran',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(
                      Icons.access_time_rounded,
                      size: 15,
                      color: AppColors.textSecondary,
                    ),
                    const SizedBox(width: 5),
                    Text(
                      _jamTampil(j['jam']),
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
                if (j['guru'] != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    j['guru'],
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKosong() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: const BoxDecoration(
              color: AppColors.surfaceAlt,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.event_busy_rounded,
              size: 34,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            'Tidak ada pelajaran hari $_hariTerpilih.',
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Kamu bisa santai, atau mengulang materi kemarin.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
          ),
        ],
      ),
    );
  }
}
