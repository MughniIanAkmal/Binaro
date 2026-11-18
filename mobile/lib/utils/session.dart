import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

/// Menyimpan sesi login siswa di penyimpanan lokal agar pengguna
/// tidak perlu login ulang setiap kali aplikasi dibuka.
///
/// Selain data user, token Sanctum juga disimpan agar request ke
/// endpoint ber-autentikasi (`auth:sanctum`) dapat dikirim dengan
/// header `Authorization: Bearer <token>`.
class AppSession {
  static const String _key = 'binaro_sesi_siswa';
  static const String _tokenKey = 'binaro_token_sanctum';

  /// Cache token di memori agar penyusunan header request tidak perlu
  /// menunggu pembacaan SharedPreferences setiap kali.
  static String? _token;

  /// Token Sanctum aktif (null bila belum login).
  static String? get token => _token;

  /// Simpan data user hasil login beserta token Sanctum.
  static Future<void> save(
    Map<String, dynamic> user, {
    String? token,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, jsonEncode(user));

    if (token != null && token.isNotEmpty) {
      _token = token;
      await prefs.setString(_tokenKey, token);
    }
  }

  /// Ambil sesi tersimpan; null bila belum pernah login.
  static Future<Map<String, dynamic>?> load() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);

    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) return null;
    try {
      final decoded = jsonDecode(raw);
      return decoded is Map<String, dynamic> ? decoded : null;
    } catch (_) {
      await clear();
      return null;
    }
  }

  /// Hapus sesi dan token (dipanggil saat logout).
  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    _token = null;
    await prefs.remove(_key);
    await prefs.remove(_tokenKey);
  }
}
