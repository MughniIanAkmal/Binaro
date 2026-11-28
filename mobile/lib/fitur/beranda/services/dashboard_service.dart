import 'package:binaro_flutter/inti/layanan/api_client.dart';

/// Data ringkasan beranda siswa.
class DashboardData {
  final Map<String, dynamic>? siswa;
  final List<dynamic> tugas;
  final List<dynamic> mapel;

  const DashboardData({
    this.siswa,
    required this.tugas,
    required this.mapel,
  });
}

/// Hasil pengambilan data dashboard beserta pesan error bila gagal.
class DashboardFetchResult {
  final DashboardData? data;
  final String? errorMessage;

  const DashboardFetchResult({this.data, this.errorMessage});
}

/// Layanan pengambilan data beranda siswa (`GET /api/siswa/dashboard`).
class DashboardService {
  DashboardService._();

  static Future<DashboardFetchResult> fetchDashboard() async {
    try {
      final response = await ApiClient.get('/siswa/dashboard');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        final data = decoded['data'] as Map<String, dynamic>;
        return DashboardFetchResult(
          data: DashboardData(
            siswa: data['siswa'] is Map<String, dynamic>
                ? Map<String, dynamic>.from(data['siswa'] as Map)
                : null,
            tugas: (data['tugas'] as List<dynamic>?) ?? const [],
            mapel: (data['mata_pelajaran'] as List<dynamic>?) ?? const [],
          ),
        );
      }
      return DashboardFetchResult(
        errorMessage:
            'Server menjawab dengan status ${response.statusCode}. Periksa koneksi lalu coba lagi.',
      );
    } catch (_) {
      return const DashboardFetchResult(
        errorMessage:
            'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.',
      );
    }
  }
}
