// lib/models/invitation.dart
import 'package:cloud_firestore/cloud_firestore.dart';

class Invitation {
  final String id;
  final String groupId;
  final String fromUid;
  final String toEmail;
  final Timestamp timestamp;

  Invitation({
    required this.id,
    required this.groupId,
    required this.fromUid,
    required this.toEmail,
    required this.timestamp,
  });

  factory Invitation.fromDoc(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return Invitation(
      id: doc.id,
      groupId: data['groupId'] ?? '',
      fromUid: data['fromUid'] ?? '',
      toEmail: data['toEmail'] ?? '',
      timestamp: data['timestamp'] ?? Timestamp.now(),
    );
  }
}