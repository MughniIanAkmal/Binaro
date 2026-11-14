import 'dart:convert';
import '../widgets/common.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../widgets/dynamic_bottom_nav.dart';
import 'daftar_bab_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';

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

  static const String _baseUrl = AppConstants.baseUrl;

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
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _kelasInfo = data['kelas'] ?? _kelasInfo;
            _totalMapel = data['total_mapel'] ?? 0;
            _wajibCount = data['wajib_count'] ?? 0;
            _mapelList = data['mapel'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() => _isLoading = false);
    }
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
      backgroundColor: AppColors.background,
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _fetchMapelData,
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
                    if (_filteredMapelList.isEmpty)
                      Container(
                        width: double.infinity,
                        margin: const EdgeInsets.only(bottom: 16),
                        padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          children: [
                            const Icon(Icons.search_off_rounded, size: 44, color: AppColors.textMuted),
                            const SizedBox(height: 10),
                            Text(
                              _searchQuery.isNotEmpty
                                  ? 'Tidak ada mata pelajaran yang cocok dengan "$_searchQuery"'
                                  : 'Belum ada data mata pelajaran.',
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      )
                    else
                      ..._filteredMapelList.map((mapel) => _buildMapelCard(mapel)),

                    const SizedBox(height: 16),

                    // Banner Motivasi Belajar
                    _buildMotivationBanner(),

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
      bottomNavigationBar: DynamicBottomNav(
        currentIndex: 2,
        user: widget.user,
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // APPBAR
  // ---------------------------------------------------------------------------
  PreferredSizeWidget _buildAppBar() => const BinaroAppBar(title: 'Pembelajaran');

  // ---------------------------------------------------------------------------
  // HEADER DAFTAR MAPEL
  // ---------------------------------------------------------------------------
  Widget _buildHeaderSection() {
    return Row(
      children: [
        const Icon(Icons.menu_book_rounded, color: AppColors.primary, size: 28),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Daftar Mapel',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              _kelasInfo,
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
  // SEARCH BAR
  // ---------------------------------------------------------------------------
  Widget _buildSearchBar() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
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
          hintStyle: TextStyle(fontSize: 13, color: AppColors.textMuted),
          prefixIcon: Icon(Icons.search_rounded, color: AppColors.textMuted, size: 22),
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
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.border,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 16,
              color: isSelected ? Colors.white : AppColors.textSecondary,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: isSelected ? Colors.white : AppColors.textSecondary,
              ),
            ),
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: isSelected ? Colors.white.withValues(alpha: 0.25) : AppColors.surfaceAlt,
                shape: BoxShape.circle,
              ),
              child: Text(
                '$count',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  color: isSelected ? Colors.white : AppColors.textSecondary,
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
    final double progress = (mapel['progress'] as num?)?.toDouble() ?? 0.0;
    final int persen = (mapel['persen'] as num?)?.toInt() ?? (progress * 100).toInt();
    final String progressText = mapel['progress_text'] ?? '$persen% Selesai';
    final String jadwal = mapel['jadwal'] ?? 'Senin & Rabu';
    final String tipeIcon = mapel['tipe_icon'] ?? 'book';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.primary, // Dark Blue container
        borderRadius: BorderRadius.circular(16),
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
                      child: Icon(_getIcon(tipeIcon), color: AppColors.primary, size: 24),
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
                const SizedBox(height: 12),
                // Progress Kuis Info & Persentase
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Progress Kuis: $progressText',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: Colors.white70,
                      ),
                    ),
                    Text(
                      '$persen%',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: AppColors.accentBright,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                // Progress Bar (Oranye di atas putih)
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: LinearProgressIndicator(
                    value: progress,
                    minHeight: 6,
                    backgroundColor: Colors.white.withValues(alpha: 0.35),
                    valueColor: const AlwaysStoppedAnimation<Color>(AppColors.accent),
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
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Info Jadwal
                Row(
                  children: [
                    const Icon(Icons.access_time_rounded, color: AppColors.textSecondary, size: 16),
                    const SizedBox(width: 6),
                    Text(
                      jadwal,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textSecondary,
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
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(16),
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
        color: AppColors.infoLight,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.primaryLight),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: const BoxDecoration(
              color: AppColors.accent,
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
                    color: AppColors.primary,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Selesaikan 2 materi lagi hari ini untuk mendapatkan lencana Bintang Cilik!',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: AppColors.textSecondary,
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
}

