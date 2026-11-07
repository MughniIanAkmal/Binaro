import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'daftar_sub_bab_page.dart';
import 'page_transitions.dart';
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
  List<dynamic> _babs = [];
  int _totalBab = 6;

  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  @override
  void initState() {
    super.initState();
    _headerMapel = {
      'nama': widget.mapel['nama'] ?? 'Matematika',
      'badge': 'Matematika Seru',
      'judul_seru': 'Petualangan Angka',
      'progress_bab': '3/6 Bab',
      'tipe_icon': widget.mapel['tipe_icon'] ?? 'math',
    };
    _fetchBabData();
  }

  Future<void> _fetchBabData() async {
    final int idMapel = widget.mapel['id_mapel'] ?? 17;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/mapel/$idMapel/bab'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _headerMapel = data['mapel'] ?? _headerMapel;
            _totalBab = data['total_bab'] ?? 6;
            _babs = data['babs'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _babs = [];
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF1D5A86)))
          : SingleChildScrollView(
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
                  if (_babs.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
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
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 68,
                            height: 68,
                            decoration: const BoxDecoration(
                              color: Color(0xFFEFF6FF),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.menu_book_outlined,
                              size: 34,
                              color: Color(0xFF1D5A86),
                            ),
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'Belum Ada Bab',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF1E293B),
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Guru belum mengunggah bab pembelajaran untuk mata pelajaran ini.',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 13,
                              color: Color(0xFF64748B),
                              height: 1.4,
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    ..._babs.map((bab) => _buildBabCard(bab)),

                  const SizedBox(height: 24),
                ],
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
      backgroundColor: const Color(0xFF1D5A86),
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
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(10),
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
                  color: Color(0xFFFBBF24),
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
  // 1. HEADER CARD (Petualangan Angka, 3/6 Bab)
  // ---------------------------------------------------------------------------
  Widget _buildHeaderCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
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
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(14),
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
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _headerMapel['badge'] ?? 'Matematika Seru',
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFFD97706),
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  _headerMapel['judul_seru'] ?? 'Petualangan Angka',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          ),
          // Badge "3/6 Bab"
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              _headerMapel['progress_bab'] ?? '3/6 Bab',
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: Color(0xFFD97706),
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
        color: const Color(0xFF1D5A86),
        borderRadius: BorderRadius.circular(20),
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
        ? (nomor % 2 == 1 ? const Color(0xFFFDE68A) : const Color(0xFFBFDBFE))
        : (nomor % 2 == 1 ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86));

    final Color badgeBgColor = isLocked
        ? (nomor % 2 == 1 ? const Color(0xFFFEF3C7) : const Color(0xFFDBEAFE))
        : (nomor % 2 == 1 ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86));

    final Color badgeTextColor = isLocked
        ? (nomor % 2 == 1 ? const Color(0xFFD97706) : const Color(0xFF1D5A86))
        : Colors.white;

    // Warna tombol "Buka >"
    final Color btnColor = (nomor % 2 == 1) ? const Color(0xFF1D5A86) : const Color(0xFFF59E0B);

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
                      color: Color(0xFFD97706),
                      letterSpacing: 0.5,
                    ),
                  ),
                Text(
                  namaBab,
                  style: TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                    color: isLocked ? const Color(0xFF64748B) : const Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          ),

          // Tombol Buka ATAU Ikon Gembok (Lock)
          if (isLocked)
            const Padding(
              padding: EdgeInsets.only(right: 8),
              child: Icon(Icons.lock_outline_rounded, color: Color(0xFF94A3B8), size: 22),
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

