import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Helper URL media & YouTube untuk fitur materi pembelajaran.
class YoutubeHelper {
  YoutubeHelper._();

  static final RegExp _iframeSrcRegex =
      RegExp('<iframe[^>]+src=["\']([^"\']+)["\']', caseSensitive: false);
  static final RegExp _youtubeIdRegex = RegExp(
    r'(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})',
    caseSensitive: false,
  );
  static final RegExp _rawIdRegex = RegExp(r'^[a-zA-Z0-9_-]{11}$');

  /// Ekstrak ID video YouTube (11 karakter) dari URL atau tag `<iframe>`.
  static String? extractYoutubeId(String? url) {
    if (url == null || url.trim().isEmpty) return null;
    String cleanUrl = url.trim();

    final iframeMatch = _iframeSrcRegex.firstMatch(cleanUrl);
    if (iframeMatch != null && iframeMatch.group(1) != null) {
      cleanUrl = iframeMatch.group(1)!;
    }

    final match = _youtubeIdRegex.firstMatch(cleanUrl);
    if (match != null && match.group(1) != null) {
      return match.group(1);
    }
    if (cleanUrl.length == 11 && _rawIdRegex.hasMatch(cleanUrl)) {
      return cleanUrl;
    }
    return null;
  }

  /// Bersihkan URL video mentah (termasuk `<iframe>`) menjadi URL yang dapat dibuka.
  static String cleanVideoUrl(String rawUrl) {
    if (rawUrl.trim().isEmpty) return '';
    final ytId = extractYoutubeId(rawUrl);
    if (ytId != null) {
      return 'https://www.youtube.com/watch?v=$ytId';
    }
    final iframeMatch = _iframeSrcRegex.firstMatch(rawUrl);
    if (iframeMatch != null && iframeMatch.group(1) != null) {
      return normalizeUrl(iframeMatch.group(1)!);
    }
    return normalizeUrl(rawUrl.trim());
  }

  /// Normalisasi URL relatif atau `localhost`/`127.0.0.1` ke origin backend aktif.
  static String normalizeUrl(String rawUrl) {
    if (rawUrl.isEmpty) return rawUrl;
    final String origin = Uri.parse(AppConstants.baseUrl).origin;
    if (rawUrl.startsWith('http://localhost') ||
        rawUrl.startsWith('http://127.0.0.1')) {
      return rawUrl.replaceFirst(
        RegExp(r'http://(localhost|127\.0\.0\.1)(:\d+)?'),
        origin,
      );
    }
    if (rawUrl.startsWith('/')) {
      return '$origin$rawUrl';
    }
    return rawUrl;
  }
}
