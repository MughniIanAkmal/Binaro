import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'hasil_ujian_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
import '../widgets/dynamic_bottom_nav.dart';
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
  static const String _baseUrl = AppConstants.baseUrl;

  bool _isLoading = true;
  int _activeTab = 0; // 0 = Tersedia, 1 = Selesai

  Map<String, dynamic> _banner = {
    'judul': 'Penilaian Semester Siswa',
    'subjudul': 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
  };
  int _tersediaCount = 0;
  int _selesaiCount = 0;
  List<Map<String, dynamic>> _ujianTersediaList = [];
  List<Map<String, dynamic>> _ujianSelesaiList = [];
  Map<String, dynamic>? _nilaiTerakhir;
  String _tips = 'Kerjakan soal yang mudah dulu ya, agar waktumu cukup dan nilaimu maksimal!';

  @override
  void initState() {
    super.initState();
    _fetchUjianData();
  }

  Future<void> _fetchUjianData() async {
    setState(() => _isLoading = true);
    final int siswaId = widget.user?['id_siswa'] ?? AppConstants.defaultSiswaId;

    try {
      final response = await http
          .get(Uri.parse('$_baseUrl/siswa/ujian?id_siswa=$siswaId'))
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          final data = decoded['data'];
          if (mounted) {
            setState(() {
              _banner = Map<String, dynamic>.from(data['banner'] ?? _banner);
              _tersediaCount = data['tersedia_count'] ?? 0;
              _selesaiCount = data['selesai_count'] ?? 0;
              _ujianTersediaList = (data['ujian_tersedia'] as List? ?? [])
                  .map((e) => Map<String, dynamic>.from(e as Map))
                  .toList();
              _ujianSelesaiList = (data['ujian_selesai'] as List? ?? [])
                  .map((e) => Map<String, dynamic>.from(e as Map))
                  .toList();
              if (data['nilai_terakhir'] != null) {
                _nilaiTerakhir = Map<String, dynamic>.from(data['nilai_terakhir'] as Map);
              } else {
                _nilaiTerakhir = null;
              }
              _tips = data['tips'] ?? _tips;
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
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
                color: AppColors.accent,
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
                    color: AppColors.accent,
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
          : RefreshIndicator(
              onRefresh: _fetchUjianData,
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
                    // 1. BANNER PENILAIAN SEMESTER SISWA
                    _buildTopBanner(),
                    const SizedBox(height: 16),

                    // 2. TOGGLE FILTER [Tersedia (2)]  [Selesai (3)]
                    _buildTabToggle(),
                    const SizedBox(height: 16),

                    // 3. DAFTAR KARTU UJIAN (Dengan Animasi Transisi Halus)
                    AnimatedSwitcher(
                      duration: const Duration(milliseconds: 280),
                      switchInCurve: Curves.easeOutCubic,
                      switchOutCurve: Curves.easeInCubic,
                      child: KeyedSubtree(
                        key: ValueKey<int>(_activeTab),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (_activeTab == 0) ...[
                              if (_ujianTersediaList.isEmpty)
                                _buildEmptyState(
                                  icon: Icons.assignment_outlined,
                                  title: 'Belum Ada Ujian Tersedia',
                                  subtitle: 'Guru belum mengunggah jadwal ujian online untuk kelasmu saat ini.',
                                )
                              else
                                ..._ujianTersediaList.map((exam) => _buildTersediaCard(exam)),
                            ] else ...[
                              if (_ujianSelesaiList.isEmpty)
                                _buildEmptyState(
                                  icon: Icons.history_toggle_off_rounded,
                                  title: 'Belum Ada Ujian Selesai',
                                  subtitle: 'Kamu belum memiliki riwayat ujian online yang telah selesai dikerjakan.',
                                )
                              else
                                ..._ujianSelesaiList.map((exam) => _buildSelesaiCard(exam)),
                            ],
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // 4. SECTION NILAI UJIAN TERAKHIR
                    if (_nilaiTerakhir != null) ...[
                      _buildNilaiTerakhirSection(),
                      const SizedBox(height: 16),
                    ],

                    // 5. KARTU TIPS MENGERJAKAN UJIAN
                    _buildTipsCard(),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
      bottomNavigationBar: DynamicBottomNav(
        currentIndex: 1,
        user: widget.user,
      ),
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
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _banner['judul'] ?? 'Penilaian Semester Siswa',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  _banner['subjudul'] ?? 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: AppColors.textSecondary,
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
              color: AppColors.primaryLight,
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.psychology_outlined, color: AppColors.primary, size: 28),
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
        color: AppColors.primary, // Deep blue container
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
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 240),
                curve: Curves.easeInOut,
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
                      color: _activeTab == 0 ? AppColors.primary : Colors.white70,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Tersedia ($_tersediaCount)',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: _activeTab == 0 ? AppColors.primary : Colors.white,
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
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 240),
                curve: Curves.easeInOut,
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
                      color: _activeTab == 1 ? AppColors.primary : Colors.white70,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Selesai ($_selesaiCount)',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: _activeTab == 1 ? AppColors.primary : Colors.white,
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
          // Row Badges (Wrap agar tidak pernah overflow di layar HP manapun)
          Wrap(
            spacing: 6,
            runSpacing: 6,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              // Badge Mapel
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                decoration: BoxDecoration(
                  color: isMatematika ? AppColors.primaryLight : AppColors.successLight,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isMatematika ? Icons.calculate_outlined : Icons.eco_outlined,
                      size: 13,
                      color: isMatematika ? AppColors.primary : AppColors.success,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      mapel,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w800,
                        color: isMatematika ? AppColors.primary : AppColors.success,
                      ),
                    ),
                  ],
                ),
              ),

              // Badge Status (Bisa Dikerjakan / Belum Dimulai)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                decoration: BoxDecoration(
                  color: canStart ? AppColors.successLight : AppColors.surfaceAlt,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (canStart) ...[
                      const Icon(Icons.circle, size: 7, color: AppColors.success),
                      const SizedBox(width: 4),
                    ],
                    Text(
                      badgeStatus,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        color: canStart ? AppColors.success : AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),

              // Badge Jadwal (Hari Ini / Besok)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                decoration: BoxDecoration(
                  color: canStart ? AppColors.warningLight : AppColors.background,
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: canStart ? AppColors.accentBright : AppColors.border,
                    width: 0.8,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.calendar_today_outlined,
                      size: 11,
                      color: canStart ? AppColors.accentDeep : AppColors.textSecondary,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      badgeJadwal,
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w600,
                        color: canStart ? AppColors.accentDeep : AppColors.textSecondary,
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
              color: AppColors.textPrimary,
            ),
          ),
          if (subJudul != null) ...[
            const SizedBox(height: 2),
            Text(
              subJudul,
              style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
            ),
          ],
          const SizedBox(height: 4),

          // Guru Pengampu
          Text(
            '👤 Guru: $guru',
            style: const TextStyle(
              fontSize: 11.5,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 12),

          // Info Container: ⏱ 60 Menit • ▦ 20 Soal Pilihan Ganda
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Wrap(
              spacing: 8,
              runSpacing: 4,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.access_time_rounded, size: 14, color: AppColors.textSecondary),
                    const SizedBox(width: 4),
                    Text(
                      '$durasi Menit',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
                const Text('•', style: TextStyle(color: AppColors.border)),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.format_list_bulleted_rounded, size: 14, color: AppColors.textSecondary),
                    const SizedBox(width: 4),
                    Text(
                      '$totalSoal Soal $tipeSoal',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
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
                      backgroundColor: AppColors.primary,
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
                      backgroundColor: AppColors.surfaceAlt,
                      side: const BorderSide(color: AppColors.border),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.lock_outline_rounded, size: 14, color: AppColors.textMuted),
                        const SizedBox(width: 6),
                        Text(
                          tombolText,
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textMuted,
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
    final bool isPublished = exam['is_published'] == true;
    final int? nilai = exam['nilai'];
    final String badgeNilai = isPublished ? '★ ${nilai ?? 0}' : 'Menunggu Nilai';
    final Color badgeBgColor = isPublished ? AppColors.accent : AppColors.primaryLight;
    final Color badgeTextColor = isPublished ? Colors.white : AppColors.primary;
    final IconData badgeIcon = isPublished ? Icons.star_rounded : Icons.hourglass_top_rounded;
    final Border? badgeBorder = isPublished ? null : Border.all(color: AppColors.primaryLight);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
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
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  mapel,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.primary,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  '$tanggal • $keterangan',
                  style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
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
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
              decoration: BoxDecoration(
                color: badgeBgColor,
                borderRadius: BorderRadius.circular(12),
                border: badgeBorder,
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(badgeIcon, color: badgeTextColor, size: 14),
                  const SizedBox(width: 4),
                  Text(
                    badgeNilai,
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w800,
                      color: badgeTextColor,
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
  // 3C. EMPTY STATE WIDGET
  // ---------------------------------------------------------------------------
  Widget _buildEmptyState({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
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
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: const BoxDecoration(
              color: AppColors.primaryLight,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 32, color: AppColors.primary),
          ),
          const SizedBox(height: 16),
          Text(
            title,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w800,
              color: AppColors.textPrimary,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            subtitle,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 12.5,
              color: AppColors.textSecondary,
              height: 1.4,
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
    if (_nilaiTerakhir == null) {
      return const SizedBox.shrink();
    }

    final Map<String, dynamic> item = _nilaiTerakhir!;
    final int idQuiz = item['id_quiz'] ?? 0;

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
                color: AppColors.textPrimary,
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
                  color: AppColors.primary,
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
              color: AppColors.primary, // Deep blue
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
                          color: AppColors.primaryLight, // Light pastel blue
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
                    color: AppColors.accent,
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
        color: AppColors.primaryLight, // Soft light blue
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.primaryLight),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Orange Lightbulb Icon Circle
          Container(
            padding: const EdgeInsets.all(8),
            decoration: const BoxDecoration(
              color: AppColors.accent,
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
                    color: AppColors.primary,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  _tips,
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: AppColors.textPrimary,
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
}
