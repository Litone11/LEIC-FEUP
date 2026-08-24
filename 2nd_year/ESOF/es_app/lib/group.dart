// lib/models/group.dart
import 'package:cloud_firestore/cloud_firestore.dart';

class Group {
  final String id;
  final String name;
  final List<dynamic> memberIds;

  Group({required this.id, required this.name, required this.memberIds});

  factory Group.fromDoc(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return Group(
      id: doc.id,
      name: data['name'] ?? '',
      memberIds: data['memberIds'] ?? [],
    );
  }
}