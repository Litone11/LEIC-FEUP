import 'package:flutter/material.dart';

class Group {
  final String name;

  Group({required this.name});
}

class GroupCreator extends StatefulWidget {
  final Function(Group) onGroupCreated;

  const GroupCreator({Key? key, required this.onGroupCreated})
      : super(key: key);

  @override
  _GroupCreatorState createState() => _GroupCreatorState();
}

class _GroupCreatorState extends State<GroupCreator> {
  final TextEditingController _controller = TextEditingController();

  void _createGroup() {
    String groupName = _controller.text.trim();
    if (groupName.isNotEmpty) {
      Group newGroup = Group(name: groupName);
      widget.onGroupCreated(newGroup);
      Navigator.of(context).pop(); // Fecha o diálogo
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text('Create Group'),
      content: TextField(
        controller: _controller,
        decoration: InputDecoration(
          labelText: 'Group Name',
          border: OutlineInputBorder(),
        ),
        autofocus: true,
        onSubmitted: (_) => _createGroup(),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(), // Cancelar
          child: Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: _createGroup,
          child: Text('Create'),
        ),
      ],
    );
  }
}