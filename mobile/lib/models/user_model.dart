class UserModel {
  final int? idUser;
  final int? idSiswa;
  final String nama;
  final String username;
  final String role;
  final String? email;
  final String? kelas;
  final String? nisn;
  final String? jenisKelamin;
  final String? noHp;
  final String? alamat;
  final String? fotoUrl;
  final int poin;
  final String peringkat;

  const UserModel({
    this.idUser,
    this.idSiswa,
    required this.nama,
    required this.username,
    this.role = 'siswa',
    this.email,
    this.kelas,
    this.nisn,
    this.jenisKelamin,
    this.noHp,
    this.alamat,
    this.fotoUrl,
    this.poin = 0,
    this.peringkat = 'Pemula',
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      idUser: json['id_user'] != null ? int.tryParse(json['id_user'].toString()) : null,
      idSiswa: json['id_siswa'] != null ? int.tryParse(json['id_siswa'].toString()) : null,
      nama: json['nm_siswa'] ?? json['nama'] ?? json['name'] ?? '',
      username: json['username'] ?? '',
      role: json['role'] ?? 'siswa',
      email: json['email'],
      kelas: json['kelas'] is Map ? json['kelas']['pararel'] : json['kelas']?.toString(),
      nisn: json['nisn']?.toString(),
      jenisKelamin: json['jenis_kelamin'] ?? json['jk'],
      noHp: json['no_hp']?.toString(),
      alamat: json['alamat'],
      fotoUrl: json['foto_url'] ?? json['foto'],
      poin: json['poin'] != null ? int.tryParse(json['poin'].toString()) ?? 0 : 0,
      peringkat: json['peringkat'] ?? 'Pemula',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_user': idUser,
      'id_siswa': idSiswa,
      'nama': nama,
      'username': username,
      'role': role,
      'email': email,
      'kelas': kelas,
      'nisn': nisn,
      'jenis_kelamin': jenisKelamin,
      'no_hp': noHp,
      'alamat': alamat,
      'foto_url': fotoUrl,
      'poin': poin,
      'peringkat': peringkat,
    };
  }
}

