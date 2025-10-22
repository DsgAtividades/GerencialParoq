-- Insert new admin user (password: admin123)
INSERT INTO obras_system_users (username, password, nome_completo, tipo_acesso) 
VALUES ('denys', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Denys', 'Administrador')
ON DUPLICATE KEY UPDATE password = VALUES(password);
