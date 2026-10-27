-- Verdant Market / Laravel 12 database for XAMPP MySQL.
-- Import this file in phpMyAdmin to create the database and starter catalog.
-- No administrator account is included; create one with the project's
-- `php artisan create:admin` command after configuring the application.

CREATE DATABASE IF NOT EXISTS `basic_ecommerce`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `basic_ecommerce`;

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL DEFAULT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `sale_price` DECIMAL(10, 2) NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `image` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `products_slug_unique` (`slug`),
    KEY `products_category_id_is_active_index` (`category_id`, `is_active`),
    KEY `products_featured_index` (`featured`),
    CONSTRAINT `products_category_id_foreign`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `order_number` VARCHAR(255) NOT NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `mobile_number` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `delivery_address` TEXT NOT NULL,
    `district_or_city` VARCHAR(255) NOT NULL,
    `order_notes` TEXT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `total` DECIMAL(10, 2) NOT NULL,
    `status` VARCHAR(255) NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `orders_order_number_unique` (`order_number`),
    KEY `orders_status_index` (`status`),
    CONSTRAINT `orders_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `product_price` DECIMAL(10, 2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `order_items_product_id_index` (`product_id`),
    CONSTRAINT `order_items_order_id_foreign`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_10_26_125950_create_categories_table', 1),
    ('2026_10_26_125951_create_products_table', 1),
    ('2026_10_26_125952_create_orders_table', 1),
    ('2026_10_26_125953_create_order_items_table', 1);

INSERT INTO `categories`
    (`id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`)
VALUES
    (1, 'Home & Living', 'home-living', 'A curated collection of premium items.', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (2, 'Electronics', 'electronics', 'A curated collection of premium items.', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (3, 'Fashion', 'fashion', 'A curated collection of premium items.', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (4, 'Beauty', 'beauty', 'A curated collection of premium items.', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `products`
    (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`)
VALUES
    (1, 1, 'Aster Smart Lamp', 'aster-smart-lamp', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 59.99, 49.99, 20, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (2, 2, 'Nexa Wireless Speaker', 'nexa-wireless-speaker', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 89.99, NULL, 15, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (3, 1, 'Oakwood Desk Chair', 'oakwood-desk-chair', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 129.00, 109.00, 12, 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (4, 2, 'Verve Noise Cancelling Headphones', 'verve-noise-cancelling-headphones', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 199.99, 169.99, 10, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (5, 3, 'Luna Everyday Tote', 'luna-everyday-tote', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 34.99, NULL, 24, 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (6, 3, 'Sora Classic Watch', 'sora-classic-watch', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 149.00, 119.00, 8, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (7, 4, 'Serene Skin Kit', 'serene-skin-kit', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 44.50, NULL, 18, 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (8, 4, 'Bloom Ceramic Diffuser', 'bloom-ceramic-diffuser', 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.', 39.99, 29.99, 22, 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
