// lib/app_drawer.dart
import 'package:es_app/Auth/auth_service.dart';
import 'package:es_app/Auth/welcome_page.dart';
import 'package:flutter/material.dart';
import 'homepage.dart';
import 'invitations_page.dart';

class AppDrawer extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: ListView(
        children: [
          DrawerHeader(child: Text('Menu', style: TextStyle(fontSize: 24))),
          ListTile(
            leading: Icon(Icons.home),
            title: Text('Home'),
            onTap: () {
              Navigator.pop(context);
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(builder: (_) => HomePage()),
              );
            },
          ),
          ListTile(
            leading: Icon(Icons.mail),
            title: Text('Invitations'),
            onTap: () {
              Navigator.pop(context);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => InvitationsPage()),
              );
            },
          ),
          Divider(),
          ListTile(
            leading: Icon(Icons.logout),
            title: Text('Sign Out'),
            onTap: () async {
              await authService.value.signOut();
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(builder: (_) => WelcomePage()),
              );
            },
          ),
        ],
      ),
    );
  }
}
