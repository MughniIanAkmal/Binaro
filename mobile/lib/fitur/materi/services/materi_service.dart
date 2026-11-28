import 'package:flutter/foundation.dart';
import 'package:binaro_flutter/inti/layanan/api_client.dart';

/// Layanan pengambilan data pembelajaran siswa (mapel, bab, sub-bab, materi).
class MateriService {
  MateriService._();

  static Future<Map<String, dynamic>?> fetchDetailMateri(int materiId) async {
    try {
      final response = await ApiClient.get('/siswa/materi/$materiId');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        return Map<String, dynamic>.from(decoded['data'] as Map);
      }
    } catch (e) {
      debugPrint('MateriService.fetchDetailMateri error: $e');
    }
    return null;
  }

  static Future<List<dynamic>?> fetchList(String endpoint, String dataKey) async {
    try {
      final response = await ApiClient.get(endpoint);
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        final list = decoded['data'][dataKey];
        return list is List<dynamic> ? list : const [];
      }
    } catch (e) {
      debugPrint('MateriService.fetchList($endpoint) error: $e');
    }
    return null;
  }
}
