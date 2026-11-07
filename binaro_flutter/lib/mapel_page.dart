import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'daftar_bab_page.dart';
import 'page_transitions.dart';
import 'profile_page.dart';

class MapelPage extends StatefulWidget {
  final Map<String, dynamic>? user;

  const MapelPage({super.key, this.user});

  @override
  State<MapelPage> createState() => _MapelPageState();
}

class _MapelPageState extends State<MapelPage> {
  bool _isLoading = true;
  String _searchQuery = '';
  int _selectedFilter = 0; // 0: Semua, 1: Wajib

  String _kelasInfo = 'Mata Pelajaran Kelas 5 SD • Semester 2';
  List<dynamic> _mapelList = [];
  int _totalMapel = 6;
  int _wajibCount = 4;

  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  @override
  void initState() {
    super.initState();
    _fetchMapelData();
  }

  Future<void> _fetchMapelData() async {
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/mapel'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _kelasInfo = data['kelas'] ?? _kelasInfo;
            _totalMapel = data['total_mapel'] ?? 6;
            _wajibCount = data['wajib_count'] ?? 4;
            _mapelList = data['mapel'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    // Fallback jika offline
    if (mounted) {
      _applyFallback();
      setState(() => _isLoading = false);
    }
  }

  void _applyFallback() {
    _mapelList = [
      {
        'id_mapel': 17,
        'nama': 'Matematika',
        'bab_total': 6,
        'bab_rentang': 'Bab 1 - 6',
        'progress': 0.72,
        'jadwal': 'Senin & Rabu',
        'warna': 'blue',
        'tipe_icon': 'math',
        'is_wajib': true,
      },
      {
        'id_mapel': 18,
        'nama': 'Ilmu Pengetahuan Alam (IPA)',
        'bab_total': 6,
        'bab_rentang': 'Bab 1 - 6',
        'progress': 0.58,
        'jadwal': 'Selasa & Kamis',
        'warna': 'blue',
        'tipe_icon': 'science',
        'is_wajib': true,
      },
      {
        'id_mapel': 3,
        'nama': 'Bahasa Indonesia',
        'bab_total': 7,
        'bab_rentang': 'Bab 1 - 7',
        'progress': 0.84,
        'jadwal': 'Senin & Jumat',
        'warna': 'blue',
        'tipe_icon': 'book',
        'is_wajib': true,
      },
      {
        'id_mapel': 21,
        'nama': 'Pendidikan Pancasila & Kewarganegaraan',
        'bab_total': 5,
        'bab_rentang': 'Bab 1 - 5',
        'progress': 0.65,
        'jadwal': 'Rabu',
        'warna': 'blue',
        'tipe_icon': 'star',
        'is_wajib': true,
      },
      {
        'id_mapel': 23,
        'nama': 'Seni Budaya & Prakarya (SBdP)',
        'bab_total': 4,
        'bab_rentang': 'Bab 1 - 4',
        'progress': 0.40,
        'jadwal': 'Sabtu',
        'warna': 'blue',
        'tipe_icon': 'palette',
        'is_wajib': false,
      },
      {
        'id_mapel': 24,
        'nama': 'Bahasa Inggris',
        'bab_total': 6,
        'bab_rentang': 'Bab 1 - 6',
        'progress': 0.50,
        'jadwal': 'Kamis',
        'warna': 'blue',
        'tipe_icon': 'chat',
        'is_wajib': false,
      },
    ];
  }

  List<dynamic> get _filteredMapelList {
    return _mapelList.where((m) {
      final name = (m['nama'] ?? '').toString().toLowerCase();
      final matchQuery = name.contains(_searchQuery.toLowerCase());
      if (!matchQuery) return false;
      if (_selectedFilter == 1) {
        return m['is_wajib'] == true;
      }
      return true;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF1D5A86)))
          : RefreshIndicator(
              onRefresh: _fetchMapelData,
              color: const Color(0xFF1D5A86),
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Daftar Mapel
                    _buildHeaderSection(),

                    const SizedBox(height: 16),

                    // Search Bar
                    _buildSearchBar(),

                    const SizedBox(height: 14),

                    // Filter Chips (Semua 6, Wajib 4)
                    _buildFilterChips(),

                    const SizedBox(height: 18),

                    // List Kartu Mapel
                    ..._filteredMapelList.map((mapel) => _buildMapelCard(mapel)),

                    const SizedBox(height: 16),

                    // Banner Motivasi Belajar
                    _buildMotivationBanner(),

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  // ---------------------------------------------------------------------------
  // APPBAR
  // ---------------------------------------------------------------------------
  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: const Color(0xFF1D5A86),
      elevation: 0,
      automaticallyImplyLeading: false,
      titleSpacing: 16,
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.school_rounded, color: Colors.white, size: 22),
          ),
          const SizedBox(width: 12),
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
                'Mata Pelajaran',
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
        Padding(
          padding: const EdgeInsets.only(right: 8),
          child: Stack(
            alignment: Alignment.topRight,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_none_rounded, color: Colors.white, size: 26),
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
        ),
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
  // HEADER DAFTAR MAPEL
  // ---------------------------------------------------------------------------
  Widget _buildHeaderSection() {
    return Row(
      children: [
        const Icon(Icons.menu_book_rounded, color: Color(0xFF1D5A86), size: 28),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Daftar Mapel',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: Color(0xFF1E293B),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              _kelasInfo,
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
  // SEARCH BAR
  // ---------------------------------------------------------------------------
  Widget _buildSearchBar() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: TextField(
        onChanged: (val) => setState(() => _searchQuery = val),
        decoration: const InputDecoration(
          hintText: 'Cari mata pelajaran...',
          hintStyle: TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
          prefixIcon: Icon(Icons.search_rounded, color: Color(0xFF94A3B8), size: 22),
          border: InputBorder.none,
          contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // FILTER CHIPS (Semua 6, Wajib 4)
  // ---------------------------------------------------------------------------
  Widget _buildFilterChips() {
    return Row(
      children: [
        _buildChipItem(
          index: 0,
          label: 'Semua',
          count: _totalMapel,
          icon: Icons.grid_view_rounded,
        ),
        const SizedBox(width: 10),
        _buildChipItem(
          index: 1,
          label: 'Wajib',
          count: _wajibCount,
          icon: Icons.check_circle_outline_rounded,
        ),
      ],
    );
  }

  Widget _buildChipItem({
    required int index,
    required String label,
    required int count,
    required IconData icon,
  }) {
    final bool isSelected = _selectedFilter == index;

    return InkWell(
      onTap: () => setState(() => _selectedFilter = index),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF1D5A86) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? const Color(0xFF1D5A86) : const Color(0xFFE2E8F0),
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 16,
              color: isSelected ? Colors.white : const Color(0xFF64748B),
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: isSelected ? Colors.white : const Color(0xFF64748B),
              ),
            ),
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: isSelected ? Colors.white.withValues(alpha: 0.25) : const Color(0xFFF1F5F9),
                shape: BoxShape.circle,
              ),
              child: Text(
                '$count',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  color: isSelected ? Colors.white : const Color(0xFF64748B),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // KARTU MAPEL (Sesuai Desain Screenshot 1)
  // ---------------------------------------------------------------------------
  Widget _buildMapelCard(Map<String, dynamic> mapel) {
    final String nama = mapel['nama'] ?? 'Mata Pelajaran';
    final String babRentang = mapel['bab_rentang'] ?? 'Bab 1 - 6';
    final double progress = (mapel['progress'] as num?)?.toDouble() ?? 0.6;
    final String jadwal = mapel['jadwal'] ?? 'Senin & Rabu';
    final String tipeIcon = mapel['tipe_icon'] ?? 'book';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF1D5A86), // Dark Blue container
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          // Bagian Atas: Icon, Nama, Bab & Progress Bar
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
            child: Column(
              children: [
                Row(
                  children: [
                    // Kotak Icon Putih
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(_getIcon(tipeIcon), color: const Color(0xFF1D5A86), size: 24),
                    ),
                    const SizedBox(width: 14),
                    // Nama Mapel & Bab
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            nama,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            babRentang,
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w500,
                              color: Colors.white70,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                // Progress Bar (Oranye di atas putih)
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: LinearProgressIndicator(
                    value: progress,
                    minHeight: 6,
                    backgroundColor: Colors.white.withValues(alpha: 0.35),
                    valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFF59E0B)),
                  ),
                ),
              ],
            ),
          ),

          // Bagian Bawah: Strip Putih Jadwal & Tombol "Buka Bab >"
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Info Jadwal
                Row(
                  children: [
                    const Icon(Icons.access_time_rounded, color: Color(0xFF64748B), size: 16),
                    const SizedBox(width: 6),
                    Text(
                      jadwal,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
                // Tombol "Buka Bab >"
                InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      SmoothPageRoute(
                        page: DaftarBabPage(mapel: mapel, user: widget.user),
                        type: TransitionType.sharedAxis,
                      ),
                    );
                  },
                  borderRadius: BorderRadius.circular(18),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1D5A86),
                      borderRadius: BorderRadius.circular(18),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Buka Bab',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                        SizedBox(width: 4),
                        Icon(Icons.chevron_right_rounded, color: Colors.white, size: 16),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  IconData _getIcon(String type) {
    switch (type) {
      case 'math':
        return Icons.calculate_rounded;
      case 'science':
        return Icons.biotech_rounded;
      case 'book':
        return Icons.menu_book_rounded;
      case 'star':
        return Icons.star_rounded;
      case 'palette':
        return Icons.palette_rounded;
      case 'chat':
        return Icons.chat_bubble_rounded;
      default:
        return Icons.menu_book_rounded;
    }
  }

  // ---------------------------------------------------------------------------
  // BANNER MOTIVASI BELAJAR (Di Bawah List)
  // ---------------------------------------------------------------------------
  Widget _buildMotivationBanner() {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF0F7FF),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFD6E8FC)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: const BoxDecoration(
              color: Color(0xFFF59E0B),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.lightbulb_rounded, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Semangat Belajarnya!',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1D5A86),
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Selesaikan 2 materi lagi hari ini untuk mendapatkan lencana Bintang Cilik!',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF475569),
                    height: 1.35,
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
  // BOTTOM NAVIGATION BAR
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
              _buildNavItem(2, Icons.menu_book_rounded, 'Mapel', isSelected: true),
              _buildNavItem(3, Icons.calendar_month_outlined, 'Jadwal'),
              _buildNavItem(4, Icons.account_circle_outlined, 'Profil'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label, {bool isSelected = false}) {
    final Color color = isSelected ? const Color(0xFFF59E0B) : Colors.white70;

    return InkWell(
      onTap: () {
        if (index == 0) {
          Navigator.pop(context); // Kembali ke Beranda
        } else if (index == 4) {
          Navigator.push(
            context,
            SmoothPageRoute(
              page: ProfilePage(user: widget.user),
              type: TransitionType.sharedAxis,
            ),
          );
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

