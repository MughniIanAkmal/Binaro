import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Menyimpan sesi login siswa di penyimpanan lokal agar pengguna
/// tidak perlu login ulang setiap kali aplikasi dibuka.
///
/// Data profil non-sensitif disimpan di [SharedPreferences], sedangkan
/// token Sanctum disimpan di [FlutterSecureStorage] (terenkripsi).
class AppSession {
  AppSession._();

  static const String _key = 'binaro_sesi_siswa';
  static const String _tokenKey = 'binaro_token_sanctum';

  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage();

  /// Cache token di memori agar penyusunan header request tidak perlu
  /// menunggu pembacaan storage setiap kali.
  static String? _token;

  /// Token Sanctum aktif (null bila belum login).
  static String? get token => _token;

  /// True bila terdapat token Sanctum aktif di memori.
  static bool get isLoggedIn => _token != null && _token!.isNotEmpty;

  /// Simpan data user hasil login beserta token Sanctum terenkripsi.
  static Future<void> save(
    Map<String, dynamic> user, {
    String? token,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, jsonEncode(user));

    if (token != null && token.isNotEmpty) {
      _token = token;
      await _secureStorage.write(key: _tokenKey, value: token);
      if (prefs.containsKey(_tokenKey)) {
        await prefs.remove(_tokenKey);
      }
    }
  }

  /// Ambil sesi tersimpan; null bila belum pernah login.
  static Future<Map<String, dynamic>?> load() async {
    final prefs = await SharedPreferences.getInstance();

    String? storedToken = await _secureStorage.read(key: _tokenKey);
    // Migrasi otomatis token lama dari SharedPreferences ke SecureStorage.
    if (storedToken == null || storedToken.isEmpty) {
      final legacyToken = prefs.getString(_tokenKey);
      if (legacyToken != null && legacyToken.isNotEmpty) {
        storedToken = legacyToken;
        await _secureStorage.write(key: _tokenKey, value: legacyToken);
        await prefs.remove(_tokenKey);
      }
    }
    _token = storedToken;

    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) return null;
    try {
      final decoded = jsonDecode(raw);
      return decoded is Map<String, dynamic> ? decoded : null;
    } catch (e) {
      debugPrint('AppSession.load decode error: $e');
      await clear();
      return null;
    }
  }

  /// Hapus sesi dan token terenkripsi (dipanggil saat logout).
  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    _token = null;
    await _secureStorage.delete(key: _tokenKey);
    await prefs.remove(_key);
    await prefs.remove(_tokenKey);
  }
}
