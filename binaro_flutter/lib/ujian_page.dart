import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dashboard_page.dart';
import 'hasil_ujian_page.dart';
import 'mapel_page.dart';
import 'page_transitions.dart';
import 'petunjuk_ujian_page.dart';
import 'profile_page.dart';

class UjianPage extends StatefulWidget {
  final Map<String, dynamic>? user;

  const UjianPage({
    super.key,
    this.user,
  });

  @override
  State<UjianPage> createState() => _UjianPageState();
}

class _UjianPageState extends State<UjianPage> {
  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  bool _isLoading = true;
  int _activeTab = 0; // 0 = Tersedia, 1 = Selesai
  final int _bottomNavIndex = 1; // Tab Ujian aktif

  Map<String, dynamic> _banner = {
    'judul': 'Penilaian Semester Siswa',
    'subjudul': 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
  };
  int _tersediaCount = 2;
  int _selesaiCount = 3;
  List<Map<String, dynamic>> _ujianTersedia = [];
  List<Map<String, dynamic>> _ujianSelesai = [];
  Map<String, dynamic>? _nilaiTerakhir;
  String _tips = 'Kerjakan soal yang mudah dulu ya, agar waktumu cukup dan nilaimu maksimal!';

  @override
  void initState() {
    super.initState();
    _fetchUjianData();
  }

  Future<void> _fetchUjianData() async {
    setState(() => _isLoading = true);
    final int siswaId = widget.user?['id_siswa'] ?? 1;

    try {
      final response = await http
          .get(Uri.parse('$_baseUrl/siswa/ujian?id_siswa=$siswaId'))
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          final data = decoded['data'];
          if (mounted) {
            setState(() {
              _banner = Map<String, dynamic>.from(data['banner'] ?? _banner);
              _tersediaCount = data['tersedia_count'] ?? 2;
              _selesaiCount = data['selesai_count'] ?? 3;
              _ujianTersedia = (data['ujian_tersedia'] as List? ?? [])
                  .map((e) => Map<String, dynamic>.from(e as Map))
                  .toList();
              _ujianSelesai = (data['ujian_selesai'] as List? ?? [])
                  .map((e) => Map<String, dynamic>.from(e as Map))
                  .toList();
              if (data['nilai_terakhir'] != null) {
                _nilaiTerakhir = Map<String, dynamic>.from(data['nilai_terakhir'] as Map);
              }
              _tips = data['tips'] ?? _tips;
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}

    // Fallback data
    if (mounted) {
      setState(() {
        _ujianTersedia = _getFallbackTersedia();
        _ujianSelesai = _getFallbackSelesai();
        _nilaiTerakhir = {
          'id_quiz': 14,
          'mapel': 'Bahasa Indonesia',
          'judul': 'Ulangan Harian Puisi A',
          'tanggal': '12 Mei • Sangat Memuaskan!',
          'nilai': 95,
          'badge': '95 – Hebat!',
        };
        _isLoading = false;
      });
    }
  }

  List<Map<String, dynamic>> _getFallbackTersedia() {
    return [
      {
        'id_quiz': 12,
        'judul_quiz': 'Penilaian Tengah Semester: Matematika Kelas 4B',
        'mapel': 'Matematika',
        'guru': 'Ibu Sarah Wijaya, S.Pd.',
        'durasi_menit': 60,
        'total_soal': 20,
        'tipe_soal': 'Pilihan Ganda',
        'badge_mapel': 'Matematika',
        'badge_status': 'Bisa Dikerjakan',
        'badge_jadwal': 'Hari Ini',
        'is_bisa_dikerjakan': true,
        'keterangan_tombol': 'Mulai Ujian',
      },
      {
        'id_quiz': 13,
        'judul_quiz': 'Kuis Harian: IPAS – Daur Hidup Hewan',
        'sub_judul': 'Materi Metamorfosis Sempurna & Tidak Sempurna',
        'mapel': 'IPAS',
        'guru': 'Bapak Rahmat Hidayat, M.Pd.',
        'durasi_menit': 45,
        'total_soal': 15,
        'tipe_soal': 'Kuis',
        'badge_mapel': 'IPAS',
        'badge_status': 'Belum Dimulai',
        'badge_jadwal': 'Besok, 09.00 WIB',
        'is_bisa_dikerjakan': false,
        'keterangan_tombol': 'Dibuka Besok Jam 09.00',
      },
    ];
  }

  List<Map<String, dynamic>> _getFallbackSelesai() {
    return [
      {
        'id_quiz': 14,
        'id_hasil': 8,
        'judul_quiz': 'Ulangan Harian Puisi A',
        'mapel': 'Bahasa Indonesia',
        'tanggal': '12 Mei',
        'keterangan': 'Sangat Memuaskan!',
        'nilai': 95,
        'badge_nilai': '95 – Hebat!',
      },
      {
        'id_quiz': 15,
        'id_hasil': 9,
        'judul_quiz': 'Penilaian Harian: Tata Surya',
        'mapel': 'IPAS',
        'tanggal': '5 Mei',
        'keterangan': 'Lulus KKM',
        'nilai': 88,
        'badge_nilai': '88 – Bagus!',
      },
      {
        'id_quiz': 16,
        'id_hasil': 10,
        'judul_quiz': 'Kuis Pancasila & Norma',
        'mapel': 'PPKn',
        'tanggal': '28 April',
        'keterangan': 'Lulus KKM',
        'nilai': 85,
        'badge_nilai': '85 – Bagus!',
      },
    ];
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1D5A86),
        elevation: 0,
        leadingWidth: 0,
        leading: const SizedBox.shrink(),
        titleSpacing: 20,
        title: Row(
          children: [
            // Logo Sahabat Belajar Topi Toga Oranye
            Container(
              width: 38,
              height: 38,
              decoration: const BoxDecoration(
                color: Color(0xFFF59E0B),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.school_rounded, color: Colors.white, size: 22),
            ),
            const SizedBox(width: 12),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'SAHABAT BELAJAR',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFFF59E0B),
                    letterSpacing: 0.8,
                  ),
                ),
                SizedBox(height: 1),
                Text(
                  'Ujian Online',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
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
            padding: const EdgeInsets.only(right: 16),
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
                  // 1. BANNER PENILAIAN SEMESTER SISWA
                  _buildTopBanner(),
                  const SizedBox(height: 16),

                  // 2. TOGGLE FILTER [Tersedia (2)]  [Selesai (3)]
                  _buildTabToggle(),
                  const SizedBox(height: 16),

                  // 3. DAFTAR KARTU UJIAN
                  if (_activeTab == 0) ...[
                    ..._ujianTersedia.map((exam) => _buildTersediaCard(exam)),
                  ] else ...[
                    ..._ujianSelesai.map((exam) => _buildSelesaiCard(exam)),
                  ],
                  const SizedBox(height: 20),

                  // 4. SECTION NILAI UJIAN TERAKHIR
                  _buildNilaiTerakhirSection(),
                  const SizedBox(height: 16),

                  // 5. KARTU TIPS MENGERJAKAN UJIAN
                  _buildTipsCard(),
                  const SizedBox(height: 24),
                ],
              ),
            ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. BANNER PENILAIAN SEMESTER SISWA
  // ---------------------------------------------------------------------------
  Widget _buildTopBanner() {
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
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _banner['judul'] ?? 'Penilaian Semester Siswa',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  _banner['subjudul'] ?? 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF64748B),
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 14),

          // Icon Head / Brain Gear Container
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.psychology_outlined, color: Color(0xFF1D5A86), size: 28),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 2. TOGGLE FILTER [Tersedia (2)]  [Selesai (3)]
  // ---------------------------------------------------------------------------
  Widget _buildTabToggle() {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: const Color(0xFF1D5A86), // Deep blue container
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          // Tab 1: Tersedia
          Expanded(
            child: GestureDetector(
              onTap: () {
                setState(() => _activeTab = 0);
              },
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 9),
                decoration: BoxDecoration(
                  color: _activeTab == 0 ? Colors.white : Colors.transparent,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: _activeTab == 0
                      ? [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.08),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ]
                      : null,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.description_outlined,
                      size: 15,
                      color: _activeTab == 0 ? const Color(0xFF1D5A86) : Colors.white70,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Tersedia ($_tersediaCount)',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: _activeTab == 0 ? const Color(0xFF1D5A86) : Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Tab 2: Selesai
          Expanded(
            child: GestureDetector(
              onTap: () {
                setState(() => _activeTab = 1);
              },
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 9),
                decoration: BoxDecoration(
                  color: _activeTab == 1 ? Colors.white : Colors.transparent,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: _activeTab == 1
                      ? [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.08),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ]
                      : null,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.check_circle_outline_rounded,
                      size: 15,
                      color: _activeTab == 1 ? const Color(0xFF1D5A86) : Colors.white70,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Selesai ($_selesaiCount)',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: _activeTab == 1 ? const Color(0xFF1D5A86) : Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3A. KARTU UJIAN TERSEDIA (Matematika & IPAS)
  // ---------------------------------------------------------------------------
  Widget _buildTersediaCard(Map<String, dynamic> exam) {
    final int idQuiz = exam['id_quiz'] ?? 12;
    final String judul = exam['judul_quiz'] ?? '';
    final String? subJudul = exam['sub_judul'];
    final String mapel = exam['mapel'] ?? 'Umum';
    final String guru = exam['guru'] ?? '';
    final int durasi = (exam['durasi_menit'] ?? 60) as int;
    final int totalSoal = (exam['total_soal'] ?? 20) as int;
    final String tipeSoal = exam['tipe_soal'] ?? 'Pilihan Ganda';
    final bool canStart = exam['is_bisa_dikerjakan'] ?? true;
    final String badgeStatus = exam['badge_status'] ?? (canStart ? 'Bisa Dikerjakan' : 'Belum Dimulai');
    final String badgeJadwal = exam['badge_jadwal'] ?? (canStart ? 'Hari Ini' : 'Besok, 09.00 WIB');
    final String tombolText = exam['keterangan_tombol'] ?? (canStart ? 'Mulai Ujian' : 'Dibuka Besok Jam 09.00');

    final bool isMatematika = mapel.toLowerCase().contains('matematika');

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row Badges
          Row(
            children: [
              // Badge Mapel
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isMatematika ? const Color(0xFFEFF6FF) : const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isMatematika ? Icons.calculate_outlined : Icons.eco_outlined,
                      size: 13,
                      color: isMatematika ? const Color(0xFF1D5A86) : const Color(0xFF059669),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      mapel,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w800,
                        color: isMatematika ? const Color(0xFF1D5A86) : const Color(0xFF059669),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 6),

              // Badge Status (Bisa Dikerjakan / Belum Dimulai)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: canStart ? const Color(0xFFDCFCE7) : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (canStart) ...[
                      const Icon(Icons.circle, size: 7, color: Color(0xFF16A34A)),
                      const SizedBox(width: 4),
                    ],
                    Text(
                      badgeStatus,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        color: canStart ? const Color(0xFF15803D) : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 6),

              // Badge Jadwal (Hari Ini / Besok)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: canStart ? const Color(0xFFFFFBEB) : const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: canStart ? const Color(0xFFFDE68A) : const Color(0xFFE2E8F0),
                    width: 0.8,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.calendar_today_outlined,
                      size: 11,
                      color: canStart ? const Color(0xFFD97706) : const Color(0xFF64748B),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      badgeJadwal,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w600,
                        color: canStart ? const Color(0xFFB45309) : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Judul Ujian
          Text(
            judul,
            style: const TextStyle(
              fontSize: 14.5,
              fontWeight: FontWeight.w800,
              color: Color(0xFF1E293B),
            ),
          ),
          if (subJudul != null) ...[
            const SizedBox(height: 2),
            Text(
              subJudul,
              style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
            ),
          ],
          const SizedBox(height: 4),

          // Guru Pengampu
          Text(
            '👤 Guru: $guru',
            style: const TextStyle(
              fontSize: 11.5,
              color: Color(0xFF64748B),
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 12),

          // Info Container: ⏱ 60 Menit • ▦ 20 Soal Pilihan Ganda
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              children: [
                const Icon(Icons.access_time_rounded, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 4),
                Text(
                  '$durasi Menit',
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF475569),
                  ),
                ),
                const SizedBox(width: 10),
                const Text('•', style: TextStyle(color: Color(0xFFCBD5E1))),
                const SizedBox(width: 10),
                const Icon(Icons.format_list_bulleted_rounded, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 4),
                Text(
                  '$totalSoal Soal $tipeSoal',
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF475569),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // Action Button
          SizedBox(
            width: double.infinity,
            height: 44,
            child: canStart
                ? ElevatedButton(
                    onPressed: () {
                      Navigator.push(
                        context,
                        SmoothPageRoute(
                          page: PetunjukUjianPage(quizId: idQuiz, user: widget.user),
                          type: TransitionType.sharedAxis,
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1D5A86),
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          tombolText,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(width: 6),
                        const Icon(Icons.arrow_forward_rounded, size: 15, color: Colors.white),
                      ],
                    ),
                  )
                : OutlinedButton(
                    onPressed: null,
                    style: OutlinedButton.styleFrom(
                      backgroundColor: const Color(0xFFF1F5F9),
                      side: const BorderSide(color: Color(0xFFE2E8F0)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.lock_outline_rounded, size: 14, color: Color(0xFF94A3B8)),
                        const SizedBox(width: 6),
                        Text(
                          tombolText,
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF94A3B8),
                          ),
                        ),
                      ],
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3B. KARTU UJIAN SELESAI
  // ---------------------------------------------------------------------------
  Widget _buildSelesaiCard(Map<String, dynamic> exam) {
    final int idQuiz = exam['id_quiz'] ?? 14;
    final String judul = exam['judul_quiz'] ?? '';
    final String mapel = exam['mapel'] ?? '';
    final String tanggal = exam['tanggal'] ?? '';
    final String keterangan = exam['keterangan'] ?? 'Selesai';
    final int nilai = exam['nilai'] ?? 90;
    final String badgeNilai = exam['badge_nilai'] ?? '$nilai';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
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
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  mapel,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  '$tanggal • $keterangan',
                  style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),

          // Badge Nilai & Tombol Buka
          InkWell(
            onTap: () {
              Navigator.push(
                context,
                SmoothPageRoute(
                  page: HasilUjianPage(user: widget.user, quizId: idQuiz),
                  type: TransitionType.sharedAxis,
                ),
              );
            },
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFF59E0B),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.star_rounded, color: Colors.white, size: 16),
                  const SizedBox(width: 4),
                  Text(
                    badgeNilai,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. SECTION NILAI UJIAN TERAKHIR
  // ---------------------------------------------------------------------------
  Widget _buildNilaiTerakhirSection() {
    final Map<String, dynamic> item = _nilaiTerakhir ?? {
      'id_quiz': 14,
      'mapel': 'Bahasa Indonesia',
      'judul': 'Ulangan Harian Puisi A',
      'tanggal': '12 Mei • Sangat Memuaskan!',
      'nilai': 95,
      'badge': '95 – Hebat!',
    };

    final int idQuiz = item['id_quiz'] ?? 14;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Nilai Ujian Terakhir',
              style: TextStyle(
                fontSize: 14.5,
                fontWeight: FontWeight.w800,
                color: Color(0xFF1E293B),
              ),
            ),
            TextButton(
              onPressed: () {
                setState(() => _activeTab = 1);
              },
              child: const Text(
                'Semua >',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1D5A86),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),

        // Dark Blue Card Sesuai Screenshot
        InkWell(
          onTap: () {
            Navigator.push(
              context,
              SmoothPageRoute(
                page: HasilUjianPage(user: widget.user, quizId: idQuiz),
                type: TransitionType.sharedAxis,
              ),
            );
          },
          borderRadius: BorderRadius.circular(18),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFF1D5A86), // Deep blue
              borderRadius: BorderRadius.circular(18),
            ),
            child: Row(
              children: [
                // Icon Medal Box
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Center(
                    child: Text('🏅', style: TextStyle(fontSize: 22)),
                  ),
                ),
                const SizedBox(width: 14),

                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item['mapel'] ?? 'Bahasa Indonesia',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF93C5FD), // Light pastel blue
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        item['judul'] ?? 'Ulangan Harian Puisi A',
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        item['tanggal'] ?? '12 Mei • Sangat Memuaskan!',
                        style: const TextStyle(
                          fontSize: 11,
                          color: Colors.white70,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),

                // Badge ★ 95 - Hebat!
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF59E0B),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.star_rounded, color: Colors.white, size: 14),
                      const SizedBox(width: 4),
                      Text(
                        item['badge'] ?? '95 – Hebat!',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 5. KARTU TIPS MENGERJAKAN UJIAN
  // ---------------------------------------------------------------------------
  Widget _buildTipsCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF), // Soft light blue
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFDBEAFE)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Orange Lightbulb Icon Circle
          Container(
            padding: const EdgeInsets.all(8),
            decoration: const BoxDecoration(
              color: Color(0xFFF59E0B),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.lightbulb_rounded, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 14),

          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Tips Mengerjakan Ujian',
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1D5A86),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  _tips,
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF334155),
                    height: 1.4,
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
  // 6. BOTTOM NAVIGATION BAR
  // ---------------------------------------------------------------------------
  Widget _buildBottomNav() {
    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFF1D5A86),
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 10,
            offset: Offset(0, -2),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildNavItem(0, Icons.home_rounded, 'Beranda'),
          _buildNavItem(1, Icons.assignment_rounded, 'Ujian'),
          _buildNavItem(2, Icons.menu_book_rounded, 'Mapel'),
          _buildNavItem(3, Icons.calendar_today_rounded, 'Jadwal'),
          _buildNavItem(4, Icons.person_rounded, 'Profil'),
        ],
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label) {
    final bool isSelected = (_bottomNavIndex == index);
    final Color color = isSelected ? const Color(0xFFF59E0B) : Colors.white70;

    return InkWell(
      onTap: () {
        if (index == _bottomNavIndex) return;

        if (index == 0) {
          // Balik ke Beranda
          Navigator.pushReplacement(
            context,
            SmoothPageRoute(
              page: DashboardPage(user: widget.user),
              type: TransitionType.sharedAxis,
            ),
          );
        } else if (index == 2) {
          // Buka Mapel
          Navigator.pushReplacement(
            context,
            SmoothPageRoute(
              page: MapelPage(user: widget.user),
              type: TransitionType.sharedAxis,
            ),
          );
        } else if (index == 4) {
          // Buka Profil
          Navigator.pushReplacement(
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
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
