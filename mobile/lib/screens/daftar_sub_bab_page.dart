import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'daftar_materi_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
import 'profile_page.dart';

class DaftarSubBabPage extends StatefulWidget {
  final Map<String, dynamic> bab;
  final Map<String, dynamic> mapel;
  final Map<String, dynamic>? user;

  const DaftarSubBabPage({
    super.key,
    required this.bab,
    required this.mapel,
    this.user,
  });

  @override
  State<DaftarSubBabPage> createState() => _DaftarSubBabPageState();
}

class _DaftarSubBabPageState extends State<DaftarSubBabPage> {
  bool _isLoading = true;

  Map<String, dynamic> _babHeader = {};
  List<dynamic> _subBabList = [];

  static const String _baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    final String mapelName = (widget.mapel['nama'] ?? widget.mapel['nama_mapel'] ?? 'Mata Pelajaran').toString().toUpperCase();
    final String babName = widget.bab['nama_bab'] ?? widget.bab['nama'] ?? 'Bab Pembelajaran';
    _babHeader = {
      'nomor': widget.bab['nomor'] ?? 1,
      'nama_mapel': mapelName,
      'nama_bab': babName,
      'info_sub': 'Sub Bab Pembelajaran',
    };
    _fetchSubBabData();
  }

  Future<void> _fetchSubBabData() async {
    final int babId = widget.bab['id_bab'] ?? 10;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/bab/$babId/sub-bab'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _babHeader = data['bab'] ?? _babHeader;
            _subBabList = data['sub_babs'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _subBabList = [];
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final int nomorBab = _babHeader['nomor'] ?? 1;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 24),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Bab $nomorBab',
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16, left: 2),
            child: GestureDetector(
              onTap: () {
                Navigator.push(
                  context,
                  SmoothPageRoute(
                    page: ProfilePage(user: widget.user),
                    type: TransitionType.sharedAxis,
                  ),
                );
              },
              child: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2.2),
                ),
              ),
            ),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _fetchSubBabData,
              color: AppColors.primary,
              backgroundColor: Colors.white,
              displacement: 28,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 1. Banner Kartu Bab (Biru Tua)
                    _buildTopBanner(),

                    const SizedBox(height: 20),

                    // 2. Judul "Pilih Sub Bab"
                    const Text(
                      'Pilih Sub Bab',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                      ),
                    ),

                    const SizedBox(height: 14),

                    // 3. List Sub Bab atau Empty State
                    if (_subBabList.isEmpty)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 24),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.02),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              width: 68,
                              height: 68,
                              decoration: const BoxDecoration(
                                color: AppColors.primaryLight,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.layers_clear_outlined,
                                size: 34,
                                color: AppColors.primary,
                              ),
                            ),
                            const SizedBox(height: 16),
                            const Text(
                              'Belum Ada Sub Bab',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textPrimary,
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Belum ada sub bab pembelajaran yang ditambahkan untuk bab ini.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                                height: 1.4,
                              ),
                            ),
                          ],
                        ),
                      )
                    else
                      ..._subBabList.map((subBab) => _buildSubBabCard(subBab)),

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. TOP BANNER (Biru Tua dengan Nomor 1 Oranye)
  // ---------------------------------------------------------------------------
  Widget _buildTopBanner() {
    final int nomorBab = _babHeader['nomor'] ?? 1;
    final String namaMapel = _babHeader['nama_mapel'] ?? 'MATEMATIKA';
    final String namaBab = _babHeader['nama_bab'] ?? 'Pecahan & Desimal';
    final String infoSub = _babHeader['info_sub'] ?? '4 Sub Bab Pembelajaran';

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          // Kotak Oranye dengan Nomor
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: AppColors.accent,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Text(
                '$nomorBab',
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),

          // Teks Keterangan Bab
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'BAB $nomorBab $namaMapel',
                  style: const TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    color: AppColors.accentBright,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  namaBab,
                  style: const TextStyle(
                    fontSize: 16.5,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.layers_outlined, size: 14, color: Colors.white70),
                    const SizedBox(width: 5),
                    Text(
                      infoSub,
                      style: const TextStyle(fontSize: 11.5, color: Colors.white70),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. SUB BAB CARD (Sesuai Desain Bab dengan Tombol Masuk/Buka)
  // ---------------------------------------------------------------------------
  Widget _buildSubBabCard(Map<String, dynamic> subBab) {
    final int nomor = subBab['nomor'] ?? 1;
    final String label = subBab['label'] ?? 'Sub Bab $nomor';
    final String namaSubBab = subBab['nama_sub_bab'] ?? 'Mengenal Bentuk Pecahan';
    final String? deskripsi = subBab['deskripsi'];
    final bool isLocked = subBab['is_locked'] == true;
    final bool isActive = subBab['is_active'] == true;
    final String tipeIcon = subBab['tipe_icon'] ?? 'fraction';

    // Warna Card & Tombol Buka
    final Color borderColor = isActive ? AppColors.accent : AppColors.primary;
    final Color btnColor = isActive
        ? AppColors.accent
        : (nomor % 2 == 1 ? AppColors.primary : AppColors.accent);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderColor, width: isActive ? 2.5 : 1.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: isLocked ? null : () => _navigateToMateri(subBab),
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                // Icon Sub Bab
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isActive
                        ? AppColors.accent
                        : (isLocked ? AppColors.surfaceAlt : AppColors.primaryLight),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    _getSubBabIcon(tipeIcon),
                    color: isActive
                        ? Colors.white
                        : (isLocked ? AppColors.textMuted : AppColors.primary),
                    size: 22,
                  ),
                ),
                const SizedBox(width: 14),

                // Keterangan Sub Bab
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        label,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: isActive ? AppColors.accent : AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        namaSubBab,
                        style: TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                          color: isLocked ? AppColors.textMuted : AppColors.textPrimary,
                        ),
                      ),
                      if (deskripsi != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          deskripsi,
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: AppColors.textSecondary,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 8),

                // Tombol Buka / Masuk ATAU Ikon Gembok (Lock)
                if (isLocked)
                  const Padding(
                    padding: EdgeInsets.only(right: 6),
                    child: Icon(Icons.lock_outline_rounded, color: AppColors.textMuted, size: 22),
                  )
                else
                  InkWell(
                    onTap: () => _navigateToMateri(subBab),
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                      decoration: BoxDecoration(
                        color: btnColor,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Buka',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                          SizedBox(width: 2),
                          Icon(Icons.chevron_right_rounded, color: Colors.white, size: 16),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _navigateToMateri(Map<String, dynamic> subBab) {
    Navigator.push(
      context,
      SmoothPageRoute(
        page: DaftarMateriPage(
          subBab: subBab,
          bab: widget.bab,
          mapel: widget.mapel,
          user: widget.user,
        ),
        type: TransitionType.sharedAxis,
      ),
    );
  }

  IconData _getSubBabIcon(String type) {
    switch (type) {
      case 'cake':
        return Icons.cake_rounded;
      case 'scale':
        return Icons.balance_rounded;
      case 'fraction':
        return Icons.pie_chart_rounded;
      case 'lock':
      default:
        return Icons.lock_outline_rounded;
    }
  }
}

