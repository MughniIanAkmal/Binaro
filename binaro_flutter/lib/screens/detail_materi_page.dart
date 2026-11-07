import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import 'package:syncfusion_flutter_pdfviewer/pdfviewer.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_platform_interface/webview_flutter_platform_interface.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';
import 'kuis_page.dart';
import 'pdf_viewer_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
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
  Map<String, dynamic> _detailMateri = {};
  YoutubePlayerController? _ytController;
  StreamSubscription<YoutubePlayerValue>? _ytSubscription;
  String? _loadedVideoId;

  bool get _canUseInAppPlayer => WebViewPlatform.instance != null && _ytController != null;

  static const String _baseUrl = AppConstants.baseUrl;

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

      // Jika platform webview tidak tersedia di runtime, jangan inisialisasi controller agar tidak melempar assertion error
      if (WebViewPlatform.instance == null) {
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
            privacyEnhancedMode: false,
            origin: 'https://www.youtube.com',
            strictRelatedVideos: true,
            playsInline: true,
          ),
        );
        _ytSubscription = _ytController!.listen((event) {
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
        // Fallback cerdas: buka pemutar video internal yang responsif dan fullscreen
        _launchMediaUrl(url, forceInApp: true);
      }
    } catch (_) {
      _launchMediaUrl(url);
    }
  }

  void _openFullScreenVideo(String url, String judul) {
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
      _ytController!.enterFullScreen();
    } else {
      // Buka browser view layar penuh jika kontroler webview belum terpasang
      _launchMediaUrl(url, forceInApp: true);
    }
  }

  Future<void> _fetchDetailMateri() async {
    final int materiId = widget.materi['id_materi'] ?? 20;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/materi/$materiId'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'] ?? {};
          setState(() {
            _detailMateri = data;
            _isLoading = false;
          });
          final String? videoUrl = data['video']?['url'];
          if (videoUrl != null && videoUrl.isNotEmpty) {
            _initYoutubePlayer(videoUrl);
          }
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _detailMateri = {};
        _isLoading = false;
      });
    }
  }

  String? _extractYoutubeId(String? url) {
    if (url == null || url.trim().isEmpty) return null;
    final cleanUrl = url.trim();
    final regExp = RegExp(
      r'(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})',
      caseSensitive: false,
    );
    final match = regExp.firstMatch(cleanUrl);
    if (match != null && match.group(1) != null) {
      return match.group(1);
    }
    if (cleanUrl.length == 11 && RegExp(r'^[a-zA-Z0-9_-]{11}$').hasMatch(cleanUrl)) {
      return cleanUrl;
    }
    return null;
  }

  String _normalizeUrl(String rawUrl) {
    if (rawUrl.isEmpty) return rawUrl;
    if (rawUrl.startsWith('http://localhost') || rawUrl.startsWith('http://127.0.0.1')) {
      final baseUri = Uri.parse(_baseUrl);
      return rawUrl.replaceFirst(
        RegExp(r'http://(localhost|127\.0\.0\.1)(:\d+)?'),
        '${baseUri.scheme}://${baseUri.host}:${baseUri.port}',
      );
    }
    if (rawUrl.startsWith('/')) {
      final baseUri = Uri.parse(_baseUrl);
      return '${baseUri.scheme}://${baseUri.host}:${baseUri.port}$rawUrl';
    }
    return rawUrl;
  }

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
    final Uri uri = Uri.parse(url);
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
                      color: Color(0xFFFFA000),
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
      body: _isLoading && _detailMateri.isEmpty
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF1D5A86)))
          : _detailMateri.isEmpty
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
                      child: RefreshIndicator(
                        onRefresh: _fetchDetailMateri,
                        color: const Color(0xFF1D5A86),
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

                    // Sticky Bottom Bar: Kembali & Lanjut Kuis (jika ada kuis)
                    _buildStickyBottomBar(),
                  ],
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
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Row(
              children: [
                Icon(icon, size: 14, color: const Color(0xFF1D5A86)),
                const SizedBox(width: 5),
                Text(
                  mapel,
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
              ],
            ),
          ),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 6),
            child: Icon(Icons.chevron_right_rounded, size: 16, color: Color(0xFF94A3B8)),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              bab,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: Color(0xFF475569),
              ),
            ),
          ),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 6),
            child: Icon(Icons.chevron_right_rounded, size: 16, color: Color(0xFF94A3B8)),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              subBab,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: Color(0xFF475569),
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
    final String url = video['url'] ?? '';
    final String? ytId = _extractYoutubeId(url);
    final bool isPlaying = _ytController?.value.playerState == PlayerState.playing;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white, // Kartu Putih Bersih sesuai tema aplikasi
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1D5A86).withValues(alpha: 0.05),
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
              color: const Color(0xFF0F263B), // Navy blue player screen
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
                          enableFullScreenOnVerticalDrag: false,
                        ),
                        // Overlay Tombol Putar saat video belum berputar
                        if (!isPlaying && (_ytController?.value.playerState != PlayerState.buffering))
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
                                        colors: [Color(0xFFFFA000), Color(0xFFF59E0B)],
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                      ),
                                      shape: BoxShape.circle,
                                      boxShadow: [
                                        BoxShadow(
                                          color: const Color(0xFFFFA000).withValues(alpha: 0.6),
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
                                colors: [Color(0xFFFFA000), Color(0xFFF59E0B)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFFFFA000).withValues(alpha: 0.55),
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
                                borderRadius: BorderRadius.circular(6),
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
                          color: Color(0xFF1E293B),
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
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'Video Pembelajaran SD',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF1D5A86)),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: isPlaying ? const Color(0xFFDCFCE7) : const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  isPlaying ? Icons.play_arrow_rounded : Icons.check_circle_outline_rounded,
                                  size: 12,
                                  color: isPlaying ? const Color(0xFF16A34A) : const Color(0xFFD97706),
                                ),
                                const SizedBox(width: 3),
                                Text(
                                  isPlaying ? 'Sedang Memutar' : 'Siap Diputar',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                    color: isPlaying ? const Color(0xFF16A34A) : const Color(0xFFD97706),
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
              // 1. Tombol Putar / Jeda
              Expanded(
                child: SizedBox(
                  height: 44,
                  child: ElevatedButton.icon(
                    onPressed: () => _handleVideoAction(url, judul),
                    icon: Icon(
                      isPlaying ? Icons.pause_circle_filled_rounded : Icons.play_circle_fill_rounded,
                      size: 20,
                    ),
                    label: Text(
                      isPlaying ? 'Jeda Video' : 'Putar di Aplikasi',
                      style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isPlaying ? const Color(0xFF1D5A86) : const Color(0xFFFFA000),
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
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: IconButton(
                  icon: const Icon(Icons.fullscreen_rounded, color: Color(0xFF1D5A86), size: 24),
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
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: IconButton(
                  icon: const Icon(Icons.open_in_new_rounded, color: Color(0xFF475569), size: 20),
                  tooltip: 'Buka di YouTube',
                  onPressed: () => _launchMediaUrl(url),
                ),
              ),
            ],
          ),
          if (_ytController?.value.error != null && _ytController!.value.error != YoutubeError.none) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF2F2),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFECACA)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline_rounded, size: 16, color: Color(0xFFDC2626)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Pemilik video membatasi pemutaran tertentu (${_ytController!.value.error.name}). Gunakan tombol "Buka di YouTube" di samping jika diperlukan.',
                      style: const TextStyle(fontSize: 11, color: Color(0xFFDC2626), fontWeight: FontWeight.w600),
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
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1D5A86).withValues(alpha: 0.05),
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
                  color: const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.picture_as_pdf_rounded, color: Color(0xFFDC2626), size: 24),
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
                        color: Color(0xFF1E293B),
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
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(5),
                          ),
                          child: const Text(
                            'Dokumen PDF',
                            style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Color(0xFF1D5A86)),
                          ),
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'Terbuka di Aplikasi',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF16A34A)),
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
            height: 380,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFCBD5E1), width: 1.2),
              color: const Color(0xFFF8FAFC),
            ),
            clipBehavior: Clip.antiAlias,
            child: pdfUrl.isNotEmpty
                ? SfPdfViewer.network(
                    pdfUrl,
                    canShowScrollHead: true,
                    canShowScrollStatus: true,
                    enableDoubleTapZooming: true,
                    onDocumentLoadFailed: (details) {
                      debugPrint('PDF load failed: ${details.description}');
                    },
                  )
                : const Center(
                    child: Text('Dokumen PDF tidak ditemukan.'),
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
                      backgroundColor: const Color(0xFF1D5A86),
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
                  icon: const Icon(Icons.download_rounded, size: 18, color: Color(0xFF1D5A86)),
                  label: const Text(
                    'Unduh PDF',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1D5A86)),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFF1D5A86), width: 1.4),
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
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
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
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.article_rounded, color: Color(0xFF1D5A86), size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
              ),
            ],
          ),
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Divider(color: Color(0xFFF1F5F9), height: 1),
          ),
          Text(
            isi,
            style: const TextStyle(
              fontSize: 13.5,
              color: Color(0xFF334155),
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
                    foregroundColor: const Color(0xFF1D5A86),
                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
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
                    onPressed: () {
                      Navigator.push(
                        context,
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
                      backgroundColor: const Color(0xFF1D5A86),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
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
