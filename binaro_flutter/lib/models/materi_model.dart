class MateriModel {
  final int idMateri;
  final int nomor;
  final String judul;
  final String tipe;
  final String badge;
  final String deskripsi;
  final String? konten;
  final String? kategori;
  final String? subJudul;
  final int durasiMenit;
  final String durasiText;
  final String? videoUrl;
  final String? youtubeId;
  final String? thumbnailUrl;
  final String? gambarIlustrasi;
  final bool isUnlocked;
  final int totalKuisSoal;
  final List<dynamic>? poinPenting;
  final String? tips;

  const MateriModel({
    required this.idMateri,
    this.nomor = 1,
    required this.judul,
    this.tipe = 'video',
    this.badge = 'MATERI 1',
    this.deskripsi = '',
    this.konten,
    this.kategori,
    this.subJudul,
    this.durasiMenit = 5,
    this.durasiText = '5 Menit',
    this.videoUrl,
    this.youtubeId,
    this.thumbnailUrl,
    this.gambarIlustrasi,
    this.isUnlocked = true,
    this.totalKuisSoal = 5,
    this.poinPenting,
    this.tips,
  });

  factory MateriModel.fromJson(Map<String, dynamic> json) {
    return MateriModel(
      idMateri: int.tryParse(json['id_materi']?.toString() ?? '0') ?? 0,
      nomor: int.tryParse(json['nomor']?.toString() ?? '1') ?? 1,
      judul: json['judul'] ?? json['judul_materi'] ?? '',
      tipe: json['tipe'] ?? 'video',
      badge: json['badge'] ?? 'MATERI ${json['nomor'] ?? 1}',
      deskripsi: json['deskripsi'] ?? '',
      konten: json['konten'],
      kategori: json['kategori'] ?? 'Materi Dasar',
      subJudul: json['sub_judul'] ?? json['deskripsi'],
      durasiMenit: int.tryParse(json['durasi_menit']?.toString() ?? '5') ?? 5,
      durasiText: json['durasi_text'] ?? '${json['durasi_menit'] ?? 5} Menit',
      videoUrl: json['video_url'],
      youtubeId: json['youtube_id'],
      thumbnailUrl: json['thumbnail_url'] ?? json['thumbnail'],
      gambarIlustrasi: json['gambar_ilustrasi'],
      isUnlocked: json['is_unlocked'] == true || json['is_unlocked'] == 1,
      totalKuisSoal: int.tryParse(json['total_kuis_soal']?.toString() ?? '5') ?? 5,
      poinPenting: json['poin_penting'] is List ? json['poin_penting'] : null,
      tips: json['tips']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_materi': idMateri,
      'nomor': nomor,
      'judul': judul,
      'tipe': tipe,
      'badge': badge,
      'deskripsi': deskripsi,
      'konten': konten,
      'kategori': kategori,
      'sub_judul': subJudul,
      'durasi_menit': durasiMenit,
      'durasi_text': durasiText,
      'video_url': videoUrl,
      'youtube_id': youtubeId,
      'thumbnail_url': thumbnailUrl,
      'gambar_ilustrasi': gambarIlustrasi,
      'is_unlocked': isUnlocked,
      'total_kuis_soal': totalKuisSoal,
      'poin_penting': poinPenting,
      'tips': tips,
    };
  }
}

