// lib/homepage.dart
import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

import 'app_drawer.dart';
import 'group_detail_page.dart';
import 'group.dart';
import 'calendar.dart';

class HomePage extends StatefulWidget {
  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final user = FirebaseAuth.instance.currentUser;
  bool _isTodayVisible = true;
  String _taskViewMode = 'Today';

  Stream<List<Map<String, dynamic>>> _getTodayTasks() {
    final now = DateTime.now();
    late DateTime fromDate;
    late DateTime toDate;

    switch (_taskViewMode) {
      case 'Next Week':
        fromDate = DateTime(now.year, now.month, now.day);
        toDate = fromDate.add(Duration(days: 7));
        break;
      case 'Next Month':
        fromDate = DateTime(now.year, now.month, now.day);
        toDate = fromDate.add(Duration(days: 31));
        break;
      default:
        fromDate = DateTime(now.year, now.month, now.day);
        toDate = fromDate.add(Duration(days: 1));
    }

    return FirebaseFirestore.instance
        .collection('groups')
        .where('memberIds', arrayContains: user!.uid)
        .snapshots()
        .asyncMap((groupSnapshot) async {
      List<Map<String, dynamic>> allTasks = [];

      for (var groupDoc in groupSnapshot.docs) {
        final taskQuery = await groupDoc.reference
            .collection('tasks')
            .where('dueDate', isGreaterThanOrEqualTo: Timestamp.fromDate(fromDate))
            .where('dueDate', isLessThan: Timestamp.fromDate(toDate))
            .get();

        for (var taskDoc in taskQuery.docs) {
          allTasks.add({
            'id': taskDoc.id,
            'groupName': groupDoc['name'],
            'groupId': groupDoc.id,
            'title': taskDoc['title'],
            'isDone': taskDoc['isDone'],
            'dueDate': taskDoc['dueDate'],
          });
        }
      }

      return allTasks;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: AppDrawer(),
      backgroundColor: Color(0xFF121212),
      body: Stack(
        children: [
          Column(
            children: [
              Expanded(
                flex: 1,
                child: Padding(
                  padding: const EdgeInsets.only(top: 40.0, left: 16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'TaskMate',
                        style: TextStyle(
                          fontSize: 32,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      Expanded(child: _buildGroupList()),
                    ],
                  ),
                ),
              ),
            ],
          ),
          AnimatedPositioned(
            duration: Duration(milliseconds: 300),
            curve: Curves.easeInOut,
            bottom: _isTodayVisible
                ? 0
                : -MediaQuery.of(context).size.height * 0.5 + 60,
            left: 0,
            right: 0,
            height: MediaQuery.of(context).size.height * 0.5,
            child: Container(
              decoration: BoxDecoration(
                color: Color(0xFF1A1A1A),
                borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
              ),
              child: Column(
                children: [
                  GestureDetector(
                    onTap: () {
                      setState(() {
                        _isTodayVisible = !_isTodayVisible;
                      });
                    },
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 12.0),
                      child: Icon(
                        _isTodayVisible
                            ? Icons.keyboard_arrow_down
                            : Icons.keyboard_arrow_up,
                        color: Colors.white70,
                        size: 28,
                      ),
                    ),
                  ),
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: ['Today', 'This Week', 'This Month'].map((label) {
                                final isSelected = _taskViewMode == label;
                                return Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 4.0),
                                  child: ChoiceChip(
                                    label: Text(label),
                                    selected: isSelected,
                                    selectedColor: Colors.teal,
                                    onSelected: (_) {
                                      setState(() {
                                        _taskViewMode = label;
                                      });
                                    },
                                  ),
                                );
                              }).toList(),
                            ),
                          ),
                          SizedBox(height: 12),
                          Expanded(
                            child: StreamBuilder<List<Map<String, dynamic>>>(
                              stream: _getTodayTasks(),
                              builder: (context, snapshot) {
                                if (snapshot.connectionState ==
                                    ConnectionState.waiting)
                                  return Center(
                                      child: CircularProgressIndicator());
                                if (!snapshot.hasData ||
                                    snapshot.data!.isEmpty) {
                                  String noneText;
                                  switch (_taskViewMode) {
                                    case 'This Week':
                                      noneText = 'No tasks for this week';
                                      break;
                                    case 'This Month':
                                      noneText = 'No tasks for this month';
                                      break;
                                    default:
                                      noneText = 'No tasks for today';
                                  }
                                  return Center(
                                      child: Text(noneText,
                                          style: TextStyle(
                                              color: Colors.white70)));
                                }

                                final tasks = snapshot.data!;
                                return ListView.builder(
                                  itemCount: tasks.length,
                                  itemBuilder: (context, index) {
                                    final task = tasks[index];
                                    // Parse and format the date
                                    final dueDate = task['dueDate'] != null && task['dueDate'] is Timestamp
                                        ? (task['dueDate'] as Timestamp).toDate()
                                        : null;
                                    final formattedDate = dueDate != null
                                        ? '${dueDate.day.toString().padLeft(2, '0')}/${dueDate.month.toString().padLeft(2, '0')}'
                                        : '';
                                    return Card(
                                      color: Color(0xFF1E1E2C),
                                      shape: RoundedRectangleBorder(
                                          borderRadius:
                                              BorderRadius.circular(12)),
                                      margin: EdgeInsets.symmetric(vertical: 6),
                                      child: Stack(
                                        children: [
                                          CheckboxListTile(
                                            controlAffinity:
                                                ListTileControlAffinity.leading,
                                            activeColor: Colors.teal,
                                            checkColor: Colors.white,
                                            title: Text(
                                              task['title'],
                                              style: TextStyle(
                                                color: Colors.white,
                                                decoration: task['isDone']
                                                    ? TextDecoration.lineThrough
                                                    : null,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                            subtitle: Text(
                                              task['groupName'],
                                              style:
                                                  TextStyle(color: Colors.white70),
                                            ),
                                            value: task['isDone'],
                                            onChanged: (value) async {
                                              await FirebaseFirestore.instance
                                                  .collection('groups')
                                                  .doc(task['groupId'])
                                                  .collection('tasks')
                                                  .doc(task['id'])
                                                  .update({'isDone': value});
                                              setState(() {});
                                            },
                                          ),
                                          if (formattedDate.isNotEmpty)
                                            Positioned(
                                              top: 8,
                                              right: 12,
                                              child: Text(
                                                formattedDate,
                                                style: TextStyle(
                                                  color: Colors.white60,
                                                  fontSize: 12,
                                                  fontStyle: FontStyle.italic,
                                                ),
                                              ),
                                            ),
                                        ],
                                      ),
                                    );
                                  },
                                );
                              },
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: Padding(
        padding: EdgeInsets.only(
          left: 24.0,
          bottom: _isTodayVisible ? 24.0 : 120.0,
        ),
        child: Stack(
          alignment: Alignment.bottomCenter,
          children: [
            Builder(
              builder: (context) => Positioned(
                bottom: 24,
                left: 24,
                child: FloatingActionButton(
                  heroTag: 'drawerBtn',
                  backgroundColor: Color(0xFF495057),
                  child: Icon(Icons.menu, color: Colors.white),
                  onPressed: () {
                    Scaffold.of(context).openDrawer();
                  },
                ),
              ),
            ),
            Positioned(
              bottom: 24,
              right: 24,
              child: FloatingActionButton(
                heroTag: 'calendarBtn',
                backgroundColor: Color(0xFF495057),
                child: Icon(Icons.calendar_today, color: Colors.white),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => CalendarPage()),
                  );
                },
              ),
            ),
            Positioned(
              bottom: 24,
              child: Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: LinearGradient(
                    colors: [Color(0xFF00B4D8), Color(0xFF0077B6)],
                  ),
                ),
                child: IconButton(
                  icon: Icon(Icons.add, color: Colors.white, size: 32),
                  tooltip: 'Create a new group',
                  onPressed: _createGroup,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGroupList() {
    if (user == null) {
      return Center(child: Text('Not logged in'));
    }
    return StreamBuilder<QuerySnapshot>(
      stream: FirebaseFirestore.instance
          .collection('groups')
          .where('memberIds', arrayContains: user!.uid)
          .snapshots(),
      builder: (context, snapshot) {
        if (snapshot.hasError) {
          return Center(
              child: Text('Error loading groups',
                  style: TextStyle(color: Colors.white)));
        }
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Center(child: CircularProgressIndicator());
        }
        final groups = snapshot.data!.docs;
        if (groups.isEmpty) {
          return Center(
              child: Text('No groups. Tap + to add.',
                  style: TextStyle(color: Colors.white)));
        }
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8.0),
          child: ListView(
            children: groups.map((doc) {
              final group = Group.fromDoc(doc);
              final groupCard = group.name == 'Inbox'
                  ? Center(
                      child: Container(
                        alignment: Alignment.center,
                        width: MediaQuery.of(context).size.width * 0.40,
                        height: 100.0,
                        margin:
                            EdgeInsets.symmetric(vertical: 15, horizontal: 40),
                        decoration: BoxDecoration(
                          color: Color(0xFF6C63A6),
                          borderRadius: BorderRadius.circular(18),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black26,
                              blurRadius: 6,
                              offset: Offset(0, 3),
                            )
                          ],
                        ),
                        child: ListTile(
                          titleAlignment: ListTileTitleAlignment.center,
                          title: Text(
                            group.name,
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => GroupDetailPage(group: group),
                              ),
                            );
                          },
                        ),
                      ),
                    )
                  : Card(
                      color: Color(0xFF1E1E2C),
                      margin:
                          EdgeInsets.symmetric(vertical: 12, horizontal: 60),
                      elevation: 4,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(18)),
                      child: Center(
                        child: Container(
                          width: MediaQuery.of(context).size.width * 0.80,
                          height: 100.0,
                          alignment: Alignment.center,
                          child: ListTile(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => GroupDetailPage(group: group),
                                ),
                              );
                            },
                            title: Text(
                              group.name,
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ),
                      ),
                    );

              if (group.name == 'Inbox') return groupCard;

              return Dismissible(
                key: Key(group.id),
                background: Container(
                  alignment: Alignment.centerLeft,
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  color: Colors.blue,
                  child: Row(
                    children: [
                      Icon(Icons.share, color: Colors.white),
                      SizedBox(width: 8),
                      Text('Share', style: TextStyle(color: Colors.white)),
                    ],
                  ),
                ),
                secondaryBackground: Container(
                  alignment: Alignment.centerRight,
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  color: Colors.red,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Icon(Icons.delete, color: Colors.white),
                      SizedBox(width: 8),
                      Text('Delete', style: TextStyle(color: Colors.white)),
                    ],
                  ),
                ),
                confirmDismiss: (direction) async {
                  if (direction == DismissDirection.startToEnd) {
                    _inviteToGroup(context, group);
                    return false;
                  } else if (direction == DismissDirection.endToStart) {
                    final confirm = await showDialog<bool>(
                      context: context,
                      builder: (_) => AlertDialog(
                        title: Text('Delete Group'),
                        content: Text(
                            'Are you sure you want to delete "${group.name}"?'),
                        actions: [
                          TextButton(
                              onPressed: () => Navigator.pop(context, false),
                              child: Text('Cancel')),
                          TextButton(
                              onPressed: () => Navigator.pop(context, true),
                              child: Text('Delete')),
                        ],
                      ),
                    );
                    return confirm == true;
                  }
                  return false;
                },
                onDismissed: (direction) {
                  if (direction == DismissDirection.endToStart) {
                    _deleteGroup(group.id);
                  }
                },
                child: groupCard,
              );
            }).toList(),
          ),
        );
      },
    );
  }

  void _createGroup() {
    String name = '';
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text('Create Group'),
        content: TextField(
          decoration: InputDecoration(labelText: 'Group Name'),
          onChanged: (value) => name = value,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel'),
          ),
          TextButton(
            onPressed: () async {
              if (name.trim().isEmpty) return;
              Navigator.pop(context);
              await FirebaseFirestore.instance.collection('groups').add({
                'name': name.trim(),
                'memberIds': [user!.uid],
              });
            },
            child: Text('Create'),
          ),
        ],
      ),
    );
  }

  void _inviteToGroup(BuildContext context, Group group) {
    String email = '';
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text('Invite to "${group.name}"'),
        content: TextField(
          decoration: InputDecoration(labelText: 'Email'),
          onChanged: (value) => email = value.trim(),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel'),
          ),
          TextButton(
            onPressed: () async {
              if (email.isEmpty) return;
              Navigator.pop(context);
              await FirebaseFirestore.instance.collection('invitations').add({
                'groupId': group.id,
                'fromUid': user!.uid,
                'toEmail': email,
                'timestamp': FieldValue.serverTimestamp(),
              });
            },
            child: Text('Send'),
          ),
        ],
      ),
    );
  }

  Future<void> _deleteGroup(String groupId) async {
    final groupRef =
        FirebaseFirestore.instance.collection('groups').doc(groupId);
    // Delete all tasks in group's subcollection (Firestore doesn't auto-delete subcollections [oai_citation_attribution:0‡stackoverflow.com](https://stackoverflow.com/questions/62104658/flutter-firebase-delete-subcollections-not-working#:~:text=To%20delete%20a%20document%2C%20you,method))
    final tasksSnapshot = await groupRef.collection('tasks').get();
    for (var doc in tasksSnapshot.docs) {
      await doc.reference.delete();
    }
    // Delete the group document
    await groupRef.delete();
  }
}
