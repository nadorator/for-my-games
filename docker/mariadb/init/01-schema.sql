-- Create database schemas for the microservices

-- Use the main database
USE formygames;

-- Create tables for User Service
CREATE TABLE visitors (
    id CHAR(36) PRIMARY KEY COMMENT 'UUID as the primary key',
    created_at DATETIME NOT NULL,
    last_active DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE editors (
    id CHAR(36) PRIMARY KEY COMMENT 'UUID as the primary key',
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator') NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tables for Content Service
CREATE TABLE games (
    id CHAR(36) PRIMARY KEY COMMENT 'UUID as the primary key',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contents (
    id CHAR(36) PRIMARY KEY COMMENT 'UUID as the primary key',
    type ENUM('test', 'configuration', 'soluce') NOT NULL,
    game_id CHAR(36) NOT NULL,
    visitor_id CHAR(36),
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('draft', 'published', 'moderated') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial admin user
INSERT INTO editors (id, email, password, role, created_at) VALUES (
    UUID(), -- Generate a UUID
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    NOW()
);

-- Insert some sample games
INSERT INTO games (id, title, description, created_at) VALUES 
(UUID(), 'The Legend of Zelda', 'An action-adventure game.', NOW()),
(UUID(), 'Final Fantasy VII', 'An iconic RPG game.', NOW()),
(UUID(), 'Super Mario Bros', 'A classic platformer game.', NOW()); 