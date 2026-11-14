import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dashboard_page.dart';
import '../utils/constants.dart';
import '../utils/page_transitions.dart';
import '../utils/session.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _nisnController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  bool _isLoading = false;
  bool _obscurePassword = true;

  // Endpoint login terpusat dari AppConstants
  static const String _apiUrl = '${AppConstants.baseUrl}/login';

  Future<void> _handleLogin() async {
    final nama = _namaController.text.trim();
    final nisn = _nisnController.text.trim();
    final password = _passwordController.text.trim();

    if (nisn.isEmpty || password.isEmpty) {
      _showSnackbar('NISN dan Kata Sandi wajib diisi!', isError: true);
      return;
    }

    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse(_apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'nama_lengkap': nama,
          'nisn': nisn,
          'password': password,
        }),
      ).timeout(const Duration(seconds: 10));

      Map<String, dynamic> data = {};
      try {
        data = jsonDecode(response.body);
      } catch (_) {}

      if (response.statusCode == 200 && data['success'] == true) {
        final userData = data['data']?['user'] ?? {};
        await AppSession.save(Map<String, dynamic>.from(userData));
        final namaSiswa = userData['nm_siswa'] ?? 'Siswa';
        _showSnackbar('Login Berhasil! Selamat datang, $namaSiswa');

        if (!mounted) return;

        // Navigasi langsung ke Landing / Dashboard Page dengan transisi halus
        Navigator.pushReplacement(
          context,
          SmoothPageRoute(
            page: DashboardPage(user: userData),
            type: TransitionType.fadeThrough,
          ),
        );
      } else {
        final errorMsg =
            data['message'] ?? 'Login gagal. Periksa NISN & Kata Sandi.';
        _showSnackbar(errorMsg, isError: true);
      }
    } catch (e) {
      _showSnackbar(
          'Gagal terhubung ke server. Pastikan "php artisan serve" aktif!',
          isError: true);
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showSnackbar(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor:
            isError ? AppColors.error : AppColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  void dispose() {
    _namaController.dispose();
    _nisnController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: AppColors.navy950,
      body: Stack(
        children: [
          // 1. GAMBAR LATAR BELAKANG ATAS + GRADIENT OVERLAY
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: size.height * 0.58,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Image.network(
                  'https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=1000&auto=format&fit=crop',
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => Container(
                    color: AppColors.navy950,
                  ),
                ),
                Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        AppColors.navy950.withValues(alpha: 0.70),
                        AppColors.navy950.withValues(alpha: 0.85),
                        AppColors.navy950.withValues(alpha: 0.95),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          // 2. KONTEN (HEADER DAN FORM CARD)
          SafeArea(
            child: SingleChildScrollView(
              physics: const ClampingScrollPhysics(),
              child: Column(
                children: [
                  const SizedBox(height: 32),

                  // Header Badge Logo Toga
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: AppColors.primarySoft,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.25),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.school_rounded,
                      color: Colors.white,
                      size: 34,
                    ),
                  ),

                  const SizedBox(height: 14),

                  // Judul "Binaro"
                  const Text(
                    'Binaro',
                    style: TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      letterSpacing: 0.5,
                    ),
                  ),

                  const SizedBox(height: 10),

                  // Teks Sambutan SDN Kalitapen 1
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 28),
                    child: Text(
                      'Selamat datang di Binaro pembelajaran SD Negeri Kalitapen 1! '
                      'Silakan login untuk mengakses berbagai informasi pembelajaran, seperti '
                      'jadwal mata pelajaran, materi pembelajaran, notifikasi PR, dan ujian. '
                      'Ayo, login sekarang dan lanjutkan perjalanan akademik kamu dengan lebih terstruktur dan efisien!',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12.5,
                        height: 1.45,
                        color: Colors.white.withValues(alpha: 0.92),
                        fontWeight: FontWeight.w400,
                      ),
                    ),
                  ),

                  const SizedBox(height: 36),
                  // 3. CARD SHEET PUTIH (FORM INPUT LOGIN)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(24, 28, 24, 36),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius:
                          BorderRadius.vertical(top: Radius.circular(32)),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black26,
                          blurRadius: 15,
                          offset: Offset(0, -5),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Login',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                        ),

                        const SizedBox(height: 20),

                        // Field 1: Nama Lengkap
                        _buildInputField(
                          controller: _namaController,
                          hint: 'Nama Lengkap',
                          icon: Icons.person,
                        ),

                        const SizedBox(height: 16),

                        // Field 2: NISN / NIS
                        _buildInputField(
                          controller: _nisnController,
                          hint: 'NISN / NIS',
                          icon: Icons.school_outlined,
                          keyboardType: TextInputType.number,
                        ),

                        const SizedBox(height: 16),

                        // Field 3: Password
                        _buildInputField(
                          controller: _passwordController,
                          hint: 'Kata Sandi',
                          icon: Icons.lock_outline_rounded,
                          isPassword: true,
                          obscureText: _obscurePassword,
                          onToggleVisibility: () {
                            setState(
                                () => _obscurePassword = !_obscurePassword);
                          },
                        ),

                        const SizedBox(height: 24),

                        // Tombol Login
                        SizedBox(
                          width: double.infinity,
                          height: 50,
                          child: ElevatedButton(
                            onPressed: _isLoading ? null : _handleLogin,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              disabledBackgroundColor: AppColors.primary.withValues(alpha: 0.6),
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                              ),
                            ),
                            child: _isLoading
                                ? const SizedBox(
                                    width: 22,
                                    height: 22,
                                    child: CircularProgressIndicator(
                                      color: Colors.white,
                                      strokeWidth: 2.5,
                                    ),
                                  )
                                : const Text(
                                    'Login',
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    bool isPassword = false,
    bool obscureText = false,
    VoidCallback? onToggleVisibility,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return TextField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      style: const TextStyle(fontSize: 15, color: AppColors.textPrimary),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 14.5),
        prefixIcon: Icon(icon, color: AppColors.textSecondary, size: 22),
        suffixIcon: isPassword
            ? IconButton(
                icon: Icon(
                  obscureText
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined,
                  color: AppColors.textMuted,
                  size: 20,
                ),
                onPressed: onToggleVisibility,
              )
            : null,
        filled: true,
        fillColor: Colors.white,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.border, width: 1.5),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.primary, width: 2),
        ),
      ),
    );
  }
}
