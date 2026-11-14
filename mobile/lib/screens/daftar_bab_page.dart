import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'daftar_sub_bab_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
import 'profile_page.dart';

class DaftarBabPage extends StatefulWidget {
  final Map<String, dynamic> mapel;
  final Map<String, dynamic>? user;

  const DaftarBabPage({super.key, required this.mapel, this.user});

  @override
  State<DaftarBabPage> createState() => _DaftarBabPageState();
}

class _DaftarBabPageState extends State<DaftarBabPage> {
  bool _isLoading = true;

  Map<String, dynamic> _headerMapel = {};
  List<dynamic> _babList = [];
  int _totalBab = 6;

  static const String _baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    final String mapelName = widget.mapel['nama'] ?? widget.mapel['nama_mapel'] ?? 'Mata Pelajaran';
    _headerMapel = {
      'nama': mapelName,
      'badge': '$mapelName Seru',
      'judul_seru': 'Eksplorasi Pembelajaran',
      'progress_bab': '- Bab',
      'tipe_icon': widget.mapel['tipe_icon'] ?? 'book',
    };
    _fetchBabData();
  }

  Future<void> _fetchBabData() async {
    final int mapelId = widget.mapel['id_mapel'] ?? 3;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/mapel/$mapelId/bab'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _headerMapel = data['mapel'] ?? _headerMapel;
            _totalBab = data['total_bab'] ?? (_babList.length);
            _babList = data['babs'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _babList = [];
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _fetchBabData,
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
                    // 1. Header Card (Petualangan Angka, 3/6 Bab)
                    _buildHeaderCard(),

                    const SizedBox(height: 18),

                    // 2. Chip "Semua Bab (6)"
                    _buildFilterChip(),

                    const SizedBox(height: 16),

                    // 3. List of Bab Cards atau Empty State
                    if (_babList.isEmpty)
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
                                Icons.menu_book_outlined,
                                size: 34,
                                color: AppColors.primary,
                              ),
                            ),
                            const SizedBox(height: 16),
                            const Text(
                              'Belum Ada Bab',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textPrimary,
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Guru belum mengunggah bab pembelajaran untuk mata pelajaran ini.',
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
                      ..._babList.map((bab) => _buildBabCard(bab)),

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // APPBAR DENGAN TOMBOL BACK
  // ---------------------------------------------------------------------------
  PreferredSizeWidget _buildAppBar() {
    final String namaMapel = _headerMapel['nama'] ?? 'Matematika';

    return AppBar(
      backgroundColor: AppColors.primary,
      elevation: 0,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 24),
        onPressed: () => Navigator.pop(context),
      ),
      titleSpacing: 0,
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: AppColors.accent,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.calculate_rounded, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'SAHABAT BELAJAR',
                style: TextStyle(
                  fontSize: 9.5,
                  letterSpacing: 0.8,
                  fontWeight: FontWeight.w800,
                  color: AppColors.accentBright,
                ),
              ),
              const SizedBox(height: 1),
              Text(
                namaMapel,
                style: const TextStyle(
                  fontSize: 16.5,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ],
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
    );
  }

  // ---------------------------------------------------------------------------
  // 1. HEADER CARD (Petualangan Angka, 3/6 Bab)
  // ---------------------------------------------------------------------------
  Widget _buildHeaderCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        children: [
          // Icon Kalkulator Kotak Oranye
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: AppColors.accent,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.calculate_rounded, color: Colors.white, size: 26),
          ),
          const SizedBox(width: 14),
          // Judul Seru & Badge
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.warningLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _headerMapel['badge'] ?? 'Matematika Seru',
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: AppColors.accentDeep,
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  _headerMapel['judul_seru'] ?? 'Petualangan Angka',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
              ],
            ),
          ),
          // Badge "3/6 Bab"
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: AppColors.warningLight,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              _headerMapel['progress_bab'] ?? '3/6 Bab',
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: AppColors.accentDeep,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 2. FILTER CHIP "Semua Bab (6)"
  // ---------------------------------------------------------------------------
  Widget _buildFilterChip() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text(
            'Semua Bab',
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
          const SizedBox(width: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.25),
              shape: BoxShape.circle,
            ),
            child: Text(
              '$_totalBab',
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. KARTU BAB (Sesuai Screenshot 2)
  // ---------------------------------------------------------------------------
  Widget _buildBabCard(Map<String, dynamic> bab) {
    final int nomor = bab['nomor'] ?? 1;
    final String label = bab['label'] ?? 'BAB $nomor';
    final String namaBab = bab['nama_bab'] ?? 'Pecahan & Desimal';
    final bool isLocked = bab['is_locked'] == true;

    // Warna border dan nomor
    final Color borderColor = isLocked
        ? (nomor % 2 == 1 ? AppColors.accentBright : AppColors.primaryLight)
        : (nomor % 2 == 1 ? AppColors.accent : AppColors.primary);

    final Color badgeBgColor = isLocked
        ? (nomor % 2 == 1 ? AppColors.warningLight : AppColors.primaryLight)
        : (nomor % 2 == 1 ? AppColors.accent : AppColors.primary);

    final Color badgeTextColor = isLocked
        ? (nomor % 2 == 1 ? AppColors.accentDeep : AppColors.primary)
        : Colors.white;

    // Warna tombol "Buka >"
    final Color btnColor = (nomor % 2 == 1) ? AppColors.primary : AppColors.accent;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderColor, width: 2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Badge Nomor Bab
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: badgeBgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Text(
                '$nomor',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: badgeTextColor,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),

          // Label "BAB X" & Nama Bab
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (nomor > 1)
                  Text(
                    label,
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: AppColors.accentDeep,
                      letterSpacing: 0.5,
                    ),
                  ),
                Text(
                  namaBab,
                  style: TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                    color: isLocked ? AppColors.textSecondary : AppColors.textPrimary,
                  ),
                ),
              ],
            ),
          ),

          // Tombol Buka ATAU Ikon Gembok (Lock)
          if (isLocked)
            const Padding(
              padding: EdgeInsets.only(right: 8),
              child: Icon(Icons.lock_outline_rounded, color: AppColors.textMuted, size: 22),
            )
          else
            InkWell(
              onTap: () {
                Navigator.push(
                  context,
                  SmoothPageRoute(
                    page: DaftarSubBabPage(
                      bab: bab,
                      mapel: widget.mapel,
                      user: widget.user,
                    ),
                    type: TransitionType.sharedAxis,
                  ),
                );
              },
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
    );
  }
}

