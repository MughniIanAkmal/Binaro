import 'package:flutter/foundation.dart';
import 'package:binaro_flutter/inti/layanan/api_client.dart';
import 'package:binaro_flutter/inti/layanan/app_preferences.dart';
import 'package:binaro_flutter/inti/layanan/local_database.dart';

/// Layanan API dan draft lokal untuk fitur Ujian & Kuis siswa.
class UjianService {
  UjianService._();

  static Future<Map<String, dynamic>?> fetchUjianList() async {
    try {
      final response = await ApiClient.get('/siswa/ujian');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        return Map<String, dynamic>.from(decoded['data'] as Map);
      }
    } catch (e) {
      debugPrint('UjianService.fetchUjianList error: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> fetchPetunjuk(int quizId) async {
    try {
      final response = await ApiClient.get('/siswa/ujian/$quizId/petunjuk');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        return Map<String, dynamic>.from(decoded['data'] as Map);
      }
    } catch (e) {
      debugPrint('UjianService.fetchPetunjuk error: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> fetchExamQuestions(int quizId) async {
    try {
      final response = await ApiClient.get('/siswa/ujian/$quizId/play');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        return Map<String, dynamic>.from(decoded['data'] as Map);
      }
    } catch (e) {
      debugPrint('UjianService.fetchExamQuestions error: $e');
    }
    return null;
  }

  static Future<void> submitExam({
    required int quizId,
    required Map<String, String> jawaban,
    required int waktuMenit,
  }) async {
    try {
      await ApiClient.post(
        '/siswa/ujian/$quizId/submit',
        body: {
          'jawaban': jawaban,
          'waktu_menit': waktuMenit,
        },
        timeout: const Duration(seconds: 10),
      );
      await LocalDatabase.instance.clearUjianDraft(quizId);
    } catch (e) {
      debugPrint('UjianService.submitExam error: $e');
    }
  }

  static Future<Map<String, dynamic>?> fetchResult(int quizId) async {
    try {
      final response = await ApiClient.get('/siswa/ujian/$quizId/result');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        return Map<String, dynamic>.from(decoded['data'] as Map);
      }
    } catch (e) {
      debugPrint('UjianService.fetchResult error: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> fetchQuiz(int quizId) async {
    try {
      final response = await ApiClient.get('/siswa/quiz/$quizId');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded != null && decoded['data'] is Map<String, dynamic>) {
        return Map<String, dynamic>.from(decoded['data'] as Map);
      }
    } catch (e) {
      debugPrint('UjianService.fetchQuiz error: $e');
    }
    return null;
  }

  static Future<void> submitQuiz({
    required int quizId,
    required Map<String, String> jawaban,
    required int waktuMenit,
  }) async {
    try {
      await ApiClient.post(
        '/siswa/quiz/$quizId/submit',
        body: {
          'jawaban': jawaban,
          'waktu_menit': waktuMenit,
        },
        timeout: const Duration(seconds: 10),
      );
    } catch (e) {
      debugPrint('UjianService.submitQuiz error: $e');
    }
  }

  /// Muat draft ujian dari SQLite bila fitur simpan draft diaktifkan.
  static Future<UjianDraftData?> loadDraftIfEnabled(int quizId) async {
    final enabled = await AppPreferences.getSimpanDraftUjian();
    if (!enabled) return null;
    try {
      return await LocalDatabase.instance.loadUjianDraft(quizId);
    } catch (e) {
      debugPrint('UjianService.loadDraftIfEnabled error: $e');
      return null;
    }
  }
}
