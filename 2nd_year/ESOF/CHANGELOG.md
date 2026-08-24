# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## Sprint 0 - 2025-03-10
### Added
- `.gitignore` file to ignore `.vscode/settings.json`.
- `flutter_local_notifications` package for scheduling task reminders (planned implementation).
- Instructions for adding notifications to tasks.
- Initial release of the project.
- Basic task management functionality:
  - Add tasks.
  - Toggle task completion.
  - Remove tasks.

### Changed
- Updated `main.dart` to include task management functionality.
- Improved state management with `ChangeNotifier` in `MyAppState`.

### Fixed
- Fixed issues with tracking `.vscode/settings.json` in Git.

## Sprint1 - 2025-04-07
### Added
- User authentication functionality:
  - Sign-up and log-in modes integrated with Firebase Authentication.
  - Firebase configuration for managing user accounts.
- UI updates to support authentication screens.

### Changed
- Updated app flow to require user authentication before accessing task management features.
- Improved user interface for better usability and design.
- Enhanced task functionality by linking tasks with dates from a calendar.