import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

import 'group.dart';
import 'task.dart';

class GroupDetailPage extends StatelessWidget {
  final Group group;
  GroupDetailPage({required this.group});

  final user = FirebaseAuth.instance.currentUser;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Color(0xFF121212),
      appBar: AppBar(
        backgroundColor: Color(0xFF1E1E2C),
        title: Text(group.name, style: TextStyle(color: Colors.white)),
        iconTheme: IconThemeData(color: Colors.white),
      ),
      body: _buildTaskList(),
      floatingActionButton: FloatingActionButton(
        backgroundColor: Color(0xFF00B4D8),
        shape: CircleBorder(),
        onPressed: () => _addTaskDialog(context),
        child: Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildTaskList() {
    return StreamBuilder<QuerySnapshot>(
      stream: FirebaseFirestore.instance
          .collection('groups')
          .doc(group.id)
          .collection('tasks')
          .snapshots(),
      builder: (context, snapshot) {
        if (snapshot.hasError) {
          return Center(child: Text('Error loading tasks'));
        }
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Center(child: CircularProgressIndicator());
        }
        final tasks = snapshot.data!.docs;
        if (tasks.isEmpty) {
          return Center(child: Text('No tasks. Tap + to add.'));
        }
        return ListView(
          children: tasks.map((doc) {
            final task = Task.fromDoc(doc);
            return Card(
              color: Color(0xFF1E1E2C),
              margin: EdgeInsets.symmetric(vertical: 4, horizontal: 8),
              child: ListTile(
                title: Text(task.title, style: TextStyle(color: Colors.white)),
                leading: Icon(
                  task.isDone ? Icons.check_circle : Icons.radio_button_unchecked,
                  color: task.isDone ? Colors.tealAccent : Colors.white70,
                ),
                onTap: () async {
                  await doc.reference.update({'isDone': !task.isDone});
                },
                trailing: IconButton(
                  icon: Icon(Icons.delete, color: Colors.redAccent),
                  onPressed: () async {
                    await doc.reference.delete();
                  },
                ),
              ),
            );
          }).toList(),
        );
      },
    );
  }

  void _addTaskDialog(BuildContext context) {
    String title = '';
    DateTime? selectedDate;

    showDialog(
      context: context,
      builder: (_) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: Text('New Task'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                decoration: InputDecoration(labelText: 'Task Title'),
                onChanged: (value) => title = value,
              ),
              SizedBox(height: 8),
              TextButton(
                onPressed: () async {
                  final picked = await showDatePicker(
                    context: context,
                    initialDate: DateTime.now(),
                    firstDate: DateTime(2020),
                    lastDate: DateTime(2100),
                  );
                  if (picked != null) {
                    setState(() => selectedDate = picked);
                  }
                },
                child: Text(
                  selectedDate == null
                      ? 'Choose Due Date'
                      : 'Due: ${selectedDate!.toLocal().toString().split(' ')[0]}',
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('Cancel'),
            ),
            TextButton(
              onPressed: () async {
                if (title.trim().isEmpty || selectedDate == null) return;
                Navigator.pop(context);
                await FirebaseFirestore.instance
                    .collection('groups')
                    .doc(group.id)
                    .collection('tasks')
                    .add({
                  'title': title.trim(),
                  'isDone': false,
                  'dueDate': Timestamp.fromDate(selectedDate!),
                });
              },
              child: Text('Add'),
            ),
          ],
        ),
      ),
    );
  }
}