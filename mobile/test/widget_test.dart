import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:binaro_flutter/main.dart';

void main() {
  testWidgets('Halaman login tampil dengan field NISN dan Kata Sandi',
      (WidgetTester tester) async {
    await tester.pumpWidget(const BinaroApp());
    await tester.pumpAndSettle();

    // Judul form login
    expect(find.text('Login'), findsWidgets);

    // Field tunggal identitas (selaras login web) + kata sandi
    expect(find.text('NISN / NIS'), findsOneWidget);
    expect(find.text('Kata Sandi'), findsOneWidget);
    expect(find.byType(TextField), findsNWidgets(2));
  });
}
