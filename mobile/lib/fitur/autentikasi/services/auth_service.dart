import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:binaro_flutter/inti/layanan/api_client.dart';
import 'package:binaro_flutter/inti/layanan/session.dart';

/// Hasil autentikasi login siswa.
class AuthResult {
  final bool success;
  final Map<String, dynamic>? user;
  final String? message;

  const AuthResult({
    required this.success,
    this.user,
    this.message,
  });
}

/// Layanan autentikasi siswa (login & logout) yang memisahkan logika HTTP
/// dan penyimpanan sesi dari lapisan UI.
class AuthService {
  AuthService._();

  /// Login siswa dengan [nisn] dan [password].
  /// Menyimpan sesi & token terenkripsi ke [AppSession] bila berhasil.
  static Future<AuthResult> login({
    required String nisn,
    required String password,
  }) async {
    final response = await ApiClient.post(
      '/login',
      body: {'nisn': nisn, 'password': password},
    );

    Map<String, dynamic> decoded = {};
    try {
      final raw = jsonDecode(response.body);
      if (raw is Map<String, dynamic>) decoded = raw;
    } catch (_) {}

    if (response.statusCode == 200 && decoded['success'] == true) {
      final payload = decoded['data'];
      final rawUser = payload is Map ? payload['user'] : null;
      final user = rawUser is Map
          ? Map<String, dynamic>.from(rawUser)
          : <String, dynamic>{};
      final token = payload is Map ? payload['token']?.toString() : null;
      await AppSession.save(user, token: token);
      return AuthResult(success: true, user: user);
    }

    final fallback = kDebugMode
        ? 'Login gagal (HTTP ${response.statusCode}): '
            '${response.body.length > 150 ? response.body.substring(0, 150) : response.body}'
        : 'Login gagal. Periksa NISN & Kata Sandi.';

    return AuthResult(
      success: false,
      message: decoded['message']?.toString() ?? fallback,
    );
  }

  /// Logout siswa dari server (best-effort) dan bersihkan sesi lokal.
  static Future<void> logout() async {
    try {
      await ApiClient.post('/logout', timeout: const Duration(seconds: 5));
    } catch (e) {
      debugPrint('AuthService.logout error: $e');
    }
    await AppSession.clear();
  }
}
