import 'dart:convert';
import 'package:http/http.dart' as http;
import 'constants.dart';
import 'session.dart';

/// Klien HTTP terpusat untuk semua request ke backend Laravel.
///
/// Menyatukan tiga hal yang dulu tersebar/terlewat di tiap layar:
/// 1. Header standar (`Accept`, `Content-Type`).
/// 2. Header `Authorization: Bearer <token>` dari sesi Sanctum.
/// 3. Header `ngrok-skip-browser-warning` agar tunnel ngrok tidak
///    membalas halaman peringatan HTML.
class ApiClient {
  /// Header dasar untuk setiap request.
  static Map<String, String> get headers {
    final token = AppSession.token;
    return {
      'Accept': 'application/json',
      'ngrok-skip-browser-warning': 'true',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
  }

  /// Header untuk request yang mengirim body JSON.
  static Map<String, String> get jsonHeaders => {
        ...headers,
        'Content-Type': 'application/json',
      };

  /// Bangun URL absolut dari path relatif terhadap `AppConstants.baseUrl`.
  static Uri uri(String path) => Uri.parse('${AppConstants.baseUrl}$path');

  static Future<http.Response> get(
    String path, {
    Duration? timeout,
  }) {
    return http
        .get(uri(path), headers: headers)
        .timeout(timeout ?? AppConstants.timeoutDuration);
  }

  static Future<http.Response> post(
    String path, {
    Object? body,
    Duration? timeout,
  }) {
    return http
        .post(
          uri(path),
          headers: jsonHeaders,
          body: body == null ? null : jsonEncode(body),
        )
        .timeout(timeout ?? AppConstants.timeoutDuration);
  }
}
