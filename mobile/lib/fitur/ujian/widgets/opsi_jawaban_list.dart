import 'package:flutter/material.dart';
import 'package:binaro_flutter/inti/konstanta/app_decorations.dart';
import 'package:binaro_flutter/inti/konstanta/constants.dart';

/// Daftar pilihan jawaban A/B/C/D yang dipakai bersama di [KuisPage] dan
/// [PengerjaanUjianPage] agar tidak terjadi duplikasi UI.
class OpsiJawabanList extends StatelessWidget {
  final Map<String, dynamic> question;
  final String selectedOption;
  final ValueChanged<String> onSelect;
  final Color activeColor;
  final Color activeBadgeBg;
  final String headerText;

  const OpsiJawabanList({
    super.key,
    required this.question,
    required this.selectedOption,
    required this.onSelect,
    this.activeColor = AppColors.primary,
    this.activeBadgeBg = AppColors.primaryLight,
    this.headerText = 'Pilih satu jawaban yang benar:',
  });

  @override
  Widget build(BuildContext context) {
    final options = [
      {'key': 'A', 'text': question['opsi_a']?.toString() ?? '-'},
      {'key': 'B', 'text': question['opsi_b']?.toString() ?? '-'},
      {'key': 'C', 'text': question['opsi_c']?.toString() ?? '-'},
      {'key': 'D', 'text': question['opsi_d']?.toString() ?? '-'},
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          headerText,
          style: const TextStyle(
            fontSize: 12.5,
            fontWeight: FontWeight.w700,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 12),
        ...options.map((opt) {
          final String key = opt['key']!;
          final String text = opt['text']!;
          final bool isSelected = (selectedOption == key);

          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: InkWell(
              onTap: () => onSelect(key),
              borderRadius: BorderRadius.circular(16),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                decoration: AppDecorations.card(
                  borderColor: isSelected ? activeColor : AppColors.border,
                  borderWidth: isSelected ? 1.8 : 1,
                ),
                child: Row(
                  children: [
                    Container(
                      width: 34,
                      height: 34,
                      decoration: BoxDecoration(
                        color: isSelected ? activeColor : AppColors.surfaceAlt,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        key,
                        style: TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          color: isSelected
                              ? Colors.white
                              : AppColors.textPrimary,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        text,
                        style: TextStyle(
                          fontSize: 13.5,
                          fontWeight:
                              isSelected ? FontWeight.w700 : FontWeight.w500,
                          color: AppColors.textPrimary,
                          height: 1.35,
                        ),
                      ),
                    ),
                    if (isSelected)
                      Icon(Icons.check_circle_rounded,
                          color: activeColor, size: 20),
                  ],
                ),
              ),
            ),
          );
        }),
      ],
    );
  }
}
