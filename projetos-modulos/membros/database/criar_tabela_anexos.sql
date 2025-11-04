-- Criar tabela membros_anexos para armazenar arquivos (fotos, documentos, etc)
CREATE TABLE IF NOT EXISTS `membros_anexos` (
  `id` varchar(36) NOT NULL,
  `membro_id` varchar(36) DEFAULT NULL,
  `tipo` enum('foto','documento','outro') COLLATE utf8mb4_general_ci DEFAULT 'outro',
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(500) NOT NULL,
  `tamanho` int(11) DEFAULT NULL COMMENT 'Tamanho em bytes',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'image/jpeg, image/png, etc',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `membro_id` (`membro_id`),
  KEY `tipo` (`tipo`),
  CONSTRAINT `fk_anexos_membro` FOREIGN KEY (`membro_id`) REFERENCES `membros_membros` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

