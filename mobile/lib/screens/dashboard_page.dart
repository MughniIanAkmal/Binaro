import 'dart:convert';
import '../widgets/common.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'daftar_bab_page.dart';
import 'mapel_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
import '../widgets/dynamic_bottom_nav.dart';
import '../widgets/state_views.dart';
import 'ujian_page.dart';

class DashboardPage extends StatefulWidget {
  final Map<String, dynamic>? user;

  const DashboardPage({super.key, this.user});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  bool _isLoading = true;
  bool _hasError = false;
  String _pesanError = '';

  // Data state
  Map<String, dynamic> _siswa = {};
  List<dynamic> _tugasList = [];
  List<dynamic> _mapelList = [];

  // URL API Laravel dari AppConstants
  static const String _baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    // Default initial data from login
    _siswa = {
      'nama': widget.user?['nm_siswa'] ?? AppConstants.defaultSiswaNama,
      'kelas': widget.user?['kelas'] != null
          ? '${widget.user?['kelas']} • ${AppConstants.defaultSiswaKelas}'
          : AppConstants.defaultSiswaKelas,
      'foto_profil': widget.user?['foto_profil'],
    };
    _fetchDashboardData();
  }

  Future<void> _fetchDashboardData() async {
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

    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/dashboard?id_siswa=$siswaId'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _siswa = data['siswa'] ?? _siswa;
            _tugasList = data['tugas'] ?? [];
            _mapelList = data['mata_pelajaran'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _pesanError =
              'Server menjawab dengan status ${response.statusCode}. Periksa koneksi lalu coba lagi.';
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _pesanError =
              'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.primary),
            )
          : _hasError
              ? ErrorState(
                  message: _pesanError,
                  onRetry: _fetchDashboardData,
                )
              : RefreshIndicator(
                  onRefresh: _fetchDashboardData,
                  color: AppColors.primary,
                  backgroundColor: Colors.white,
                  displacement: 28,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(
                      parent: BouncingScrollPhysics(),
                    ),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Greeting & Logo Box
                        _buildGreetingSection(),

                        const SizedBox(height: 22),

                        // Menu Section (Mapel, Ujian, Jadwal)
                        _buildMenuSection(),

                        const SizedBox(height: 24),

                        // Tugas & PR Mendatang Section
                        _buildTugasSection(),

                        const SizedBox(height: 24),

                        // Mata Pelajaran Section
                        _buildMataPelajaranSection(),

                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
      bottomNavigationBar: DynamicBottomNav(
        currentIndex: 0,
        user: widget.user ?? _siswa,
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. TOP APP BAR
  // ---------------------------------------------------------------------------
  PreferredSizeWidget _buildAppBar() => const BinaroAppBar(title: 'Beranda');

  // ---------------------------------------------------------------------------
  // 2. GREETING SECTION
  // ---------------------------------------------------------------------------
  Widget _buildGreetingSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Halo, ${_siswa['nama'] ?? AppConstants.defaultSiswaNama}!',
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 6),
        Row(
          children: [
            const Icon(
              Icons.school_rounded,
              size: 15,
              color: AppColors.primary,
            ),
            const SizedBox(width: 6),
            Text(
              _siswa['kelas'] ?? AppConstants.defaultSiswaKelas,
              style: const TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 3. MENU SECTION (Mapel, Ujian, Jadwal)
  // ---------------------------------------------------------------------------
  Widget _buildMenuSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Menu',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildMenuButton(
                title: 'Pembelajaran',
                icon: Icons.menu_book_rounded,
                color: AppColors.primary,
                onTap: () {
                  Navigator.push(
                    context,
                    SmoothPageRoute(
                      page: MapelPage(user: widget.user ?? _siswa),
                      type: TransitionType.sharedAxis,
                    ),
                  );
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMenuButton(
                title: 'Ujian',
                icon: Icons.quiz_outlined,
                color: AppColors.accent,
                onTap: () {
                  Navigator.push(
                    context,
                    SmoothPageRoute(
                      page: UjianPage(user: widget.user ?? _siswa),
                      type: TransitionType.sharedAxis,
                    ),
                  );
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMenuButton(
                title: 'Jadwal',
                icon: Icons.calendar_month_outlined,
                color: AppColors.primary,
                onTap: _showJadwalSegeraHadir,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildMenuButton({
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 18),
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.3),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: Colors.white, size: 28),
            const SizedBox(height: 8),
            Text(
              title,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. TUGAS & PR MENDATANG SECTION
  // ---------------------------------------------------------------------------
  Widget _buildTugasSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Tugas & PR Mendatang',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 12),

        // List Tugas Cards
        if (_tugasList.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
            ),
            child: const Column(
              children: [
                Icon(Icons.assignment_turned_in_outlined, size: 36, color: AppColors.success),
                SizedBox(height: 8),
                Text(
                  'Tidak ada tugas atau PR aktif saat ini.',
                  style: TextStyle(fontSize: 13, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
                ),
              ],
            ),
          )
        else ...[
          ..._tugasList.map((t) => _buildTugasCard(Map<String, dynamic>.from(t as Map))),
        ],
      ],
    );
  }

  /// Menu Jadwal belum tersedia — beri tahu pengguna, jangan diam saja.
  void _showJadwalSegeraHadir() {
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Row(
          children: [
            Icon(Icons.calendar_month_rounded, color: Colors.white, size: 20),
            SizedBox(width: 10),
            Expanded(
              child: Text(
                'Fitur Jadwal Pelajaran segera hadir!',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
        backgroundColor: AppColors.primary,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Widget _buildTugasCard(Map<String, dynamic> tugas) {
    final bool isOrange = tugas['warna'] == 'orange';
    final Color barColor = isOrange ? AppColors.accent : AppColors.primary;
    final String mapel = tugas['mapel'] ?? 'UMUM';
    final String judul = tugas['judul'] ?? '-';
    final String deadline = tugas['deadline'] ?? '-';

    IconData iconData = Icons.calculate_outlined;
    if (tugas['tipe_icon'] == 'science' || mapel.contains('IPA')) {
      iconData = Icons.biotech_outlined;
    } else if (tugas['tipe_icon'] == 'book' || mapel.contains('INDONESIA')) {
      iconData = Icons.book_outlined;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Bagian Atas: Icon + Mapel + Judul PR
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.primaryLight,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(iconData, color: AppColors.primary, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        mapel,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: isOrange
                              ? AppColors.accent
                              : AppColors.primary,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        judul,
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Bagian Bawah: Bar Warna (Tenggat + Tombol Buka)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: BoxDecoration(
              color: barColor,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(17)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.access_time_rounded,
                        color: Colors.white, size: 16),
                    const SizedBox(width: 6),
                    Text(
                      deadline,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
                // Tombol Buka >
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Buka',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: barColor,
                        ),
                      ),
                      Icon(Icons.chevron_right_rounded, size: 15, color: barColor),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 5. MATA PELAJARAN SECTION (2x2 Grid)
  // ---------------------------------------------------------------------------
  Widget _buildMataPelajaranSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Mata Pelajaran',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
            ),
            TextButton(
              onPressed: () {
                Navigator.push(
                  context,
                  SmoothPageRoute(
                    page: MapelPage(user: widget.user ?? _siswa),
                    type: TransitionType.sharedAxis,
                  ),
                );
              },
              child: const Text(
                'Semua',
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: AppColors.primary,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),

        // Grid 2 Kolom
        if (_mapelList.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
            ),
            child: const Center(
              child: Text(
                'Belum ada mata pelajaran yang tersedia.',
                style: TextStyle(fontSize: 13, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
              ),
            ),
          )
        else
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _mapelList.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.05,
            ),
            itemBuilder: (context, index) {
              final m = Map<String, dynamic>.from(_mapelList[index] as Map);
              return _buildMapelCard(m);
            },
          ),
      ],
    );
  }

  Widget _buildMapelCard(Map<String, dynamic> mapel) {
    final bool isOrange = mapel['warna'] == 'orange';
    final Color accentColor = isOrange ? AppColors.accent : AppColors.primary;
    final Color badgeBg = isOrange ? AppColors.warningLight : AppColors.primaryLight;

    IconData iconData = Icons.grid_view_rounded;
    if (mapel['tipe_icon'] == 'science') {
      iconData = Icons.biotech_outlined;
    } else if (mapel['tipe_icon'] == 'book') {
      iconData = Icons.book_outlined;
    } else if (mapel['tipe_icon'] == 'shield') {
      iconData = Icons.shield_outlined;
    }

    final double progress = (mapel['progress'] as num?)?.toDouble() ?? 0.0;
    final int persen = (mapel['persen'] as num?)?.toInt() ?? (progress * 100).toInt();

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          SmoothPageRoute(
            page: DaftarBabPage(mapel: mapel, user: widget.user ?? _siswa),
            type: TransitionType.sharedAxis,
          ),
        );
      },
      borderRadius: BorderRadius.circular(18),
      child: Container(
        padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
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
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Row Atas: Icon Box & Badge Bab
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: badgeBg,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(iconData, color: accentColor, size: 20),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                decoration: BoxDecoration(
                  color: badgeBg,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  mapel['badge'] ?? 'Bab 1',
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    color: accentColor,
                  ),
                ),
              ),
            ],
          ),

          // Judul Mapel & Jumlah Materi
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                mapel['nama'] ?? '-',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 3),
              Text(
                mapel['materi_info'] ?? '-',
                style: const TextStyle(
                  fontSize: 11,
                  color: AppColors.textMuted,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),

          // Progress Bar
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Progress Kuis',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  Text(
                    '$persen%',
                    style: TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                      color: accentColor,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: LinearProgressIndicator(
                  value: progress,
                  minHeight: 5,
                  backgroundColor: badgeBg,
                  valueColor: AlwaysStoppedAnimation<Color>(accentColor),
                ),
              ),
            ],
          ),
        ],
      ),
    ),
    );
  }

  // ---------------------------------------------------------------------------
  // 6. BOTTOM NAVIGATION BAR — memakai DynamicBottomNav bersama (satu-satunya nav)
  // ---------------------------------------------------------------------------
}
