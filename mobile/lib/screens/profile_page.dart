import 'dart:convert';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import '../widgets/dynamic_bottom_nav.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'login_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';

class ProfilePage extends StatefulWidget {
  final Map<String, dynamic>? user;

  const ProfilePage({super.key, this.user});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  bool _isLoading = true;

  // Data Profil dari API
  Map<String, dynamic> _siswa = {};
  Map<String, dynamic> _rekap = {};
  Map<String, dynamic> _dataSekolah = {};
  String _versi = 'Sahabat Belajar v1.2.0-SD';

  static const String _baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    _siswa = {
      'id_siswa': widget.user?['id_siswa'] ?? AppConstants.defaultSiswaId,
      'nama': widget.user?['nm_siswa'] ?? widget.user?['nama'] ?? AppConstants.defaultSiswaNama,
      'kelas': widget.user?['kelas'] ?? AppConstants.defaultSiswaKelas,
      'nisn': widget.user?['nisn'] ?? '0012345678',
      'barcode': 'BIN-0012345678-2RBTDA',
    };
    _fetchProfileData();
  }

  Future<void> _fetchProfileData() async {
    final int siswaId = _siswa['id_siswa'] ?? AppConstants.defaultSiswaId;
    try {
      final response = await http
          .get(
            Uri.parse('$_baseUrl/siswa/profile?id_siswa=$siswaId'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(AppConstants.timeoutDuration);

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        if (result['success'] == true && mounted) {
          final data = result['data'];
          setState(() {
            _siswa = data['siswa'] ?? _siswa;
            _rekap = data['rekap_absensi'] ?? {};
            _dataSekolah = data['data_sekolah'] ?? {};
            _versi = data['versi'] ?? _versi;
            _isLoading = false;
          });
          return;
        }
      }
    } catch (_) {}

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  // ===========================================================================
  // 1. POPUP BARCODE DENGAN EFEK BACKGROUND BLUR
  // ===========================================================================
  void _showBarcodePopup() {
    final String barcodeData = _siswa['barcode'] ?? _siswa['nisn'] ?? '0012345678';
    final String nisn = _siswa['nisn'] ?? '0012345678';
    final String nama = _siswa['nama'] ?? 'Budi Santoso';

    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: 'BarcodeDialog',
      barrierColor: Colors.black.withValues(alpha: 0.5),
      transitionDuration: const Duration(milliseconds: 250),
      pageBuilder: (ctx, anim1, anim2) {
        return BackdropFilter(
          // Efek Blur di seluruh layar background
          filter: ImageFilter.blur(sigmaX: 9, sigmaY: 9),
          child: Center(
            child: Material(
              color: Colors.transparent,
              child: Container(
                width: MediaQuery.of(ctx).size.width * 0.88,
                margin: const EdgeInsets.symmetric(horizontal: 20),
                padding: const EdgeInsets.fromLTRB(22, 22, 22, 24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(26),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.25),
                      blurRadius: 25,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Header Barcode Modal
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.qr_code_2_rounded,
                                color: Color(0xFF1D5A86), size: 26),
                            SizedBox(width: 8),
                            Text(
                              'Barcode Absensi',
                              style: TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF1E293B),
                              ),
                            ),
                          ],
                        ),
                        IconButton(
                          onPressed: () => Navigator.pop(ctx),
                          icon: const Icon(Icons.close_rounded,
                              color: Color(0xFF94A3B8), size: 24),
                          visualDensity: VisualDensity.compact,
                        ),
                      ],
                    ),
                    const Divider(height: 20, color: Color(0xFFE2E8F0)),
                    const SizedBox(height: 10),

                    // QR Code Besar & Fokus
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: const Color(0xFFE2E8F0), width: 2),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.04),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: QrImageView(
                        data: barcodeData,
                        version: QrVersions.auto,
                        size: 210,
                        backgroundColor: Colors.white,
                        eyeStyle: const QrEyeStyle(
                          eyeShape: QrEyeShape.square,
                          color: Color(0xFF1E293B),
                        ),
                        dataModuleStyle: const QrDataModuleStyle(
                          dataModuleShape: QrDataModuleShape.square,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Nama & NISN
                    Text(
                      nama,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1E293B),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'NISN: $nisn',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1D5A86),
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 10),
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16),
                      child: Text(
                        'Arahkan barcode ini ke alat pemindai absensi di gerbang sekolah.',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Opsi Cetak & Simpan Barcode di dalam Popup
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _showToast('Kartu siap dicetak'),
                            icon: const Icon(Icons.print_outlined, size: 16),
                            label: const Text('Cetak Kartu', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF1D5A86),
                              side: const BorderSide(color: Color(0xFFBFDBFE)),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _showToast('Barcode berhasil disimpan ke galeri'),
                            icon: const Icon(Icons.download_rounded, size: 16),
                            label: const Text('Simpan Barcode', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF1D5A86),
                              side: const BorderSide(color: Color(0xFFBFDBFE)),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // Tombol Tutup
                    SizedBox(
                      width: double.infinity,
                      height: 46,
                      child: ElevatedButton(
                        onPressed: () => Navigator.pop(ctx),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF1D5A86),
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        child: const Text(
                          'Selesai',
                          style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
      transitionBuilder: (ctx, anim1, anim2, child) {
        return FadeTransition(
          opacity: anim1,
          child: ScaleTransition(
            scale: Tween<double>(begin: 0.85, end: 1.0).animate(
              CurvedAnimation(parent: anim1, curve: Curves.easeOutBack),
            ),
            child: child,
          ),
        );
      },
    );
  }

  // ===========================================================================
  // 2. POPUP UBAH PASSWORD DENGAN EFEK BACKGROUND BLUR
  // ===========================================================================
  void _showUbahPasswordPopup() {
    final TextEditingController sandiLamaController = TextEditingController();
    final TextEditingController sandiBaruController = TextEditingController();
    final TextEditingController konfirmasiController = TextEditingController();

    bool obscureLama = true;
    bool obscureBaru = true;
    bool obscureKonfirmasi = true;
    bool isSubmitting = false;

    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: 'PasswordDialog',
      barrierColor: Colors.black.withValues(alpha: 0.5),
      transitionDuration: const Duration(milliseconds: 250),
      pageBuilder: (ctx, anim1, anim2) {
        return StatefulBuilder(
          builder: (dialogCtx, setDialogState) {
            return BackdropFilter(
              // Efek Blur di seluruh layar background
              filter: ImageFilter.blur(sigmaX: 9, sigmaY: 9),
              child: Center(
                child: Material(
                  color: Colors.transparent,
                  child: Container(
                    width: MediaQuery.of(ctx).size.width * 0.90,
                    margin: const EdgeInsets.symmetric(horizontal: 20),
                    padding: const EdgeInsets.fromLTRB(22, 22, 22, 24),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(26),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.25),
                          blurRadius: 25,
                          offset: const Offset(0, 10),
                        ),
                      ],
                    ),
                    child: SingleChildScrollView(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header Popup
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Row(
                                children: [
                                  Icon(Icons.lock_reset_rounded,
                                      color: Color(0xFF1D5A86), size: 26),
                                  SizedBox(width: 8),
                                  Text(
                                    'Ubah Kata Sandi',
                                    style: TextStyle(
                                      fontSize: 17,
                                      fontWeight: FontWeight.w800,
                                      color: Color(0xFF1E293B),
                                    ),
                                  ),
                                ],
                              ),
                              IconButton(
                                onPressed: () => Navigator.pop(dialogCtx),
                                icon: const Icon(Icons.close_rounded,
                                    color: Color(0xFF94A3B8), size: 24),
                                visualDensity: VisualDensity.compact,
                              ),
                            ],
                          ),
                          const Divider(height: 18, color: Color(0xFFE2E8F0)),
                          const SizedBox(height: 10),

                          // Field: Sandi Saat Ini
                          const Text(
                            'Sandi Saat Ini',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E293B),
                            ),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: sandiLamaController,
                            obscureText: obscureLama,
                            decoration: _buildInputDecoration(
                              hint: 'Masukkan kata sandi lama',
                              isPassword: true,
                              obscure: obscureLama,
                              onToggle: () {
                                setDialogState(() => obscureLama = !obscureLama);
                              },
                            ),
                          ),

                          const SizedBox(height: 14),

                          // Field: Sandi Baru
                          const Text(
                            'Sandi Baru',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E293B),
                            ),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: sandiBaruController,
                            obscureText: obscureBaru,
                            decoration: _buildInputDecoration(
                              hint: 'Minimal 6 karakter',
                              isPassword: true,
                              obscure: obscureBaru,
                              onToggle: () {
                                setDialogState(() => obscureBaru = !obscureBaru);
                              },
                            ),
                          ),

                          const SizedBox(height: 14),

                          // Field: Ulangi Sandi Baru
                          const Text(
                            'Ulangi Sandi Baru',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E293B),
                            ),
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: konfirmasiController,
                            obscureText: obscureKonfirmasi,
                            decoration: _buildInputDecoration(
                              hint: 'Ketik kembali sandi baru',
                              isPassword: true,
                              obscure: obscureKonfirmasi,
                              onToggle: () {
                                setDialogState(() => obscureKonfirmasi = !obscureKonfirmasi);
                              },
                            ),
                          ),

                          const SizedBox(height: 22),

                          // Tombol Simpan
                          SizedBox(
                            width: double.infinity,
                            height: 48,
                            child: ElevatedButton(
                              onPressed: isSubmitting
                                  ? null
                                  : () async {
                                      final lama = sandiLamaController.text.trim();
                                      final baru = sandiBaruController.text.trim();
                                      final konf = konfirmasiController.text.trim();

                                      if (lama.isEmpty || baru.isEmpty || konf.isEmpty) {
                                        _showToast('Semua kolom wajib diisi!', isError: true);
                                        return;
                                      }
                                      if (baru != konf) {
                                        _showToast('Konfirmasi sandi baru tidak cocok!', isError: true);
                                        return;
                                      }
                                      if (baru.length < 4) {
                                        _showToast('Sandi baru minimal 4 karakter!', isError: true);
                                        return;
                                      }

                                      setDialogState(() => isSubmitting = true);

                                      try {
                                        final res = await http.post(
                                          Uri.parse('$_baseUrl/siswa/update-password'),
                                          headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                          },
                                          body: jsonEncode({
                                            'id_siswa': _siswa['id_siswa'] ?? 7,
                                            'password_lama': lama,
                                            'password_baru': baru,
                                            'konfirmasi_password': konf,
                                          }),
                                        );

                                        final jsonRes = jsonDecode(res.body);

                                        if (res.statusCode == 200 && jsonRes['success'] == true) {
                                          if (dialogCtx.mounted) {
                                            Navigator.pop(dialogCtx);
                                          }
                                          if (mounted) {
                                            _showToast('Kata sandi berhasil diperbarui!');
                                          }
                                        } else {
                                          _showToast(jsonRes['message'] ?? 'Gagal mengubah kata sandi', isError: true);
                                        }
                                      } catch (err) {
                                        _showToast('Gagal menghubungi server.', isError: true);
                                      } finally {
                                        setDialogState(() => isSubmitting = false);
                                      }
                                    },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF1D5A86),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                              ),
                              child: isSubmitting
                                  ? const SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(
                                        color: Colors.white,
                                        strokeWidth: 2,
                                      ),
                                    )
                                  : const Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.check_circle_outline_rounded, size: 18),
                                        SizedBox(width: 8),
                                        Text(
                                          'Simpan Kata Sandi Baru',
                                          style: TextStyle(
                                            fontWeight: FontWeight.w700,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
      transitionBuilder: (ctx, anim1, anim2, child) {
        return FadeTransition(
          opacity: anim1,
          child: ScaleTransition(
            scale: Tween<double>(begin: 0.88, end: 1.0).animate(
              CurvedAnimation(parent: anim1, curve: Curves.easeOutBack),
            ),
            child: child,
          ),
        );
      },
    );
  }

  InputDecoration _buildInputDecoration({
    required String hint,
    required bool isPassword,
    required bool obscure,
    required VoidCallback onToggle,
  }) {
    return InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      suffixIcon: IconButton(
        icon: Icon(
          obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          color: const Color(0xFF94A3B8),
          size: 19,
        ),
        onPressed: onToggle,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1.2),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF1D5A86), width: 1.8),
      ),
    );
  }

  void _showToast(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? const Color(0xFFDC2626) : const Color(0xFF16A34A),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: _buildAppBar(),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF1D5A86)))
          : RefreshIndicator(
              onRefresh: _fetchProfileData,
              color: const Color(0xFF1D5A86),
              backgroundColor: Colors.white,
              displacement: 28,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                child: Column(
                  children: [
                    // 1. Header Profil Siswa (Avatar, Nama, NISN)
                    _buildProfileHeader(),

                    const SizedBox(height: 22),

                    // 2. Rekap Absensi (Bulan Ini)
                    _buildRekapAbsensiCard(),

                    const SizedBox(height: 18),

                    // 3. Kartu & Barcode Absensi (Dengan Tombol "Tunjukkan Barcode")
                    _buildBarcodeCard(),

                    const SizedBox(height: 18),

                    // 4. Data Sekolah & Siswa
                    _buildDataSekolahCard(),

                    const SizedBox(height: 18),

                    // 5. Ganti Kata Sandi (Dengan Tombol "Ubah Password")
                    _buildGantiPasswordCard(),

                    const SizedBox(height: 18),

                    // 6. Tombol Keluar dari Akun
                    _buildLogoutButton(),

                    const SizedBox(height: 18),

                    // 7. Versi Aplikasi
                    _buildVersiCard(),

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
      bottomNavigationBar: DynamicBottomNav(
        currentIndex: 4,
        user: widget.user ?? _siswa,
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // APPBAR
  // ---------------------------------------------------------------------------
  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: const Color(0xFF1D5A86),
      elevation: 0,
      automaticallyImplyLeading: false,
      titleSpacing: 16,
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFF59E0B),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.school_rounded, color: Colors.white, size: 22),
          ),
          const SizedBox(width: 12),
          const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'SAHABAT BELAJAR',
                style: TextStyle(
                  fontSize: 10,
                  letterSpacing: 0.8,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFFFBBF24),
                ),
              ),
              SizedBox(height: 2),
              Text(
                'Profil Siswa',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
      actions: [
        Padding(
          padding: const EdgeInsets.only(right: 8),
          child: Stack(
            alignment: Alignment.topRight,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_none_rounded, color: Colors.white, size: 26),
                onPressed: () {},
              ),
              Positioned(
                right: 12,
                top: 12,
                child: Container(
                  width: 9,
                  height: 9,
                  decoration: const BoxDecoration(
                    color: Color(0xFFF59E0B),
                    shape: BoxShape.circle,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 1. HEADER PROFIL
  // ---------------------------------------------------------------------------
  Widget _buildProfileHeader() {
    return Column(
      children: [
        // Lingkaran Profil Siswa Abu-abu dengan Border Putih (Sesuai Permintaan)
        Container(
          width: 90,
          height: 90,
          decoration: BoxDecoration(
            color: const Color(0xFFC0C0C0),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white, width: 3.5),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.12),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // Nama Siswa
        Text(
          _siswa['nama'] ?? 'Budi Santoso',
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: Color(0xFF1E293B),
          ),
        ),
        const SizedBox(height: 6),

        // Kelas & Sekolah
        Text(
          _siswa['kelas'] ?? 'Kelas 4B • SDN Kalitapen 01',
          style: const TextStyle(
            fontSize: 13.5,
            fontWeight: FontWeight.w500,
            color: Color(0xFF64748B),
          ),
        ),
        const SizedBox(height: 12),

        // Pill NISN dengan Tombol Salin
        InkWell(
          onTap: () {
            Clipboard.setData(ClipboardData(text: _siswa['nisn'] ?? ''));
            _showToast('NISN berhasil disalin ke clipboard');
          },
          borderRadius: BorderRadius.circular(20),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'NISN: ${_siswa['nisn'] ?? '-'}',
                  style: const TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
                const SizedBox(width: 6),
                const Icon(Icons.copy_rounded, size: 14, color: Color(0xFF1D5A86)),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 2. KARTU BARCODE ABSENSI (HANYA 1 TOMBOL "TUNJUKKAN BARCODE")
  // ---------------------------------------------------------------------------
  Widget _buildBarcodeCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.qr_code_2_rounded, color: Color(0xFF1D5A86), size: 20),
                  SizedBox(width: 8),
                  Text(
                    'Kartu & Barcode Absensi',
                    style: TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Text(
                  'Absensi',
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Text(
            'Tunjukkan barcode digital ini ke alat pemindai absensi saat masuk atau pulang sekolah.',
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B), height: 1.4),
          ),
          const SizedBox(height: 14),

          // 1 Tombol Saja: "Tunjukkan Barcode" (Membuka Popup Fullscreen dengan Background Blur)
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton.icon(
              onPressed: _showBarcodePopup,
              icon: const Icon(Icons.qr_code_scanner_rounded, size: 20),
              label: const Text(
                'Tunjukkan Barcode',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1D5A86),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. REKAP ABSENSI BULAN INI
  // ---------------------------------------------------------------------------
  Widget _buildRekapAbsensiCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.calendar_month_outlined, color: Color(0xFF1D5A86), size: 20),
                  SizedBox(width: 8),
                  Text(
                    'Rekap Absensi (Bulan Ini)',
                    style: TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  _rekap['bulan'] ?? '-',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // 4 Kotak Statistik (Hadir, Izin, Sakit, Alpa)
          Row(
            children: [
              _buildStatBox((_rekap['hadir'] ?? 0).toString(), 'Hadir', const Color(0xFFD1FAE5), const Color(0xFF059669)),
              const SizedBox(width: 8),
              _buildStatBox((_rekap['izin'] ?? 0).toString(), 'Izin', const Color(0xFFDBEAFE), const Color(0xFF2563EB)),
              const SizedBox(width: 8),
              _buildStatBox((_rekap['sakit'] ?? 0).toString(), 'Sakit', const Color(0xFFFEF3C7), const Color(0xFFD97706)),
              const SizedBox(width: 8),
              _buildStatBox((_rekap['alpa'] ?? 0).toString(), 'Alpa', const Color(0xFFF1F5F9), const Color(0xFF64748B)),
            ],
          ),

          const SizedBox(height: 14),

          // Banner Status Hari Ini
          if (_rekap['hari_ini'] != null && _rekap['hari_ini'] is Map)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFECFDF5),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFA7F3D0)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 20),
                      const SizedBox(width: 10),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Hari Ini: ${_rekap['hari_ini']['status'] ?? 'Hadir'}',
                            style: const TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF065F46),
                            ),
                          ),
                          Text(
                            _rekap['hari_ini']['keterangan'] ?? '-',
                            style: const TextStyle(fontSize: 11, color: Color(0xFF047857)),
                          ),
                        ],
                      ),
                    ],
                  ),
                  if (_rekap['hari_ini']['badge'] != null && _rekap['hari_ini']['badge'].toString().isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        _rekap['hari_ini']['badge'].toString(),
                        style: const TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF059669),
                        ),
                      ),
                    ),
                ],
              ),
            ),

          const SizedBox(height: 12),

          // Tombol Buka Layar Absen Penuh
          Center(
            child: TextButton(
              onPressed: () {},
              style: TextButton.styleFrom(
                foregroundColor: const Color(0xFF1D5A86),
                textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('Buka Layar Absen Penuh'),
                  SizedBox(width: 4),
                  Icon(Icons.arrow_forward_rounded, size: 16),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatBox(String count, String label, Color bgColor, Color textColor) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          children: [
            Text(
              count,
              style: TextStyle(
                fontSize: 19,
                fontWeight: FontWeight.w800,
                color: textColor,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: textColor,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. DATA SEKOLAH & SISWA
  // ---------------------------------------------------------------------------
  Widget _buildDataSekolahCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.badge_outlined, color: Color(0xFF1D5A86), size: 20),
              SizedBox(width: 8),
              Text(
                'Data Sekolah & Siswa',
                style: TextStyle(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF1E293B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          _buildInfoRow(
            icon: Icons.people_outline_rounded,
            title: 'Nama Wali / Orang Tua',
            value: _dataSekolah['wali'] ?? '-',
          ),
          const Divider(height: 20, color: Color(0xFFF1F5F9)),
          _buildInfoRow(
            icon: Icons.tag_rounded,
            title: 'Nomor Absen',
            value: _dataSekolah['no_absen'] ?? '-',
          ),
          const Divider(height: 20, color: Color(0xFFF1F5F9)),
          _buildInfoRow(
            icon: Icons.event_note_rounded,
            title: 'Tahun Ajaran',
            value: _dataSekolah['tahun_ajaran'] ?? '-',
            isLink: true,
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String title,
    required String value,
    bool isLink = false,
  }) {
    return Row(
      children: [
        Icon(icon, color: const Color(0xFF64748B), size: 18),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            title,
            style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: isLink ? const Color(0xFF1D5A86) : const Color(0xFF1E293B),
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // 5. TOMBOL KELUAR DARI AKUN
  // ---------------------------------------------------------------------------
  Widget _buildLogoutButton() {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: ElevatedButton.icon(
        onPressed: _showLogoutConfirmDialog,
        icon: const Icon(Icons.logout_rounded, size: 19),
        label: const Text(
          'Keluar dari Akun',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFFEE2E2),
          foregroundColor: const Color(0xFFDC2626),
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      ),
    );
  }

  void _showLogoutConfirmDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Keluar', style: TextStyle(fontWeight: FontWeight.w800)),
        content: const Text('Apakah Anda yakin ingin keluar dari akun ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pushAndRemoveUntil(
                context,
                SmoothPageRoute(
                  page: const LoginPage(),
                  type: TransitionType.fadeThrough,
                ),
                (route) => false,
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
            child: const Text('Keluar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 6. GANTI KATA SANDI (DENGAN TOMBOL "UBAH PASSWORD" POPUP)
  // ---------------------------------------------------------------------------
  Widget _buildGantiPasswordCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.lock_reset_rounded, color: Color(0xFF1D5A86), size: 20),
                  SizedBox(width: 8),
                  Text(
                    'Ganti Kata Sandi',
                    style: TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Text(
                  'Keamanan',
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1D5A86),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Text(
            'Perbarui kata sandi secara berkala untuk menjaga keamanan akun pembelajaran Anda.',
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B), height: 1.4),
          ),
          const SizedBox(height: 14),

          // Tombol "Ubah Password" (Membuka Popup dengan Background Blur)
          SizedBox(
            width: double.infinity,
            height: 46,
            child: ElevatedButton.icon(
              onPressed: _showUbahPasswordPopup,
              icon: const Icon(Icons.password_rounded, size: 19),
              label: const Text(
                'Ubah Password',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1D5A86),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 7. VERSI APLIKASI
  // ---------------------------------------------------------------------------
  Widget _buildVersiCard() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Row(
            children: [
              Icon(Icons.info_outline_rounded, color: Color(0xFF1D5A86), size: 19),
              SizedBox(width: 8),
              Text(
                'Versi Aplikasi',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1E293B),
                ),
              ),
            ],
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              _versi,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                color: Color(0xFF1D5A86),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
