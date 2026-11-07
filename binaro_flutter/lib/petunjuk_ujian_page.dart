import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'pengerjaan_ujian_page.dart';
import 'page_transitions.dart';
import 'profile_page.dart';

class PetunjukUjianPage extends StatefulWidget {
  final Map<String, dynamic>? user;
  final int quizId;

  const PetunjukUjianPage({
    super.key,
    this.user,
    required this.quizId,
  });

  @override
  State<PetunjukUjianPage> createState() => _PetunjukUjianPageState();
}

class _PetunjukUjianPageState extends State<PetunjukUjianPage> {
  static const String _baseUrl = 'http://192.168.18.9:8000/api';

  bool _isLoading = true;
  bool _isReady = false; // Checkbox "Saya sudah siap ujian!"
  Map<String, dynamic> _data = {};

  @override
  void initState() {
    super.initState();
    _fetchPetunjuk();
  }

  Future<void> _fetchPetunjuk() async {
    setState(() => _isLoading = true);

    try {
      final response = await http
          .get(Uri.parse('$_baseUrl/siswa/ujian/${widget.quizId}/petunjuk'))
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

    if (mounted) {
      setState(() {
        _data = {};
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final String kategori = _data['kategori_badge'] ?? 'UJIAN TENGAH SEMESTER';
    final String judul = _data['judul_quiz'] ?? 'PTS Matematika Kelas 4B';
    final String guru = _data['guru'] ?? 'Ibu Sarah Wijaya, S.Pd.';
    final Map<String, dynamic> ringkasan = _data['ringkasan'] != null && _data['ringkasan'] is Map
        ? Map<String, dynamic>.from(_data['ringkasan'] as Map)
        : {'waktu_menit': 60, 'total_soal': 20, 'target_kkm': 75, 'koreksi': 'Otomatis'};

    final List<dynamic> petunjuk = _data['petunjuk_pengerjaan'] ?? [
      {'nomor': 1, 'warna': 'blue', 'teks': 'Berdoa sebelum mulai.'},
      {'nomor': 2, 'warna': 'orange', 'teks': 'Pilih salah satu jawaban A, B, C, atau D.'},
      {'nomor': 3, 'warna': 'green', 'teks': 'Periksa kembali lalu kumpulkan.'},
    ];

    final String tipsPesan = _data['tips_semangat']?['pesan'] ?? 'Tenang, teliti, dan jangan lupa berdoa ya!';
    final String footer = _data['footer'] ?? 'Ada kendala saat membuka soal? Beritahu guru pengawasmu';

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
          'Petunjuk Ujian',
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
          : _data.isEmpty
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
                            Icons.assignment_late_outlined,
                            size: 34,
                            color: Color(0xFF1D5A86),
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Ujian Tidak Ditemukan',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF1E293B),
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Informasi jadwal petunjuk ujian ini belum tersedia atau sudah ditutup.',
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
              : SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 1. KARTU HEADER UJIAN
                  _buildHeaderCard(kategori, judul, guru),
                  const SizedBox(height: 20),

                  // 2. SECTION RINGKASAN UJIAN (GRID 2x2)
                  const Text(
                    'RINGKASAN UJIAN',
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF64748B),
                      letterSpacing: 0.6,
                    ),
                  ),
                  const SizedBox(height: 12),
                  _buildSummaryGrid(ringkasan),
                  const SizedBox(height: 18),

                  // 3. KARTU PETUNJUK PENGERJAAN
                  _buildInstructionCard(petunjuk),
                  const SizedBox(height: 14),

                  // 4. KARTU TIPS SEMANGAT BELAJAR
                  _buildTipsCard(tipsPesan),
                  const SizedBox(height: 14),

                  // 5. CHECKBOX SAYA SUDAH SIAP UJIAN!
                  _buildReadyCheckbox(),
                  const SizedBox(height: 18),

                  // 6. TOMBOL MULAI UJIAN SEKARANG
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      onPressed: _isReady
                          ? () {
                              Navigator.push(
                                context,
                                SmoothPageRoute(
                                  page: PengerjaanUjianPage(
                                    quizId: widget.quizId,
                                    user: widget.user,
                                  ),
                                  type: TransitionType.sharedAxis,
                                ),
                              );
                            }
                          : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _isReady ? const Color(0xFF1D5A86) : const Color(0xFF94A3B8),
                        disabledBackgroundColor: const Color(0xFF94A3B8).withValues(alpha: 0.5),
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Mulai Ujian Sekarang',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: _isReady ? Colors.white : Colors.white70,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Icon(
                            Icons.arrow_forward_rounded,
                            size: 16,
                            color: _isReady ? Colors.white : Colors.white70,
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Footer Info
                  Center(
                    child: Text(
                      footer,
                      style: const TextStyle(
                        fontSize: 11.5,
                        color: Color(0xFF94A3B8),
                        fontWeight: FontWeight.w500,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. KARTU HEADER UJIAN
  // ---------------------------------------------------------------------------
  Widget _buildHeaderCard(String kategori, String judul, String guru) {
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
          // Blue Icon Container
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFF1D5A86),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.calculate_outlined, color: Colors.white, size: 28),
          ),
          const SizedBox(width: 14),

          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Badge Ujian Tengah Semester
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    kategori,
                    style: const TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1D5A86),
                      letterSpacing: 0.4,
                    ),
                  ),
                ),
                const SizedBox(height: 6),

                // Judul PTS Matematika
                Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 15.5,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 3),

                // Guru Pengampu
                Text(
                  'Guru: $guru',
                  style: const TextStyle(
                    fontSize: 12,
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
  // 2. GRID RINGKASAN UJIAN (2x2)
  // ---------------------------------------------------------------------------
  Widget _buildSummaryGrid(Map<String, dynamic> r) {
    final int waktu = (r['waktu_menit'] ?? 60) as int;
    final int soal = (r['total_soal'] ?? 20) as int;
    final int kkm = (r['target_kkm'] ?? 75) as int;
    final String koreksi = r['koreksi'] ?? 'Otomatis';

    return GridView.count(
      crossAxisCount: 2,
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      childAspectRatio: 1.85,
      children: [
        // 1. Waktu
        _buildSummaryItem(
          icon: Icons.access_time_filled_rounded,
          iconBg: const Color(0xFFEFF6FF),
          iconColor: const Color(0xFF1D5A86),
          label: 'Waktu',
          value: '$waktu Menit',
        ),

        // 2. Soal
        _buildSummaryItem(
          icon: Icons.format_list_bulleted_rounded,
          iconBg: const Color(0xFFFFFBEB),
          iconColor: const Color(0xFFF59E0B),
          label: 'Soal',
          value: '$soal Butir',
        ),

        // 3. Target KKM
        _buildSummaryItem(
          icon: Icons.emoji_events_rounded,
          iconBg: const Color(0xFFDCFCE7),
          iconColor: const Color(0xFF16A34A),
          label: 'Target KKM',
          value: '$kkm',
        ),

        // 4. Koreksi
        _buildSummaryItem(
          icon: Icons.auto_awesome_rounded,
          iconBg: const Color(0xFFF3E8FF),
          iconColor: const Color(0xFF9333EA),
          label: 'Koreksi',
          value: koreksi,
        ),
      ],
    );
  }

  Widget _buildSummaryItem({
    required IconData icon,
    required Color iconBg,
    required Color iconColor,
    required String label,
    required String value,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
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
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: iconBg,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 10.5,
                    color: Color(0xFF64748B),
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
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
  // 3. KARTU PETUNJUK PENGERJAAN
  // ---------------------------------------------------------------------------
  Widget _buildInstructionCard(List<dynamic> items) {
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
          // Header Petunjuk Pengerjaan
          const Row(
            children: [
              Icon(Icons.menu_book_rounded, color: Color(0xFF1D5A86), size: 20),
              SizedBox(width: 8),
              Text(
                'Petunjuk Pengerjaan',
                style: TextStyle(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF1E293B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Steps list
          ...items.map((item) {
            final int no = item['nomor'] ?? 1;
            final String teks = item['teks'] ?? '';
            final String warna = item['warna'] ?? 'blue';

            Color circleColor = const Color(0xFF1D5A86);
            if (warna == 'orange' || no == 2) {
              circleColor = const Color(0xFFF59E0B);
            } else if (warna == 'green' || no == 3) {
              circleColor = const Color(0xFF16A34A);
            }

            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: circleColor,
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      '$no',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      teks,
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF334155),
                        fontWeight: FontWeight.w600,
                        height: 1.4,
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
  // 4. KARTU TIPS SEMANGAT BELAJAR
  // ---------------------------------------------------------------------------
  Widget _buildTipsCard(String pesan) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB), // Soft yellow
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.sentiment_very_satisfied_rounded, color: Colors.white, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Tips Semangat Belajar',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF92400E),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  pesan,
                  style: const TextStyle(
                    fontSize: 12,
                    color: Color(0xFFB45309),
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
  // 5. READY CHECKBOX CARD (◯ Saya sudah siap ujian!)
  // ---------------------------------------------------------------------------
  Widget _buildReadyCheckbox() {
    return InkWell(
      onTap: () {
        setState(() {
          _isReady = !_isReady;
        });
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: _isReady ? const Color(0xFF1D5A86) : const Color(0xFFE2E8F0),
            width: _isReady ? 1.5 : 1,
          ),
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
            Container(
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                color: _isReady ? const Color(0xFF16A34A) : Colors.transparent,
                shape: BoxShape.circle,
                border: Border.all(
                  color: _isReady ? const Color(0xFF16A34A) : const Color(0xFFCBD5E1),
                  width: 2,
                ),
              ),
              child: _isReady
                  ? const Icon(Icons.check, size: 14, color: Colors.white)
                  : null,
            ),
            const SizedBox(width: 12),
            const Text(
              'Saya sudah siap ujian!',
              style: TextStyle(
                fontSize: 13.5,
                fontWeight: FontWeight.w700,
                color: Color(0xFF1E293B),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

