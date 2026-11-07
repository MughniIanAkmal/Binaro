import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'detail_materi_page.dart';
import 'kuis_page.dart';
import 'page_transitions.dart';
import 'profile_page.dart';

class DaftarMateriPage extends StatefulWidget {
  final Map<String, dynamic> subBab;
  final Map<String, dynamic> bab;
  final Map<String, dynamic> mapel;
  final Map<String, dynamic>? user;

  const DaftarMateriPage({
    super.key,
    required this.subBab,
    required this.bab,
    required this.mapel,
    this.user,
  });

  @override
  State<DaftarMateriPage> createState() => _DaftarMateriPageState();
}

class _DaftarMateriPageState extends State<DaftarMateriPage> {
  bool _isLoading = true;

  String _breadcrumb = 'Bab 1 > Sub Bab 3';
  List<dynamic> _materis = [];

  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  @override
  void initState() {
    super.initState();
    final int bNum = widget.bab['nomor'] ?? 1;
    final int sbNum = widget.subBab['nomor'] ?? 3;
    _breadcrumb = 'Bab $bNum > Sub Bab $sbNum';
    _fetchMateriList();
  }

  Future<void> _fetchMateriList() async {
    final int idSubBab = widget.subBab['id_sub_bab'] ?? 12;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/sub-bab/$idSubBab/materi'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _breadcrumb = data['breadcrumb'] ?? _breadcrumb;
            _materis = data['materis'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _materis = [];
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
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
          _breadcrumb,
          style: const TextStyle(
            fontSize: 17,
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
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Daftar Materi',
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // List of Material Cards or Empty State
                  if (_materis.isEmpty)
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
                            'Belum Ada Materi',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF1E293B),
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Guru belum mengunggah materi pembelajaran untuk sub bab ini. Silakan cek kembali nanti ya!',
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
                    ..._materis.map((m) => _buildMateriCard(m)),

                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // KARTU MATERI (Sesuai Screenshot 4)
  // ---------------------------------------------------------------------------
  Widget _buildMateriCard(Map<String, dynamic> materi) {
    final int nomor = materi['nomor'] ?? 1;
    final String tipe = materi['tipe_materi'] ?? 'video';
    final String badge = materi['badge'] ?? 'MATERI $nomor';
    final String infoMeta = materi['info_meta'] ?? '5 Menit';
    final String judul = materi['judul_materi'] ?? 'Materi Pembelajaran';
    final String deskripsi = materi['deskripsi'] ?? '';
    final bool hasThumbnail = materi['has_thumbnail'] == true;

    final bool isOrange = (materi['warna'] == 'orange') || (nomor == 2);
    final Color borderColor = isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);
    final Color badgeBgColor = isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: borderColor, width: 2),
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
          // Row Header: Icon + Badges
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Icon Bulat / Kotak Sesuai Tipe
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: isOrange ? const Color(0xFFF59E0B) : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  _getMateriIcon(tipe),
                  color: isOrange ? Colors.white : const Color(0xFF1D5A86),
                  size: 24,
                ),
              ),
              const SizedBox(width: 14),

              // Badges & Meta
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        // Pill MATERI 1/2/3
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: badgeBgColor,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            badge,
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        // Meta Info (Durasi / Hal / Soal)
                        Row(
                          children: [
                            Icon(
                              _getMetaIcon(tipe),
                              size: 13,
                              color: const Color(0xFF64748B),
                            ),
                            const SizedBox(width: 4),
                            Text(
                              infoMeta,
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF64748B),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Judul Materi
                    Text(
                      judul,
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1E293B),
                        height: 1.25,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 8),

          // Deskripsi
          Text(
            deskripsi,
            style: const TextStyle(
              fontSize: 12,
              color: Color(0xFF64748B),
              height: 1.35,
            ),
          ),

          // Thumbnail Ilustrasi Siswa & Pizza (Khusus Materi 2)
          if (hasThumbnail) ...[
            const SizedBox(height: 12),
            Container(
              height: 120,
              width: double.infinity,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                color: const Color(0xFFFFFBEB),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Stack(
                alignment: Alignment.bottomLeft,
                children: [
                  const Center(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.sentiment_very_satisfied_rounded, size: 48, color: Color(0xFFF59E0B)),
                        SizedBox(width: 14),
                        Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '🍕 Belajar Membagi Pizza',
                              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFFD97706)),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Materi Bergambar Interaktif',
                              style: TextStyle(fontSize: 11, color: Color(0xFF92400E)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  Positioned(
                    bottom: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.menu_book_rounded, color: Colors.white, size: 12),
                          SizedBox(width: 4),
                          Text(
                            'Halaman 2 dari 6',
                            style: TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: 14),

          // Tombol "Buka Materi ->"
          SizedBox(
            width: double.infinity,
            height: 44,
            child: ElevatedButton(
              onPressed: () {
                final String tipe = (materi['tipe_materi'] ?? '').toString().toLowerCase();
                final bool isKuis = tipe == 'kuis' || (materi['badge'] ?? '').toString().contains('3');

                if (isKuis) {
                  Navigator.push(
                    context,
                    SmoothPageRoute(
                      page: KuisPage(user: widget.user, quizId: 11),
                      type: TransitionType.sharedAxis,
                    ),
                  );
                } else {
                  Navigator.push(
                    context,
                    SmoothPageRoute(
                      page: DetailMateriPage(
                        materi: materi,
                        subBab: widget.subBab,
                        bab: widget.bab,
                        mapel: widget.mapel,
                        user: widget.user,
                      ),
                      type: TransitionType.sharedAxis,
                    ),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Buka Materi',
                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
                  ),
                  SizedBox(width: 6),
                  Icon(Icons.arrow_forward_rounded, size: 16),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  IconData _getMateriIcon(String type) {
    switch (type) {
      case 'video':
        return Icons.play_circle_filled_rounded;
      case 'dokumen':
        return Icons.menu_book_rounded;
      case 'kuis':
        return Icons.stars_rounded;
      default:
        return Icons.article_rounded;
    }
  }

  IconData _getMetaIcon(String type) {
    switch (type) {
      case 'video':
        return Icons.access_time_rounded;
      case 'dokumen':
        return Icons.menu_book_outlined;
      case 'kuis':
        return Icons.help_outline_rounded;
      default:
        return Icons.info_outline_rounded;
    }
  }
}
