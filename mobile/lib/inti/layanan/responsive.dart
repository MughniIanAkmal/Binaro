import 'package:flutter/material.dart';

/// Fondasi responsif Binaro: ukuran komponen dihitung dari ukuran layar
/// yang sebenarnya, bukan angka paten untuk satu ukuran HP.
///
/// Kelas layar (lebar logis):
/// - ringkas  : < 600        (HP)
/// - sedang   : 600 – 1024   (tablet / HP lipat terbuka)
/// - lebar    : > 1024       (desktop / tablet besar landscape)
///
/// Di HP tampilannya sama seperti sebelumnya; di layar yang lebih besar
/// padding dan lebar konten bertambah dan daftar kartu bisa menjadi
/// beberapa kolom, sehingga komponen selalu menyesuaikan layar.
class Responsive {
  final double lebar;

  const Responsive(this.lebar);

  factory Responsive.of(BuildContext context) {
    return Responsive(MediaQuery.sizeOf(context).width);
  }

  bool get isRingkas => lebar < 600;
  bool get isSedang => lebar >= 600 && lebar <= 1024;
  bool get isLebar => lebar > 1024;

  /// Padding horizontal halaman: membesar mengikuti lebar layar,
  /// dijepit agar tidak berlebihan (20 di HP, maks. 48).
  double get paddingHalaman => (lebar * 0.05).clamp(20.0, 48.0);

  /// Lebar maksimal area konten. HP memakai seluruh lebar; tablet
  /// 840; desktop 1080 — konten berhenti melebar dan berkumpul di
  /// tengah dengan ruang napas yang cukup.
  double get lebarKontenMaks {
    if (isRingkas) return double.infinity;
    if (isSedang) return 840;
    return 1080;
  }

  /// Lebar maksimal khusus halaman baca/pengerjaan (kuis, materi):
  /// lebih ramping agar baris teks tetap nyaman dibaca.
  double get lebarBacaMaks {
    if (isRingkas) return double.infinity;
    return 760;
  }
}

/// Bungkus konten halaman agar lebarnya menyesuaikan kelas layar:
/// di HP penuh seperti biasa, di tablet/desktop konten terpusat
/// dengan lebar maksimal. Padding di dalam konten tetap milik
/// halamannya sendiri, jadi tampilan HP tidak berubah sama sekali.
class KontenResponsif extends StatelessWidget {
  final Widget child;

  /// Pakai lebar baca (lebih ramping) alih-alih lebar konten umum.
  final bool modeBaca;

  const KontenResponsif({
    super.key,
    required this.child,
    this.modeBaca = false,
  });

  @override
  Widget build(BuildContext context) {
    final r = Responsive.of(context);
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(
          maxWidth: modeBaca ? r.lebarBacaMaks : r.lebarKontenMaks,
        ),
        child: child,
      ),
    );
  }
}
