import 'package:flutter/material.dart';
import 'package:binaro_flutter/fitur/notifikasi/services/notifikasi_service.dart';
import 'package:binaro_flutter/fitur/notifikasi/widgets/notifikasi_panel.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Tombol lonceng di header aplikasi: menampilkan jumlah notifikasi PR
/// yang belum dibaca, dan membuka panel notifikasi saat diketuk
/// (tanpa pindah halaman).
class LoncengNotifikasi extends StatefulWidget {
  const LoncengNotifikasi({super.key});

  @override
  State<LoncengNotifikasi> createState() => _LoncengNotifikasiState();
}

class _LoncengNotifikasiState extends State<LoncengNotifikasi> {
  int _belumDibaca = 0;

  @override
  void initState() {
    super.initState();
    _muatJumlah();
  }

  Future<void> _muatJumlah() async {
    final jumlah = await NotifikasiService.fetchUnreadCount();
    if (mounted) setState(() => _belumDibaca = jumlah);
  }

  Future<void> _bukaPanel() async {
    await NotifikasiPanel.tampilkan(context);
    // Panel menandai notifikasi sebagai dibaca saat dibuka; segarkan badge.
    if (mounted) _muatJumlah();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        IconButton(
          icon: Icon(
            _belumDibaca > 0
                ? Icons.notifications_rounded
                : Icons.notifications_none_rounded,
            color: Colors.white,
            size: 24,
          ),
          onPressed: _bukaPanel,
        ),
        if (_belumDibaca > 0)
          Positioned(
            right: 5,
            top: 5,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 4.5),
              constraints: const BoxConstraints(minWidth: 17, minHeight: 17),
              decoration: BoxDecoration(
                color: AppColors.error,
                borderRadius: BorderRadius.circular(999),
                border: Border.all(color: AppColors.primary, width: 1.4),
              ),
              child: Center(
                child: Text(
                  _belumDibaca > 9 ? '9+' : '$_belumDibaca',
                  style: const TextStyle(
                    fontSize: 9.5,
                    height: 1.1,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }
}
