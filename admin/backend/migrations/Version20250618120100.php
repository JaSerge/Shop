<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250618120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление тестовых товаров';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO products (name, type, description, quantity, price, created_at, updated_at) VALUES
            ('Ноутбук Pro 15', 'Электроника', '15.6" экран, 16 ГБ RAM, SSD 512 ГБ', 12, 89990.00, NOW(), NOW()),
            ('Беспроводная мышь', 'Аксессуары', 'Эрgonomic, Bluetooth 5.0', 45, 2490.00, NOW(), NOW()),
            ('Кофе Arabica', 'Продукты', 'Зерновой кофе, 1 кг', 30, 890.50, NOW(), NOW()),
            ('Стул офисный', 'Мебель', 'Регулировка высоты, сетчатая спинка', 8, 12500.00, NOW(), NOW()),
            ('USB-C кабель', 'Аксессуары', 'Длина 2 м, быстрая зарядка', 100, 590.00, NOW(), NOW())
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM products');
    }
}
