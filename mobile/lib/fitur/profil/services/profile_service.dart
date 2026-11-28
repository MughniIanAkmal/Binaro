import 'dart:convert';
import 'dart:typed_data';
import 'package:http/http.dart' as http;
import 'package:binaro_flutter/inti/layanan/api_client.dart';
import 'package:binaro_flutter/inti/layanan/session.dart';

/// Hasil pengambilan data profil siswa.
class ProfileFetchResult {
  final Map<String, dynamic>? siswa;
  final Map<String, dynamic>? rekapAbsensi;
  final Map<String, dynamic>? dataSekolah;
  final String? versi;
  final String? errorMessage;

  const ProfileFetchResult({
    this.siswa,
    this.rekapAbsensi,
    this.dataSekolah,
    this.versi,
    this.errorMessage,
  });

  bool get isSuccess => siswa != null;
}

/// Hasil aksi profil (upload/hapus foto, ubah kata sandi).
class ProfileActionResult {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;

  const ProfileActionResult({
    required this.success,
    required this.message,
    this.data,
  });
}

/// Layanan API untuk fitur profil siswa.
class ProfileService {
  ProfileService._();

  static Future<ProfileFetchResult> fetchProfile() async {
    try {
      final response = await ApiClient.get('/siswa/profile');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        final data = decoded['data'] as Map<String, dynamic>;
        return ProfileFetchResult(
          siswa: data['siswa'] is Map<String, dynamic>
              ? Map<String, dynamic>.from(data['siswa'] as Map)
              : const {},
          rekapAbsensi: data['rekap_absensi'] is Map<String, dynamic>
              ? Map<String, dynamic>.from(data['rekap_absensi'] as Map)
              : const {},
          dataSekolah: data['data_sekolah'] is Map<String, dynamic>
              ? Map<String, dynamic>.from(data['data_sekolah'] as Map)
              : const {},
          versi: data['versi']?.toString(),
        );
      }
      return ProfileFetchResult(
        errorMessage:
            'Server menjawab dengan status ${response.statusCode}. Periksa koneksi lalu coba lagi.',
      );
    } catch (_) {
      return const ProfileFetchResult(
        errorMessage:
            'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.',
      );
    }
  }

  static Future<ProfileActionResult> uploadPhoto({
    required Uint8List bytes,
    required String filename,
  }) async {
    try {
      final request =
          http.MultipartRequest('POST', ApiClient.uri('/siswa/foto-profil'));
      request.headers.addAll(ApiClient.headers);
      request.files.add(
        http.MultipartFile.fromBytes('foto', bytes, filename: filename),
      );
      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);
      final result = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && result['success'] == true) {
        return ProfileActionResult(
          success: true,
          message: 'Foto profil berhasil diperbarui',
          data: result['data'] is Map<String, dynamic>
              ? Map<String, dynamic>.from(result['data'] as Map)
              : null,
        );
      }
      return ProfileActionResult(
        success: false,
        message:
            result['message']?.toString() ?? 'Gagal mengunggah foto profil',
      );
    } catch (_) {
      return const ProfileActionResult(
        success: false,
        message: 'Tidak dapat terhubung ke server. Coba lagi ya.',
      );
    }
  }

  static Future<ProfileActionResult> deletePhoto() async {
    try {
      final response = await ApiClient.post('/siswa/foto-profil/hapus');
      final result = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode == 200 && result['success'] == true) {
        return const ProfileActionResult(
          success: true,
          message: 'Foto profil berhasil dihapus',
        );
      }
      return ProfileActionResult(
        success: false,
        message: result['message']?.toString() ?? 'Gagal menghapus foto',
      );
    } catch (_) {
      return const ProfileActionResult(
        success: false,
        message: 'Tidak dapat terhubung ke server. Coba lagi ya.',
      );
    }
  }

  static Future<ProfileActionResult> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await ApiClient.post(
        '/siswa/update-password',
        body: {
          'password_lama': currentPassword,
          'password_baru': newPassword,
          'konfirmasi_password': confirmPassword,
        },
      );
      final result = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode == 200 && result['success'] == true) {
        return ProfileActionResult(
          success: true,
          message: result['message']?.toString() ??
              'Kata sandi berhasil diperbarui!',
        );
      }
      return ProfileActionResult(
        success: false,
        message:
            result['message']?.toString() ?? 'Gagal mengubah kata sandi',
      );
    } catch (_) {
      return const ProfileActionResult(
        success: false,
        message: 'Tidak dapat terhubung ke server. Coba lagi ya.',
      );
    }
  }

  /// Sinkronkan URL foto profil ke sesi lokal agar halaman lain ikut terbarui.
  static Future<void> syncSessionPhoto({
    required dynamic fotoProfil,
    required dynamic fotoUrl,
  }) async {
    final user = await AppSession.load();
    if (user == null) return;
    user['foto_profil'] = fotoProfil;
    user['foto_url'] = fotoUrl;
    await AppSession.save(user, token: AppSession.token);
  }
}
