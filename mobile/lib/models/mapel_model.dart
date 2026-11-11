class MapelModel {
  final int idMapel;
  final String namaMapel;
  final String? namaAsli;
  final String? bab;
  final int totalBab;
  final int materiTotal;
  final int materiSelesai;
  final double progress;
  final String tipe;
  final String warna;
  final String jadwal;
  final bool isWajib;

  const MapelModel({
    required this.idMapel,
    required this.namaMapel,
    this.namaAsli,
    this.bab,
    this.totalBab = 0,
    this.materiTotal = 0,
    this.materiSelesai = 0,
    this.progress = 0.0,
    this.tipe = 'book',
    this.warna = 'blue',
    this.jadwal = 'Senin & Rabu',
    this.isWajib = true,
  });

  factory MapelModel.fromJson(Map<String, dynamic> json) {
    return MapelModel(
      idMapel: int.tryParse(json['id_mapel'].toString()) ?? 0,
      namaMapel: json['nama_mapel'] ?? '',
      namaAsli: json['nama_asli'],
      bab: json['bab'],
      totalBab: int.tryParse(json['total_bab']?.toString() ?? json['bab_count']?.toString() ?? '0') ?? 0,
      materiTotal: int.tryParse(json['materi_total']?.toString() ?? '0') ?? 0,
      materiSelesai: int.tryParse(json['materi_selesai']?.toString() ?? '0') ?? 0,
      progress: (json['progress'] != null)
          ? double.tryParse(json['progress'].toString()) ?? 0.0
          : 0.0,
      tipe: json['tipe'] ?? 'book',
      warna: json['warna'] ?? 'blue',
      jadwal: json['jadwal'] ?? 'Senin & Rabu',
      isWajib: json['is_wajib'] == true || json['is_wajib'] == 1,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_mapel': idMapel,
      'nama_mapel': namaMapel,
      'nama_asli': namaAsli,
      'bab': bab,
      'total_bab': totalBab,
      'materi_total': materiTotal,
      'materi_selesai': materiSelesai,
      'progress': progress,
      'tipe': tipe,
      'warna': warna,
      'jadwal': jadwal,
      'is_wajib': isWajib,
    };
  }
}

