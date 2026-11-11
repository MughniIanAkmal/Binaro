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

/// Dynamic Center Spotlight Bottom Navigation Bar
/// Menampilkan halaman aktif selalu di posisi TENGAH dengan tombol melayang (Hero Elevated)
/// yang menonjol dan berganti dinamis seiring halaman yang dikunjungi.
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

class _DynamicBottomNavState extends State<DynamicBottomNav> with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

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
      label: 'Mapel',
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

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1600),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 1.0, end: 1.05).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  void _handleNavigation(int targetIndex) {
    if (widget.onNavTap != null) {
      widget.onNavTap!(targetIndex);
      return;
    }

    if (targetIndex == widget.currentIndex) return;

    if (targetIndex == 0) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: DashboardPage(user: widget.user),
          type: TransitionType.fadeThrough,
        ),
      );
    } else if (targetIndex == 1) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: UjianPage(user: widget.user),
          type: TransitionType.fadeThrough,
        ),
      );
    } else if (targetIndex == 2) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: MapelPage(user: widget.user),
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
              Text(
                'Fitur Jadwal Pelajaran segera hadir!',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
            ],
          ),
          backgroundColor: const Color(0xFF1D5A86),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          duration: const Duration(seconds: 2),
        ),
      );
    } else if (targetIndex == 4) {
      Navigator.pushReplacement(
        context,
        SmoothPageRoute(
          page: ProfilePage(user: widget.user),
          type: TransitionType.fadeThrough,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding = MediaQuery.paddingOf(context).bottom;

    // Item aktif saat ini
    final activeItem = _allNavItems.firstWhere(
      (item) => item.index == widget.currentIndex,
      orElse: () => _allNavItems[0],
    );

    // 4 Item lainnya yang tidak aktif
    final remainingItems = _allNavItems.where((item) => item.index != widget.currentIndex).toList();
    final leftItems = remainingItems.take(2).toList();
    final rightItems = remainingItems.skip(2).take(2).toList();

    return Stack(
      clipBehavior: Clip.none,
      alignment: Alignment.bottomCenter,
      children: [
        // Bilah Navigasi Dasar
        Container(
          height: 64 + bottomPadding,
          decoration: const BoxDecoration(
            color: Color(0xFF1D5A86),
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
              children: [
                // Sayap Kiri (2 Item)
                Expanded(
                  flex: 5,
                  child: Row(
                    children: leftItems.map((item) => _buildSideNavItem(item)).toList(),
                  ),
                ),

                // Ruang Khusus Panggung Tengah
                const SizedBox(width: 76),

                // Sayap Kanan (2 Item)
                Expanded(
                  flex: 5,
                  child: Row(
                    children: rightItems.map((item) => _buildSideNavItem(item)).toList(),
                  ),
                ),
              ],
            ),
          ),
        ),

        // Panggung Tengah Hero Spotlight (Melayang -18px di atas bilah)
        Positioned(
          top: -18,
          child: _buildCenterHeroButton(activeItem),
        ),
      ],
    );
  }

  /// Tombol samping (tidak aktif)
  Widget _buildSideNavItem(NavItem item) {
    return Expanded(
      child: InkWell(
        onTap: () => _handleNavigation(item.index),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                item.icon,
                color: Colors.white70,
                size: 23,
              ),
              const SizedBox(height: 3),
              Text(
                item.label,
                style: const TextStyle(
                  color: Colors.white70,
                  fontSize: 10.5,
                  fontWeight: FontWeight.w500,
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

  /// Tombol tengah melayang (Hero Spotlight)
  Widget _buildCenterHeroButton(NavItem activeItem) {
    return SizedBox(
      width: 76,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Lingkaran melayang di atas bilah navigasi
          ScaleTransition(
            scale: _pulseAnimation,
            child: Container(
              width: 54,
              height: 54,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: const LinearGradient(
                  colors: [Color(0xFFFBBF24), Color(0xFFF59E0B), Color(0xFFD97706)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                border: Border.all(color: Colors.white, width: 3),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFFF59E0B).withValues(alpha: 0.55),
                    blurRadius: 16,
                    spreadRadius: 2,
                    offset: const Offset(0, 4),
                  ),
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.2),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () => _handleNavigation(activeItem.index),
                  customBorder: const CircleBorder(),
                  child: Center(
                    child: AnimatedSwitcher(
                      duration: const Duration(milliseconds: 320),
                      transitionBuilder: (child, animation) {
                        return RotationTransition(
                          turns: Tween<double>(begin: 0.85, end: 1.0).animate(
                            CurvedAnimation(parent: animation, curve: Curves.easeOutBack),
                          ),
                          child: ScaleTransition(
                            scale: animation,
                            child: FadeTransition(
                              opacity: animation,
                              child: child,
                            ),
                          ),
                        );
                      },
                      child: Icon(
                        activeItem.activeIcon,
                        key: ValueKey<int>(activeItem.index),
                        color: Colors.white,
                        size: 28,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 3),

          // Label teks aktif di bawah lingkaran
          AnimatedSwitcher(
            duration: const Duration(milliseconds: 250),
            transitionBuilder: (child, animation) {
              return FadeTransition(
                opacity: animation,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0.0, 0.3),
                    end: Offset.zero,
                  ).animate(animation),
                  child: child,
                ),
              );
            },
            child: Text(
              activeItem.label,
              key: ValueKey<String>(activeItem.label),
              style: const TextStyle(
                color: Color(0xFFFBBF24),
                fontSize: 11,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.3,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

