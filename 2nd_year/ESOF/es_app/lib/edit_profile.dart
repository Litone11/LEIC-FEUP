import 'package:flutter/material.dart';

class EditProfileScreen extends StatefulWidget {
  @override
  _EditProfileScreenState createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this); // Duas abas
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Edit Profile'),
        bottom: TabBar(
          controller: _tabController,
          tabs: [
            Tab(text: 'Update Username'),
            Tab(text: 'Change Password'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildUpdateUsernameTab(),
          _buildChangePasswordTab(),
        ],
      ),
    );
  }

  Widget _buildUpdateUsernameTab() {
    final TextEditingController usernameController = TextEditingController();

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('New Username:', style: TextStyle(fontSize: 18)),
          TextField(
            controller: usernameController,
            decoration: InputDecoration(
              hintText: 'Enter new username',
              border: OutlineInputBorder(),
            ),
          ),
          SizedBox(height: 20),
          ElevatedButton(
            onPressed: () {
              // Lógica para atualizar o nome de usuário
              String newUsername = usernameController.text;
              // Você pode adicionar aqui a lógica para atualizar o nome de usuário
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Username updated to $newUsername')),
              );
            },
            child: Text('Update Username'),
          ),
        ],
      ),
    );
  }

  Widget _buildChangePasswordTab() {
    final TextEditingController oldPasswordController = TextEditingController();
    final TextEditingController newPasswordController = TextEditingController();
    final TextEditingController confirmPasswordController = TextEditingController();

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Old Password:', style: TextStyle(fontSize: 18)),
          TextField(
            controller: oldPasswordController,
            obscureText: true,
            decoration: InputDecoration(
              hintText: 'Enter old password',
              border: OutlineInputBorder(),
            ),
          ),
          SizedBox(height: 20),
          Text('New Password:', style: TextStyle(fontSize: 18)),
          TextField(
            controller: newPasswordController,
            obscureText: true,
            decoration: InputDecoration(
              hintText: 'Enter new password',
              border: OutlineInputBorder(),
            ),
          ),
          SizedBox(height: 20),
          Text('Confirm New Password:', style: TextStyle(fontSize: 18)),
          TextField(
            controller: confirmPasswordController,
            obscureText: true,
            decoration: InputDecoration(
              hintText: 'Confirm new password',
              border: OutlineInputBorder(),
            ),
          ),
          SizedBox(height: 20),
          ElevatedButton(
            onPressed: () {
              // Lógica para mudar a senha
              String newPassword = newPasswordController.text;
              String confirmPassword = confirmPasswordController.text;

              if (newPassword == confirmPassword) {
                // Atualize a senha
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Password updated successfully')),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Passwords do not match')),
                );
              }
            },
            child: Text('Change Password'),
          ),
        ],
      ),
    );
  }
}
