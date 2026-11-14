import 'dart:async';
import 'dart:convert';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'hasil_ujian_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
import 'profile_page.dart';

class PengerjaanUjianPage extends StatefulWidget {
  final Map<String, dynamic>? user;
  final int quizId;

  const PengerjaanUjianPage({
    super.key,
    this.user,
    required this.quizId,
  });

  @override
  State<PengerjaanUjianPage> createState() => _PengerjaanUjianPageState();
}

class _PengerjaanUjianPageState extends State<PengerjaanUjianPage> {
  static const String _baseUrl = AppConstants.baseUrl;

  bool _isLoading = true;
  String _examTitle = 'Ujian Online';
  int _totalSoal = 0;
  int _currentIndex = 0;
  List<Map<String, dynamic>> _soalList = [];

  // Pilihan jawaban siswa: map dari id_soal atau index -> opsi terpilih ('A', 'B', 'C', 'D')
  final Map<int, String> _selectedAnswers = {};

  // Set nomor soal yang ditandai ragu-ragu
  final Set<int> _raguRaguSet = {};

  // Countdown timer (detik)
  Timer? _timer;
  int _remainingSeconds = 3600;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _fetchExamQuestions();
    _startCountdown();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _startCountdown() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) return;
      if (_remainingSeconds > 0) {
        setState(() {
          _remainingSeconds--;
        });
      } else {
        t.cancel();
        _submitExam(autoSubmit: true);
      }
    });
  }

  String get _formattedTime {
    final int minutes = _remainingSeconds ~/ 60;
    final int seconds = _remainingSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  Future<void> _fetchExamQuestions() async {
    setState(() => _isLoading = true);

    try {
      final response = await http
          .get(Uri.parse('$_baseUrl/siswa/ujian/${widget.quizId}/play'))
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          final data = decoded['data'];
          final List<dynamic> list = data['soal'] ?? [];

          if (mounted) {
            setState(() {
              _examTitle = data['judul_quiz'] ?? _examTitle;
              _totalSoal = data['total_soal'] ?? list.length;
              _remainingSeconds = data['sisa_detik'] ?? _remainingSeconds;
              _soalList = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
              _currentIndex = 0;
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _soalList = [];
        _totalSoal = 0;
        _isLoading = false;
      });
    }
  }

  Future<void> _submitExam({bool autoSubmit = false}) async {
    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);

    try {
      final Map<String, String> answersPayload = {};
      _selectedAnswers.forEach((index, opt) {
        if (index < _soalList.length) {
          final idSoal = _soalList[index]['id_soal']?.toString() ?? '$index';
          answersPayload[idSoal] = opt;
        }
      });

      await http.post(
        Uri.parse('$_baseUrl/siswa/ujian/${widget.quizId}/submit'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'id_siswa': widget.user?['id_siswa'] ?? 1,
          'jawaban': answersPayload,
          'waktu_menit': 38,
        }),
      ).timeout(const Duration(seconds: 5));
    } catch (_) {}

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    // Navigasi ke halaman hasil ujian
    Navigator.pushReplacement(
      context,
      SmoothPageRoute(
        page: HasilUjianPage(user: widget.user, quizId: widget.quizId),
        type: TransitionType.sharedAxis,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final Map<String, dynamic>? currentQuestion =
        (_soalList.isNotEmpty && _currentIndex < _soalList.length)
            ? _soalList[_currentIndex]
            : null;

    final double progressValue = _totalSoal > 0 ? (_currentIndex + 1) / _totalSoal : 0.25;

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
        title: Text(
          _examTitle,
          style: const TextStyle(
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
          : currentQuestion == null
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
                          'Soal Ujian Belum Tersedia',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Guru belum menginput butir soal untuk ujian ini. Silakan hubungi guru pengawasmu.',
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
                      // 1. Progress & Timer Row
                      _buildProgressTimerBar(progressValue),
                      const SizedBox(height: 18),

                      // 2. Kartu Soal Lengkap dengan Diagram & Foto
                      _buildQuestionCard(currentQuestion),
                      const SizedBox(height: 18),

                      // 3. Pilihan Jawaban (A, B, C, D)
                      _buildOptionsList(currentQuestion),
                      const SizedBox(height: 24),

                      // 4. Sticky Bottom Action Buttons: [Sebelumnya] [Ragu-ragu] [Selanjutnya]
                      _buildActionButtons(),
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. PROGRESS & TIMER ROW (Soal 5 dari 20  |  48:14)
  // ---------------------------------------------------------------------------
  Widget _buildProgressTimerBar(double progressValue) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Teks "Soal 5 dari 20"
            RichText(
              text: TextSpan(
                style: const TextStyle(fontSize: 14, color: AppColors.textPrimary),
                children: [
                  const TextSpan(text: 'Soal '),
                  TextSpan(
                    text: '${_currentIndex + 1}',
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      color: AppColors.primary,
                      fontSize: 15,
                    ),
                  ),
                  TextSpan(text: ' dari $_totalSoal'),
                ],
              ),
            ),

            // Timer Pill Oranye "48:14"
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.accent, // Vibrant amber orange
                borderRadius: BorderRadius.circular(16),
              ),
              child: Text(
                _formattedTime,
                style: const TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  letterSpacing: 0.5,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),

        // Progress Bar Halus
        ClipRRect(
          borderRadius: BorderRadius.circular(12),
          child: LinearProgressIndicator(
            value: progressValue,
            minHeight: 6,
            backgroundColor: AppColors.border,
            valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 2. KARTU SOAL & DIAGRAM PECAHAN SEMANGKA 8 POTONG
  // ---------------------------------------------------------------------------
  Widget _buildQuestionCard(Map<String, dynamic> q) {
    final String tag = q['tag'] ?? 'Pecahan Sederhana';
    final int poin = (q['poin'] ?? 5) as int;
    final String pertanyaan = q['pertanyaan'] ?? '';
    final bool hasDiagram = q['diagram'] != null || _currentIndex == 4;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
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
          // Tag & Poin Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  tag,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.primary,
                  ),
                ),
              ),
              Text(
                'Poin: $poin',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textMuted,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Teks Pertanyaan
          Text(
            pertanyaan,
            style: const TextStyle(
              fontSize: 14.5,
              fontWeight: FontWeight.w800,
              color: AppColors.textPrimary,
              height: 1.45,
            ),
          ),
          const SizedBox(height: 16),

          // Diagram Pecahan 8 Potong
          if (hasDiagram) ...[
            _buildPieDiagramCard(),
            const SizedBox(height: 14),
            _buildWatermelonPhotoBox(),
          ],
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // DIAGRAM PECAHAN 8 POTONG (CUSTOM CANVAS LENGKAP)
  // ---------------------------------------------------------------------------
  Widget _buildPieDiagramCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
      decoration: BoxDecoration(
        color: AppColors.background,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          const Text(
            'DIAGRAM PECAHAN 8 POTONG',
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w800,
              color: AppColors.textSecondary,
              letterSpacing: 0.8,
            ),
          ),
          const SizedBox(height: 14),

          // Canvas Lingkaran 8 Slice (3 Diarsir Biru Gelap, 5 Abu-abu)
          SizedBox(
            width: 140,
            height: 140,
            child: CustomPaint(
              painter: PieDiagram8Painter(),
            ),
          ),
          const SizedBox(height: 16),

          // Legend
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _buildLegendDot(AppColors.primary, 'Dimakan Budi (3)'),
              const SizedBox(width: 16),
              _buildLegendDot(AppColors.border, 'Sisa Semangka (5)', textColor: AppColors.textSecondary),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildLegendDot(Color color, String label, {Color textColor = AppColors.textPrimary}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 10,
          height: 10,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
            border: Border.all(color: AppColors.border, width: 0.5),
          ),
        ),
        const SizedBox(width: 6),
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: textColor,
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // FOTO SEMANGKA DIPOTONG 8
  // ---------------------------------------------------------------------------
  Widget _buildWatermelonPhotoBox() {
    return Container(
      width: double.infinity,
      height: 130,
      decoration: BoxDecoration(
        color: AppColors.warningLight,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.warningLight),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          // Ilustrasi Semangka di atas talenan
          const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('🍉', style: TextStyle(fontSize: 48)),
              SizedBox(width: 12),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '1 Buah Semangka Segar',
                    style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: AppColors.accentDeep),
                  ),
                  SizedBox(height: 2),
                  Text(
                    'Dipotong menjadi 8 bagian sama rata',
                    style: TextStyle(fontSize: 11, color: AppColors.accentDeep),
                  ),
                ],
              ),
            ],
          ),

          // Badge Pojok Bawah: 🍉 Semangka dipotong 8
          Positioned(
            left: 12,
            bottom: 10,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.65),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('🍉', style: TextStyle(fontSize: 11)),
                  SizedBox(width: 4),
                  Text(
                    'Semangka dipotong 8',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
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
  // 3. PILIHAN JAWABAN (A, B, C, D)
  // ---------------------------------------------------------------------------
  Widget _buildOptionsList(Map<String, dynamic> q) {
    final String selectedOpt = _selectedAnswers[_currentIndex] ?? '';

    final options = [
      {'key': 'A', 'text': q['opsi_a'] ?? '1/8 bagian'},
      {'key': 'B', 'text': q['opsi_b'] ?? '3/8 bagian'},
      {'key': 'C', 'text': q['opsi_c'] ?? '5/8 bagian'},
      {'key': 'D', 'text': q['opsi_d'] ?? '8/3 bagian'},
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Pilih satu jawaban yang benar:',
          style: TextStyle(
            fontSize: 12.5,
            fontWeight: FontWeight.w700,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 12),

        ...options.map((opt) {
          final String key = opt['key']!;
          final String text = opt['text']!;
          final bool isSelected = (selectedOpt == key);

          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: InkWell(
              onTap: () {
                setState(() {
                  _selectedAnswers[_currentIndex] = key;
                });
              },
              borderRadius: BorderRadius.circular(16),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isSelected ? AppColors.primary : AppColors.border,
                    width: isSelected ? 1.8 : 1,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: isSelected
                          ? AppColors.primary.withValues(alpha: 0.06)
                          : Colors.black.withValues(alpha: 0.02),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    // Badge Huruf Lingkaran
                    Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color: isSelected ? AppColors.primary : AppColors.primaryLight,
                        shape: BoxShape.circle,
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        key,
                        style: TextStyle(
                          color: isSelected ? Colors.white : AppColors.primary,
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                        ),
                      ),
                    ),
                    const SizedBox(width: 14),

                    // Teks Opsi
                    Expanded(
                      child: Text(
                        text,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ),

                    // Radio / Checkmark Icon
                    if (isSelected)
                      Container(
                        width: 22,
                        height: 22,
                        decoration: const BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.check, size: 14, color: Colors.white),
                      )
                    else
                      Container(
                        width: 22,
                        height: 22,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: AppColors.border, width: 1.8),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          );
        }),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 4. ACTION BUTTONS: [Sebelumnya] [Ragu-ragu] [Selanjutnya]
  // ---------------------------------------------------------------------------
  Widget _buildActionButtons() {
    final bool isLast = (_currentIndex == _totalSoal - 1);
    final bool isRagu = _raguRaguSet.contains(_currentIndex);

    return Row(
      children: [
        // Tombol Sebelumnya
        Expanded(
          flex: 4,
          child: SizedBox(
            height: 44,
            child: OutlinedButton(
              onPressed: _currentIndex > 0
                  ? () {
                      setState(() {
                        _currentIndex--;
                      });
                    }
                  : null,
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppColors.border),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                backgroundColor: Colors.white,
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.arrow_back_rounded, size: 15, color: AppColors.primary),
                  SizedBox(width: 4),
                  Text(
                    'Sebelumnya',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primary,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(width: 8),

        // Tombol Ragu-ragu
        Expanded(
          flex: 4,
          child: SizedBox(
            height: 44,
            child: OutlinedButton(
              onPressed: () {
                setState(() {
                  if (isRagu) {
                    _raguRaguSet.remove(_currentIndex);
                  } else {
                    _raguRaguSet.add(_currentIndex);
                  }
                });
              },
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppColors.accentBright, width: 1.5),
                backgroundColor: isRagu ? AppColors.warningLight : AppColors.warningLight,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.bookmark_border_rounded,
                    size: 15,
                    color: isRagu ? AppColors.accentDeep : AppColors.accentDeep,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    'Ragu-ragu',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: isRagu ? AppColors.accentDeep : AppColors.accentDeep,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(width: 8),

        // Tombol Selanjutnya / Selesai
        Expanded(
          flex: 4,
          child: SizedBox(
            height: 44,
            child: ElevatedButton(
              onPressed: () {
                if (isLast) {
                  _submitExam();
                } else {
                  setState(() {
                    _currentIndex++;
                  });
                }
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
                    isLast ? 'Kumpulkan' : 'Selanjutnya',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(width: 4),
                  Icon(
                    isLast ? Icons.check_circle_rounded : Icons.arrow_forward_rounded,
                    size: 15,
                    color: Colors.white,
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}

// =============================================================================
// PIE DIAGRAM 8 POTONG CUSTOM PAINTER
// 8 Slices: 3 Slices Deep Blue (#1D5A86), 5 Slices Soft Grey (#E2E8F0)
// =============================================================================
class PieDiagram8Painter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final double radius = size.width * 0.44;

    final paintBlue = Paint()
      ..color = AppColors.primary
      ..style = PaintingStyle.fill;

    final paintGrey = Paint()
      ..color = AppColors.border
      ..style = PaintingStyle.fill;

    final paintBorder = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.0;

    const double sliceAngle = 2 * math.pi / 8; // 45 derajat per slice
    final rect = Rect.fromCircle(center: center, radius: radius);

    // 8 Slice: Slice 0, 1, 2 = Dimakan Budi (3), Slice 3, 4, 5, 6, 7 = Sisa (5)
    // Sesuai screenshot, slice biru berada di kuadran kanan atas dan kanan bawah
    double startAngle = -math.pi / 2; // Mulai dari atas (jam 12)

    for (int i = 0; i < 8; i++) {
      // 3 slice pertama diisi biru (dimakan)
      final bool isEaten = (i < 3);
      final paint = isEaten ? paintBlue : paintGrey;

      final path = Path();
      path.moveTo(center.dx, center.dy);
      path.arcTo(rect, startAngle, sliceAngle, false);
      path.close();

      canvas.drawPath(path, paint);
      canvas.drawPath(path, paintBorder);

      startAngle += sliceAngle;
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
