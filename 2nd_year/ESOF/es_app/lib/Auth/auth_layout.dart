import 'package:es_app/Auth/auth_service.dart';
import 'package:flutter/material.dart';
import 'package:es_app/Auth/app_loading_page.dart';
import 'package:es_app/Auth/welcome_page.dart';
import 'package:es_app/homepage.dart';

class AuthLayout extends StatelessWidget {
  const AuthLayout({
    super.key,
    this.pageIfNotConnected,
  });

  final Widget? pageIfNotConnected;

  @override
  Widget build(BuildContext) {
    return ValueListenableBuilder(
        valueListenable: authService,
        builder: (context, authService, child) {
          return StreamBuilder(
            stream: authService.authStateChanges,
            builder: (context, snapshot) {
              Widget? widget;
                if (snapshot.connectionState == ConnectionState.waiting) {
                  widget = AppLoadingPage();
                } else if (snapshot.hasData) {
                  widget = HomePage();
                } else {
                  widget = pageIfNotConnected ?? const WelcomePage();
                }
                return widget;
              },
          );
        },
      );
  }
}
