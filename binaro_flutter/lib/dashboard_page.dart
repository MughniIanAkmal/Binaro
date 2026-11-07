import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'page_transitions.dart';
import 'profile_page.dart';

class DashboardPage extends StatefulWidget {
  final Map<String, dynamic>? user;

  const DashboardPage({super.key, this.user});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  int _currentIndex = 0;
  bool _isLoading = true;

  // Data state
  Map<String, dynamic> _siswa = {};
  List<dynamic> _tugas = [];
  int _tugasTotal = 0;
  List<dynamic> _mataPelajaran = [];

  // URL API Laravel
  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  @override
  void initState() {
    super.initState();
    // Default initial data from login
    _siswa = {
      'nama': widget.user?['nm_siswa'] ?? 'Budi Santoso',
      'kelas': '${widget.user?['kelas'] ?? 'Kelas 5'} • SDN Kalitapen 01',
      'foto_profil': widget.user?['foto_profil'],
    };
    _fetchDashboardData();
  }

  Future<void> _fetchDashboardData() async {
    final idSiswa = widget.user?['id_siswa'] ?? 7;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/dashboard?id_siswa=$idSiswa'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _siswa = data['siswa'] ?? _siswa;
            _tugas = data['tugas'] ?? [];
            _tugasTotal = data['tugas_total'] ?? _tugas.length;
            _mataPelajaran = data['mata_pelajaran'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {
      // Fallback fallback sample matching database if offline
    }

    if (mounted) {
      _applyFallbackData();
      setState(() => _isLoading = false);
    }
  }

  void _applyFallbackData() {
    if (_tugas.isEmpty) {
      _tugas = [
        {
          'mapel': 'IPA',
          'judul': 'Pengamatan Tunas Kacang Hijau',
          'deadline': 'Tenggat: Besok, 07.15 WIB',
          'warna': 'blue',
          'tipe_icon': 'science',
        },
        {
          'mapel': 'BHS. INDONESIA',
          'judul': 'Rangkuman Dongeng Kancil',
          'deadline': 'Tenggat: Senin, 07.30 WIB',
          'warna': 'orange',
          'tipe_icon': 'book',
        },
        {
          'mapel': 'MATEMATIKA',
          'judul': 'Soal Cerita Pecahan 1/2 dan 1/4',
          'deadline': 'Tenggat: Jumat, 10.00 WIB',
          'warna': 'blue',
          'tipe_icon': 'math',
        },
      ];
      _tugasTotal = 5;
    }

    if (_mataPelajaran.isEmpty) {
      _mataPelajaran = [
        {
          'nama': 'Matematika',
          'badge': 'Bab 3',
          'materi_info': '14 dari 20 Materi',
          'progress': 0.70,
          'warna': 'orange',
          'tipe_icon': 'math',
        },
        {
          'nama': 'IPA',
          'badge': 'Bab 4',
          'materi_info': '10 dari 16 Materi',
          'progress': 0.62,
          'warna': 'blue',
          'tipe_icon': 'science',
        },
        {
          'nama': 'Bhs. Indonesia',
          'badge': 'Bab 2',
          'materi_info': '12 dari 15 Materi',
          'progress': 0.80,
          'warna': 'blue',
          'tipe_icon': 'book',
        },
        {
          'nama': 'Pancasila',
          'badge': 'Bab 2',
          'materi_info': '8 dari 12 Materi',
          'progress': 0.66,
          'warna': 'orange',
          'tipe_icon': 'shield',
        },
      ];
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF1D5A86)),
            )
          : RefreshIndicator(
              onRefresh: _fetchDashboardData,
              color: const Color(0xFF1D5A86),
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
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
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. TOP APP BAR
  // ---------------------------------------------------------------------------
  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: const Color(0xFF1D5A86),
      elevation: 0,
      automaticallyImplyLeading: false,
      titleSpacing: 16,
      title: Row(
        children: [
          // Orange logo toga badge
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.school_rounded,
              color: Colors.white,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          // Title texts
          const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'SAHABAT BELAJAR',
                style: TextStyle(
                  fontSize: 10,
                  letterSpacing: 0.8,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFFFBBF24),
                ),
              ),
              SizedBox(height: 2),
              Text(
                'Beranda',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
      actions: [
        // Bell notification icon with badge
        Stack(
          alignment: Alignment.topRight,
          children: [
            IconButton(
              icon: const Icon(Icons.notifications_none_rounded,
                  color: Colors.white, size: 26),
              onPressed: () {},
            ),
            Positioned(
              right: 12,
              top: 12,
              child: Container(
                width: 9,
                height: 9,
                decoration: const BoxDecoration(
                  color: Color(0xFFF59E0B),
                  shape: BoxShape.circle,
                ),
              ),
            ),
          ],
        ),
        // Placeholder Avatar Lingkaran Abu-abu dengan Border Putih (sesuai screenshot)
        Padding(
          padding: const EdgeInsets.only(right: 16, left: 6),
          child: GestureDetector(
            onTap: () {
              Navigator.push(
                context,
                SmoothPageRoute(
                  page: ProfilePage(user: widget.user ?? _siswa),
                  type: TransitionType.sharedAxis,
                ),
              );
            },
            child: Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: const Color(0xFFC0C0C0),
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
  // 2. GREETING SECTION
  // ---------------------------------------------------------------------------
  Widget _buildGreetingSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Halo, ${_siswa['nama'] ?? 'Budi Santoso'}!',
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: Color(0xFF1E293B),
          ),
        ),
        const SizedBox(height: 6),
        Row(
          children: [
            const Icon(
              Icons.school_rounded,
              size: 15,
              color: Color(0xFF1D5A86),
            ),
            const SizedBox(width: 6),
            Text(
              _siswa['kelas'] ?? 'Kelas 5 • SDN Kalitapen 01',
              style: const TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
                color: Color(0xFF64748B),
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
            color: Color(0xFF1E293B),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildMenuButton(
                title: 'Mapel',
                icon: Icons.menu_book_rounded,
                color: const Color(0xFF1D5A86),
                onTap: () {},
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMenuButton(
                title: 'Ujian',
                icon: Icons.quiz_outlined,
                color: const Color(0xFFF59E0B),
                onTap: () {},
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMenuButton(
                title: 'Jadwal',
                icon: Icons.calendar_month_outlined,
                color: const Color(0xFF1D5A86),
                onTap: () {},
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
            color: Color(0xFF1E293B),
          ),
        ),
        const SizedBox(height: 12),

        // List Tugas Cards
        ..._tugas.map((t) => _buildTugasCard(Map<String, dynamic>.from(t as Map))),

        const SizedBox(height: 12),

        // Link Lihat Semua Tugas
        Center(
          child: TextButton(
            onPressed: () {},
            style: TextButton.styleFrom(
              foregroundColor: const Color(0xFF1D5A86),
              textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('Lihat Semua Tugas ($_tugasTotal)'),
                const SizedBox(width: 4),
                const Icon(Icons.arrow_forward_rounded, size: 16),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildTugasCard(Map<String, dynamic> tugas) {
    final bool isOrange = tugas['warna'] == 'orange';
    final Color barColor = isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);
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
        border: Border.all(color: const Color(0xFFE2E8F0)),
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
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(iconData, color: const Color(0xFF1D5A86), size: 24),
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
                              ? const Color(0xFFF59E0B)
                              : const Color(0xFF1D5A86),
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        judul,
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1E293B),
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
                color: Color(0xFF1E293B),
              ),
            ),
            TextButton(
              onPressed: () {},
              child: const Text(
                'Semua',
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1D5A86),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),

        // Grid 2 Kolom
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: _mataPelajaran.length,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.15,
          ),
          itemBuilder: (context, index) {
            final m = Map<String, dynamic>.from(_mataPelajaran[index] as Map);
            return _buildMapelCard(m);
          },
        ),
      ],
    );
  }

  Widget _buildMapelCard(Map<String, dynamic> mapel) {
    final bool isOrange = mapel['warna'] == 'orange';
    final Color accentColor = isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);
    final Color badgeBg = isOrange ? const Color(0xFFFEF3C7) : const Color(0xFFDBEAFE);

    IconData iconData = Icons.grid_view_rounded;
    if (mapel['tipe_icon'] == 'science') {
      iconData = Icons.biotech_outlined;
    } else if (mapel['tipe_icon'] == 'book') {
      iconData = Icons.book_outlined;
    } else if (mapel['tipe_icon'] == 'shield') {
      iconData = Icons.shield_outlined;
    }

    final double progress = (mapel['progress'] ?? 0.5).toDouble();

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
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
                  color: Color(0xFF1E293B),
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 3),
              Text(
                mapel['materi_info'] ?? '-',
                style: const TextStyle(
                  fontSize: 11,
                  color: Color(0xFF94A3B8),
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),

          // Progress Bar
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 6,
              backgroundColor: badgeBg,
              valueColor: AlwaysStoppedAnimation<Color>(accentColor),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 6. BOTTOM NAVIGATION BAR (Beranda, Ujian, Mapel, Jadwal, Profil)
  // ---------------------------------------------------------------------------
  Widget _buildBottomNav() {
    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFF1D5A86),
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
        boxShadow: [
          BoxShadow(
            color: Colors.black26,
            blurRadius: 10,
            offset: Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildNavItem(0, Icons.home_rounded, 'Beranda'),
              _buildNavItem(1, Icons.quiz_outlined, 'Ujian'),
              _buildNavItem(2, Icons.menu_book_rounded, 'Mapel'),
              _buildNavItem(3, Icons.calendar_month_outlined, 'Jadwal'),
              _buildNavItem(4, Icons.account_circle_outlined, 'Profil'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label) {
    final bool isSelected = _currentIndex == index;
    final Color color = isSelected ? const Color(0xFFF59E0B) : Colors.white70;

    return InkWell(
      onTap: () {
        setState(() => _currentIndex = index);
        if (index == 4) {
          // Buka Halaman Profil Siswa dengan transisi halus
          Navigator.push(
            context,
            SmoothPageRoute(
              page: ProfilePage(user: widget.user ?? _siswa),
              type: TransitionType.sharedAxis,
            ),
          ).then((_) {
            if (mounted) {
              setState(() => _currentIndex = 0);
            }
          });
        }
      },
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 3),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
