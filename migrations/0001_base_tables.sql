CREATE TABLE IF NOT EXISTS migrations (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS usgiers (
    user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS books (
    book_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    owner_user_id INT NOT NULL,
    title VARCHAR(128) NOT NULL,
    text MEDIUMTEXT,
    is_deleted BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS shares (
    owner_user_id INT NOT NULL,
    grantee_user_id INT NOT NULL,
    UNIQUE (owner_user_id, grantee_user_id),
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id),
    FOREIGN KEY (grantee_user_id) REFERENCES users(user_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;