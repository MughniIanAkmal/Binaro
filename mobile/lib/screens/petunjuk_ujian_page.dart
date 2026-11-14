import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'pengerjaan_ujian_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
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
  static const String _baseUrl = AppConstants.baseUrl;

  bool _isLoading = true;
  bool _isReady = false; // Checkbox "Saya sudah siap ujian!"
  Map<String, dynamic> _petunjukData = {};

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
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          if (mounted) {
            setState(() {
              _petunjukData = Map<String, dynamic>.from(decoded['data'] as Map);
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _petunjukData = {};
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final String kategori = _petunjukData['kategori_badge'] ?? 'UJIAN ONLINE';
    final String judul = _petunjukData['judul_quiz'] ?? 'Ujian Online Siswa';
    final String guru = _petunjukData['guru'] ?? '-';
    final Map<String, dynamic> ringkasan = _petunjukData['ringkasan'] != null && _petunjukData['ringkasan'] is Map
        ? Map<String, dynamic>.from(_petunjukData['ringkasan'] as Map)
        : {'waktu_menit': 60, 'total_soal': 0, 'target_kkm': 75, 'koreksi': 'Otomatis'};

    final List<dynamic> petunjuk = _petunjukData['petunjuk_pengerjaan'] ?? [
      {'nomor': 1, 'warna': 'blue', 'teks': 'Berdoa sebelum mulai.'},
      {'nomor': 2, 'warna': 'orange', 'teks': 'Pilih salah satu jawaban A, B, C, atau D.'},
      {'nomor': 3, 'warna': 'green', 'teks': 'Periksa kembali lalu kumpulkan.'},
    ];

    final String tipsPesan = _petunjukData['tips_semangat']?['pesan'] ?? 'Tenang, teliti, dan jangan lupa berdoa ya!';
    final String footer = _petunjukData['footer'] ?? 'Ada kendala saat membuka soal? Beritahu guru pengawasmu';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
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
                  color: AppColors.border,
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2.2),
                ),
              ),
            ),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _petunjukData.isEmpty
              ? Center(
                  child: Container(
                    margin: const EdgeInsets.all(24),
                    padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 24),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.border),
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
                            color: AppColors.primaryLight,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.assignment_late_outlined,
                            size: 34,
                            color: AppColors.primary,
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Ujian Tidak Ditemukan',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Informasi jadwal petunjuk ujian ini belum tersedia atau sudah ditutup.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 13,
                            color: AppColors.textSecondary,
                            height: 1.4,
                          ),
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: () => Navigator.pop(context),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
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
                      color: AppColors.textSecondary,
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
                        backgroundColor: _isReady ? AppColors.primary : AppColors.textMuted,
                        disabledBackgroundColor: AppColors.textMuted.withValues(alpha: 0.5),
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                        color: AppColors.textMuted,
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
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
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
              color: AppColors.primary,
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
                    color: AppColors.primaryLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    kategori,
                    style: const TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: AppColors.primary,
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
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 3),

                // Guru Pengampu
                Text(
                  'Guru: $guru',
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
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
          iconBg: AppColors.primaryLight,
          iconColor: AppColors.primary,
          label: 'Waktu',
          value: '$waktu Menit',
        ),

        // 2. Soal
        _buildSummaryItem(
          icon: Icons.format_list_bulleted_rounded,
          iconBg: AppColors.warningLight,
          iconColor: AppColors.accent,
          label: 'Soal',
          value: '$soal Butir',
        ),

        // 3. Target KKM
        _buildSummaryItem(
          icon: Icons.emoji_events_rounded,
          iconBg: AppColors.successLight,
          iconColor: AppColors.success,
          label: 'Target KKM',
          value: '$kkm',
        ),

        // 4. Koreksi
        _buildSummaryItem(
          icon: Icons.auto_awesome_rounded,
          iconBg: AppColors.infoLight,
          iconColor: AppColors.info,
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
        border: Border.all(color: AppColors.border),
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
              borderRadius: BorderRadius.circular(12),
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
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
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
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
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
              Icon(Icons.menu_book_rounded, color: AppColors.primary, size: 20),
              SizedBox(width: 8),
              Text(
                'Petunjuk Pengerjaan',
                style: TextStyle(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w800,
                  color: AppColors.textPrimary,
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

            Color circleColor = AppColors.primary;
            if (warna == 'orange' || no == 2) {
              circleColor = AppColors.accent;
            } else if (warna == 'green' || no == 3) {
              circleColor = AppColors.success;
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
                        color: AppColors.textPrimary,
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
        color: AppColors.warningLight, // Soft yellow
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.accentBright),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.accent,
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
                    color: AppColors.accentDeep,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  pesan,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.accentDeep,
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
            color: _isReady ? AppColors.primary : AppColors.border,
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
                color: _isReady ? AppColors.success : Colors.transparent,
                shape: BoxShape.circle,
                border: Border.all(
                  color: _isReady ? AppColors.success : AppColors.border,
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
                color: AppColors.textPrimary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

