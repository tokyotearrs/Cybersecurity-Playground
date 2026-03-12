CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user'
);

INSERT INTO users (username, password, role)
VALUES
    ('admin', TO_BASE64(AES_ENCRYPT('"j7i+X2$9w71', 'playground_secret_key')), 'admin'),
    ('alice', TO_BASE64(AES_ENCRYPT('P@ssw0rd123!', 'playground_secret_key')), 'user'),
    ('bob', TO_BASE64(AES_ENCRYPT('7xK!mP9#qa2L', 'playground_secret_key')), 'user'),
    ('charlie', TO_BASE64(AES_ENCRYPT('Test!2345abc', 'playground_secret_key')), 'user'),
    ('diana', TO_BASE64(AES_ENCRYPT('Z9@lQ1#vB7sK', 'playground_secret_key')), 'user'),
    ('eric', TO_BASE64(AES_ENCRYPT('Secure*7781', 'playground_secret_key')), 'user'),
    ('frank', TO_BASE64(AES_ENCRYPT('4Tg!pL0@xWz2', 'playground_secret_key')), 'user'),
    ('grace', TO_BASE64(AES_ENCRYPT('MyPwd#2026!', 'playground_secret_key')), 'user'),
    ('henry', TO_BASE64(AES_ENCRYPT('Qw8!Lm2@ZaP', 'playground_secret_key')), 'user');
