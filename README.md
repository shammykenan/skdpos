# SKD POS — Point of Sale System for The Lounge

> A full-featured, PHP-based Point of Sale system built for **The Lounge by CJV & TEK** — a modern coffee shop and dining spot at Lias Town Plaza, Marilao, Bulacan, Philippines.

---

## 📸 Screenshots

| Landing Page | Menu | POS Interface | Admin Dashboard |
|:------------:|:----:|:-------------:|:---------------:|
| ![Landing](public/assets/images/screenshots/landing.png) | ![Menu](public/assets/images/screenshots/menu.png) | ![POS](public/assets/images/screenshots/pos.png) | ![Admin](public/assets/images/screenshots/admin.png) |

*(Add actual screenshots to `public/assets/images/screenshots/` to display here)*

---

## ✨ Features

### 🏪 Customer-Facing (Landing Page)
- **Modern responsive landing page** — Hero section, featured items, full menu with category filtering, about section, hours, and contact
- **Real-time WiFi QR code** — Guests scan to connect; password never leaves the local network
- **Staff login modal** — Secure authentication for cashier/kitchen/admin roles
- **Mobile-first design** — Works beautifully on phone, tablet, and desktop

### 💰 Point of Sale (Cashier)
- **Table management** — Visual table layout with status (available, occupied, reserved)
- **Order taking** — Category-based menu browsing, variants, add-ons, quantity controls
- **Discounts** — PWD, Senior Citizen, and promotional discounts with configurable limits
- **Payment processing** — Cash, card, GCash, split payments
- **Receipt printing** — ESC/POS thermal printer support (via `mike42/escpos-php`)
- **Order voiding & refunds** — Full audit trail with reason tracking

### 🍳 Kitchen Display
- **Real-time order queue** — WebSocket-powered (Ratchet) live updates
- **Item status workflow** — Pending → Preparing → Ready → Served
- **Partial item actions** — Mark individual items as served/cancelled

### 👑 Admin Panel
- **Dashboard** — Sales summary, Z-readings, top items, low-stock alerts
- **Menu management** — CRUD for categories, items, variants, add-ons, featured items
- **Inventory & Recipes** — Ingredient tracking, recipe costing, auto-stock deduction
- **User management** — Role-based access (admin, cashier, kitchen)
- **BIR Settings** — Philippine BIR compliance (TIN, VAT, PTU, accreditation, machine serial)
- **Reports** — Daily/monthly sales, category breakdown, payment methods, discounts, voids, refunds
- **Database backup** — One-click SQL dump download
- **Activity logs** — Full audit trail of all critical actions

### 🔧 Technical Highlights
- **Auto-migrating schema** — `DatabaseSchema.php` syncs tables on every request (dev-friendly)
- **Environment-driven config** — `.env` for secrets, works on LAN/localhost/proxy without code changes
- **CSRF protection** — Built-in token validation on all forms/AJAX
- **Prepared statements everywhere** — PDO with parameterized queries
- **WebSocket server** — `ws_server.php` for real-time kitchen updates

---

## 🏗️ Architecture

```
skdpos/
├── app/
│   ├── ajax/              # AJAX endpoints (JSON API)
│   ├── controllers/       # Page controllers (MVC-ish)
│   ├── core/              # Helpers, utilities, schema sync
│   ├── models/            # Data access layer
│   ├── print/             # Receipt / Z-reading / X-reading templates
│   ├── services/          # Mailer, etc.
│   └── views/             # PHP templates (admin, cashier, kitchen, auth)
├── config/
│   ├── db.php             # DB connection + auto-schema sync
│   └── .env               # Environment variables (not tracked)
├── database/
│   └── dinewithdinPOS_schema.sql  # Full schema (source of truth)
├── public/                # Document root (assets, index.php router)
├── vendor/                # Composer dependencies
├── composer.json
└── ws_server.php          # Ratchet WebSocket server
```

### Request Flow
```
Browser → public/index.php (router)
    → controllers/*_controller.php
        → models/*.php (PDO)
        → views/*/*.php (templates)
    → ajax/*.php (JSON endpoints for POS/kitchen)
```

---

## 🚀 Quick Start

### Prerequisites
- **PHP ≥ 8.1** (tested on 8.2+)
- **MySQL/MariaDB** (XAMPP default)
- **Composer**
- **Thermal printer** (optional, for receipts)

### Installation

```bash
# 1. Clone
git clone https://github.com/<your-username>/skdpos.git
cd skdpos

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp config/.env.example config/.env   # (create this file first, see below)
# Edit config/.env with your DB credentials

# 4. Set up database
# Option A: Let auto-schema sync create tables on first request (dev)
# Option B: Import the canonical schema
mysql -u root -p dinewithdin < database/dinewithdinPOS_schema.sql

# 5. Start WebSocket server (for kitchen real-time)
php ws_server.php
# Runs on port 8080 by default

# 6. Serve the app
# Point XAMPP/Apache DocumentRoot to public/
# Or use PHP built-in server for quick testing:
php -S localhost:8000 -t public
```

### `.env` Example
```env
DB_HOST=127.0.0.1
DB_NAME=dinewithdin
DB_USER=root
DB_PASS=
APP_BASE_URL=http://localhost/skdpos/public

# Mailer (for password reset)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_EMAIL=your-email@gmail.com
MAIL_FROM_NAME="The Lounge POS"

# WiFi (for landing page QR)
WIFI_SSID=TheLounge_Guest
WIFI_PASSWORD=your-wifi-password
```

---

## 🗄️ Database Schema (Key Tables)

| Table | Purpose |
|-------|---------|
| `users` | Staff accounts (admin, cashier, kitchen) |
| `categories` | Menu categories |
| `menu_items` | Products with base price, cost, image |
| `menu_variants` | Size/option variants (e.g., Small/Medium/Large) |
| `addons` | Modifiers (extra shot, syrup, etc.) |
| `ingredients` | Stock items with unit, low-stock alert |
| `recipes` | Links menu items → ingredients with qty |
| `orders` | Header: table, user, totals, status, discounts |
| `order_items` | Line items with variants, add-ons, status |
| `payments` | Payment records per order |
| `refunds` | Refund/void audit trail |
| `z_readings` | End-of-day BIR-compliant Z-readings |
| `business_info` | BIR registration details, VAT, discounts |
| `activity_logs` | Full audit trail |

> The canonical schema lives in `database/dinewithdinPOS_schema.sql`. The app also runs `dbs_sync_database_schema_if_needed()` on every request to keep dev databases in sync automatically.

---

## 🖨️ Thermal Printer Setup

1. Install ESC/POS driver for your printer (Epson TM-T20, TM-T88, etc.)
2. Share the printer on the network or connect via USB to the POS machine
3. In `app/print/receipt_format.php`, set the printer connector:
   ```php
   $connector = new WindowsPrintConnector("POS58");  // Share name
   // or
   $connector = new NetworkPrintConnector("192.168.1.100", 9100);
   ```
4. Test: `php -r "require 'vendor/autoload.php'; use Mike42\Escpos\PrintConnectors\WindowsPrintConnector; $c = new WindowsPrintConnector('POS58'); $p = new Mike42\Escpos\Printer($c); $p->text('Test\n'); $p->cut(); $p->close();"`

---

## 🔐 Roles & Permissions

| Role | Access |
|------|--------|
| **admin** | Full admin panel, reports, user mgmt, BIR settings, backup |
| **cashier** | POS, tables, orders, payments, refunds, X/Z readings |
| **kitchen** | Kitchen display, order item status updates |

---

## 📱 WebSocket (Real-time Kitchen)

```bash
# Start server (keep running)
php ws_server.php

# Client connects from kitchen view via JS:
const ws = new WebSocket('ws://localhost:8080');
ws.onmessage = e => { /* update order queue */ };
```

Events: `new_order`, `item_status_update`, `order_completed`, `order_void`.

---

## 🇵🇭 BIR Compliance (Philippines)

- **VAT-registered** or **Non-VAT** toggle
- **PTU Number**, **Accreditation No.**, **Machine Serial No.**
- **Z-Reading** generation with BIR-mandatory fields
- **Official Receipt / Sales Invoice** label switching
- **PWD/Senior discount** enforcement (min 20% per law)
- **Promo discount** cap (configurable max)

---

## 🧪 Development Notes

### Adding a New AJAX Endpoint
1. Create `app/ajax/your_endpoint.php`
2. Include `app/core/init.php`
3. Validate CSRF: `csrf_validate()`
4. Return JSON via `handleAjaxSuccess()` / `handleAjaxError()`

### Adding a Database Column
1. Update `database/dinewithdinPOS_schema.sql`
2. The auto-sync will add it on next request (dev)
3. For production, run the ALTER manually or via migration

### Code Style
- PSR-12-ish (tabs for indent, braces on same line)
- `snake_case` for DB columns, `PascalCase` for PHP classes
- All SQL uses prepared statements

---

## 📦 Dependencies

| Package | Purpose |
|---------|---------|
| `vlucas/phpdotenv` | `.env` loading |
| `cboden/ratchet` | WebSocket server |
| `phpmailer/phpmailer` | Email (password reset) |
| `mike42/escpos-php` | Thermal receipt printing |

---

## 📄 License

Proprietary — Internal use for **The Lounge by CJV & TEK** only.

---

## 👥 Authors

- **Shammy Kenan Din** — Lead Developer
- **CJV & TEK Team** — Product & Operations

---

## 🙏 Acknowledgments

- Built on PHP 8 + PDO + vanilla JS (no heavy framework)
- Thermal printing via [mike42/escpos-php](https://github.com/mike42/escpos-php)
- Real-time via [Ratchet](https://github.com/ratchetphp/Ratchet)
- Icons by [Font Awesome](https://fontawesome.com/)
- Fonts: [DM Sans](https://fonts.google.com/specimen/DM+Sans) & [DM Serif Display](https://fonts.google.com/specimen/DM+Serif+Display)

---

> **Built with precision · Powered by SKD**