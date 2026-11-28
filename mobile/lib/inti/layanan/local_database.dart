import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

/// Data draft ujian yang dipulihkan dari SQLite.
class UjianDraftData {
  final Map<int, String> answers;
  final Set<int> raguRagu;

  const UjianDraftData({
    required this.answers,
    required this.raguRagu,
  });
}

/// Pengelola database SQLite lokal untuk penyimpanan data terstruktur:
/// 1. `ujian_drafts` — draft jawaban & tanda ragu-ragu siswa saat ujian.
/// 2. `jadwal_cache` — cache jadwal pelajaran siswa untuk fallback offline.
class LocalDatabase {
  LocalDatabase._();
  static final LocalDatabase instance = LocalDatabase._();

  static const String _dbName = 'binaro_local.db';
  static const int _dbVersion = 1;
  static const String _tableUjianDrafts = 'ujian_drafts';
  static const String _tableJadwalCache = 'jadwal_cache';

  Database? _db;

  Future<Database> get database async {
    if (_db != null) return _db!;
    final dbPath = await getDatabasesPath();
    final fullPath = p.join(dbPath, _dbName);
    _db = await openDatabase(
      fullPath,
      version: _dbVersion,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE $_tableUjianDrafts (
            quiz_id INTEGER NOT NULL,
            soal_index INTEGER NOT NULL,
            jawaban TEXT,
            is_ragu INTEGER NOT NULL DEFAULT 0,
            updated_at INTEGER NOT NULL,
            PRIMARY KEY (quiz_id, soal_index)
          )
        ''');

        await db.execute('''
          CREATE TABLE $_tableJadwalCache (
            siswa_id INTEGER PRIMARY KEY,
            payload TEXT NOT NULL,
            updated_at INTEGER NOT NULL
          )
        ''');
      },
      onUpgrade: (db, oldVersion, newVersion) async {
        // Siap untuk migrasi skema di versi mendatang.
      },
    );
    return _db!;
  }

  /// Simpan atau perbarui draft jawaban dan status ragu-ragu untuk satu soal.
  Future<void> saveUjianDraft({
    required int quizId,
    required int soalIndex,
    String? jawaban,
    required bool isRagu,
  }) async {
    final db = await database;
    await db.insert(
      _tableUjianDrafts,
      {
        'quiz_id': quizId,
        'soal_index': soalIndex,
        'jawaban': jawaban,
        'is_ragu': isRagu ? 1 : 0,
        'updated_at': DateTime.now().millisecondsSinceEpoch,
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Muat seluruh draft jawaban dan tanda ragu-ragu untuk [quizId].
  Future<UjianDraftData> loadUjianDraft(int quizId) async {
    final db = await database;
    final rows = await db.query(
      _tableUjianDrafts,
      where: 'quiz_id = ?',
      whereArgs: [quizId],
    );

    final answers = <int, String>{};
    final raguRagu = <int>{};

    for (final row in rows) {
      final index = row['soal_index'] as int?;
      if (index == null) continue;
      final jawaban = row['jawaban'] as String?;
      if (jawaban != null && jawaban.isNotEmpty) {
        answers[index] = jawaban;
      }
      if ((row['is_ragu'] as int? ?? 0) == 1) {
        raguRagu.add(index);
      }
    }

    return UjianDraftData(answers: answers, raguRagu: raguRagu);
  }

  /// Hapus semua draft ujian setelah ujian berhasil dikumpulkan.
  Future<void> clearUjianDraft(int quizId) async {
    final db = await database;
    await db.delete(
      _tableUjianDrafts,
      where: 'quiz_id = ?',
      whereArgs: [quizId],
    );
  }

  /// Simpan cache jadwal pelajaran siswa dalam format JSON.
  Future<void> saveJadwalCache(int siswaId, List<dynamic> jadwal) async {
    final db = await database;
    await db.insert(
      _tableJadwalCache,
      {
        'siswa_id': siswaId,
        'payload': jsonEncode(jadwal),
        'updated_at': DateTime.now().millisecondsSinceEpoch,
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Muat cache jadwal pelajaran siswa; null bila belum pernah di-cache.
  Future<List<dynamic>?> loadJadwalCache(int siswaId) async {
    final db = await database;
    final rows = await db.query(
      _tableJadwalCache,
      where: 'siswa_id = ?',
      whereArgs: [siswaId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    final raw = rows.first['payload'] as String?;
    if (raw == null || raw.isEmpty) return null;
    try {
      final decoded = jsonDecode(raw);
      return decoded is List<dynamic> ? decoded : null;
    } catch (e) {
      debugPrint('LocalDatabase.loadJadwalCache decode error: $e');
      return null;
    }
  }

  /// Tutup koneksi database lokal bila diperlukan.
  Future<void> close() async {
    await _db?.close();
    _db = null;
  }
}
