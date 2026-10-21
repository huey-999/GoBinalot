# GoBinalot

GoBinalot is a PHP-based restaurant ordering and management website for a Filipino food brand serving traditional binalot meals and other home-style dishes.

## Features

- Responsive restaurant landing page
- Menu showcase with category filtering
- Customer-facing login and registration pages
- Admin dashboard for managing:
  - Dashboard
  - Orders
  - Payments
  - Deliveries
  - Menu
  - Reports
- Sidebar navigation and mobile-friendly layout
- Bootstrap-based UI styling
- JavaScript interactions for filtering menu items and toggling UI states

## Tech Stack

- PHP
- HTML
- CSS
- JavaScript
- Bootstrap 5
- Font Awesome
- XAMPP / Apache local server

## Project Structure

```text
GoBinalot/
├── admin/
│   ├── dashboard.php
│   ├── deliveries.php
│   ├── menus.php
│   ├── orders.php
│   ├── payments.php
│   └── reports.php
├── css/
│   └── style.css
├── img/
├── includes/
│   ├── admin_header.php
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── js/
│   └── script.js
├── index.php
├── login.php
├── register.php
├── README.md
└── .
```

## Local Setup

1. Install XAMPP or any PHP-enabled local server.
2. Place this project folder in your web server directory, usually:
   - `C:/xampp/htdocs/GoBinalot`
3. Start Apache in XAMPP.
4. Open your browser and go to:
   - `http://localhost/GoBinalot/`

## Admin Access

The admin pages are located under the `admin/` folder.

Example:

- `http://localhost/GoBinalot/admin/dashboard.php`

## Notes

- The project currently uses static demo content and front-end logic for the restaurant UI.
- Backend database integration can be added later for real orders, users, payments, and reports.

## Future Improvements

- Database integration with MySQL
- Real authentication and session handling
- Order processing and payment verification
- CRUD for menu items and admin data
- Reporting and analytics dashboard

## License

This project is for educational and demo purposes.
