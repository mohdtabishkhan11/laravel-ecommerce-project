# 🛒 E-commerce Web Application  
**Built with Laravel & Razorpay**


## ✨ Features

- **Admin Panel:** Secure login, full CRUD for products (with multiple images).
- **Order Management:** View all completed orders and customer shipping details.
- **Customer Frontend:** Product dashboard, "Add to Cart", image carousels, dynamic cart in navbar.
- **Shopping Cart:** Add, view, update, and delete items.
- **Payment Integration:** Two-step checkout with Razorpay (test mode).
- **Relational Database:** MySQL schema managed by Laravel migrations.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.2+, Laravel 11+
- **Frontend:** Blade, Bootstrap 5, JavaScript
- **Database:** MySQL 8+
- **Payment Gateway:** Razorpay

---

## ⚡ Quickstart

### 1. Prerequisites

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL Server

### 2. Clone the Repository

```bash
git clone [your-repository-url]
cd [your-project-folder]
```

### 3. Install Dependencies

```bash
# PHP dependencies
composer install

# JS dependencies
npm install
```

### 4. Environment Configuration

- Copy `.env.example` to `.env`:

    ```bash
    cp .env.example .env
    ```

- Generate app key:

    ```bash
    php artisan key:generate
    ```

- Update your `.env` with database credentials:

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ecom_project_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

- Create an empty database named `ecom_project_db`.

- Add your Razorpay test keys to `.env`:

    ```
    RAZORPAY_KEY_ID=your_key_id_here
    RAZORPAY_KEY_SECRET=your_key_secret_here
    ```

### 5. Run Database Migrations

```bash
php artisan migrate:fresh
```

### 6. Link Storage Directory

```bash
php artisan storage:link
```

### 7. Compile Frontend Assets

```bash
npm run dev
```

### 8. Serve the Application

```bash
php artisan serve
```

Visit: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 📝 How to Use

- **Register:** Go to `/register` to create your first user account.
- **Admin Panel:**
    - Add products (with multiple images) via the "Products" link.
    - View completed orders and shipping details via the "Orders" link.
- **Customer View:**
    - Browse products on the dashboard.
    - Add items to cart, view cart, and proceed to checkout.
    - Fill shipping details and click "Place Order & Pay" to test Razorpay integration.

---

## 💡 Happy Coding!

---
