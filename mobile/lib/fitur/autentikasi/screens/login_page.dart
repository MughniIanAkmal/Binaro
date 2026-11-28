import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:binaro_flutter/fitur/autentikasi/services/auth_service.dart';
import 'package:binaro_flutter/fitur/beranda/screens/dashboard_page.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';
import 'package:binaro_flutter/inti/layanan/page_transitions.dart';
import 'package:binaro_flutter/inti/widgets/common.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _nisnController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  bool _isLoading = false;
  bool _obscurePassword = true;

  Future<void> _handleLogin() async {
    final nisn = _nisnController.text.trim();
    final password = _passwordController.text.trim();

    if (nisn.isEmpty || password.isEmpty) {
      showAppSnackbar(
        context,
        'NISN dan Kata Sandi wajib diisi!',
        type: SnackbarType.error,
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await AuthService.login(nisn: nisn, password: password);
      if (!mounted) return;

      if (result.success) {
        final namaSiswa =
            result.user?['nm_siswa'] ?? AppConstants.defaultSiswaNama;
        showAppSnackbar(
          context,
          'Login Berhasil! Selamat datang, $namaSiswa',
          type: SnackbarType.success,
        );
        Navigator.pushReplacement(
          context,
          SmoothPageRoute(
            page: DashboardPage(user: result.user),
            type: TransitionType.fadeThrough,
          ),
        );
      } else {
        showAppSnackbar(
          context,
          result.message ?? 'Login gagal.',
          type: SnackbarType.error,
        );
      }
    } catch (e) {
      debugPrint('Login error (${AppConstants.baseUrl}): $e');
      if (!mounted) return;
      showAppSnackbar(
        context,
        kDebugMode
            ? 'Gagal terhubung ke ${AppConstants.baseUrl}: $e'
            : 'Gagal terhubung ke server. Pastikan "php artisan serve" aktif!',
        type: SnackbarType.error,
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _nisnController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.navy950,
      body: Stack(
        children: [
          // 1. GAMBAR LATAR BELAKANG ATAS + GRADIENT OVERLAY
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
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
            child: LayoutBuilder(
              builder: (context, constraints) {
                // Di layar lebar (tablet/desktop) kolom login dibatasi
                // 520 dan berkumpul di tengah; di HP lebarnya penuh
                // seperti biasa.
                return Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 520),
                    child: SingleChildScrollView(
                  physics: const ClampingScrollPhysics(),
                  child: ConstrainedBox(
                    constraints:
                        BoxConstraints(minHeight: constraints.maxHeight),
                    child: IntrinsicHeight(
                      child: Column(
                        children: [
                  const SizedBox(height: 32),

                  // Logo Binaro
                  Image.asset(
                    'assets/images/logo_binaro_warna.png',
                    height: 64,
                    fit: BoxFit.contain,
                  ),

                  const SizedBox(height: 16),

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

                  const SizedBox(height: 24),

                  // Sisa ruang layar jadi jarak di sini: kartu form selalu
                  // menempel di bawah pada layar tinggi, dan tetap bisa
                  // di-scroll pada layar pendek.
                  const Spacer(),

                  // 3. CARD SHEET PUTIH (FORM INPUT LOGIN)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(24, 28, 24, 36),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius:
                          BorderRadius.vertical(top: Radius.circular(24)),
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

                        // Field 1: NISN / NIS (selaras dengan login web)
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
                                borderRadius: BorderRadius.circular(12),
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
                    ),
                  ),
                );
              },
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
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.border, width: 1.5),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 2),
        ),
      ),
    );
  }
}
