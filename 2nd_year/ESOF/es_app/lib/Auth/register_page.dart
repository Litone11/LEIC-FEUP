import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:es_app/Auth/auth_service.dart';
import 'package:es_app/Auth/reset_password_page.dart';
import 'package:es_app/Auth/welcome_page.dart';
import 'package:es_app/homepage.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  TextEditingController controllerUsername = TextEditingController();
  TextEditingController controllerEmail = TextEditingController();
  TextEditingController controllerPassword = TextEditingController();
  final formKey = GlobalKey<FormState>();
  String errorMessage = '';

  @override
  void dispose() {
    controllerUsername.dispose();
    controllerEmail.dispose();
    controllerPassword.dispose();
    super.dispose();
  }

  void register() async {
    try {
      // Verificar se o username já existe
      final existingUsers = await FirebaseFirestore.instance
          .collection('users')
          .where('username', isEqualTo: controllerUsername.text.trim())
          .get();

      if (existingUsers.docs.isNotEmpty) {
        setState(() {
          errorMessage = 'Username already taken';
        });
        return;
      }

      // Criar conta
      final userCredential = await authService.value.createAccount(
        email: controllerEmail.text,
        password: controllerPassword.text,
      );
      final user = userCredential.user;

      if (user != null) {
        // Guardar user no Firestore
        await FirebaseFirestore.instance.collection('users').doc(user.uid).set({
          'username': controllerUsername.text.trim(),
          'email': user.email,
          'createdAt': FieldValue.serverTimestamp(),
        });

        // Criar grupo "Inbox"
        await FirebaseFirestore.instance.collection('groups').add({
          'name': 'Inbox',
          'creatorId': user.uid,
          'memberIds': [user.uid],
          'timestamp': FieldValue.serverTimestamp(),
        });

        // Ir para HomePage
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => HomePage()),
        );
      }
    } on FirebaseAuthException catch (e) {
      setState(() {
        errorMessage = e.message ?? 'There is an error';
      });
    }
  }

  InputDecoration customInputDecoration(String label, IconData icon) {
    return InputDecoration(
      prefixIcon: Icon(icon, color: Colors.deepPurple),
      labelText: label,
      filled: true,
      fillColor: Colors.deepPurple.shade50,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(20),
        borderSide: BorderSide.none,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        backgroundColor: Colors.deepPurple,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const WelcomePage()),
            );
          },
        ),
        title: const Text('Register', style: TextStyle(color: Colors.white)),
        centerTitle: true,
        elevation: 0,
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 28.0),
          child: Form(
            key: formKey,
            child: Column(
              children: [
                Text(
                  'Create Account ✨',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.deepPurple,
                  ),
                ),
                const SizedBox(height: 24),

                // Username
                TextFormField(
                  controller: controllerUsername,
                  decoration: customInputDecoration('Username', Icons.person),
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Please enter a username';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // Email
                TextFormField(
                  controller: controllerEmail,
                  decoration: customInputDecoration('Email', Icons.email),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Please enter your email';
                    }
                    if (!value.contains('@')) {
                      return 'Please enter a valid email';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // Password
                TextFormField(
                  controller: controllerPassword,
                  decoration: customInputDecoration('Password', Icons.lock),
                  obscureText: true,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Please enter your password';
                    }
                    if (value.length < 6) {
                      return 'Password must be at least 6 characters';
                    }
                    return null;
                  },
                ),

                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const ResetPasswordPage(email: ''),
                        ),
                      );
                    },
                    child: const Text('Forgot Password?'),
                  ),
                ),

                if (errorMessage.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 8.0),
                    child: Text(
                      errorMessage,
                      style: const TextStyle(color: Colors.red),
                    ),
                  ),

                const SizedBox(height: 24),

                ElevatedButton(
                  onPressed: () {
                    if (formKey.currentState!.validate()) {
                      register();
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    minimumSize: const Size(double.infinity, 55),
                    backgroundColor: Colors.deepPurple,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(30),
                    ),
                  ),
                  child: const Text('Register', style: TextStyle(fontSize: 18)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
