import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:syncfusion_flutter_pdfviewer/pdfviewer.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_platform_interface/webview_flutter_platform_interface.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';
import 'package:binaro_flutter/fitur/ujian/screens/kuis_page.dart';
import 'package:binaro_flutter/fitur/materi/screens/pdf_viewer_page.dart';
import 'package:binaro_flutter/fitur/materi/services/materi_service.dart';
import 'package:binaro_flutter/fitur/materi/services/youtube_helper.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';
import 'package:binaro_flutter/inti/layanan/responsive.dart';
import 'package:binaro_flutter/inti/layanan/page_transitions.dart';
import 'package:binaro_flutter/inti/widgets/common.dart';
import 'package:binaro_flutter/fitur/ujian/widgets/konfirmasi_kuis_dialog.dart';
import 'package:binaro_flutter/fitur/profil/screens/profile_page.dart';

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
  bool _pdfGagalMuat = false;
  Map<String, dynamic> _detailMateri = {};
  YoutubePlayerController? _ytController;
  StreamSubscription<YoutubePlayerValue>? _ytSubscription;
  String? _loadedVideoId;
  bool _isFullScreen = false;

  /// Pemutar video di dalam aplikasi hanya didukung Android & iOS —
  /// webview resminya memang hanya ada di dua platform itu. Di desktop
  /// (Windows/macOS/Linux) kartu video memakai mode thumbnail dan video
  /// dibuka di YouTube, karena webview Android yang dipaksakan terdaftar
  /// di desktop hanya menghasilkan kotak kosong dan merusak tampilan kartu.
  bool get _platformDukungPlayer =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.android ||
          defaultTargetPlatform == TargetPlatform.iOS);

  bool get _canUseInAppPlayer => _platformDukungPlayer && _ytController != null;

  void _updateOrientation(bool isFullScreen) {
    if (_isFullScreen == isFullScreen) return;
    _isFullScreen = isFullScreen;
    try {
      if (isFullScreen) {
        // Otomatis putar ke landscape (layar penuh horizontal)
        SystemChrome.setPreferredOrientations([
          DeviceOrientation.landscapeLeft,
          DeviceOrientation.landscapeRight,
        ]);
        SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
      } else {
        // Kembalikan ke portrait (layar tegak normal)
        SystemChrome.setPreferredOrientations([
          DeviceOrientation.portraitUp,
          DeviceOrientation.portraitDown,
        ]);
        SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
      }
    } catch (_) {}
    if (mounted) setState(() {});
  }

  @override
  void initState() {
    super.initState();
    try {
      AndroidWebViewPlatform.registerWith();
    } catch (_) {
      try {
        AndroidWebViewPlatform.registerWith();
      } catch (_) {
        try {
          WebViewPlatform.instance ??= AndroidWebViewPlatform();
        } catch (_) {}
      }
    }
    final String? initialVideoUrl = widget.materi['url_video'] ?? widget.materi['video_url'];
    if (initialVideoUrl != null && initialVideoUrl.isNotEmpty) {
      _initYoutubePlayer(initialVideoUrl);
    }
    _fetchDetailMateri();
  }

  @override
  void dispose() {
    _updateOrientation(false);
    _ytSubscription?.cancel();
    _ytController?.close();
    super.dispose();
  }

  void _initYoutubePlayer(String url) {
    try {
      try {
        AndroidWebViewPlatform.registerWith();
      } catch (_) {
        try {
          WebViewPlatform.instance ??= AndroidWebViewPlatform();
        } catch (_) {}
      }

      // Jangan buat controller di platform tanpa webview resmi (desktop):
      // kartu video otomatis memakai mode thumbnail + buka di YouTube.
      if (!_platformDukungPlayer) {
        _ytController = null;
        return;
      }

      final String? ytId = _extractYoutubeId(url);
      if (ytId != null && ytId.isNotEmpty) {
        if (_loadedVideoId == ytId && _ytController != null) {
          return;
        }
        _loadedVideoId = ytId;
        _ytSubscription?.cancel();
        _ytController?.close();
        _ytController = YoutubePlayerController.fromVideoId(
          videoId: ytId,
          autoPlay: false,
          params: const YoutubePlayerParams(
            showControls: true,
            showFullscreenButton: true,
            mute: false,
            showVideoAnnotations: false,
            enableCaption: false,
            enableJavaScript: true,
            privacyEnhancedMode: true,
            strictRelatedVideos: true,
            playsInline: true,
          ),
        );
        // Daftarkan listener rotasi layar penuh otomatis
        _ytController!.setFullScreenListener((isFullScreen) {
          _updateOrientation(isFullScreen);
        });
        _ytSubscription = _ytController!.listen((event) {
          final isFs = event.fullScreenOption.enabled;
          if (isFs != _isFullScreen) {
            _updateOrientation(isFs);
          }
          if (mounted) setState(() {});
        });
        if (mounted) setState(() {});
      }
    } catch (_) {
      _ytController = null;
    }
  }

  void _handleVideoAction(String url, [String? judul]) {
    try {
      final hasError = _ytController?.value.error != null && _ytController!.value.error != YoutubeError.none;
      if (hasError) {
        _launchMediaUrl(url);
        return;
      }
      if (_ytController == null) {
        _initYoutubePlayer(url);
      }
      if (_canUseInAppPlayer && _ytController != null) {
        final state = _ytController!.value.playerState;
        if (state == PlayerState.playing) {
          _ytController!.pauseVideo();
        } else if (state == PlayerState.paused) {
          _ytController!.playVideo();
        } else {
          final ytId = _extractYoutubeId(url);
          if (ytId != null) {
            _ytController!.loadVideoById(videoId: ytId);
          } else {
            _ytController!.playVideo();
          }
        }
      } else {
        // Fallback cerdas: buka aplikasi eksternal YouTube / browser
        _launchMediaUrl(url);
      }
    } catch (_) {
      _launchMediaUrl(url);
    }
  }

  void _openFullScreenVideo(String url, String judul) {
    final hasError = _ytController?.value.error != null && _ytController!.value.error != YoutubeError.none;
    if (hasError) {
      _launchMediaUrl(url);
      return;
    }
    if (_canUseInAppPlayer && _ytController != null) {
      final state = _ytController!.value.playerState;
      if (state == PlayerState.unStarted || state == PlayerState.unknown || state == PlayerState.cued) {
        final ytId = _extractYoutubeId(url);
        if (ytId != null) {
          _ytController!.loadVideoById(videoId: ytId);
        } else {
          _ytController!.playVideo();
        }
      }
      _updateOrientation(true);
      _ytController!.enterFullScreen();
    } else {
      // Buka aplikasi eksternal YouTube / browser jika pemutar belum terpasang
      _launchMediaUrl(url);
    }
  }

  Future<void> _fetchDetailMateri() async {
    final int materiId = widget.materi['id_materi'] ?? 20;
    final data = await MateriService.fetchDetailMateri(materiId);
    if (!mounted) return;

    if (data != null) {
      setState(() {
        _detailMateri = data;
        _isLoading = false;
      });
      final String? videoUrl = data['video']?['url'];
      if (videoUrl != null && videoUrl.isNotEmpty) {
        _initYoutubePlayer(videoUrl);
      }
    } else {
      setState(() {
        _detailMateri = {};
        _isLoading = false;
      });
    }
  }

  String? _extractYoutubeId(String? url) => YoutubeHelper.extractYoutubeId(url);

  String _cleanVideoUrl(String rawUrl) => YoutubeHelper.cleanVideoUrl(rawUrl);

  String _normalizeUrl(String rawUrl) => YoutubeHelper.normalizeUrl(rawUrl);

  Future<void> _downloadPdfFile(String targetUrl, String fileName) async {
    final String cleanUrl = _normalizeUrl(targetUrl);
    final String finalUrl = cleanUrl.contains('?') ? '$cleanUrl&download=1' : '$cleanUrl?download=1';
    try {
      final Uri uri = Uri.parse(finalUrl);
      final bool launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!launched) {
        await launchUrl(uri, mode: LaunchMode.platformDefault);
      }
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: AppColors.primary,
            content: Row(
              children: [
                const Icon(Icons.download_done_rounded, color: Colors.white, size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Mengunduh $fileName... Periksa folder Unduhan HP Anda.',
                    style: const TextStyle(fontSize: 13),
                  ),
                ),
              ],
            ),
            duration: const Duration(seconds: 4),
          ),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal memulai unduhan file.')),
        );
      }
    }
  }

  Future<void> _launchMediaUrl(String url, {bool forceInApp = false}) async {
    final cleanUrl = _cleanVideoUrl(url);
    if (cleanUrl.isEmpty) return;
    final Uri uri = Uri.parse(cleanUrl);
    try {
      if (forceInApp) {
        final bool launchedInApp = await launchUrl(
          uri,
          mode: LaunchMode.inAppBrowserView,
        );
        if (launchedInApp) return;
      }
      final bool launched = await launchUrl(
        uri,
        mode: LaunchMode.externalApplication,
      );
      if (!launched) {
        await launchUrl(uri, mode: LaunchMode.platformDefault);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Tidak dapat membuka tautan: $url')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !_isFullScreen && (_ytController?.value.fullScreenOption.enabled != true),
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) {
          if (_isFullScreen || (_ytController?.value.fullScreenOption.enabled == true)) {
            _ytController?.exitFullScreen();
            _updateOrientation(false);
          }
        }
      },
      child: Scaffold(
      backgroundColor: AppColors.background,
      appBar: BinaroAppBar(
        title: 'Materi Pelajaran',
        showBack: true,
        actions: [
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
                  color: AppColors.border,
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2.2),
                ),
              ),
            ),
          ),
        ],
      ),
      body: _isLoading && _detailMateri.isEmpty
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _detailMateri.isEmpty
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
                            Icons.menu_book_outlined,
                            size: 34,
                            color: AppColors.primary,
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Materi Belum Tersedia',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Konten materi pembelajaran ini belum diunggah oleh guru pengampu.',
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
              : Column(
                  children: [
                    Expanded(
                      child: RefreshIndicator(
                        onRefresh: _fetchDetailMateri,
                        color: AppColors.primary,
                        backgroundColor: Colors.white,
                        displacement: 28,
                        child: SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(
                            parent: BouncingScrollPhysics(),
                          ),
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                          child: KontenResponsif(
                            modeBaca: true,
                            child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // 1. Breadcrumb Chip Dinamis dari Database
                              _buildBreadcrumbChip(),
                            const SizedBox(height: 16),

                            // 2. Video Player Card (Desain Cerah & Clean sesuai Mockup)
                            Builder(
                              builder: (context) {
                                Map<String, dynamic>? videoData;
                                if (_detailMateri['video'] != null && _detailMateri['video'] is Map) {
                                  videoData = Map<String, dynamic>.from(_detailMateri['video'] as Map);
                                } else if (_detailMateri['url_video'] != null && _detailMateri['url_video'].toString().isNotEmpty) {
                                  videoData = {
                                    'judul': _detailMateri['judul_materi'] ?? widget.materi['judul_materi'] ?? 'Video Pembelajaran',
                                    'url': _detailMateri['url_video'],
                                  };
                                } else if (widget.materi['url_video'] != null && widget.materi['url_video'].toString().isNotEmpty) {
                                  videoData = {
                                    'judul': widget.materi['judul_materi'] ?? 'Video Pembelajaran',
                                    'url': widget.materi['url_video'],
                                  };
                                }

                                if (videoData != null && videoData['url'] != null && videoData['url'].toString().isNotEmpty) {
                                  return Padding(
                                    padding: const EdgeInsets.only(bottom: 18),
                                    child: _buildVideoPlayerCard(videoData),
                                  );
                                }
                                return const SizedBox.shrink();
                              },
                            ),

                            // 3. Dokumen PDF Card (Hanya jika ada file PDF dari Database)
                            if (_detailMateri['pdf'] != null && _detailMateri['pdf']['url'] != null) ...[
                              _buildPdfCard(Map<String, dynamic>.from(_detailMateri['pdf'] as Map)),
                              const SizedBox(height: 18),
                            ],

                            // 4. Konten Teks Pelajaran (Hanya jika ada teks isi_materi asli dari Database)
                            if (_detailMateri['konten'] != null &&
                                _detailMateri['konten']['isi'] != null &&
                                _detailMateri['konten']['isi'].toString().trim().isNotEmpty) ...[
                              _buildReadingContentCard(Map<String, dynamic>.from(_detailMateri['konten'] as Map)),
                              const SizedBox(height: 18),
                            ],

                            const SizedBox(height: 16),
                          ],
                            ),
                        ),
                      ),
                    ),
                  ),

                    // Sticky Bottom Bar: Kembali & Lanjut Kuis (jika ada kuis)
                    _buildStickyBottomBar(),
                  ],
                ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. BREADCRUMB CHIP (100% Dinamis dari Database)
  // ---------------------------------------------------------------------------
  Widget _buildBreadcrumbChip() {
    final Map<String, dynamic>? breadcrumb = _detailMateri['breadcrumb'] != null && _detailMateri['breadcrumb'] is Map
        ? Map<String, dynamic>.from(_detailMateri['breadcrumb'] as Map)
        : null;

    final String mapel = breadcrumb?['mapel'] ?? widget.mapel?['nama'] ?? 'Mata Pelajaran';
    final String bab = breadcrumb?['bab'] ?? widget.bab?['nama_bab'] ?? 'Bab';
    final String subBab = breadcrumb?['sub_bab'] ?? widget.subBab?['nama_sub_bab'] ?? 'Sub Bab';

    final bool isMath = mapel.toLowerCase().contains('matematika');
    final IconData icon = isMath ? Icons.calculate_rounded : Icons.menu_book_rounded;

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: AppColors.primaryLight,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.primaryLight),
            ),
            child: Row(
              children: [
                Icon(icon, size: 14, color: AppColors.primary),
                const SizedBox(width: 5),
                Text(
                  mapel,
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: AppColors.primary,
                  ),
                ),
              ],
            ),
          ),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 6),
            child: Icon(Icons.chevron_right_rounded, size: 16, color: AppColors.textMuted),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: AppColors.surfaceAlt,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              bab,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 6),
            child: Icon(Icons.chevron_right_rounded, size: 16, color: AppColors.textMuted),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: AppColors.surfaceAlt,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              subBab,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 2. VIDEO PLAYER CARD (Desain Cerah & Clean - Sesuai Mockup Asli)
  // ---------------------------------------------------------------------------
  Widget _buildVideoPlayerCard(Map<String, dynamic> video) {
    final String judul = video['judul'] ?? _detailMateri['judul_materi'] ?? 'Video Pembelajaran';
    final String rawUrl = video['url'] ?? '';
    final String url = _cleanVideoUrl(rawUrl);
    final String? ytId = _extractYoutubeId(rawUrl);
    final bool isPlaying = _ytController?.value.playerState == PlayerState.playing;
    final bool hasError = _ytController?.value.error != null && _ytController!.value.error != YoutubeError.none;
    // Mode eksternal: video dibuka di YouTube (platform tanpa pemutar
    // dalam aplikasi seperti desktop, atau pemutaran sematan dibatasi).
    final bool modeEksternal = !_canUseInAppPlayer || hasError;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white, // Kartu Putih Bersih sesuai tema aplikasi
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.05),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Frame Layar Pemutar Video (16:9 Aspect Ratio)
          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              color: AppColors.navy950, // Navy blue player screen
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.12),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            clipBehavior: Clip.antiAlias,
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: _canUseInAppPlayer
                  ? Stack(
                      fit: StackFit.expand,
                      children: [
                        YoutubePlayer(
                          controller: _ytController!,
                          aspectRatio: 16 / 9,
                          enableFullScreenOnVerticalDrag: true,
                          autoFullScreen: true,
                        ),
                        // Overlay Khusus Jika Video Dibatasi Pemutaran Sematan oleh YouTube
                        if (hasError)
                          Positioned.fill(
                            child: Container(
                              color: AppColors.textPrimary,
                              child: Stack(
                                fit: StackFit.expand,
                                children: [
                                  if (ytId != null)
                                    Opacity(
                                      opacity: 0.25,
                                      child: Image.network(
                                        'https://img.youtube.com/vi/$ytId/hqdefault.jpg',
                                        fit: BoxFit.cover,
                                        errorBuilder: (_, __, ___) => const SizedBox(),
                                      ),
                                    ),
                                  Center(
                                    child: Padding(
                                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                      child: Column(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.all(10),
                                            decoration: BoxDecoration(
                                              color: AppColors.error.withValues(alpha: 0.2),
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Icon(
                                              Icons.play_disabled_rounded,
                                              color: AppColors.error,
                                              size: 32,
                                            ),
                                          ),
                                          const SizedBox(height: 8),
                                          const Text(
                                            'Pemutaran Sematan Dibatasi',
                                            textAlign: TextAlign.center,
                                            style: TextStyle(
                                              color: Colors.white,
                                              fontSize: 13,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          const Text(
                                            'Pemilik video mewajibkan pemutaran langsung di aplikasi YouTube.',
                                            textAlign: TextAlign.center,
                                            style: TextStyle(
                                              color: AppColors.textMuted,
                                              fontSize: 11,
                                            ),
                                          ),
                                          const SizedBox(height: 12),
                                          ElevatedButton.icon(
                                            onPressed: () => _launchMediaUrl(url),
                                            icon: const Icon(Icons.open_in_new_rounded, size: 15),
                                            label: const Text(
                                              'Tonton di YouTube',
                                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                            ),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: AppColors.error,
                                              foregroundColor: Colors.white,
                                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                              shape: RoundedRectangleBorder(
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        // Overlay Tombol Putar saat video belum berputar
                        if (!hasError && !isPlaying && (_ytController?.value.playerState != PlayerState.buffering))
                          Positioned.fill(
                            child: Material(
                              color: Colors.transparent,
                              child: InkWell(
                                onTap: () => _handleVideoAction(url, judul),
                                child: Center(
                                  child: Container(
                                    width: 60,
                                    height: 60,
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        colors: [AppColors.accent, AppColors.accent],
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                      ),
                                      shape: BoxShape.circle,
                                      boxShadow: [
                                        BoxShadow(
                                          color: AppColors.accent.withValues(alpha: 0.6),
                                          blurRadius: 18,
                                          offset: const Offset(0, 4),
                                        ),
                                      ],
                                    ),
                                    child: const Icon(
                                      Icons.play_arrow_rounded,
                                      color: Colors.white,
                                      size: 40,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        // Tombol shortcut Layar Penuh di sudut kanan atas pemutar
                        Positioned(
                          top: 8,
                          right: 8,
                          child: Material(
                            color: Colors.black.withValues(alpha: 0.6),
                            shape: const CircleBorder(),
                            child: IconButton(
                              icon: const Icon(Icons.fullscreen_rounded, color: Colors.white, size: 20),
                              tooltip: 'Layar Penuh',
                              onPressed: () => _openFullScreenVideo(url, judul),
                            ),
                          ),
                        ),
                      ],
                    )
                  : InkWell(
                      onTap: () => _handleVideoAction(url, judul),
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          // 1. Background Thumbnail Cerah Asli
                          if (ytId != null)
                            Image.network(
                              'https://img.youtube.com/vi/$ytId/hqdefault.jpg',
                              width: double.infinity,
                              height: double.infinity,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => const Center(
                                child: Icon(Icons.ondemand_video_rounded, color: Colors.white54, size: 48),
                              ),
                            )
                          else
                            const Center(
                              child: Icon(Icons.ondemand_video_rounded, color: Colors.white54, size: 48),
                            ),

                          // 2. Tombol Play Oranye Menyala di Tengah
                          Container(
                            width: 58,
                            height: 58,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [AppColors.accent, AppColors.accent],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.accent.withValues(alpha: 0.55),
                                  blurRadius: 18,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: const Icon(
                              Icons.play_arrow_rounded,
                              color: Colors.white,
                              size: 38,
                            ),
                          ),

                          // 3. Badge Layar Penuh di pojok kanan bawah thumbnail
                          Positioned(
                            bottom: 8,
                            right: 8,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.75),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.fullscreen_rounded, color: Colors.white, size: 14),
                                  SizedBox(width: 4),
                                  Text(
                                    'Layar Penuh',
                                    style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
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

          const SizedBox(height: 12),

          // Detail Keterangan Video di bawah Layar (Berlatar Putih Cerah)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        judul,
                        style: const TextStyle(
                          fontSize: 15.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.textPrimary,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppColors.primaryLight,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text(
                              'Video Pembelajaran SD',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.primary),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: isPlaying ? AppColors.successLight : AppColors.warningLight,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  isPlaying ? Icons.play_arrow_rounded : Icons.check_circle_outline_rounded,
                                  size: 12,
                                  color: isPlaying ? AppColors.success : AppColors.accentDeep,
                                ),
                                const SizedBox(width: 3),
                                Text(
                                  isPlaying ? 'Sedang Memutar' : 'Siap Diputar',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                    color: isPlaying ? AppColors.success : AppColors.accentDeep,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          // Tombol Putar / Jeda Video Cerah & Elegan + Tombol Full Screen + Shortcut Eksternal
          Row(
            children: [
              // 1. Tombol Putar / Jeda / Buka Eksternal
              Expanded(
                child: SizedBox(
                  height: 44,
                  child: ElevatedButton.icon(
                    onPressed: () => _handleVideoAction(url, judul),
                    icon: Icon(
                      modeEksternal
                          ? Icons.open_in_new_rounded
                          : (isPlaying ? Icons.pause_circle_filled_rounded : Icons.play_circle_fill_rounded),
                      size: 20,
                    ),
                    label: Text(
                      modeEksternal ? 'Tonton di YouTube' : (isPlaying ? 'Jeda Video' : 'Putar di Aplikasi'),
                      style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: hasError
                          ? AppColors.error
                          : (isPlaying ? AppColors.primary : AppColors.accent),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),

              // 2. Tombol Opsi Layar Penuh (Landscape Immersive)
              Container(
                height: 44,
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.primaryLight),
                ),
                child: IconButton(
                  icon: const Icon(Icons.fullscreen_rounded, color: AppColors.primary, size: 24),
                  tooltip: 'Layar Penuh',
                  onPressed: () => _openFullScreenVideo(url, judul),
                ),
              ),
              const SizedBox(width: 8),

              // 3. Tombol Eksternal YouTube
              Container(
                height: 44,
                width: 44,
                decoration: BoxDecoration(
                  color: AppColors.surfaceAlt,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: IconButton(
                  icon: const Icon(Icons.open_in_new_rounded, color: AppColors.textSecondary, size: 20),
                  tooltip: 'Buka di YouTube',
                  onPressed: () => _launchMediaUrl(url),
                ),
              ),
            ],
          ),
          if (hasError) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: AppColors.errorLight,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.errorLight),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline_rounded, size: 20, color: AppColors.error),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Pemutaran Sematan Dibatasi',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.error),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Pemilik video membatasi pemutaran pihak ketiga (${_ytController?.value.error.name ?? 'sameAsNotEmbeddable'}). Ketuk tombol untuk langsung menonton.',
                          style: const TextStyle(fontSize: 11, color: AppColors.error),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    onPressed: () => _launchMediaUrl(url),
                    icon: const Icon(Icons.play_arrow_rounded, size: 16),
                    label: const Text('Buka', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.error,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      visualDensity: VisualDensity.compact,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. DOKUMEN PDF CARD (Tampilan File PDF Terbuka di Aplikasi + Opsi Download)
  // ---------------------------------------------------------------------------
  Widget _buildPdfCard(Map<String, dynamic> pdf) {
    final String rawUrl = pdf['url'] ?? '';
    final String pdfUrl = _normalizeUrl(rawUrl);
    final String rawDownloadUrl = pdf['download_url'] ?? rawUrl;
    final String downloadUrl = _normalizeUrl(rawDownloadUrl);
    final String fileName = pdf['file_pdf'] ?? _detailMateri['judul_materi'] ?? 'Dokumen Materi.pdf';
    final String judul = _detailMateri['judul_materi'] ?? fileName;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.05),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Dokumen PDF
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: AppColors.errorLight,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.picture_as_pdf_rounded, color: AppColors.error, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      judul,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.primaryLight,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            'Dokumen PDF',
                            style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.primary),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _pdfGagalMuat ? 'Pratinjau Gagal Dimuat' : 'Terbuka di Aplikasi',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: _pdfGagalMuat ? AppColors.error : AppColors.success,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Tampilan File PDF yang Terbuka di Aplikasi (Interactive In-App Viewer)
          Container(
            height: _pdfGagalMuat ? 200 : 380,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border, width: 1.2),
              color: AppColors.background,
            ),
            clipBehavior: Clip.antiAlias,
            child: pdfUrl.isEmpty
                ? const Center(
                    child: Text('Dokumen PDF tidak ditemukan.'),
                  )
                : _pdfGagalMuat
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                width: 52,
                                height: 52,
                                decoration: const BoxDecoration(
                                  color: AppColors.errorLight,
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.picture_as_pdf_rounded, color: AppColors.error, size: 26),
                              ),
                              const SizedBox(height: 12),
                              const Text(
                                'Pratinjau PDF tidak dapat dimuat',
                                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                                textAlign: TextAlign.center,
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'Gunakan tombol Buka Layar Penuh atau Unduh PDF di bawah untuk tetap membaca dokumennya.',
                                style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary, height: 1.4),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        ),
                      )
                    : SfPdfViewer.network(
                        pdfUrl,
                        canShowScrollHead: true,
                        canShowScrollStatus: true,
                        enableDoubleTapZooming: true,
                        onDocumentLoaded: (details) {
                          if (_pdfGagalMuat && mounted) {
                            setState(() => _pdfGagalMuat = false);
                          }
                        },
                        onDocumentLoadFailed: (details) {
                          debugPrint('PDF load failed: ${details.description}');
                          if (mounted) {
                            setState(() => _pdfGagalMuat = true);
                          }
                        },
                      ),
          ),

          const SizedBox(height: 12),

          // Tombol Aksi: Baca Layar Penuh & Unduh File PDF
          Row(
            children: [
              // 1. Tombol Baca Layar Penuh
              Expanded(
                child: SizedBox(
                  height: 44,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => PdfViewerPage(
                            pdfUrl: pdfUrl,
                            title: judul,
                            downloadUrl: downloadUrl,
                          ),
                        ),
                      );
                    },
                    icon: const Icon(Icons.fullscreen_rounded, size: 20),
                    label: const Text(
                      'Buka Layar Penuh',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),

              // 2. Tombol Unduh File PDF
              SizedBox(
                height: 44,
                child: OutlinedButton.icon(
                  onPressed: () => _downloadPdfFile(downloadUrl, fileName),
                  icon: const Icon(Icons.download_rounded, size: 18, color: AppColors.primary),
                  label: const Text(
                    'Unduh PDF',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.primary),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: AppColors.primary, width: 1.4),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. KONTEN TEKS PELAJARAN (Hanya teks materi asli database)
  // ---------------------------------------------------------------------------
  Widget _buildReadingContentCard(Map<String, dynamic> konten) {
    final String judul = konten['judul'] ?? _detailMateri['judul_materi'] ?? 'Materi Pembelajaran';
    final String isi = konten['isi'] ?? '';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
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
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.article_rounded, color: AppColors.primary, size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
              ),
            ],
          ),
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Divider(color: AppColors.surfaceAlt, height: 1),
          ),
          Text(
            isi,
            style: const TextStyle(
              fontSize: 13.5,
              color: AppColors.textPrimary,
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // STICKY BOTTOM BAR (Kembali & Lanjut ke Kuis jika tersedia)
  // ---------------------------------------------------------------------------
  Widget _buildStickyBottomBar() {
    final Map<String, dynamic>? kuis = _detailMateri['kuis_lanjutan'] != null && _detailMateri['kuis_lanjutan'] is Map
        ? Map<String, dynamic>.from(_detailMateri['kuis_lanjutan'] as Map)
        : null;

    final int? idQuiz = kuis?['id_quiz'];

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
            Expanded(
              flex: idQuiz != null ? 4 : 10,
              child: SizedBox(
                height: 48,
                child: OutlinedButton.icon(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back_rounded, size: 18),
                  label: const Text(
                    'Kembali',
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(color: AppColors.border),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ),
            if (idQuiz != null) ...[
              const SizedBox(width: 12),
              Expanded(
                flex: 6,
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      // Pop up konfirmasi dulu di halaman ini; pindah ke
                      // halaman kuis hanya bila siswa menekan "Ya, Mulai Kuis".
                      final NavigatorState navigator = Navigator.of(context);
                      final bool mulai = await KonfirmasiKuisDialog.tampilkan(context, quizId: idQuiz);
                      if (!mulai) return;
                      navigator.push(
                        SmoothPageRoute(
                          page: KuisPage(
                            user: widget.user,
                            quizId: idQuiz,
                          ),
                          type: TransitionType.fadeThrough,
                        ),
                      );
                    },
                    icon: const Text('Lanjut ke Kuis', style: TextStyle(fontWeight: FontWeight.w700)),
                    label: const Icon(Icons.arrow_forward_rounded, size: 18),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
