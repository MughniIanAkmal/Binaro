import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'daftar_materi_page.dart';
import 'page_transitions.dart';
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
  List<dynamic> _subBabs = [];

  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  @override
  void initState() {
    super.initState();
    _babHeader = {
      'nomor': widget.bab['nomor'] ?? 1,
      'nama_mapel': (widget.mapel['nama'] ?? 'MATEMATIKA').toString().toUpperCase(),
      'nama_bab': widget.bab['nama_bab'] ?? 'Pecahan & Desimal',
      'info_sub': '4 Sub Bab Pembelajaran',
    };
    _fetchSubBabData();
  }

  Future<void> _fetchSubBabData() async {
    final int idBab = widget.bab['id_bab'] ?? 10;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/bab/$idBab/sub-bab'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _babHeader = data['bab'] ?? _babHeader;
            _subBabs = data['sub_babs'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      _applyFallback();
      setState(() => _isLoading = false);
    }
  }

  void _applyFallback() {
    _subBabs = [
      {
        'id_sub_bab': 10,
        'nomor': 1,
        'label': 'Sub Bab 1',
        'nama_sub_bab': 'Mengenal Bentuk Pecahan',
        'deskripsi': null,
        'tipe_icon': 'cake',
        'is_locked': false,
        'is_active': false,
      },
      {
        'id_sub_bab': 11,
        'nomor': 2,
        'label': 'Sub Bab 2',
        'nama_sub_bab': 'Pecahan Senilai',
        'deskripsi': null,
        'tipe_icon': 'scale',
        'is_locked': false,
        'is_active': false,
      },
      {
        'id_sub_bab': 12,
        'nomor': 3,
        'label': 'Sub Bab 3 •',
        'nama_sub_bab': 'Membandingkan Pecahan',
        'deskripsi': 'Besar, kecil, dan sama rasa!',
        'tipe_icon': 'fraction',
        'is_locked': false,
        'is_active': true,
      },
      {
        'id_sub_bab': 13,
        'nomor': 4,
        'label': 'Sub Bab 4',
        'nama_sub_bab': 'Pecahan Desimal Sederhana',
        'deskripsi': null,
        'tipe_icon': 'lock',
        'is_locked': true,
        'is_active': false,
      },
    ];
  }

  @override
  Widget build(BuildContext context) {
    final int nomorBab = _babHeader['nomor'] ?? 1;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1D5A86),
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
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF1D5A86)))
          : SingleChildScrollView(
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
                      color: Color(0xFF1E293B),
                    ),
                  ),

                  const SizedBox(height: 14),

                  // 3. List Sub Bab (Sesuai Screenshot 3)
                  ..._subBabs.map((subBab) => _buildSubBabCard(subBab)),

                  const SizedBox(height: 24),
                ],
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
        color: const Color(0xFF1D5A86),
        borderRadius: BorderRadius.circular(20),
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
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(14),
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
                    color: Color(0xFFFBBF24),
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
  // 3. SUB BAB CARD (Sesuai Screenshot 3)
  // ---------------------------------------------------------------------------
  Widget _buildSubBabCard(Map<String, dynamic> subBab) {
    final String label = subBab['label'] ?? 'Sub Bab';
    final String namaSubBab = subBab['nama_sub_bab'] ?? 'Mengenal Bentuk Pecahan';
    final String? deskripsi = subBab['deskripsi'];
    final bool isLocked = subBab['is_locked'] == true;
    final bool isActive = subBab['is_active'] == true;
    final String tipeIcon = subBab['tipe_icon'] ?? 'fraction';

    // Warna Card
    final Color borderColor = isActive ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: borderColor, width: isActive ? 2.5 : 1.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                // Icon Sub Bab
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isActive
                        ? const Color(0xFFF59E0B)
                        : (isLocked ? const Color(0xFFF1F5F9) : const Color(0xFFEFF6FF)),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(
                    _getSubBabIcon(tipeIcon),
                    color: isActive
                        ? Colors.white
                        : (isLocked ? const Color(0xFF94A3B8) : const Color(0xFF1D5A86)),
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
                          color: isActive ? const Color(0xFFF59E0B) : const Color(0xFF64748B),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        namaSubBab,
                        style: TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                          color: isLocked ? const Color(0xFF94A3B8) : const Color(0xFF1E293B),
                        ),
                      ),
                      if (deskripsi != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          deskripsi,
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: Color(0xFF64748B),
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Jika Active, tampilkan Tombol "Buka Daftar Materi >" (Oranye Penuh)
          if (isActive)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
              child: SizedBox(
                width: double.infinity,
                height: 46,
                child: ElevatedButton.icon(
                  onPressed: () {
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
                  },
                  icon: const Icon(Icons.arrow_circle_right_rounded, size: 20),
                  label: const Text(
                    'Buka Daftar Materi',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFF59E0B),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ),
        ],
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
