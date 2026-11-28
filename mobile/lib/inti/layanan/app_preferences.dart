import 'package:shared_preferences/shared_preferences.dart';

/// Pengelola preferensi pengguna berbasis [SharedPreferences] untuk
/// pengaturan ringan aplikasi (flag UI dan perilaku offline/draft).
class AppPreferences {
  AppPreferences._();

  static const String _keyAutoConfirmPetunjuk = 'pref_auto_confirm_petunjuk';
  static const String _keySimpanDraftUjian = 'pref_simpan_draft_ujian';
  static const String _keyCacheJadwalOffline = 'pref_cache_jadwal_offline';

  static SharedPreferences? _cachedPrefs;

  static Future<SharedPreferences> _instance() async {
    return _cachedPrefs ??= await SharedPreferences.getInstance();
  }

  static Future<bool> _getBool(String key, bool defaultValue) async {
    final prefs = await _instance();
    return prefs.getBool(key) ?? defaultValue;
  }

  static Future<void> _setBool(String key, bool value) async {
    final prefs = await _instance();
    await prefs.setBool(key, value);
  }

  /// Apakah checkbox "Saya sudah siap ujian!" otomatis tercentang.
  static Future<bool> getAutoConfirmPetunjuk() =>
      _getBool(_keyAutoConfirmPetunjuk, false);

  /// Simpan preferensi otomatis centang petunjuk ujian.
  static Future<void> setAutoConfirmPetunjuk(bool value) =>
      _setBool(_keyAutoConfirmPetunjuk, value);

  /// Apakah jawaban ujian disimpan otomatis sebagai draft di SQLite.
  static Future<bool> getSimpanDraftUjian() =>
      _getBool(_keySimpanDraftUjian, true);

  /// Simpan preferensi auto-save draft ujian.
  static Future<void> setSimpanDraftUjian(bool value) =>
      _setBool(_keySimpanDraftUjian, value);

  /// Apakah jadwal pelajaran di-cache ke SQLite untuk akses offline.
  static Future<bool> getCacheJadwalOffline() =>
      _getBool(_keyCacheJadwalOffline, true);

  /// Simpan preferensi cache jadwal offline.
  static Future<void> setCacheJadwalOffline(bool value) =>
      _setBool(_keyCacheJadwalOffline, value);

  /// Kembalikan seluruh preferensi ke nilai awal (default).
  static Future<void> resetAll() async {
    final prefs = await _instance();
    await prefs.remove(_keyAutoConfirmPetunjuk);
    await prefs.remove(_keySimpanDraftUjian);
    await prefs.remove(_keyCacheJadwalOffline);
  }
}
