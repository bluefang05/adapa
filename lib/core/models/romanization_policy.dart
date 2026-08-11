enum RomanizationMode {
  visible,
  hintOnly,
  hidden,
  disabled,
}

class RomanizationPolicy {
  const RomanizationPolicy({
    required this.mode,
    required this.canReveal,
  });

  final RomanizationMode mode;
  final bool canReveal;

  bool get visibleByDefault => mode == RomanizationMode.visible;
  bool get completelyDisabled => mode == RomanizationMode.disabled;

  factory RomanizationPolicy.fromDynamic(dynamic value) {
    if (value is String) {
      if (value == 'visible_por_defecto') {
        return const RomanizationPolicy(
          mode: RomanizationMode.visible,
          canReveal: true,
        );
      }
    }

    if (value is Map) {
      final map = Map<String, dynamic>.from(value);
      final raw = map['default']?.toString();
      final explicitHint = map['available_as_hint'];

      switch (raw) {
        case 'hint_only':
          return const RomanizationPolicy(
            mode: RomanizationMode.hintOnly,
            canReveal: true,
          );
        case 'hidden':
          return RomanizationPolicy(
            mode: RomanizationMode.hidden,
            canReveal: explicitHint != false,
          );
        case 'disabled':
          return const RomanizationPolicy(
            mode: RomanizationMode.disabled,
            canReveal: false,
          );
      }
    }

    return const RomanizationPolicy(
      mode: RomanizationMode.hidden,
      canReveal: false,
    );
  }
}
