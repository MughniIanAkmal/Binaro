class SubBabModel {
  final int idSubBab;
  final int nomorSubBab;
  final String judulSubBab;
  final String deskripsi;
  final int materiCount;
  final bool isUnlocked;
  final bool isActive;
  final String iconType;

  const SubBabModel({
    required this.idSubBab,
    required this.nomorSubBab,
    required this.judulSubBab,
    required this.deskripsi,
    this.materiCount = 0,
    this.isUnlocked = true,
    this.isActive = false,
    this.iconType = 'layer',
  });

  factory SubBabModel.fromJson(Map<String, dynamic> json) {
    return SubBabModel(
      idSubBab: int.tryParse(json['id_sub_bab']?.toString() ?? '0') ?? 0,
      nomorSubBab: int.tryParse(json['nomor_sub_bab']?.toString() ?? '1') ?? 1,
      judulSubBab: json['judul_sub_bab'] ?? json['nama_sub_bab'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      materiCount: int.tryParse(json['materi_count']?.toString() ?? '0') ?? 0,
      isUnlocked: json['is_unlocked'] == true || json['is_unlocked'] == 1,
      isActive: json['is_active'] == true || json['is_active'] == 1,
      iconType: json['icon_type'] ?? 'layer',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_sub_bab': idSubBab,
      'nomor_sub_bab': nomorSubBab,
      'judul_sub_bab': judulSubBab,
      'deskripsi': deskripsi,
      'materi_count': materiCount,
      'is_unlocked': isUnlocked,
      'is_active': isActive,
      'icon_type': iconType,
    };
  }
}

