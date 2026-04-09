USE webtech_lab;

CREATE TABLE IF NOT EXISTS experiment10_shoppers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(128) NOT NULL UNIQUE,
    shopper_name VARCHAR(120) DEFAULT NULL,
    preferred_category VARCHAR(80) DEFAULT NULL,
    last_viewed_product VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS experiment10_cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shopper_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(120) NOT NULL,
    category VARCHAR(80) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_experiment10_shopper
        FOREIGN KEY (shopper_id) REFERENCES experiment10_shoppers(id)
        ON DELETE CASCADE
);
