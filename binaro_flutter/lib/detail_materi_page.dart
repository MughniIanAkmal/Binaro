import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'kuis_page.dart';
import 'page_transitions.dart';
import 'profile_page.dart';

class DetailMateriPage extends StatefulWidget {
  final Map<String, dynamic> materi;
  final Map<String, dynamic>? subBab;
  final Map<String, dynamic>? bab;
  final Map<String, dynamic>? mapel;
  final Map<String, dynamic>? user;

  const DetailMateriPage({
    super.key,
    required this.materi,
    this.subBab,
    this.bab,
    this.mapel,
    this.user,
  });

  @override
  State<DetailMateriPage> createState() => _DetailMateriPageState();
}

class _DetailMateriPageState extends State<DetailMateriPage> {
  bool _isLoading = true;
  bool _isPlayingVideo = false;

  Map<String, dynamic> _detail = {};

  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  @override
  void initState() {
    super.initState();
    _fetchDetailMateri();
  }

  Future<void> _fetchDetailMateri() async {
    final int idMateri = widget.materi['id_materi'] ?? 18;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/materi/$idMateri'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          setState(() {
            _detail = result['data'] ?? {};
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _detail = {};
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
        title: const Text(
          'Materi Pelajaran',
          style: TextStyle(
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
          : _detail.isEmpty
              ? Center(
                  child: Container(
                    margin: const EdgeInsets.all(24),
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
                      mainAxisSize: MainAxisSize.min,
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
                          'Materi Belum Tersedia',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF1E293B),
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Konten materi pembelajaran ini belum diunggah oleh guru pengampu.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 13,
                            color: Color(0xFF64748B),
                            height: 1.4,
                          ),
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: () => Navigator.pop(context),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF1D5A86),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: const Text('Kembali', style: TextStyle(color: Colors.white)),
                        ),
                      ],
                    ),
                  ),
                )
              : Column(
              children: [
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // 1. Breadcrumb Chip
                        _buildBreadcrumbChip(),

                        const SizedBox(height: 16),

                        // 2. Video Player Container
                        _buildVideoPlayerCard(),

                        const SizedBox(height: 18),

                        // 3. Ilustrasi Konsep 1/2
                        _buildConceptIllustrationCard(),

                        const SizedBox(height: 18),

                        // 4. Konten Bacaan Pelajaran
                        _buildReadingContentCard(),

                        const SizedBox(height: 18),

                        // 5. Tips Penting Sahabat Belajar (Solid Oranye)
                        _buildTipsCard(),

                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),

                // Sticky Bottom Bar: Kembali & Lanjut Kuis
                _buildStickyBottomBar(),
              ],
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. BREADCRUMB CHIP
  // ---------------------------------------------------------------------------
  Widget _buildBreadcrumbChip() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: const Row(
              children: [
                Icon(Icons.calculate_rounded, size: 14, color: Color(0xFF1D5A86)),
                SizedBox(width: 5),
                Text(
                  'Matematika',
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 6),
          const Icon(Icons.chevron_right_rounded, size: 16, color: Color(0xFF94A3B8)),
          const SizedBox(width: 6),
          const Text(
            'Bab 1 Pecahan',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
          ),
          const SizedBox(width: 6),
          const Icon(Icons.chevron_right_rounded, size: 16, color: Color(0xFF94A3B8)),
          const SizedBox(width: 6),
          const Text(
            'Sub Bab 3 Pecahan 1/2',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF1D5A86)),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 2. VIDEO PLAYER CARD (Dark Container dengan Tombol Play Oranye)
  // ---------------------------------------------------------------------------
  Widget _buildVideoPlayerCard() {
    final video = _detail['video'] ?? {};
    final String judul = video['judul'] ?? 'Animasi Seru: Membagi Buah Menjadi Dua Sama Besar';
    final String durasi = video['durasi'] ?? '04:25';
    final String kualitas = video['kualitas'] ?? 'HD 720p';

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF0F2B48), // Dark Navy Blue Player
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 14,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          // Top Bar Video: Badge & Kualitas
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.ondemand_video_rounded, color: Colors.white, size: 14),
                      const SizedBox(width: 6),
                      Text(
                        'Video Pembelajaran • $durasi',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.3),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    kualitas,
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.white70),
                  ),
                ),
              ],
            ),
          ),

          // Center: Tombol Play Bulat Oranye
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 24),
            child: Center(
              child: InkWell(
                onTap: () {
                  setState(() => _isPlayingVideo = !_isPlayingVideo);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(_isPlayingVideo ? 'Memutar video pembelajaran...' : 'Video dijeda.'),
                      duration: const Duration(seconds: 1),
                    ),
                  );
                },
                borderRadius: BorderRadius.circular(40),
                child: Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF59E0B),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFFF59E0B).withValues(alpha: 0.4),
                        blurRadius: 16,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: Icon(
                    _isPlayingVideo ? Icons.pause_rounded : Icons.play_arrow_rounded,
                    color: Colors.white,
                    size: 38,
                  ),
                ),
              ),
            ),
          ),

          // Bottom Bar Video: Judul & Progress Slider
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 10),
                // Progress Bar (Oranye di atas abu-abu)
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: const LinearProgressIndicator(
                    value: 0.35,
                    minHeight: 4,
                    backgroundColor: Colors.white24,
                    valueColor: AlwaysStoppedAnimation<Color>(Color(0xFFF59E0B)),
                  ),
                ),
                const SizedBox(height: 8),
                const Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.play_arrow_rounded, color: Colors.white70, size: 16),
                        SizedBox(width: 4),
                        Icon(Icons.volume_up_rounded, color: Colors.white70, size: 16),
                        SizedBox(width: 8),
                        Text(
                          '01:15 / 04:25',
                          style: TextStyle(fontSize: 11, color: Colors.white70),
                        ),
                      ],
                    ),
                    Icon(Icons.fullscreen_rounded, color: Colors.white70, size: 20),
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
  // 3. ILUSTRASI KONSEP 1/2 (Apel Terbelah Dua)
  // ---------------------------------------------------------------------------
  Widget _buildConceptIllustrationCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF7ED), // Cream orange background
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFFED7AA), width: 1.5),
      ),
      child: Column(
        children: [
          const Row(
            children: [
              Icon(Icons.photo_library_outlined, size: 16, color: Color(0xFFD97706)),
              SizedBox(width: 6),
              Text(
                'Ilustrasi Konsep 1/2',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFFD97706),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Visual Apel Dibelah Dua: 1/2 dan 1/2
          Container(
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.03),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Apel 1 Utuh
                    Column(
                      children: [
                        Container(
                          width: 58,
                          height: 58,
                          decoration: const BoxDecoration(
                            color: Color(0xFFFEE2E2),
                            shape: BoxShape.circle,
                          ),
                          child: const Center(
                            child: Text('🍎', style: TextStyle(fontSize: 32)),
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          '1 Utuh',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFFDC2626)),
                        ),
                      ],
                    ),
                    const SizedBox(width: 18),
                    const Text('➔', style: TextStyle(fontSize: 22, color: Color(0xFF94A3B8))),
                    const SizedBox(width: 18),

                    // Belahan Kiri 1/2
                    Column(
                      children: [
                        Container(
                          width: 54,
                          height: 54,
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: const BorderRadius.horizontal(left: Radius.circular(28)),
                            border: Border.all(color: const Color(0xFFBFDBFE), width: 1.5),
                          ),
                          child: const Center(
                            child: Text(
                              '1/2',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF1D5A86)),
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Setengah',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF1D5A86)),
                        ),
                      ],
                    ),
                    const SizedBox(width: 8),

                    // Belahan Kanan 1/2
                    Column(
                      children: [
                        Container(
                          width: 54,
                          height: 54,
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF3C7),
                            borderRadius: const BorderRadius.horizontal(right: Radius.circular(28)),
                            border: Border.all(color: const Color(0xFFFDE68A), width: 1.5),
                          ),
                          child: const Center(
                            child: Text(
                              '1/2',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFFD97706)),
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Setengah',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFFD97706)),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFBBF7D0)),
                  ),
                  child: const Text(
                    'ONE WHOLE APPLE = TWO EQUAL HALVES',
                    style: TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF16A34A),
                      letterSpacing: 0.6,
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

  // ---------------------------------------------------------------------------
  // 4. KONTEN BACAAN PELAJARAN
  // ---------------------------------------------------------------------------
  Widget _buildReadingContentCard() {
    final konten = _detail['konten'] ?? {};
    final String badge = konten['badge'] ?? 'Materi Dasar';
    final String judul = konten['judul'] ?? 'Mengenal Pecahan 1/2 (Setengah)';
    final String subjudul = konten['subjudul'] ?? 'Belajar membagi benda secara adil dan sama besar!';
    final String p1 = konten['paragraf_1'] ?? '';
    final String p2 = konten['paragraf_2'] ?? '';
    final List<dynamic> poin = konten['poin_pembagi'] ?? [];
    final String contoh = konten['contoh_sekitar'] ?? '';

    return Container(
      padding: const EdgeInsets.all(18),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Badge "Materi Dasar"
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.school_rounded, color: Color(0xFFD97706), size: 14),
                const SizedBox(width: 5),
                Text(
                  badge,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFFD97706),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // Judul Materi
          Text(
            judul,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subjudul,
            style: const TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w600,
              color: Color(0xFFD97706),
            ),
          ),
          const SizedBox(height: 14),

          // Paragraf 1
          Text(
            p1,
            style: const TextStyle(
              fontSize: 13,
              color: Color(0xFF334155),
              height: 1.5,
            ),
          ),
          const SizedBox(height: 10),

          // Paragraf 2
          RichText(
            text: TextSpan(
              style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.5),
              children: [
                TextSpan(
                  text: p2.isNotEmpty
                      ? p2
                      : 'Setiap potongan apel itu disebut satu perdua atau setengah, dan dalam matematika ditulis sebagai ',
                ),
                WidgetSpan(
                  alignment: PlaceholderAlignment.middle,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: const Text(
                      '1/2',
                      style: TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF1D5A86), fontSize: 13),
                    ),
                  ),
                ),
                const TextSpan(text: ' .'),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // Kotak MEMAHAMI ANGKA PECAHAN 1/2
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.info_outline_rounded, color: Color(0xFF1D5A86), size: 18),
                    SizedBox(width: 8),
                    Text(
                      'MEMAHAMI ANGKA PECAHAN 1/2',
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1D5A86),
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Poin 1 & Poin 2
                ...poin.map((p) {
                  final bool isBlue = p['warna'] == 'blue';
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 32,
                          height: 32,
                          decoration: BoxDecoration(
                            color: isBlue ? const Color(0xFF1D5A86) : const Color(0xFFF59E0B),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Center(
                            child: Text(
                              p['nomor'] ?? '1',
                              style: const TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w800,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                p['judul'] ?? '',
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF1E293B),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                p['keterangan'] ?? '',
                                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Box Contoh di Sekitar Kita
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDF4),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFBBF7D0)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.nature_people_rounded, color: Color(0xFF16A34A), size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Contoh di Sekitar Kita',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF166534),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        contoh,
                        style: const TextStyle(fontSize: 12, color: Color(0xFF15803D), height: 1.4),
                      ),
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
  // 5. TIPS PENTING SAHABAT BELAJAR (Solid Oranye)
  // ---------------------------------------------------------------------------
  Widget _buildTipsCard() {
    final List<dynamic> tips = _detail['tips'] ?? [];

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFF59E0B), // Solid Orange
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFF59E0B).withValues(alpha: 0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.lightbulb_rounded, color: Color(0xFFF59E0B), size: 18),
              ),
              const SizedBox(width: 10),
              const Text(
                'TIPS PENTING SAHABAT BELAJAR',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  letterSpacing: 0.6,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          ...tips.map((t) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('• ', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                  Expanded(
                    child: Text(
                      '$t',
                      style: const TextStyle(
                        fontSize: 12.5,
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        height: 1.35,
                      ),
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // STICKY BOTTOM BAR (Kembali & Lanjut ke Kuis)
  // ---------------------------------------------------------------------------
  Widget _buildStickyBottomBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 10,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            // Tombol Kembali
            Expanded(
              flex: 4,
              child: SizedBox(
                height: 48,
                child: OutlinedButton.icon(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back_rounded, size: 18),
                  label: const Text('Kembali', style: TextStyle(fontWeight: FontWeight.w700)),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFF1D5A86),
                    side: const BorderSide(color: Color(0xFF1D5A86), width: 1.8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),

            // Tombol Lanjut ke Kuis (5 Soal)
            Expanded(
              flex: 6,
              child: SizedBox(
                height: 48,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      SmoothPageRoute(
                        page: KuisPage(user: widget.user, quizId: 11),
                        type: TransitionType.sharedAxis,
                      ),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1D5A86),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Lanjut ke Kuis (5 Soal)',
                        style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                      ),
                      SizedBox(width: 6),
                      Icon(Icons.arrow_forward_rounded, size: 16),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
