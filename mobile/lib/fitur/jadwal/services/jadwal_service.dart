import 'package:flutter/foundation.dart';
import 'package:binaro_flutter/inti/layanan/api_client.dart';
import 'package:binaro_flutter/inti/layanan/app_preferences.dart';
import 'package:binaro_flutter/inti/layanan/local_database.dart';

/// Hasil pengambilan jadwal pelajaran, termasuk penanda bila berasal dari cache.
class JadwalFetchResult {
  final List<dynamic>? jadwal;
  final String? errorMessage;

  const JadwalFetchResult({this.jadwal, this.errorMessage});

  bool get isSuccess => jadwal != null;
}

/// Layanan pengambilan jadwal pelajaran (`GET /api/siswa/jadwal`)
/// dengan fallback cache SQLite saat offline.
class JadwalService {
  JadwalService._();

  static Future<JadwalFetchResult> fetchJadwal(int siswaId) async {
    final gunakanCache = await AppPreferences.getCacheJadwalOffline();

    try {
      final response = await ApiClient.get('/siswa/jadwal');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        final data = decoded['data'] as Map<String, dynamic>;
        final jadwalBaru = (data['jadwal'] as List<dynamic>?) ?? const [];
        if (gunakanCache) {
          try {
            await LocalDatabase.instance.saveJadwalCache(siswaId, jadwalBaru);
          } catch (e) {
            debugPrint('JadwalService.saveJadwalCache error: $e');
          }
        }
        return JadwalFetchResult(jadwal: jadwalBaru);
      }

      if (gunakanCache) {
        final cached = await _loadCache(siswaId);
        if (cached != null) return JadwalFetchResult(jadwal: cached);
      }
      return JadwalFetchResult(
        errorMessage:
            'Server menjawab dengan status ${response.statusCode}. Periksa koneksi lalu coba lagi.',
      );
    } catch (e) {
      debugPrint('JadwalService.fetchJadwal error: $e');
      if (gunakanCache) {
        final cached = await _loadCache(siswaId);
        if (cached != null) return JadwalFetchResult(jadwal: cached);
      }
      return const JadwalFetchResult(
        errorMessage:
            'Tidak bisa terhubung ke server. Periksa koneksi internet kamu lalu coba lagi.',
      );
    }
  }

  static Future<List<dynamic>?> _loadCache(int siswaId) async {
    try {
      return await LocalDatabase.instance.loadJadwalCache(siswaId);
    } catch (e) {
      debugPrint('JadwalService._loadCache error: $e');
      return null;
    }
  }
}
