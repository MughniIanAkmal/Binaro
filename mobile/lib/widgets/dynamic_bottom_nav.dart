import 'package:flutter/material.dart';
import '../screens/dashboard_page.dart';
import '../screens/mapel_page.dart';
import '../screens/profile_page.dart';
import '../screens/ujian_page.dart';
import '../utils/page_transitions.dart';

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

/// Bottom navigation baku Binaro: lima tombol dengan posisi tetap.
/// Halaman aktif ditandai warna kuning (aksen) di tempatnya — tanpa
/// perpindahan posisi (revisi UI: spotlight tengah dihapus).
class DynamicBottomNav extends StatelessWidget {
  final int currentIndex;
  final Map<String, dynamic>? user;
  final ValueChanged<int>? onNavTap;

  const DynamicBottomNav({
    super.key,
    required this.currentIndex,
    this.user,
    this.onNavTap,
  });

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

  void _handleNavigation(BuildContext context, int targetIndex) {
    if (onNavTap != null) {
      onNavTap!(targetIndex);
      return;
    }

    if (targetIndex == currentIndex) return;

    if (targetIndex == 0) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: DashboardPage(user: user),
          type: TransitionType.fadeThrough,
        ),
      );
    } else if (targetIndex == 1) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: UjianPage(user: user),
          type: TransitionType.fadeThrough,
        ),
      );
    } else if (targetIndex == 2) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: MapelPage(user: user),
          type: TransitionType.fadeThrough,
        ),
      );
    } else if (targetIndex == 3) {
      ScaffoldMessenger.of(context).hideCurrentSnackBar();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.calendar_month_rounded, color: Colors.white, size: 20),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Fitur Jadwal Pelajaran segera hadir!',
                  style: TextStyle(fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
          backgroundColor: const Color(0xFF13527D),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          duration: const Duration(seconds: 2),
        ),
      );
    } else if (targetIndex == 4) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: ProfilePage(user: user),
          type: TransitionType.fadeThrough,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding = MediaQuery.paddingOf(context).bottom;

    return Container(
      height: 68 + bottomPadding,
      decoration: const BoxDecoration(
        color: Color(0xFF13527D),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black26,
            blurRadius: 16,
            offset: Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children:
              _allNavItems.map((item) => _buildNavItem(context, item)).toList(),
        ),
      ),
    );
  }

  Widget _buildNavItem(BuildContext context, NavItem item) {
    final bool isActive = item.index == currentIndex;

    // Label: kuning saat aktif, putih pudar saat tidak.
    final Color labelColor =
        isActive ? const Color(0xFFF59E0B) : Colors.white70;

    return Expanded(
      child: InkWell(
        onTap: () => _handleNavigation(context, item.index),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Slot ikon setinggi tetap agar item tidak bergeser saat
              // berganti aktif/tidak (lingkaran 40dp vs ikon 24dp).
              SizedBox(
                height: 40,
                width: 40,
                child: Center(
                  child: isActive
                      ? Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            gradient: const LinearGradient(
                              colors: [
                                Color(0xFFFBBF24),
                                Color(0xFFF59E0B),
                                Color(0xFFD97706),
                              ],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            border: Border.all(color: Colors.white, width: 3),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFFF59E0B)
                                    .withValues(alpha: 0.45),
                                blurRadius: 12,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: Icon(
                            item.activeIcon,
                            color: Colors.white,
                            size: 22,
                          ),
                        )
                      : Icon(item.icon, color: Colors.white70, size: 24),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                item.label,
                style: TextStyle(
                  color: labelColor,
                  fontSize: 11,
                  fontWeight: isActive ? FontWeight.w800 : FontWeight.w500,
                  letterSpacing: 0.2,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
