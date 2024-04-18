<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130104702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Associations CHANGE email_assoc email_assoc VARCHAR(512) DEFAULT NULL, CHANGE initiale initiale VARCHAR(255) NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE dirigeant_president dirigeant_president VARCHAR(255) NOT NULL, CHANGE dirigeant_vice_president dirigeant_vice_president VARCHAR(255) NOT NULL, CHANGE dirigeant_tresorier dirigeant_tresorier VARCHAR(255) NOT NULL, CHANGE dirigeant_secretaire dirigeant_secretaire VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE Users CHANGE n_licence n_licence VARCHAR(255) NOT NULL, CHANGE telephone telephone VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE renouvellement_at renouvellement_at DATETIME NOT NULL, CHANGE complement complement VARCHAR(255) NOT NULL, CHANGE is_imprimed is_imprimed TINYINT(1) NOT NULL, CHANGE impression impression TINYINT(1) NOT NULL, CHANGE imprimed_at imprimed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE Users RENAME INDEX idx_1483a5e93ca2e1a6 TO IDX_D5428AED3CA2E1A6');
        $this->addSql('ALTER TABLE Users RENAME INDEX idx_91101f499826015 TO IDX_D5428AED98260155');
        $this->addSql('ALTER TABLE Users RENAME INDEX nom TO IDX_D5428AED6C6E55B5');
        $this->addSql('ALTER TABLE dirigeants RENAME INDEX idx_3e4ad1b33ca2e1a6 TO IDX_3CB249BF3CA2E1A6');
        $this->addSql('ALTER TABLE tickets CHANGE destinataire destinataire VARCHAR(255) NOT NULL, CHANGE envoyeur envoyeur VARCHAR(255) NOT NULL, CHANGE message message TINYTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Associations CHANGE email_assoc email_assoc VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE initiale initiale VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE dirigeant_president dirigeant_president VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE dirigeant_vice_president dirigeant_vice_president VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE dirigeant_tresorier dirigeant_tresorier VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE dirigeant_secretaire dirigeant_secretaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE Users CHANGE n_licence n_licence VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE complement complement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE telephone telephone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE is_imprimed is_imprimed TINYINT(1) DEFAULT \'0\', CHANGE impression impression TINYINT(1) DEFAULT NULL, CHANGE imprimed_at imprimed_at DATETIME DEFAULT NULL, CHANGE renouvellement_at renouvellement_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE Users RENAME INDEX idx_d5428aed3ca2e1a6 TO IDX_1483A5E93CA2E1A6');
        $this->addSql('ALTER TABLE Users RENAME INDEX idx_d5428aed98260155 TO IDX_91101F499826015');
        $this->addSql('ALTER TABLE Users RENAME INDEX idx_d5428aed6c6e55b5 TO nom');
        $this->addSql('ALTER TABLE dirigeants RENAME INDEX idx_3cb249bf3ca2e1a6 TO IDX_3E4AD1B33CA2E1A6');
        $this->addSql('ALTER TABLE tickets CHANGE destinataire destinataire VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE envoyeur envoyeur VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CHANGE message message TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`');
    }
}
