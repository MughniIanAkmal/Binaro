import 'dart:async';
import 'dart:convert';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'profile_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';

class KuisPage extends StatefulWidget {
  final Map<String, dynamic>? user;
  final int? quizId;

  const KuisPage({
    super.key,
    this.user,
    this.quizId,
  });

  @override
  State<KuisPage> createState() => _KuisPageState();
}

class _KuisPageState extends State<KuisPage> {
  static const String _baseUrl = AppConstants.baseUrl;

  bool _isLoading = true;
  String _quizTitle = 'Kuis Siswa';
  int _totalSoal = 0;
  int _currentIndex = 0;
  List<Map<String, dynamic>> _soalList = [];

  // Pilihan jawaban siswa: map dari index soal -> opsi yang dipilih ('A', 'B', 'C', 'D')
  final Map<int, String> _selectedAnswers = {};

  // Timer countdown
  Timer? _countdownTimer;
  int _remainingSeconds = 900;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _fetchQuizData();
    _startTimer();
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    super.dispose();
  }

  void _startTimer() {
    _countdownTimer?.cancel();
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_remainingSeconds > 0) {
        setState(() {
          _remainingSeconds--;
        });
      } else {
        timer.cancel();
        _submitQuiz(autoSubmit: true);
      }
    });
  }

  String get _formattedTime {
    final int minutes = _remainingSeconds ~/ 60;
    final int seconds = _remainingSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  Future<void> _fetchQuizData() async {
    setState(() => _isLoading = true);
    final String url = widget.quizId != null
        ? '$_baseUrl/siswa/quiz/${widget.quizId}'
        : '$_baseUrl/siswa/quiz';

    try {
      final response = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true && decoded['data'] != null) {
          final data = decoded['data'];
          final List<dynamic> soals = data['soal'] ?? [];

          if (mounted) {
            setState(() {
              _quizTitle = data['judul_quiz'] ?? _quizTitle;
              _totalSoal = data['total_soal'] ?? soals.length;
              _soalList = soals
                  .map((e) => Map<String, dynamic>.from(e as Map))
                  .toList();
              _currentIndex = 0;
              _remainingSeconds = data['durasi_detik'] ?? 900;
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

  Future<void> _submitQuiz({bool autoSubmit = false}) async {
    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);

    int benar = 0;
    for (int i = 0; i < _soalList.length; i++) {
      final q = _soalList[i];
      final userAns = _selectedAnswers[i];
      if (userAns != null &&
          userAns.toUpperCase() == (q['kunci_jawaban'] ?? '').toString().toUpperCase()) {
        benar++;
      }
    }
    final int score = _soalList.isNotEmpty ? ((benar / _soalList.length) * 100).round() : 0;

    // Coba kirim ke API backend
    try {
      final quizId = widget.quizId ?? 11;
      final Map<String, String> answersPayload = {};
      _selectedAnswers.forEach((index, opt) {
        if (index < _soalList.length) {
          final idSoal = _soalList[index]['id_soal']?.toString() ?? '$index';
          answersPayload[idSoal] = opt;
        }
      });

      await http.post(
        Uri.parse('$_baseUrl/siswa/quiz/$quizId/submit'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'id_siswa': widget.user?['id_siswa'] ?? 1,
          'jawaban': answersPayload,
          'waktu_menit': (900 - _remainingSeconds) ~/ 60,
        }),
      ).timeout(const Duration(seconds: 5));
    } catch (_) {}

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    _showResultDialog(score, benar, _soalList.length);
  }

  void _showResultDialog(int score, int benar, int total) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        final bool isHigh = score >= 80;
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 26),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Star Icon Badge
                Container(
                  width: 76,
                  height: 76,
                  decoration: BoxDecoration(
                    color: isHigh ? const Color(0xFFFEF3C7) : const Color(0xFFEFF6FF),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isHigh ? Icons.emoji_events_rounded : Icons.star_rounded,
                    color: isHigh ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86),
                    size: 44,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  isHigh ? 'Luar Biasa, Hebat!' : 'Bagus, Tetap Semangat!',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                const Text(
                  'Kamu telah menyelesaikan latihan kuis ini dengan baik.',
                  style: TextStyle(
                    fontSize: 13,
                    color: Color(0xFF64748B),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 20),

                // Score Card
                Container(
                  padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      Column(
                        children: [
                          const Text('Nilai Akhir',
                              style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                          const SizedBox(height: 4),
                          Text(
                            '$score',
                            style: TextStyle(
                              fontSize: 26,
                              fontWeight: FontWeight.w900,
                              color: isHigh ? const Color(0xFFF59E0B) : const Color(0xFF1D5A86),
                            ),
                          ),
                        ],
                      ),
                      Container(height: 36, width: 1, color: const Color(0xFFCBD5E1)),
                      Column(
                        children: [
                          const Text('Jawaban Benar',
                              style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                          const SizedBox(height: 4),
                          Text(
                            '$benar / $total',
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF16A34A),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Button Kembali
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      Navigator.pop(context);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1D5A86),
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text(
                      'Kembali ke Pembelajaran',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final Map<String, dynamic>? currentQuestion =
        (_soalList.isNotEmpty && _currentIndex < _soalList.length)
            ? _soalList[_currentIndex]
            : null;

    final String currentTips = currentQuestion?['tips'] ??
        'Hitung total semua potongan semangka sebagai penyebut (angka bawah) ya!';

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
          'Latihan Soal',
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
          : currentQuestion == null
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
                            Icons.quiz_outlined,
                            size: 34,
                            color: Color(0xFF1D5A86),
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Kuis Belum Tersedia',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF1E293B),
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Guru belum mengunggah butir soal kuis untuk materi ini.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 13,
                            color: Color(0xFF64748B),
                            height: 1.4,
                          ),
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
                      // 1. Header Kuis & Progress Bar
                      _buildHeaderCard(),
                      const SizedBox(height: 16),

                      // 2. Kartu Soal & Ilustrasi
                      _buildQuestionCard(currentQuestion),
                      const SizedBox(height: 16),

                      // 3. Pilihan Jawaban (A, B, C, D)
                      _buildOptionsList(currentQuestion),
                      const SizedBox(height: 14),

                      // 4. Kartu Tips
                      _buildTipsCard(currentTips),
                      const SizedBox(height: 24),

                      // 5. Tombol Aksi Bawah: Sebelumnya & Berikutnya
                      _buildBottomActions(),
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. HEADER KUIS DENGAN TIMER DAN SEGMEN PROGRESS
  // ---------------------------------------------------------------------------
  Widget _buildHeaderCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
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
          // Row Atas: Icon Kuis + Judul + Countdown Timer Pill
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.grid_view_rounded, size: 18, color: Color(0xFF1D5A86)),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  _quizTitle,
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              // Timer Pill
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.access_time_rounded, size: 14, color: Color(0xFF1D5A86)),
                    const SizedBox(width: 4),
                    Text(
                      _formattedTime,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1D5A86),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Row Bawah: 5 Segmen Bar Progres & "Soal 3 dari 5"
          Row(
            children: [
              Expanded(
                child: Row(
                  children: List.generate(_totalSoal, (index) {
                    Color segColor;
                    if (index == _currentIndex) {
                      segColor = const Color(0xFF1D5A86); // Soal aktif: biru gelap
                    } else if (_selectedAnswers.containsKey(index)) {
                      segColor = const Color(0xFFF59E0B); // Sudah dijawab: oranye
                    } else {
                      segColor = const Color(0xFFDBEAFE); // Belum dijawab: biru muda
                    }

                    return Expanded(
                      child: Container(
                        margin: EdgeInsets.only(right: index < _totalSoal - 1 ? 6 : 0),
                        height: 7,
                        decoration: BoxDecoration(
                          color: segColor,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    );
                  }),
                ),
              ),
              const SizedBox(width: 14),
              Text(
                'Soal ${_currentIndex + 1} dari $_totalSoal',
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1D5A86),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 2. KARTU SOAL & ILUSTRASI SEMANGKA
  // ---------------------------------------------------------------------------
  Widget _buildQuestionCard(Map<String, dynamic> q) {
    Map<String, dynamic>? ilustrasi;
    if (q['ilustrasi'] != null && q['ilustrasi'] is Map) {
      ilustrasi = Map<String, dynamic>.from(q['ilustrasi'] as Map);
    }

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
          // Header Soal: Nomor (Lingkaran Biru) + Teks Pertanyaan
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: const BoxDecoration(
                  color: Color(0xFF1D5A86),
                  shape: BoxShape.circle,
                ),
                alignment: Alignment.center,
                child: Text(
                  '${_currentIndex + 1}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 13.5,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  q['pertanyaan'] ?? '',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1E293B),
                    height: 1.45,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Box Ilustrasi
          if (ilustrasi != null || _currentIndex == 2)
            _buildIllustrationBox(ilustrasi),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // ILUSTRASI SEMANGKA 4 BAGIAN DENGAN 1 BAGIAN DIAMBIL (CUSTOM CANVAS)
  // ---------------------------------------------------------------------------
  Widget _buildIllustrationBox(Map<String, dynamic>? ilustrasi) {
    final String caption = ilustrasi?['caption'] ?? '1 dari 4 potongan semangka diambil oleh Budi';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
      decoration: BoxDecoration(
        color: const Color(0xFFF0F9FF), // Light pastel sky blue
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE0F2FE)),
      ),
      child: Column(
        children: [
          // Canvas Gambar Semangka
          SizedBox(
            width: 170,
            height: 170,
            child: CustomPaint(
              painter: WatermelonPainter(),
            ),
          ),
          const SizedBox(height: 14),

          // Teks Caption Ilustrasi
          Text(
            caption,
            style: const TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w600,
              color: Color(0xFF1D5A86),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. DAFTAR PILIHAN JAWABAN (A, B, C, D)
  // ---------------------------------------------------------------------------
  Widget _buildOptionsList(Map<String, dynamic> q) {
    final String selectedKey = _selectedAnswers[_currentIndex] ?? '';

    final options = [
      {'key': 'A', 'text': q['opsi_a'] ?? '1/2 bagian'},
      {'key': 'B', 'text': q['opsi_b'] ?? '1/4 bagian'},
      {'key': 'C', 'text': q['opsi_c'] ?? '2/4 bagian'},
      {'key': 'D', 'text': q['opsi_d'] ?? '3/4 bagian'},
    ];

    return Column(
      children: options.map((opt) {
        final String key = opt['key']!;
        final String text = opt['text']!;
        final bool isSelected = (selectedKey == key);

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
                  color: isSelected ? const Color(0xFFF59E0B) : const Color(0xFFE2E8F0),
                  width: isSelected ? 1.8 : 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: isSelected
                        ? const Color(0xFFF59E0B).withValues(alpha: 0.08)
                        : Colors.black.withValues(alpha: 0.02),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Circle Badge Huruf (A, B, C, D)
                  Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: isSelected ? const Color(0xFFF59E0B) : const Color(0xFFEFF6FF),
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      key,
                      style: TextStyle(
                        color: isSelected ? Colors.white : const Color(0xFF1D5A86),
                        fontWeight: FontWeight.w800,
                        fontSize: 14,
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),

                  // Teks Pilihan
                  Expanded(
                    child: Text(
                      text,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                        color: const Color(0xFF1E293B),
                      ),
                    ),
                  ),

                  // Indikator Radio (Oranye jika terpilih dengan Checkmark)
                  if (isSelected)
                    Container(
                      width: 22,
                      height: 22,
                      decoration: const BoxDecoration(
                        color: Color(0xFFF59E0B),
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
                        border: Border.all(color: const Color(0xFFCBD5E1), width: 1.8),
                      ),
                    ),
                ],
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. KARTU TIPS
  // ---------------------------------------------------------------------------
  Widget _buildTipsCard(String tips) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB), // Cream yellow
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.lightbulb_rounded, color: Color(0xFFD97706), size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: RichText(
              text: TextSpan(
                style: const TextStyle(fontSize: 12, color: Color(0xFF78350F), height: 1.45),
                children: [
                  const TextSpan(
                    text: 'Tips: ',
                    style: TextStyle(fontWeight: FontWeight.w800, color: Color(0xFFB45309)),
                  ),
                  TextSpan(
                    text: tips,
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
  // 5. TOMBOL AKSI BAWAH: SEBELUMNYA & BERIKUTNYA
  // ---------------------------------------------------------------------------
  Widget _buildBottomActions() {
    final bool isLast = (_currentIndex == _totalSoal - 1);

    return Row(
      children: [
        // Tombol Sebelumnya
        Expanded(
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
                side: const BorderSide(color: Color(0xFFCBD5E1)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                backgroundColor: Colors.white,
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.arrow_back_rounded, size: 16, color: Color(0xFF1D5A86)),
                  SizedBox(width: 6),
                  Text(
                    'Sebelumnya',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1D5A86),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(width: 14),

        // Tombol Berikutnya / Selesai
        Expanded(
          child: SizedBox(
            height: 44,
            child: ElevatedButton(
              onPressed: () {
                if (isLast) {
                  _submitQuiz();
                } else {
                  setState(() {
                    _currentIndex++;
                  });
                }
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
                    isLast ? 'Selesai' : 'Berikutnya',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(width: 6),
                  Icon(
                    isLast ? Icons.check_circle_outline_rounded : Icons.arrow_forward_rounded,
                    size: 16,
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
// CUSTOM PAINTER: SEMANGKA DIBAGI 4 DENGAN 1 POTONGAN TERPISAH & BADGE "1"
// =============================================================================
class WatermelonPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final double radius = size.width * 0.42;
    const double gap = 4.0;
    const double separateDistance = 14.0; // Jarak potongan Budi yang diambil

    // Warna semangka
    const Color fleshColor = Color(0xFFEF4444); // Merah daging semangka
    const Color rindLightColor = Color(0xFFBBF7D0); // Garis putih kehijauan dalam
    const Color rindDarkColor = Color(0xFF15803D); // Kulit hijau gelap
    const Color seedColor = Color(0xFF1E293B); // Biji hitam

    // Paint objek
    final fleshPaint = Paint()
      ..color = fleshColor
      ..style = PaintingStyle.fill;

    final rindLightPaint = Paint()
      ..color = rindLightColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.0;

    final rindDarkPaint = Paint()
      ..color = rindDarkColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 6.0;

    final seedPaint = Paint()
      ..color = seedColor
      ..style = PaintingStyle.fill;

    // -------------------------------------------------------------------------
    // Fungsi bantuan menggambar 1 potongan kuadran semangka
    // -------------------------------------------------------------------------
    void drawSlice({
      required double startAngle,
      required Offset sliceCenter,
      required List<Offset> seedRelativePositions,
      bool isSelectedSlice = false,
    }) {
      final rect = Rect.fromCircle(center: sliceCenter, radius: radius);

      // Path daging
      final path = Path();
      path.moveTo(sliceCenter.dx, sliceCenter.dy);
      path.arcTo(rect, startAngle, math.pi / 2, false);
      path.close();

      canvas.drawPath(path, fleshPaint);

      // Busur kulit semangka (bagian lengkung)
      final rindRect = Rect.fromCircle(center: sliceCenter, radius: radius - 1.5);
      canvas.drawArc(rindRect, startAngle, math.pi / 2, false, rindLightPaint);
      canvas.drawArc(rindRect, startAngle, math.pi / 2, false, rindDarkPaint);

      // Biji semangka
      for (final sPos in seedRelativePositions) {
        final seedCenter = sliceCenter + sPos;
        canvas.drawOval(
          Rect.fromCenter(center: seedCenter, width: 4.5, height: 6.5),
          seedPaint,
        );
      }

      // Jika ini potongan yang diambil (terpisah), beri badge nomor "1" di pojoknya
      if (isSelectedSlice) {
        final badgeCenter = Offset(sliceCenter.dx + radius * 0.45, sliceCenter.dy - radius * 0.45);
        final badgePaint = Paint()
          ..color = const Color(0xFFF59E0B)
          ..style = PaintingStyle.fill;

        canvas.drawRRect(
          RRect.fromRectAndRadius(
            Rect.fromCenter(center: badgeCenter, width: 20, height: 20),
            const Radius.circular(6),
          ),
          badgePaint,
        );

        final textPainter = TextPainter(
          text: const TextSpan(
            text: '1',
            style: TextStyle(
              color: Colors.white,
              fontSize: 12,
              fontWeight: FontWeight.w900,
            ),
          ),
          textDirection: TextDirection.ltr,
        );
        textPainter.layout();
        textPainter.paint(
          canvas,
          Offset(badgeCenter.dx - textPainter.width / 2, badgeCenter.dy - textPainter.height / 2),
        );
      }
    }

    // 1. Potongan Kuadran Kiri-Atas (Top-Left: angle pi to 3*pi/2)
    drawSlice(
      startAngle: math.pi,
      sliceCenter: Offset(center.dx - gap, center.dy - gap),
      seedRelativePositions: const [
        Offset(-22, -22),
        Offset(-36, -14),
        Offset(-18, -36),
      ],
    );

    // 2. Potongan Kuadran Kiri-Bawah (Bottom-Left: angle pi/2 to pi)
    drawSlice(
      startAngle: math.pi / 2,
      sliceCenter: Offset(center.dx - gap, center.dy + gap),
      seedRelativePositions: const [
        Offset(-22, 22),
        Offset(-36, 14),
        Offset(-18, 36),
      ],
    );

    // 3. Potongan Kuadran Kanan-Bawah (Bottom-Right: angle 0 to pi/2)
    drawSlice(
      startAngle: 0,
      sliceCenter: Offset(center.dx + gap, center.dy + gap),
      seedRelativePositions: const [
        Offset(22, 22),
        Offset(36, 14),
        Offset(18, 36),
      ],
    );

    // 4. Potongan Kuadran Kanan-Atas (Top-Right: angle -pi/2 to 0) -> Diambil Budi!
    // Digeser ke kanan atas dengan jarak terpisah
    drawSlice(
      startAngle: -math.pi / 2,
      sliceCenter: Offset(center.dx + gap + separateDistance, center.dy - gap - separateDistance),
      seedRelativePositions: const [
        Offset(22, -22),
        Offset(36, -14),
        Offset(18, -36),
      ],
      isSelectedSlice: true,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
