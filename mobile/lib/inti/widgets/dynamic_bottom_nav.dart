import 'package:flutter/material.dart';
import 'package:binaro_flutter/fitur/beranda/screens/dashboard_page.dart';
import 'package:binaro_flutter/fitur/jadwal/screens/jadwal_page.dart';
import 'package:binaro_flutter/fitur/materi/screens/mapel_page.dart';
import 'package:binaro_flutter/fitur/profil/screens/profile_page.dart';
import 'package:binaro_flutter/fitur/ujian/screens/ujian_page.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';
import 'package:binaro_flutter/inti/layanan/page_transitions.dart';

/// Model representasi item navigasi
class NavItem {
  final int index;
  final String label;
  final IconData icon;
  final IconData activeIcon;

  const NavItem({
    required this.index,
    required this.label,
    required this.icon,
    required this.activeIcon,
  });
}

/// Bottom navigation baku Binaro: bar navy melayang dengan lima item tetap.
///
/// Bentuk mengikuti referensi desain — bar membulat yang tidak menempel tepi
/// layar, dan item aktif naik menjadi gelembung bulat yang menonjol melewati
/// tepi atas bar (tengah gelembung tepat di garis atas bar). Label tetap di
/// bawah, jadi posisi kelima item tidak bergeser saat tab berganti.
class DynamicBottomNav extends StatefulWidget {
  final int currentIndex;
  final Map<String, dynamic>? user;
  final ValueChanged<int>? onNavTap;

  const DynamicBottomNav({
    super.key,
    required this.currentIndex,
    this.user,
    this.onNavTap,
  });

  @override
  State<DynamicBottomNav> createState() => _DynamicBottomNavState();
}

class _DynamicBottomNavState extends State<DynamicBottomNav>
    with SingleTickerProviderStateMixin {
  // --- Geometri (dp), diturunkan dari proporsi referensi -------------------
  // Referensi 736x1374: bar 560x138, gelembung 92, label 12px.
  static const double _barHeight = 66;
  static const double _bubbleSize = 46;
  static const double _bubbleRise = _bubbleSize / 2;
  static const double _idleIconSize = 24;
  static const double _labelFontSize = 10.5;
  static const double _sideMargin = 12;
  static const double _bottomMargin = 10;
  // Jarak pusat ikon & label dari tepi atas bar (0,28 dan 0,57 tinggi bar).
  static const double _idleIconCenter = 19;
  static const double _labelCenter = 38;

  static const List<NavItem> _allNavItems = [
    NavItem(
      index: 0,
      label: 'Beranda',
      icon: Icons.home_outlined,
      activeIcon: Icons.home_rounded,
    ),
    NavItem(
      index: 1,
      label: 'Ujian',
      icon: Icons.assignment_outlined,
      activeIcon: Icons.assignment_rounded,
    ),
    NavItem(
      index: 2,
      label: 'Pembelajaran',
      icon: Icons.menu_book_outlined,
      activeIcon: Icons.menu_book_rounded,
    ),
    NavItem(
      index: 3,
      label: 'Jadwal',
      icon: Icons.calendar_month_outlined,
      activeIcon: Icons.calendar_month_rounded,
    ),
    NavItem(
      index: 4,
      label: 'Profil',
      icon: Icons.person_outline_rounded,
      activeIcon: Icons.person_rounded,
    ),
  ];

  late final AnimationController _popController;
  int? _pressedIndex;

  @override
  void initState() {
    super.initState();
    // Halaman tab dibangun ulang lewat pushReplacement, jadi gelembung item
    // aktif selalu "muncul" dari bar setiap kali tab ditekan.
    _popController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 620),
    )..forward();
  }

  @override
  void dispose() {
    _popController.dispose();
    super.dispose();
  }

  static final List<Widget Function(Map<String, dynamic>?)> _tabBuilders = [
    (user) => DashboardPage(user: user),
    (user) => UjianPage(user: user),
    (user) => MapelPage(user: user),
    (user) => JadwalPage(user: user),
    (user) => ProfilePage(user: user),
  ];

  void _handleNavigation(int targetIndex) {
    if (widget.onNavTap != null) {
      widget.onNavTap!(targetIndex);
      if (targetIndex == widget.currentIndex) {
        _popController.forward(from: 0);
      }
      return;
    }

    // Menekan tab yang sedang aktif: putar ulang animasi tanpa pindah halaman.
    if (targetIndex == widget.currentIndex) {
      _popController.forward(from: 0);
      return;
    }

    if (targetIndex < 0 || targetIndex >= _tabBuilders.length) return;
    Navigator.pushReplacement(
      context,
      SmoothPageRoute(
        page: _tabBuilders[targetIndex](widget.user),
        type: TransitionType.fadeThrough,
      ),
    );
  }

  // Lebar maksimal bar di layar besar: bar tidak melebar tanpa batas
  // mengikuti layar tablet/desktop, melainkan berhenti di 560 dan
  // berkumpul di tengah. Di HP (lebar < 584) tampilannya tidak berubah.
  static const double _lebarMaksBar = 560;

  @override
  Widget build(BuildContext context) {
    final double bottomInset = MediaQuery.paddingOf(context).bottom;
    final double barHeight = _barHeight + bottomInset;

    // Di layar lebar, margin samping membesar agar lebar bar berhenti
    // di _lebarMaksBar dan bar berkumpul di tengah layar.
    final double lebarLayar = MediaQuery.sizeOf(context).width;
    final double marginSamping = lebarLayar > _lebarMaksBar + (_sideMargin * 2)
        ? (lebarLayar - _lebarMaksBar) / 2
        : _sideMargin;

    return Padding(
      padding: EdgeInsets.only(
        left: marginSamping,
        right: marginSamping,
        bottom: _bottomMargin,
      ),
      child: SizedBox(
        height: _bubbleRise + barHeight,
        child: Stack(
          children: [
            // Bar navy melayang: keempat sudut membulat, tidak menempel tepi.
            Positioned(
              top: _bubbleRise,
              left: 0,
              right: 0,
              height: barHeight,
              child: DecoratedBox(
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.navy950.withValues(alpha: 0.28),
                      blurRadius: 18,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
              ),
            ),
            Positioned.fill(
              child: Row(
                children:
                    _allNavItems.map((item) => _buildNavItem(item)).toList(),
              ),
            ),
            ],
          ),
        ),
    );
  }

  Widget _buildNavItem(NavItem item) {
    final bool isActive = item.index == widget.currentIndex;
    final bool isPressed = _pressedIndex == item.index;

    // Ikon biasa duduk di dalam bar; gelembung aktif naik ke tepi atas bar.
    final double iconTop = isActive
        ? 0
        : _bubbleRise + _idleIconCenter - (_idleIconSize / 2);

    return Expanded(
      child: Semantics(
        button: true,
        selected: isActive,
        label: item.label,
        // Label dibaca sekali saja dari node Semantics; teks di dalamnya
        // tidak perlu menggandakan pengumuman pembaca layar.
        excludeSemantics: true,
        onTap: () => _handleNavigation(item.index),
        child: GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTapDown: (_) => setState(() => _pressedIndex = item.index),
          onTapCancel: () => setState(() => _pressedIndex = null),
          onTapUp: (_) => setState(() => _pressedIndex = null),
          onTap: () => _handleNavigation(item.index),
          child: Stack(
            alignment: Alignment.topCenter,
            children: [
              Positioned(
                top: iconTop,
                child: isActive
                    ? _buildActiveBubble(item)
                    : _buildIdleIcon(item, isPressed),
              ),
              Positioned(
                top: _bubbleRise + _labelCenter - (_labelFontSize / 2),
                child: AnimatedDefaultTextStyle(
                  duration: const Duration(milliseconds: 180),
                  curve: Curves.easeOut,
                  style: TextStyle(
                    color: isActive ? AppColors.accentBright : Colors.white70,
                    fontSize: _labelFontSize,
                    fontWeight: isActive ? FontWeight.w800 : FontWeight.w500,
                    letterSpacing: 0.2,
                    height: 1,
                  ),
                  child: Text(
                    item.label,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Gelembung item aktif: memantul naik dari dalam bar ke posisi akhir
  /// (tengah gelembung tepat di garis atas bar).
  Widget _buildActiveBubble(NavItem item) {
    return AnimatedBuilder(
      animation: _popController,
      builder: (context, child) {
        final double progress = _popController.value;
        final double scale =
            0.66 + 0.34 * Curves.elasticOut.transform(progress);
        final double lift = 16 * (1 - Curves.easeOutCubic.transform(progress));
        return Transform.translate(
          offset: Offset(0, lift),
          child: Transform.scale(scale: scale, child: child),
        );
      },
      child: Container(
        width: _bubbleSize,
        height: _bubbleSize,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: const LinearGradient(
            colors: [
              AppColors.accentBright,
              AppColors.accent,
              AppColors.accentDeep,
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          border: Border.all(color: Colors.white, width: 3),
          boxShadow: [
            BoxShadow(
              color: AppColors.accentDeep.withValues(alpha: 0.45),
              blurRadius: 14,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Icon(item.activeIcon, color: Colors.white, size: 24),
      ),
    );
  }

  Widget _buildIdleIcon(NavItem item, bool isPressed) {
    return AnimatedScale(
      scale: isPressed ? 0.86 : 1,
      duration: const Duration(milliseconds: 130),
      curve: Curves.easeOut,
      child: Icon(item.icon, color: Colors.white70, size: _idleIconSize),
    );
  }
}
