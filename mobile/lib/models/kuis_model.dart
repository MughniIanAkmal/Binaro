class KuisModel {
  final int idKuis;
  final String judul;
  final String bab;
  final int durasiMenit;
  final int totalSoal;
  final List<SoalKuisModel> soal;

  const KuisModel({
    required this.idKuis,
    required this.judul,
    this.bab = 'Bab 1',
    this.durasiMenit = 15,
    this.totalSoal = 0,
    this.soal = const [],
  });

  factory KuisModel.fromJson(Map<String, dynamic> json) {
    var rawSoal = json['soal'];
    List<SoalKuisModel> soalList = [];
    if (rawSoal is List) {
      soalList = rawSoal.map((s) => SoalKuisModel.fromJson(s)).toList();
    }

    return KuisModel(
      idKuis: int.tryParse(json['id']?.toString() ?? json['id_quiz']?.toString() ?? '0') ?? 0,
      judul: json['judul'] ?? json['judul_quiz'] ?? '',
      bab: json['bab'] ?? 'Bab 1',
      durasiMenit: int.tryParse(json['durasi_menit']?.toString() ?? '15') ?? 15,
      totalSoal: int.tryParse(json['total_soal']?.toString() ?? '${soalList.length}') ?? soalList.length,
      soal: soalList,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': idKuis,
      'judul': judul,
      'bab': bab,
      'durasi_menit': durasiMenit,
      'total_soal': totalSoal,
      'soal': soal.map((s) => s.toJson()).toList(),
    };
  }
}

class OpsiKuisModel {
  final String label;
  final String teks;

  const OpsiKuisModel({
    required this.label,
    required this.teks,
  });

  factory OpsiKuisModel.fromJson(Map<String, dynamic> json) {
    return OpsiKuisModel(
      label: json['label'] ?? '',
      teks: json['teks'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'label': label,
      'teks': teks,
    };
  }
}

class SoalKuisModel {
  final int id;
  final int nomor;
  final String pertanyaan;
  final String? ilustrasi;
  final List<OpsiKuisModel> opsi;
  final String? kunci;
  final String? tips;

  const SoalKuisModel({
    required this.id,
    this.nomor = 1,
    required this.pertanyaan,
    this.ilustrasi,
    this.opsi = const [],
    this.kunci,
    this.tips,
  });

  factory SoalKuisModel.fromJson(Map<String, dynamic> json) {
    var rawOpsi = json['opsi'];
    List<OpsiKuisModel> opsiList = [];
    if (rawOpsi is List) {
      opsiList = rawOpsi.map((o) => OpsiKuisModel.fromJson(o)).toList();
    }

    return SoalKuisModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nomor: int.tryParse(json['nomor']?.toString() ?? '1') ?? 1,
      pertanyaan: json['pertanyaan'] ?? '',
      ilustrasi: json['ilustrasi'],
      opsi: opsiList,
      kunci: json['kunci'] ?? json['kunci_jawaban'],
      tips: json['tips'] ?? json['pembahasan'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nomor': nomor,
      'pertanyaan': pertanyaan,
      'ilustrasi': ilustrasi,
      'opsi': opsi.map((o) => o.toJson()).toList(),
      'kunci': kunci,
      'tips': tips,
    };
  }
}

