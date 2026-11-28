/// Extension helper untuk parsing field JSON secara aman tanpa boilerplate.
extension JsonMapExt on Map<String, dynamic> {
  /// Ambil nilai integer dari [key], mendukung `int`, `num`, maupun `String`.
  int parseInt(String key, {int defaultValue = 0}) {
    final val = this[key];
    if (val is int) return val;
    if (val is num) return val.toInt();
    return int.tryParse(val?.toString() ?? '') ?? defaultValue;
  }

  /// Ambil nilai double dari [key], mendukung `double`, `num`, maupun `String`.
  double parseDouble(String key, {double defaultValue = 0.0}) {
    final val = this[key];
    if (val is double) return val;
    if (val is num) return val.toDouble();
    return double.tryParse(val?.toString() ?? '') ?? defaultValue;
  }

  /// Ambil nilai string dari [key] dengan fallback [defaultValue].
  String parseString(String key, {String defaultValue = ''}) {
    final val = this[key];
    if (val == null) return defaultValue;
    return val.toString();
  }

  /// Ambil nilai boolean dari [key], mendukung `bool`, `int` (1/0), dan `String`.
  bool parseBool(String key, {bool defaultValue = false}) {
    final val = this[key];
    if (val is bool) return val;
    if (val is int) return val == 1;
    if (val is String) {
      final lower = val.toLowerCase();
      if (lower == 'true' || lower == '1') return true;
      if (lower == 'false' || lower == '0') return false;
    }
    return defaultValue;
  }
}
