# GoBinalot

GoBinalot is a PHP restaurant ordering and admin management prototype for a Filipino food brand focused on traditional binalot meals and home-style dishes.

## Project status

This project is currently a working front-end prototype with functional PHP page structure and role-based admin/customer navigation. It includes:

- Landing page and public storefront layout
- Login and registration flows
- Admin dashboard and sidebar navigation
- Menu item and category management screens
- Orders, payments, deliveries, reports, and settings pages
- Bootstrap + custom CSS styling

Some parts are still demo/static and not yet fully connected to a real database-backed business workflow.

## Tech stack

- PHP
- HTML
- CSS
- JavaScript
- Bootstrap 5
- Font Awesome
- MySQL (for database-backed features)
- XAMPP / Apache local environment

## Current app structure

```text
GoBinalot/
├── gb/
│   ├── dashboard.php
│   ├── deliveries.php
│   ├── menus.php
│   ├── my_orders.php
│   ├── orders.php
│   ├── payments.php
│   ├── profile.php
│   ├── reports.php
│   ├── settings.php
│   └── binalot_menus.php
├── css/
│   └── style.css
├── img/
├── includes/
│   ├── actions.php
│   ├── admin_footer.php
│   ├── admin_header.php
│   ├── auth.php
│   ├── config.php
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── js/
│   └── script.js
├── index.php
├── login.php
├── logout.php
├── register.php
├── README.md
└── .
```

## Local setup

1. Install XAMPP or any PHP + MySQL environment.
2. Place the project in your server folder, usually:
   - `C:/xampp/htdocs/GoBinalot`
3. Start Apache and MySQL in XAMPP.
4. Create a MySQL database named `gobinalot`.
5. Import or prepare the required tables for:
   - users
   - menu items
   - categories
   - orders
   - payments
   - delivery data
6. Open the app in a browser:
   - `http://localhost/GoBinalot/`

## Important notes

### Database connection status

The project currently attempts to connect to MySQL with the settings in `includes/config.php`:

- Host: `localhost`
- User: `root`
- Database: `gobinalot`

If the database is not created yet, the app may show connection errors or fail during authentication and action processing.

### Demo status

The project is not fully production-ready yet. The following areas are still demo/static or partially implemented:

- menu/category CRUD actions
- real order workflows
- payment verification flow
- user role enforcement beyond basic session checks
- persistent data storage for many admin actions

## Admin access

The admin pages are in the `gb/` folder.

Example:

- `http://localhost/GoBinalot/gb/dashboard.php`

To access protected admin pages, a valid user record with the role `admin` should exist in the `users` table.

## Current cleanup checklist

A few items should be cleaned up as the project matures:

- Add proper database migration scripts for the project schema.
- Remove or guard demo-only logic that assumes data always exists.
- Standardize form submissions and validation across pages.
- Replace placeholder content with real business logic and database queries.
- Add a proper error handling and logging layer.
- Add tests for auth, menu updates, and order flows.

## Roadmap

Planned improvements:

- real MySQL database integration
- secure authentication and session handling
- CRUD support for menu items and categories
- order management and delivery updates
- payment processing and reporting
- responsive polish and UX improvements
- deployment-ready configuration files

## License

This project is intended for educational and demo purposes.
