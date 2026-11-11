class BabModel {
  final int idBab;
  final int nomorBab;
  final String judulBab;
  final String deskripsi;
  final int subBabCount;
  final bool isUnlocked;

  const BabModel({
    required this.idBab,
    required this.nomorBab,
    required this.judulBab,
    required this.deskripsi,
    this.subBabCount = 0,
    this.isUnlocked = true,
  });

  factory BabModel.fromJson(Map<String, dynamic> json) {
    return BabModel(
      idBab: int.tryParse(json['id_bab']?.toString() ?? '0') ?? 0,
      nomorBab: int.tryParse(json['nomor_bab']?.toString() ?? '1') ?? 1,
      judulBab: json['judul_bab'] ?? json['nama_bab'] ?? '',
      deskripsi: json['deskripsi'] ?? 'BAB ${json['nomor_bab'] ?? 1}',
      subBabCount: int.tryParse(json['sub_bab_count']?.toString() ?? '0') ?? 0,
      isUnlocked: json['is_unlocked'] == true || json['is_unlocked'] == 1,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_bab': idBab,
      'nomor_bab': nomorBab,
      'judul_bab': judulBab,
      'deskripsi': deskripsi,
      'sub_bab_count': subBabCount,
      'is_unlocked': isUnlocked,
    };
  }
}

