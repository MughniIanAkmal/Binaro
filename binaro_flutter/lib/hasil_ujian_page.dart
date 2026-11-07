import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'profile_page.dart';
import 'page_transitions.dart';

class HasilUjianPage extends StatefulWidget {
  final Map<String, dynamic>? user;
  final int quizId;

  const HasilUjianPage({
    super.key,
    this.user,
    required this.quizId,
  });

  @override
  State<HasilUjianPage> createState() => _HasilUjianPageState();
}

class _HasilUjianPageState extends State<HasilUjianPage> {
  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  bool _isLoading = true;
  Map<String, dynamic> _data = {};

  @override
  void initState() {
    super.initState();
    _fetchResult();
  }

  Future<void> _fetchResult() async {
    setState(() => _isLoading = true);
    final int siswaId = widget.user?['id_siswa'] ?? 1;

    try {
      final response = await http
          .get(Uri.parse('$_baseUrl/siswa/ujian/${widget.quizId}/result?id_siswa=$siswaId'))
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          if (mounted) {
            setState(() {
              _data = Map<String, dynamic>.from(decoded['data'] as Map);
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}

    // Fallback data jika server tidak merespon
    if (mounted) {
      setState(() {
        _data = {
          'id_quiz': widget.quizId,
          'nama_siswa': widget.user?['nama_siswa'] ?? 'Budi Santoso',
          'pesan_banner': 'Ujian telah selesai dikumpulkan tepat waktu!',
          'target_kkm': 75,
          'is_lulus_kkm': true,
          'label_kkm': 'LULUS KKM (Target: 75)',
          'nilai_akhir': 90,
          'status_teks': 'Sangat Baik & Membanggakan',
          'jumlah_benar': 18,
          'jumlah_salah': 2,
          'durasi_menit': 38,
          'mapel': {
            'nama': 'PTS Matematika Kelas 4B',
            'guru': 'Ibu Sarah Wijaya, S.Pd.',
            'catatan_guru': 'Bagus sekali pemahaman materi pecahannya, pertahankan ya Budi!',
          },
        };
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final String namaSiswa = _data['nama_siswa'] ?? widget.user?['nama_siswa'] ?? 'Budi Santoso';
    final int nilai = (_data['nilai_akhir'] ?? 90) as int;
    final int benar = (_data['jumlah_benar'] ?? 18) as int;
    final int salah = (_data['jumlah_salah'] ?? 2) as int;
    final int menit = (_data['durasi_menit'] ?? 38) as int;
    final String statusTeks = _data['status_teks'] ?? 'Sangat Baik & Membanggakan';
    final String labelKkm = _data['label_kkm'] ?? 'LULUS KKM (Target: 75)';
    final Map<String, dynamic> mapel = _data['mapel'] != null && _data['mapel'] is Map
        ? Map<String, dynamic>.from(_data['mapel'] as Map)
        : {
            'nama': 'PTS Matematika Kelas 4B',
            'guru': 'Ibu Sarah Wijaya, S.Pd.',
            'catatan_guru': 'Bagus sekali pemahaman materi pecahannya, pertahankan ya Budi!',
          };

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1D5A86),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 24),
          onPressed: () => Navigator.pop(context),
        ),
        titleSpacing: 0,
        title: const Text(
          'Hasil Ujian Siswa',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
        actions: [
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
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
              child: Column(
                children: [
                  // 1. BANNER HEBAT
                  _buildSuccessBanner(namaSiswa),
                  const SizedBox(height: 16),

                  // 2. KARTU DONUT NILAI (90 DARI 100)
                  _buildScoreDonutCard(nilai, labelKkm, statusTeks),
                  const SizedBox(height: 16),

                  // 3. 3 KARTU STATISTIK (18 BENAR, 2 SALAH, 38' MENIT)
                  _buildStatsRow(benar, salah, menit),
                  const SizedBox(height: 16),

                  // 4. KARTU MATA PELAJARAN & CATATAN GURU
                  _buildTeacherFeedbackCard(mapel),
                  const SizedBox(height: 24),

                  // 5. TOMBOL KEMBALI KE DAFTAR UJIAN
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      onPressed: () {
                        // Kembali ke daftar ujian
                        Navigator.pop(context);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF1D5A86),
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.arrow_back_rounded, color: Colors.white, size: 18),
                          SizedBox(width: 8),
                          Text(
                            'Kembali ke Daftar Ujian',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. SUCCESS BANNER
  // ---------------------------------------------------------------------------
  Widget _buildSuccessBanner(String namaSiswa) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB), // Cream yellow background
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFFEF3C7)),
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
          // Icon Party Popper / Confetti
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Text('🎉', style: TextStyle(fontSize: 22)),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hebat, $namaSiswa!',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Ujian telah selesai dikumpulkan tepat waktu!',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF64748B),
                    fontWeight: FontWeight.w500,
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
  // 2. DONUT SCORE CARD
  // ---------------------------------------------------------------------------
  Widget _buildScoreDonutCard(int nilai, String labelKkm, String statusTeks) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
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
        children: [
          // Badge Lulus KKM
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFDCFCE7), // Soft green
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.check_circle_rounded, color: Color(0xFF16A34A), size: 16),
                const SizedBox(width: 6),
                Text(
                  labelKkm,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF15803D),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Donut Ring (Thick Blue Circular Ring)
          SizedBox(
            width: 140,
            height: 140,
            child: Stack(
              alignment: Alignment.center,
              children: [
                SizedBox(
                  width: 140,
                  height: 140,
                  child: CircularProgressIndicator(
                    value: nilai / 100.0,
                    strokeWidth: 12,
                    backgroundColor: const Color(0xFFF1F5F9),
                    valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF1D5A86)),
                    strokeCap: StrokeCap.round,
                  ),
                ),
                Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$nilai',
                      style: const TextStyle(
                        fontSize: 38,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF1D5A86),
                        height: 1.1,
                      ),
                    ),
                    const Text(
                      'dari 100',
                      style: TextStyle(
                        fontSize: 12,
                        color: Color(0xFF94A3B8),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // Status Teks
          Text(
            statusTeks,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w800,
              color: Color(0xFF1E293B),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. STATS ROW (18 Benar, 2 Salah, 38' Menit)
  // ---------------------------------------------------------------------------
  Widget _buildStatsRow(int benar, int salah, int menit) {
    return Row(
      children: [
        // Card 1: Benar
        Expanded(
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 16),
            decoration: BoxDecoration(
              color: const Color(0xFF1D5A86), // Deep blue
              borderRadius: BorderRadius.circular(18),
            ),
            child: Column(
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.check_rounded, color: Color(0xFF1D5A86), size: 20),
                ),
                const SizedBox(height: 10),
                Text(
                  '$benar',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Benar',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.white70,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(width: 12),

        // Card 2: Salah
        Expanded(
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 16),
            decoration: BoxDecoration(
              color: const Color(0xFFF59E0B), // Vibrant orange
              borderRadius: BorderRadius.circular(18),
            ),
            child: Column(
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.close_rounded, color: Color(0xFFF59E0B), size: 20),
                ),
                const SizedBox(height: 10),
                Text(
                  '$salah',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Salah',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.white70,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(width: 12),

        // Card 3: Menit
        Expanded(
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 16),
            decoration: BoxDecoration(
              color: const Color(0xFF1D5A86), // Deep blue
              borderRadius: BorderRadius.circular(18),
            ),
            child: Column(
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.access_time_rounded, color: Color(0xFF1D5A86), size: 20),
                ),
                const SizedBox(height: 10),
                Text(
                  "$menit'",
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Menit',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.white70,
                    fontWeight: FontWeight.w500,
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
  // 4. TEACHER FEEDBACK CARD
  // ---------------------------------------------------------------------------
  Widget _buildTeacherFeedbackCard(Map<String, dynamic> mapel) {
    final String namaMapel = mapel['nama'] ?? 'PTS Matematika Kelas 4B';
    final String guru = mapel['guru'] ?? 'Ibu Sarah Wijaya, S.Pd.';
    final String catatan = mapel['catatan_guru'] ??
        'Bagus sekali pemahaman materi pecahannya, pertahankan ya Budi!';

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
          // Header Mapel
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.assignment_outlined, color: Color(0xFF1D5A86), size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'MATA PELAJARAN',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF94A3B8),
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      namaMapel,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1E293B),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      guru,
                      style: const TextStyle(
                        fontSize: 11.5,
                        color: Color(0xFF64748B),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Inner Box Catatan Guru
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Avatar Guru
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: const Color(0xFFDBEAFE),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                  child: const Icon(Icons.person, color: Color(0xFF1D5A86), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.chat_bubble_outline_rounded, size: 14, color: Color(0xFF1D5A86)),
                          SizedBox(width: 5),
                          Text(
                            'Catatan Guru',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1D5A86),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '"$catatan"',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Color(0xFF334155),
                          fontStyle: FontStyle.italic,
                          height: 1.4,
                        ),
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
}
