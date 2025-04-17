# Prayer Group Management Website - README

## Overview

This is a lightweight web application designed to help manage group prayers in a company setting. The application allows users to create prayer groups, join existing groups, and receive notifications when group leaders schedule prayer times.

## Features

- **User Management**
  - Registration and login
  - Profile management
  - Password changing

- **Prayer Group Management**
  - Create new prayer groups
  - Join existing groups
  - View group details and members
  - Leave groups

- **Notification System**
  - Group leaders can schedule prayer times
  - Members receive notifications
  - Members can respond (joining or declining)
  - View upcoming and past prayer notifications

## Technical Details

- **Backend**: PHP with SQLite database
- **Frontend**: HTML, CSS, JavaScript
- **Dependencies**: None (self-contained)
- **Responsive Design**: Works on both desktop and mobile devices

## Architecture

The application follows a simple MVC-like architecture:

- **Models**: PHP classes in the `includes` directory handle data operations
- **Views**: PHP files in the root directory render the user interface
- **Controllers**: Logic embedded in the view files handles user requests

The SQLite database is stored in the `db` directory and contains tables for users, prayer groups, group memberships, notifications, and notification responses.

## Installation

See the `INSTALL.md` file for detailed installation instructions.

## License

This software is provided as-is for internal company use.
