import 'package:flutter/material.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

class CalendarPage extends StatefulWidget {
  @override
  _CalendarPageState createState() => _CalendarPageState();
}

class _CalendarPageState extends State<CalendarPage> {
  Map<DateTime, List> _events = {};
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;
  List _selectedEvents = [];

  @override
  void initState() {
    super.initState();
    _selectedDay = DateTime.now();
    _loadTasks().then((_) {
      final selectedKey = DateTime(_selectedDay!.year, _selectedDay!.month, _selectedDay!.day);
      setState(() {
        _selectedEvents = _events[selectedKey] ?? [];
      });
    });
  }

  Future<void> _loadTasks() async {
    final groupSnapshots = await FirebaseFirestore.instance.collection('groups').get();
    final Map<DateTime, List> taskMap = {};

    for (var groupDoc in groupSnapshots.docs) {
      final tasksSnapshot = await groupDoc.reference.collection('tasks').get();
      for (var taskDoc in tasksSnapshot.docs) {
        final data = taskDoc.data();
        if (data.containsKey('dueDate') && data.containsKey('isDone')) {
          final dueDate = (data['dueDate'] as Timestamp).toDate();
          final key = DateTime(dueDate.year, dueDate.month, dueDate.day);
          taskMap.putIfAbsent(key, () => []).add({
            'id': taskDoc.id,
            'groupId': groupDoc.id,
            'groupName': groupDoc['name'],
            'title': data['title'],
            'isDone': data['isDone'],
          });
        }
      }
    }

    setState(() {
      _events = taskMap;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF121212),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.only(bottom: 16.0),
              child: Row(
                children: [
                  TextButton.icon(
                    onPressed: () => Navigator.pop(context),
                    icon: Icon(Icons.arrow_back, color: Colors.white),
                    label: Text(
                      'Back',
                      style: TextStyle(color: Colors.white, fontSize: 16),
                    ),
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                  ),
                ],
              ),
            ),
            TableCalendar(
              firstDay: DateTime.utc(2020, 1, 1),
              lastDay: DateTime.utc(2030, 12, 31),
              focusedDay: _focusedDay,
              selectedDayPredicate: (day) {
                return isSameDay(_selectedDay, day);
              },
              onDaySelected: (selectedDay, focusedDay) {
                setState(() {
                  _selectedDay = selectedDay;
                  _focusedDay = focusedDay;
                  final selectedKey = DateTime(selectedDay.year, selectedDay.month, selectedDay.day);
                  _selectedEvents = _events[selectedKey] ?? [];
                });
              },
              calendarStyle: const CalendarStyle(
                defaultTextStyle: TextStyle(color: Colors.white),
                weekendTextStyle: TextStyle(color: Color(0xFFFFB74D)), // soft orange
                todayDecoration: BoxDecoration(
                  color: Color(0xFF64B5F6), // light blue
                  shape: BoxShape.circle,
                ),
                selectedDecoration: BoxDecoration(
                  color: Color(0xFF81C784), // light green
                  shape: BoxShape.circle,
                ),
              ),
              calendarBuilders: CalendarBuilders(
                markerBuilder: (context, date, events) {
                  final key = DateTime(date.year, date.month, date.day);
                  if (_events.containsKey(key)) {
                    return Center(
                      child: Container(
                        width: 35,
                        height: 35,
                        decoration: BoxDecoration(
                          color: Colors.teal.withOpacity(0.5),
                          shape: BoxShape.circle,
                        ),
                        alignment: Alignment.center,
                        child: Text(
                          '${date.day}',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                      ),
                    );
                  }
                  return null;
                },
              ),
              daysOfWeekStyle: const DaysOfWeekStyle(
                weekdayStyle: TextStyle(color: Color(0xFFB0BEC5)), // light grey-blue
                weekendStyle: TextStyle(color: Color(0xFFFFB74D)), // soft orange
              ),
              headerStyle: const HeaderStyle(
                formatButtonVisible: false,
                titleCentered: true,
                titleTextStyle: TextStyle(color: Colors.white, fontSize: 18),
                leftChevronIcon: Icon(Icons.chevron_left, color: Colors.white),
                rightChevronIcon: Icon(Icons.chevron_right, color: Colors.white),
              ),
            ),
            if (_selectedDay != null)
              Padding(
                padding: const EdgeInsets.only(top: 16.0, bottom: 8),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    'Tasks for ${_selectedDay!.toLocal().toString().split(' ')[0]}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            _selectedDay != null && _selectedEvents.isEmpty
              ? Expanded(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: const [
                        Icon(Icons.inbox, color: Colors.white54, size: 48),
                        SizedBox(height: 12),
                        Text(
                          'No tasks found',
                          style: TextStyle(
                            color: Colors.white54,
                            fontSize: 16,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : Flexible(
                  child: ListView.builder(
                    itemCount: _selectedEvents.length,
                    itemBuilder: (context, index) {
                      final task = _selectedEvents[index];
                      return Container(
                        margin: const EdgeInsets.symmetric(vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1E1E2C),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: const [
                            BoxShadow(
                              color: Colors.black26,
                              blurRadius: 4,
                              offset: Offset(0, 2),
                            )
                          ],
                        ),
                        child: CheckboxListTile(
                          controlAffinity: ListTileControlAffinity.leading,
                          activeColor: Colors.teal,
                          checkColor: Colors.white,
                          title: Text(
                            task['title'] ?? '',
                            style: TextStyle(
                              color: Colors.white,
                              decoration: task['isDone'] ? TextDecoration.lineThrough : null,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          subtitle: Align(
                            alignment: Alignment.centerRight,
                            child: Text(
                              task['groupName'] ?? '',
                              style: const TextStyle(color: Colors.white70, fontSize: 13),
                            ),
                          ),
                          value: task['isDone'],
                          onChanged: (value) async {
                            final newValue = !(task['isDone'] ?? false);
                            setState(() {
                              task['isDone'] = newValue;
                            });
                            FirebaseFirestore.instance
                                .collection('groups')
                                .doc(task['groupId'])
                                .collection('tasks')
                                .doc(task['id'])
                                .update({'isDone': newValue});
                          },
                        ),
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