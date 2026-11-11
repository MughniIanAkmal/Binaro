import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/models.dart';
import '../utils/constants.dart';

/// Centralized API service for interacting with the Laravel backend.
/// Follows Dart/Flutter coding standards and best practices.
class ApiService {
  static const String baseUrl = AppConstants.baseUrl;
  static const Duration timeoutDuration = AppConstants.timeoutDuration;

  /// Standard HTTP headers
  static Map<String, String> get defaultHeaders => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };

  // ==================== AUTHENTICATION ====================

  /// Login with username/nisn and password
  static Future<Map<String, dynamic>> login({
    required String nama,
    required String nisn,
    required String password,
  }) async {
    final response = await http
        .post(
          Uri.parse('$baseUrl/login'),
          headers: defaultHeaders,
          body: jsonEncode({
            'nama_lengkap': nama,
            'nisn': nisn,
            'password': password,
          }),
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  // ==================== DASHBOARD & PROFILE ====================

  /// Fetch dashboard data for a student
  static Future<Map<String, dynamic>> getDashboard(int siswaId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/dashboard?id_siswa=$siswaId'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  /// Fetch profile data for a student
  static Future<Map<String, dynamic>> getProfile(int siswaId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/profile?id_siswa=$siswaId'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  /// Update password for a student
  static Future<Map<String, dynamic>> updatePassword({
    required int siswaId,
    required String passwordLama,
    required String passwordBaru,
    required String konfirmasiPassword,
  }) async {
    final response = await http
        .post(
          Uri.parse('$baseUrl/siswa/update-password'),
          headers: defaultHeaders,
          body: jsonEncode({
            'id_siswa': siswaId,
            'password_lama': passwordLama,
            'password_baru': passwordBaru,
            'konfirmasi_password': konfirmasiPassword,
          }),
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  // ==================== MATA PELAJARAN & MATERI ====================

  /// Fetch all subjects (Mata Pelajaran)
  static Future<List<MapelModel>> getMapelList() async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/mapel'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      final List<dynamic> list = data['data']['mapel'] ?? data['data'];
      return list.map((m) => MapelModel.fromJson(m)).toList();
    }
    return [];
  }

  /// Fetch Bab list for a specific Mata Pelajaran
  static Future<List<BabModel>> getBabList(int mapelId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/mapel/$mapelId/bab'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      final List<dynamic> list = data['data']['babs'] ?? data['data'];
      return list.map((b) => BabModel.fromJson(b)).toList();
    }
    return [];
  }

  /// Fetch Sub-Bab list for a specific Bab
  static Future<List<SubBabModel>> getSubBabList(int babId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/bab/$babId/sub-bab'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      final List<dynamic> list = data['data']['sub_babs'] ?? data['data'];
      return list.map((sb) => SubBabModel.fromJson(sb)).toList();
    }
    return [];
  }

  /// Fetch Materi list for a specific Sub-Bab
  static Future<List<MateriModel>> getMateriList(int subBabId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/sub-bab/$subBabId/materi'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      final List<dynamic> list = data['data']['materis'] ?? data['data'];
      return list.map((m) => MateriModel.fromJson(m)).toList();
    }
    return [];
  }

  /// Fetch detail of a single Materi
  static Future<MateriModel?> getMateriDetail(int materiId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/materi/$materiId'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      return MateriModel.fromJson(data['data']);
    }
    return null;
  }

  // ==================== KUIS & LATIHAN ====================

  /// Fetch quiz detail and questions
  static Future<KuisModel?> getQuizDetail(int? quizId) async {
    final String url = quizId != null
        ? '$baseUrl/siswa/quiz/$quizId'
        : '$baseUrl/siswa/quiz';
    final response = await http
        .get(Uri.parse(url), headers: defaultHeaders)
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      return KuisModel.fromJson(data['data']);
    }
    return null;
  }

  /// Submit quiz answers
  static Future<Map<String, dynamic>> submitQuiz({
    required int quizId,
    required int siswaId,
    required Map<int, String> jawaban,
    required int sisaWaktuDetik,
  }) async {
    final response = await http
        .post(
          Uri.parse('$baseUrl/siswa/quiz/$quizId/submit'),
          headers: defaultHeaders,
          body: jsonEncode({
            'id_siswa': siswaId,
            'jawaban': jawaban.map((key, value) => MapEntry(key.toString(), value)),
            'sisa_waktu_detik': sisaWaktuDetik,
          }),
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  // ==================== UJIAN ====================

  /// Fetch exams list (tersedia & selesai)
  static Future<Map<String, dynamic>> getUjianList(int siswaId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/ujian?id_siswa=$siswaId'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  /// Fetch exam instructions (petunjuk)
  static Future<PetunjukUjianModel?> getUjianPetunjuk(int quizId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/ujian/$quizId/petunjuk'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      return PetunjukUjianModel.fromJson(data['data']);
    }
    return null;
  }

  /// Fetch exam questions to play
  static Future<Map<String, dynamic>> getUjianPlay(int quizId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/ujian/$quizId/play'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  /// Submit exam answers
  static Future<Map<String, dynamic>> submitUjian({
    required int quizId,
    required int siswaId,
    required Map<int, String> jawaban,
    required int sisaWaktuDetik,
  }) async {
    final response = await http
        .post(
          Uri.parse('$baseUrl/siswa/ujian/$quizId/submit'),
          headers: defaultHeaders,
          body: jsonEncode({
            'id_siswa': siswaId,
            'jawaban': jawaban.map((key, value) => MapEntry(key.toString(), value)),
            'sisa_waktu_detik': sisaWaktuDetik,
          }),
        )
        .timeout(timeoutDuration);
    return jsonDecode(response.body);
  }

  /// Fetch exam results
  static Future<HasilUjianModel?> getUjianResult(int quizId, int siswaId) async {
    final response = await http
        .get(
          Uri.parse('$baseUrl/siswa/ujian/$quizId/result?id_siswa=$siswaId'),
          headers: defaultHeaders,
        )
        .timeout(timeoutDuration);
    final data = jsonDecode(response.body);
    if (data['success'] == true && data['data'] != null) {
      return HasilUjianModel.fromJson(data['data']);
    }
    return null;
  }
}
