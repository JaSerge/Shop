<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250618120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблиц product_types и products';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE product_types (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(100) NOT NULL,
                UNIQUE INDEX UNIQ_product_types_name (name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE products (
                id INT AUTO_INCREMENT NOT NULL,
                type_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                quantity INT NOT NULL,
                price NUMERIC(10, 2) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_products_type_id (type_id),
                PRIMARY KEY(id),
                CONSTRAINT FK_products_type_id FOREIGN KEY (type_id) REFERENCES product_types (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE product_types');
    }
}
