import 'package:flutter/material.dart';
import 'login_page.dart';

void main() {
  runApp(const BinaroApp());
}

class BinaroApp extends StatelessWidget {
  const BinaroApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Binaro - SDN Kalitapen 1',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        fontFamily: 'Roboto',
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF1D5A86)),
        useMaterial3: true,
      ),
      home: const LoginPage(),
    );
  }
}

