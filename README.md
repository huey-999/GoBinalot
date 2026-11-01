# GoBinalot

GoBinalot is a PHP-based restaurant ordering and admin management prototype for a Filipino food brand focused on traditional binalot meals and home-style dishes.

## Project status

This project is currently a working prototype with a usable front-end, PHP page structure, and session-based user flow. The app includes:

- Public home and storefront views
- Login and registration flow
- Session-based authentication and redirection
- Admin/customer role-based page routing
- Dashboard, menu, order, report, and settings screens
- Bootstrap 5 + custom styling and basic JavaScript interactions
- Menu/category/address management actions tied to PHP processing logic
- Customer cart, checkout summary, and saved-address flow

The app is not yet a full production-ready business system, but the foundation is in place for continued backend development.

## Recent fixes (customer ordering flow)

- Fixed missing closing braces in `css/style.css` and `js/script.js` that broke parsing.
- Fixed PHP syntax error in `gb/binalot_menus.php`.
- Category filter pills and search bar now filter menu items by category/name/description.
- Empty-state message centers correctly when all items are filtered out.
- Checkout summary shows an itemized list (names, quantities, subtotals) and updates `Items` count correctly.
- "Proceed to Checkout" button is visually disabled and does not open the modal when the cart is empty; modal "Place Order" button is also disabled when empty.
- Checkout modal customer name shows first + last name as printed text instead of an editable input field.
- "Default" saved-address badge styling is confined to the badge span only.

## What is working

- PHP page structure and navigation are organized and consistent
- Auth flow starts from `login.php`, `register.php`, and `includes/actions.php`
- Users can register and log in with password hashing via `password_hash()`
- Session data is stored for authenticated users with role-based redirect logic
- Admin screens exist under `gb/` and use a shared header/sidebar structure
- Category and menu item CRUD logic is present in `includes/actions.php`
- Menu image upload handling is implemented with validation and deduplication checks
- Customer cart, checkout, and saved-addresses flow functional

## What still needs cleanup before the app is considered complete

### 1. Database setup needs to be formalized
- The project still relies on a manually created MySQL database named `gobinalot`
- There is no migration or schema file in the repository yet
- Table structure for `users`, `menu_items`, `categories`, `orders`, `payments`, `addresses`, and `deliveries` should be documented and versioned

### 2. Auth and authorization are still basic
- Login and registration work at a functional level, but role enforcement is still minimal
- Access checks exist, but they are not yet centrally enforced across all protected pages
- Session hardening should be improved before production use

### 3. Demo/static data still exists in some modules
- Several admin pages are UI-focused and still resemble placeholders rather than fully connected workflow screens
- Some content and business actions still assume data exists without graceful fallback handling
- Payment, orders, and delivery pages should be verified for real workflow logic rather than mock/demo behavior

### 4. Error handling should be more consistent
- Database connection errors are printed directly in `includes/config.php`
- Some actions fail with generic messages, but there is no centralized logging or error layer
- Invalid form submissions and edge cases should be consistently validated and reported

### 5. File upload and media handling needs stricter review
- Image uploads are present and generally validated (mime type + SHA-1 dedup)
- Uploaded files should be further restricted to approved directories and naming conventions
- Unused images cleanup is implemented, but global validation and file security should be checked across admin flows

### 6. Testing and validation are still missing
- No automated tests are present for auth, menu updates, category management, or order processing
- The app should be verified against a clean database setup before release

## Tech stack

- PHP
- HTML
- CSS
- JavaScript
- Bootstrap 5
- Font Awesome
- MySQL / PDO
- XAMPP / Apache local environment

## Current app structure

```text
GoBinalot/
├── gb/
│   ├── binalot_menus.php
│   ├── dashboard.php
│   ├── deliveries.php
│   ├── menus.php
│   ├── my_orders.php
│   ├── orders.php
│   ├── payments.php
│   ├── profile.php
│   ├── reports.php
│   └── settings.php
├── css/
│   └── style.css
├── img/
│   └── menu/
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
│   ├── action.js
│   └── script.js
├── index.php
├── login.php
├── logout.php
├── register.php
├── README.md
├── GoBinalot_Current_Project_Progress.md
├── GoBinalot_Final_Project_Plan.md
└── .
```

## Local setup

1. Install XAMPP or any PHP + MySQL environment.
2. Place the project in your server folder, usually:
   - `C:/xampp/htdocs/GoBinalot`
3. Start Apache and MySQL in XAMPP.
4. Create a MySQL database named `gobinalot`.
5. Create the necessary schema for the main tables:
   - `users`
   - `categories`
   - `menu_items`
   - `orders`
   - `payments`
   - `deliveries`
   - `addresses`
6. Start the app in a browser:
   - `http://localhost/GoBinalot/`

## Database connection notes

The project currently expects a MySQL connection using the settings in `includes/config.php`:

- Host: `localhost`
- Username: `root`
- Database: `gobinalot`

If the database is not yet created, the app may show connection or auth errors during login and form actions.

## Admin access

Admin screens are under the `gb/` directory.

Example:

- `http://localhost/GoBinalot/gb/dashboard.php`

A valid `users` record with role `admin` should exist for access to protected admin pages.

## Final cleanup checklist

Before this project is considered ready for a fuller business rollout, these should be addressed:

- Add migration scripts for the project schema
- Remove or guard demo-only logic that assumes data always exists
- Standardize validation across all forms
- Connect remaining admin workflows to real database queries
- Harden session and role-based authorization rules
- Improve centralized error logging and message handling
- Review upload storage and security for menu images
- Add tests for authentication, menu updates, and order flows
- Document all required database tables and expected columns
- Clean up placeholder content and static/demo labels in UI modules

## Roadmap

Planned improvements:

- Real MySQL integration and schema documentation
- Secure authentication and session handling
- CRUD support for menu items and categories
- Order management and delivery updates
- Payment processing and reporting
- Responsive UX improvements and UI polish
- Deployment-ready configuration and environment setup

## License

This project is intended for educational and demo purposes.
