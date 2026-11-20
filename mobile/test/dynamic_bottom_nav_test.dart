import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:binaro_flutter/utils/constants.dart';
import 'package:binaro_flutter/widgets/dynamic_bottom_nav.dart';

/// Bungkus nav dalam kerangka layar agar MediaQuery dan tema ikut terpakai.
Widget _wrap({required int currentIndex, ValueChanged<int>? onNavTap}) {
  return MaterialApp(
    theme: ThemeData(fontFamily: 'Plus Jakarta Sans'),
    home: Scaffold(
      backgroundColor: AppColors.background,
      bottomNavigationBar: DynamicBottomNav(
        currentIndex: currentIndex,
        onNavTap: onNavTap ?? (_) {},
      ),
    ),
  );
}

void main() {
  testWidgets('kelima label tab selalu tampil', (tester) async {
    await tester.pumpWidget(_wrap(currentIndex: 0));
    await tester.pumpAndSettle();

    expect(find.text('Beranda'), findsOneWidget);
    expect(find.text('Ujian'), findsOneWidget);
    expect(find.text('Pembelajaran'), findsOneWidget);
    expect(find.text('Jadwal'), findsOneWidget);
    expect(find.text('Profil'), findsOneWidget);
  });

  testWidgets('tab aktif ditandai selected, tab lain tidak', (tester) async {
    await tester.pumpWidget(_wrap(currentIndex: 2));
    await tester.pumpAndSettle();

    final SemanticsHandle handle = tester.ensureSemantics();
    expect(
      tester.getSemantics(find.text('Pembelajaran')),
      matchesSemantics(
        label: 'Pembelajaran',
        isButton: true,
        isSelected: true,
        hasSelectedState: true,
        hasTapAction: true,
      ),
    );
    expect(
      tester.getSemantics(find.text('Beranda')),
      matchesSemantics(
        label: 'Beranda',
        isButton: true,
        isSelected: false,
        hasSelectedState: true,
        hasTapAction: true,
      ),
    );
    handle.dispose();
  });

  testWidgets('menekan tab mengirim indeks yang benar', (tester) async {
    int? tapped;
    await tester.pumpWidget(
      _wrap(currentIndex: 0, onNavTap: (index) => tapped = index),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Profil'));
    await tester.pumpAndSettle();

    expect(tapped, 4);
  });

  testWidgets('gelembung aktif menonjol di atas bar', (tester) async {
    await tester.pumpWidget(_wrap(currentIndex: 1));
    await tester.pumpAndSettle();

    // Bar navy dan gelembung aktif harus terlihat sebagai dua kotak berbeda;
    // puncak gelembung berada di atas puncak bar.
    final Finder navFinder = find.byType(DynamicBottomNav);
    final Rect navRect = tester.getRect(navFinder);
    final Rect labelRect = tester.getRect(find.text('Ujian'));

    // Label tetap berada di dalam area nav, tidak keluar dari bar.
    expect(navRect.contains(labelRect.center), isTrue);
    // Tinggi nav cukup untuk rise gelembung + tinggi bar (66 dp) + margin.
    expect(navRect.height, greaterThanOrEqualTo(66 + 23));
  });
}
