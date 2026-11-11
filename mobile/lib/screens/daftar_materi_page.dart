import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'detail_materi_page.dart';
import 'kuis_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
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
  List<dynamic> _materiList = [];

  static const String _baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    final int bNum = widget.bab['nomor'] ?? 1;
    final int sbNum = widget.subBab['nomor'] ?? 1;
    _breadcrumb = 'Bab $bNum > Sub Bab $sbNum';
    _fetchMateriList();
  }

  Future<void> _fetchMateriList() async {
    final int subBabId = widget.subBab['id_sub_bab'] ?? 12;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/sub-bab/$subBabId/materi'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _breadcrumb = data['breadcrumb'] ?? _breadcrumb;
            _materiList = data['materis'] ?? [];
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _materiList = [];
        _isLoading = false;
      });
    }
  }

  String? _extractYoutubeId(String? url) {
    if (url == null || url.isEmpty) return null;
    final regExp = RegExp(
      r'(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})',
      caseSensitive: false,
    );
    final match = regExp.firstMatch(url);
    return match?.group(1);
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
          : RefreshIndicator(
              onRefresh: _fetchMateriList,
              color: const Color(0xFF1D5A86),
              backgroundColor: Colors.white,
              displacement: 28,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
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
                    if (_materiList.isEmpty)
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
                      ..._materiList.map((m) => _buildMateriCard(m)),

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // KARTU MATERI (Sesuai Desain & Permintaan User: Kuis = Kerjakan Quiz)
  // ---------------------------------------------------------------------------
  Widget _buildMateriCard(Map<String, dynamic> materi) {
    final int nomor = materi['nomor'] ?? 1;
    final String tipe = (materi['tipe_materi'] ?? 'video').toString().toLowerCase();
    final bool isKuis = tipe == 'kuis' || materi['id_quiz'] != null;
    final int? quizId = materi['id_quiz'];

    final String badge = isKuis
        ? ((materi['badge'] != null && materi['badge'].toString().toUpperCase().startsWith('KUIS'))
            ? materi['badge']
            : 'KUIS $nomor')
        : (materi['badge'] ?? 'MATERI $nomor');
    final String infoMeta = isKuis ? 'Kuis Mandiri' : (materi['info_meta'] ?? 'Materi Belajar');
    final String judul = materi['judul_materi'] ?? (isKuis ? 'Kuis Latihan' : 'Materi Pembelajaran');

    String deskripsi = materi['deskripsi'] ?? '';
    if (isKuis && (deskripsi.isEmpty || deskripsi.toLowerCase().contains('buka materi'))) {
      deskripsi = 'Silakan kerjakan quiz untuk menguji pemahaman materi ini.';
    } else if (deskripsi.isEmpty) {
      deskripsi = 'Silakan buka materi untuk mempelajari topik ini.';
    }

    final bool isOrange = isKuis || (materi['warna'] == 'orange') || (nomor == 2);
    final Color borderColor = isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);
    final Color badgeBgColor = isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86);

    void navigateToDestination() {
      if (isKuis) {
        Navigator.push(
          context,
          SmoothPageRoute(
            page: KuisPage(user: widget.user, quizId: quizId),
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
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: navigateToDestination,
          child: Padding(
            padding: const EdgeInsets.all(16),
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
                              // Pill KUIS / MATERI 1/2/3
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

                          // Judul Materi / Kuis
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

                // Preview Thumbnail jika Materi berupa Video YouTube
                if (tipe == 'video' && materi['url_video'] != null) ...[
                  Builder(
                    builder: (ctx) {
                      final String? ytId = _extractYoutubeId(materi['url_video']?.toString());
                      if (ytId == null) return const SizedBox.shrink();
                      return Padding(
                        padding: const EdgeInsets.only(top: 12),
                        child: InkWell(
                          onTap: navigateToDestination,
                          borderRadius: BorderRadius.circular(12),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Stack(
                              alignment: Alignment.center,
                              children: [
                                Image.network(
                                  'https://img.youtube.com/vi/$ytId/hqdefault.jpg',
                                  height: 130,
                                  width: double.infinity,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                                ),
                                Container(
                                  width: 44,
                                  height: 44,
                                  decoration: BoxDecoration(
                                    gradient: const LinearGradient(
                                      colors: [Color(0xFFFFA000), Color(0xFFF59E0B)],
                                    ),
                                    shape: BoxShape.circle,
                                    boxShadow: [
                                      BoxShadow(
                                        color: const Color(0xFFFFA000).withValues(alpha: 0.55),
                                        blurRadius: 10,
                                        offset: const Offset(0, 3),
                                      ),
                                    ],
                                  ),
                                  child: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 30),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ],

                const SizedBox(height: 14),

                // Tombol Aksi: "Kerjakan Quiz" jika Kuis, atau "Buka Materi" jika Materi biasa
                SizedBox(
                  width: double.infinity,
                  height: 44,
                  child: ElevatedButton(
                    onPressed: navigateToDestination,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isOrange ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          isKuis ? 'Kerjakan Quiz' : 'Buka Materi',
                          style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
                        ),
                        const SizedBox(width: 8),
                        Icon(
                          isKuis ? Icons.assignment_turned_in_rounded : Icons.arrow_forward_rounded,
                          size: 18,
                        ),
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
