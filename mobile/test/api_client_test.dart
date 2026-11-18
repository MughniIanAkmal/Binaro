import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:binaro_flutter/utils/api_client.dart';
import 'package:binaro_flutter/utils/constants.dart';
import 'package:binaro_flutter/utils/session.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  test('tanpa sesi: header tanpa Authorization', () async {
    await AppSession.load();
    final headers = ApiClient.headers;

    expect(headers['Accept'], 'application/json');
    expect(headers['ngrok-skip-browser-warning'], 'true');
    expect(headers.containsKey('Authorization'), isFalse);
  });

  test('setelah login: token dipakai pada header Authorization', () async {
    await AppSession.save(
      {'id_siswa': 1, 'nm_siswa': 'Budi Santoso'},
      token: '1|abc123',
    );

    expect(AppSession.token, '1|abc123');
    expect(ApiClient.headers['Authorization'], 'Bearer 1|abc123');
    expect(ApiClient.jsonHeaders['Content-Type'], 'application/json');
  });

  test('token tetap tersedia setelah aplikasi dibuka ulang', () async {
    await AppSession.save({'id_siswa': 1}, token: '1|persisted');

    // Simulasi proses baru: cache memori kosong, dibaca ulang dari storage.
    final sesi = await AppSession.load();

    expect(sesi, isNotNull);
    expect(AppSession.token, '1|persisted');
    expect(ApiClient.headers['Authorization'], 'Bearer 1|persisted');
  });

  test('logout menghapus token dari header', () async {
    await AppSession.save({'id_siswa': 1}, token: '1|abc123');
    await AppSession.clear();

    expect(AppSession.token, isNull);
    expect(ApiClient.headers.containsKey('Authorization'), isFalse);
  });

  test('uri() membangun URL dari baseUrl', () {
    final uri = ApiClient.uri('/siswa/mapel');

    expect(uri.path, '/api/siswa/mapel');
    expect(uri.toString(), '${AppConstants.baseUrl}/siswa/mapel');
  });
}
