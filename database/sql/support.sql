CREATE TABLE IF NOT EXISTS support_threads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('suggestion', 'bug') NOT NULL,
    subject VARCHAR(200) NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX support_threads_owner_updated (user_id, updated_at),
    INDEX support_threads_type_updated (type, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    kind ENUM('reply', 'status') NOT NULL DEFAULT 'reply',
    content TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (thread_id) REFERENCES support_threads(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX support_messages_thread_id (thread_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_submission_reviews (
    thread_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    status ENUM('pending', 'approved', 'refused') NOT NULL DEFAULT 'pending',
    response_message TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (thread_id) REFERENCES support_threads(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX support_submission_review_status (status, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_access_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved', 'refused') NOT NULL DEFAULT 'pending',
    response_message TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (thread_id) REFERENCES support_threads(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    UNIQUE KEY support_access_request_user (thread_id, user_id),
    INDEX support_access_request_status (thread_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_reads (
    thread_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    last_read_message_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (thread_id, user_id),
    FOREIGN KEY (thread_id) REFERENCES support_threads(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (last_read_message_id) REFERENCES support_messages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_notification_links (
    notification_id INT NOT NULL PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES support_messages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_access_notification_links (
    notification_id INT NOT NULL PRIMARY KEY,
    access_request_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (access_request_id) REFERENCES support_access_requests(id),
    INDEX support_access_notification_request (access_request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
