import 'package:es_app/Auth/auth_service.dart'; // Supondo que o seu serviço de autenticação esteja aqui
import 'package:flutter/material.dart';

class ChangePasswordPage extends StatefulWidget {
  const ChangePasswordPage({
    super.key,
    required this.email,
  });
  final String email;

  @override
  State<ChangePasswordPage> createState() => _ChangePasswordPageState();
}

class _ChangePasswordPageState extends State<ChangePasswordPage> {
  TextEditingController controllerEmail = TextEditingController();
  TextEditingController controllerCurrentPassword = TextEditingController();
  TextEditingController controllerNewPassword = TextEditingController();
  TextEditingController controllerConfirmPassword = TextEditingController();  // Corrigido
  final formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    controllerEmail.text = widget.email;
  }

  @override
  void dispose() {
    controllerEmail.dispose();
    controllerCurrentPassword.dispose();
    controllerNewPassword.dispose();
    controllerConfirmPassword.dispose();  // Adicionado para garantir que todos os controladores sejam descartados
    super.dispose();
  }

  void updatePassword() async {
    if (controllerNewPassword.text == controllerConfirmPassword.text) {
      // Lógica para alterar a senha
      try {
        await authService.value.resetPasswordFromCurrentPassword(
          currentPassword: controllerCurrentPassword.text,
          newPassword: controllerNewPassword.text,
          email: controllerEmail.text,
        );
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Password updated successfully')),
        );
        Navigator.pop(context);  // Voltar para a tela anterior após a atualização
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error updating password: $e')),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Passwords do not match')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Change Password'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: formKey, // Usando o Form com validação
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Old Password:', style: TextStyle(fontSize: 18)),
              TextFormField(
                controller: controllerCurrentPassword,
                obscureText: true,
                decoration: InputDecoration(
                  hintText: 'Enter old password',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter the current password';
                  }
                  return null;
                },
              ),
              SizedBox(height: 20),
              Text('New Password:', style: TextStyle(fontSize: 18)),
              TextFormField(
                controller: controllerNewPassword,
                obscureText: true,
                decoration: InputDecoration(
                  hintText: 'Enter new password',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter a new password';
                  }
                  return null;
                },
              ),
              SizedBox(height: 20),
              Text('Confirm New Password:', style: TextStyle(fontSize: 18)),
              TextFormField(
                controller: controllerConfirmPassword,
                obscureText: true,
                decoration: InputDecoration(
                  hintText: 'Confirm new password',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please confirm the new password';
                  }
                  return null;
                },
              ),
              SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  if (formKey.currentState!.validate()) {
                    updatePassword();
                  }
                },
                child: Text('Change Password'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
