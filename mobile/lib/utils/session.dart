import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

/// Menyimpan sesi login siswa di penyimpanan lokal agar pengguna
/// tidak perlu login ulang setiap kali aplikasi dibuka.
class AppSession {
  static const String _key = 'binaro_sesi_siswa';

  /// Simpan data user hasil login.
  static Future<void> save(Map<String, dynamic> user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, jsonEncode(user));
  }

  /// Ambil sesi tersimpan; null bila belum pernah login.
  static Future<Map<String, dynamic>?> load() async {
    final prefs = await SharedPreferences.getInstance();
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

  /// Hapus sesi (dipanggil saat logout).
  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_key);
  }
}
