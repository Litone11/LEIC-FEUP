import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

class InvitationsPage extends StatelessWidget {
  static const routeName = '/invitations';
  final user = FirebaseAuth.instance.currentUser;

  @override
  Widget build(BuildContext context) {
    if (user == null) {
      return Scaffold(body: Center(child: Text('Please log in')));
    }
    return Scaffold(
      appBar: AppBar(
        title: Text('Invitations'),
      ),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Received Invitations', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            Expanded(
              child: StreamBuilder<QuerySnapshot>(
                stream: FirebaseFirestore.instance
                    .collection('invitations')
                    .where('toEmail', isEqualTo: user!.email)
                    .snapshots(),
                builder: (context, snapshot) {
                  if (snapshot.hasError) {
                    return Text('Error loading invitations');
                  }
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return Center(child: CircularProgressIndicator());
                  }
                  final invites = snapshot.data!.docs;
                  if (invites.isEmpty) {
                    return Center(child: Text('No received invitations'));
                  }
                  return ListView(
                    children: invites.map((doc) {
                      return _InvitationTile(doc.id, doc.data() as Map<String, dynamic>);
                    }).toList(),
                  );
                },
              ),
            ),
            SizedBox(height: 16),
            Text('Sent Invitations', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            Expanded(
              child: StreamBuilder<QuerySnapshot>(
                stream: FirebaseFirestore.instance
                    .collection('invitations')
                    .where('fromUid', isEqualTo: user!.uid)
                    .snapshots(),
                builder: (context, snapshot) {
                  if (snapshot.hasError) {
                    return Text('Error loading invitations');
                  }
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return Center(child: CircularProgressIndicator());
                  }
                  final invites = snapshot.data!.docs;
                  if (invites.isEmpty) {
                    return Center(child: Text('No sent invitations'));
                  }
                  return ListView(
                    children: invites.map((doc) {
                      final data = doc.data() as Map<String, dynamic>;
                      return ListTile(
                        title: FutureBuilder<DocumentSnapshot>(
                          future: FirebaseFirestore.instance.collection('groups').doc(data['groupId']).get(),
                          builder: (context, groupSnap) {
                            if (!groupSnap.hasData || !groupSnap.data!.exists) {
                              return Text('Unknown group');
                            }
                            final groupData = groupSnap.data!.data() as Map<String, dynamic>;
                            final name = groupData['name'] ?? 'Unnamed group';
                            return Text('Group: $name');
                          },
                        ),
                        subtitle: Text('To: ' + data['toEmail']),
                      );
                    }).toList(),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InvitationTile extends StatelessWidget {
  final String inviteId;
  final Map<String, dynamic> data;

  _InvitationTile(this.inviteId, this.data);

  @override
  Widget build(BuildContext context) {
    final groupId = data['groupId'] as String;
    final fromUid = data['fromUid'] as String;
    final toEmail = data['toEmail'] as String;

    return FutureBuilder<DocumentSnapshot>(
      future: FirebaseFirestore.instance.collection('groups').doc(groupId).get(),
      builder: (context, groupSnap) {
        if (groupSnap.connectionState == ConnectionState.waiting) {
          return ListTile(title: Text('Loading...'));
        }
        if (!groupSnap.hasData || groupSnap.data == null) {
          return ListTile(title: Text('Unknown group'));
        }
        final groupName = groupSnap.data!['name'] ?? 'Unknown';
        return FutureBuilder<DocumentSnapshot>(
          future: FirebaseFirestore.instance.collection('users').doc(fromUid).get(),
          builder: (context, userSnap) {
            String inviterUsername = 'Unknown';
            if (userSnap.hasData && userSnap.data != null && userSnap.data!.exists) {
              final userData = userSnap.data!.data() as Map<String, dynamic>;
              inviterUsername = userData['username'] ?? 'Unknown';
            }

            return Card(
              child: ListTile(
                title: Text(groupName),
                subtitle: Text('Invited by: @$inviterUsername'),
                trailing: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    IconButton(
                      icon: Icon(Icons.check, color: Colors.green),
                      onPressed: () async {
                        final user = FirebaseAuth.instance.currentUser;
                        if (user == null) return;

                        String uidToAdd;
                        var userQuery = await FirebaseFirestore.instance
                            .collection('users')
                            .where('email', isEqualTo: toEmail)
                            .get();
                        if (userQuery.docs.isNotEmpty) {
                          uidToAdd = userQuery.docs.first.id;
                        } else {
                          uidToAdd = user.uid;
                        }

                        final groupRef = FirebaseFirestore.instance.collection('groups').doc(groupId);
                        await groupRef.update({
                          'memberIds': FieldValue.arrayUnion([uidToAdd])
                        });

                        await FirebaseFirestore.instance.collection('invitations').doc(inviteId).delete();
                      },
                    ),
                    IconButton(
                      icon: Icon(Icons.close, color: Colors.red),
                      onPressed: () async {
                        await FirebaseFirestore.instance.collection('invitations').doc(inviteId).delete();
                      },
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }
}

