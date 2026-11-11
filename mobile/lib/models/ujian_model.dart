class UjianModel {
  final int id;
  final String mapel;
  final String status;
  final String statusColor;
  final String badgeJadwal;
  final String judul;
  final String guru;
  final int durasiMenit;
  final String durasiText;
  final int totalSoal;
  final String soalText;
  final bool isUnlocked;

  const UjianModel({
    required this.id,
    required this.mapel,
    this.status = 'Bisa Dikerjakan',
    this.statusColor = 'green',
    this.badgeJadwal = 'Hari Ini',
    required this.judul,
    this.guru = 'Guru Pengampu',
    this.durasiMenit = 60,
    this.durasiText = '60 Menit',
    this.totalSoal = 20,
    this.soalText = '20 Soal Pilihan Ganda',
    this.isUnlocked = true,
  });

  factory UjianModel.fromJson(Map<String, dynamic> json) {
    return UjianModel(
      id: int.tryParse(json['id']?.toString() ?? json['id_quiz']?.toString() ?? '0') ?? 0,
      mapel: json['mapel'] ?? '',
      status: json['status'] ?? 'Bisa Dikerjakan',
      statusColor: json['status_color'] ?? 'green',
      badgeJadwal: json['badge_jadwal'] ?? 'Hari Ini',
      judul: json['judul'] ?? json['judul_quiz'] ?? '',
      guru: json['guru'] ?? 'Guru Pengampu',
      durasiMenit: int.tryParse(json['durasi_menit']?.toString() ?? '60') ?? 60,
      durasiText: json['durasi_text'] ?? '${json['durasi_menit'] ?? 60} Menit',
      totalSoal: int.tryParse(json['total_soal']?.toString() ?? '20') ?? 20,
      soalText: json['soal_text'] ?? '${json['total_soal'] ?? 20} Soal Pilihan Ganda',
      isUnlocked: json['is_unlocked'] == true || json['is_unlocked'] == 1,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'mapel': mapel,
      'status': status,
      'status_color': statusColor,
      'badge_jadwal': badgeJadwal,
      'judul': judul,
      'guru': guru,
      'durasi_menit': durasiMenit,
      'durasi_text': durasiText,
      'total_soal': totalSoal,
      'soal_text': soalText,
      'is_unlocked': isUnlocked,
    };
  }
}

class PetunjukUjianModel {
  final int id;
  final String kategori;
  final String judul;
  final String guru;
  final int waktuMenit;
  final int totalSoal;
  final int targetKkm;
  final String koreksi;
  final List<String> petunjuk;
  final String tips;

  const PetunjukUjianModel({
    required this.id,
    this.kategori = 'UJIAN TENGAH SEMESTER',
    required this.judul,
    this.guru = '',
    this.waktuMenit = 60,
    this.totalSoal = 20,
    this.targetKkm = 75,
    this.koreksi = 'Otomatis',
    this.petunjuk = const [],
    this.tips = 'Tenang, teliti, dan jangan lupa berdoa ya!',
  });

  factory PetunjukUjianModel.fromJson(Map<String, dynamic> json) {
    var rawPetunjuk = json['petunjuk'];
    List<String> listPetunjuk = [];
    if (rawPetunjuk is List) {
      listPetunjuk = rawPetunjuk.map((e) => e.toString()).toList();
    }

    return PetunjukUjianModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      kategori: json['kategori'] ?? 'UJIAN TENGAH SEMESTER',
      judul: json['judul'] ?? '',
      guru: json['guru'] ?? '',
      waktuMenit: int.tryParse(json['waktu_menit']?.toString() ?? '60') ?? 60,
      totalSoal: int.tryParse(json['total_soal']?.toString() ?? '20') ?? 20,
      targetKkm: int.tryParse(json['target_kkm']?.toString() ?? '75') ?? 75,
      koreksi: json['koreksi'] ?? 'Otomatis',
      petunjuk: listPetunjuk,
      tips: json['tips'] ?? 'Tenang, teliti, dan jangan lupa berdoa ya!',
    );
  }
}

class HasilUjianModel {
  final int id;
  final String siswaNama;
  final String judul;
  final String mapel;
  final String guru;
  final int nilai;
  final int targetKkm;
  final bool lulus;
  final String predikat;
  final int totalSoal;
  final int totalBenar;
  final int totalSalah;
  final int durasiMenit;
  final String catatanGuru;

  const HasilUjianModel({
    required this.id,
    this.siswaNama = 'Budi Santoso',
    required this.judul,
    this.mapel = 'Matematika',
    this.guru = 'Guru Pengampu',
    this.nilai = 0,
    this.targetKkm = 75,
    this.lulus = false,
    this.predikat = 'Cukup',
    this.totalSoal = 0,
    this.totalBenar = 0,
    this.totalSalah = 0,
    this.durasiMenit = 0,
    this.catatanGuru = '',
  });

  factory HasilUjianModel.fromJson(Map<String, dynamic> json) {
    return HasilUjianModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      siswaNama: json['siswa_nama'] ?? 'Budi Santoso',
      judul: json['judul'] ?? '',
      mapel: json['mapel'] ?? '',
      guru: json['guru'] ?? '',
      nilai: int.tryParse(json['nilai']?.toString() ?? '0') ?? 0,
      targetKkm: int.tryParse(json['target_kkm']?.toString() ?? '75') ?? 75,
      lulus: json['lulus'] == true || json['lulus'] == 1,
      predikat: json['predikat'] ?? 'Cukup',
      totalSoal: int.tryParse(json['total_soal']?.toString() ?? '0') ?? 0,
      totalBenar: int.tryParse(json['total_benar']?.toString() ?? '0') ?? 0,
      totalSalah: int.tryParse(json['total_salah']?.toString() ?? '0') ?? 0,
      durasiMenit: int.tryParse(json['durasi_menit']?.toString() ?? '0') ?? 0,
      catatanGuru: json['catatan_guru'] ?? '',
    );
  }
}

