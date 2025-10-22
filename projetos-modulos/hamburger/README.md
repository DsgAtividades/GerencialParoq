# Sistema Festa do Hambúrguer

## Como rodar localmente (XAMPP)

1. **Clone ou copie os arquivos para a pasta `www` do XAMPP:**
   - Exemplo: `C:/wamp64/www/hamburger`

2. **Crie o banco de dados MySQL:**
   - Acesse o phpMyAdmin ou use o terminal MySQL.
   - Execute o script abaixo para criar as tabelas:

```sql
CREATE DATABASE IF NOT EXISTS hamburger;
USE hamburger;

CREATE TABLE ingressos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(20) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('pendente','entregue') DEFAULT 'pendente',
    quantidade INT DEFAULT 1
);

CREATE TABLE fila (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ingresso_id INT NOT NULL,
    hora_entrada DATETIME NOT NULL,
    status ENUM('em_espera','entregue') DEFAULT 'em_espera',
    FOREIGN KEY (ingresso_id) REFERENCES ingressos(id)
);

CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ingresso_id INT NOT NULL,
    hora_entrega DATETIME NOT NULL,
    FOREIGN KEY (ingresso_id) REFERENCES ingressos(id)
);

-- Para permitir mais de um hambúrguer por ingresso:
ALTER TABLE ingressos ADD COLUMN quantidade INT DEFAULT 1;
```

3. **Configure o acesso ao banco:**
   - Edite `config/config.php` se necessário (usuário, senha, host).

4. **Acesse o sistema:**
   - No navegador, acesse: `http://localhost/hamburger/index.php`

## Estrutura de Pastas
- `controllers/` — Lógica dos controladores
- `models/` — Modelos de dados
- `views/` — Telas do sistema
- `core/` — Núcleo (Router, Database)
- `config/` — Configurações
- `public/` — (opcional) Arquivos públicos

## Funcionalidades
- Venda de ingressos
- Entrada na fila
- Fila em tempo real (TV)
- Baixa de entrega
- Dashboard

---
Dúvidas? Abra um issue ou entre em contato. 