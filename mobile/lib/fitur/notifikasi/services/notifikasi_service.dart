import 'package:flutter/foundation.dart';
import 'package:binaro_flutter/inti/layanan/api_client.dart';

/// Data notifikasi siswa dari backend.
class NotifikasiData {
  final List<dynamic> items;
  final int belumDibaca;

  const NotifikasiData({
    required this.items,
    required this.belumDibaca,
  });
}

/// Layanan notifikasi PR siswa (`/siswa/notifikasi`).
class NotifikasiService {
  NotifikasiService._();

  static Future<int> fetchUnreadCount() async {
    try {
      final response = await ApiClient.get('/siswa/notifikasi');
      final decoded = ApiClient.decodeSuccessBody(response);
      if (decoded == null || decoded['data'] is! Map<String, dynamic>) {
        return 0;
      }
      final jumlah = decoded['data']['belum_dibaca'];
      return jumlah is num ? jumlah.toInt() : 0;
    } catch (e) {
      debugPrint('NotifikasiService.fetchUnreadCount error: $e');
      return 0;
    }
  }

  static Future<NotifikasiData?> fetchNotifikasi() async {
    final response = await ApiClient.get('/siswa/notifikasi');
    final decoded = ApiClient.decodeSuccessBody(response);
    if (decoded == null || decoded['data'] is! Map<String, dynamic>) {
      return null;
    }
    final data = decoded['data'] as Map<String, dynamic>;
    final belumDibaca = data['belum_dibaca'];
    return NotifikasiData(
      items: (data['notifikasi'] as List<dynamic>?) ?? const [],
      belumDibaca: belumDibaca is int ? belumDibaca : 0,
    );
  }

  static Future<void> markAllRead() async {
    try {
      await ApiClient.post('/siswa/notifikasi/baca');
    } catch (e) {
      debugPrint('NotifikasiService.markAllRead error: $e');
    }
  }
}
