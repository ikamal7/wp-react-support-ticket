# WP React Support Ticket

A simple support ticket system for WordPress with a React-based admin panel.

## Description

WP React Support Ticket is a lightweight yet powerful support ticket system for WordPress. It provides a modern React-based admin interface for managing tickets and a simple shortcode-based frontend for users to submit and view their tickets.

### Features

- **React Admin Panel**: Modern, responsive admin interface built with React
- **Ticket Management**: Create, view, update, and manage support tickets
- **User-friendly Frontend**: Simple forms for users to submit and track tickets
- **Shortcode Support**: Easy integration with any page or post
- **Priority & Department**: Organize tickets by priority and department
- **Status Tracking**: Track ticket status (open, closed, etc.)
- **Reply System**: Threaded replies for ongoing communication

## Installation

1. Download the plugin zip file
2. Go to WordPress admin > Plugins > Add New
3. Click on "Upload Plugin" and select the zip file
4. Activate the plugin

## Usage

### Admin Panel

After activation, you'll find a new menu item "Support Tickets" in your WordPress admin menu. This will take you to the React-based admin panel where you can manage all tickets.

### Frontend

Use the following shortcodes to add ticket functionality to your pages:

- `[support_ticket_form]` - Displays a form for users to submit new tickets
- `[support_ticket_list]` - Shows a list of tickets for the current user

## Development Setup

### Prerequisites

- [Node.js](https://nodejs.org/) (v14 or later recommended)
- [npm](https://www.npmjs.com/) (v6 or later)
- [WordPress](https://wordpress.org/) development environment

### Getting Started

1. Clone the repository:
   ```
   git clone https://github.com/ikamal7/wp-react-support-ticket.git
   ```

2. Navigate to the plugin directory:
   ```
   cd wp-react-support-ticket
   ```

3. Install dependencies:
   ```
   npm install
   ```

4. Start the development server:
   ```
   npm start
   ```

5. Build for production:
   ```
   npm run build
   ```

### Project Structure

```
wp-react-support-ticket/
├── build/                  # Compiled files (generated)
├── includes/               # PHP classes for backend functionality
│   ├── class-ticket-controller.php
│   └── class-rest-api.php
├── src/                    # React source files
│   ├── components/         # React components
│   ├── index.js            # Main React application
│   └── styles.css          # Styles
├── templates/              # Frontend templates
├── wp-react-support-ticket.php # Main plugin file
├── package.json            # npm dependencies and scripts
└── README.md               # This file
```

### Development Workflow

- The plugin uses [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) for build tooling
- Run `npm start` to start the development server with hot reloading
- Run `npm run build` to create a production build
- Run `npm run format` to format your code according to WordPress coding standards

### REST API

The plugin registers custom REST API endpoints under the namespace `wp-support-ticket/v1`. These endpoints are used by the React admin panel to communicate with the WordPress backend.

## License

GPL v2 or later

## Support

For support, feature requests, or bug reports, please [open an issue](https://github.com/ikamal7/wp-react-support-ticket/issues) on GitHub.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add some amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## Credits

Developed by [Kamal Hosen](https://github.com/ikamal7).