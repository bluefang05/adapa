import 'dart:math' as math;

import 'package:flutter/material.dart';

/// Replays a short horizontal shake whenever [signal] changes.
class ShakeFeedback extends StatefulWidget {
  const ShakeFeedback({
    super.key,
    required this.signal,
    required this.child,
  });

  final int signal;
  final Widget child;

  @override
  State<ShakeFeedback> createState() => _ShakeFeedbackState();
}

class _ShakeFeedbackState extends State<ShakeFeedback>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 330),
    );
  }

  @override
  void didUpdateWidget(covariant ShakeFeedback oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.signal != oldWidget.signal) {
      _controller.forward(from: 0);
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      child: widget.child,
      builder: (context, child) {
        final progress = _controller.value;
        final decay = 1 - progress;
        final dx = math.sin(progress * math.pi * 6) * 9 * decay;
        return Transform.translate(
          offset: Offset(dx, 0),
          child: child,
        );
      },
    );
  }
}
