<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250618120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление типов товаров и товаров';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO product_types (name) VALUES
            ('Электроника'),
            ('Аксессуары'),
            ('Продукты'),
            ('Мебель')
            SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO products (name, type_id, description, quantity, price, created_at, updated_at) VALUES
            ('Ноутбук Pro 15', 1, '15.6" экран, 16 ГБ RAM, SSD 512 ГБ', 12, 89990.00, NOW(), NOW()),
            ('Беспроводная мышь', 2, 'Bluetooth 5.0', 45, 2490.00, NOW(), NOW()),
            ('Кофе Arabica', 3, 'Зерновой кофе, 1 кг', 30, 890.50, NOW(), NOW()),
            ('Стул офисный', 4, 'Регулировка высоты, сетчатая спинка', 8, 12500.00, NOW(), NOW()),
            ('USB-C кабель 2м', 2, 'Длина 2 м, быстрая зарядка', 100, 590.00, NOW(), NOW()),
            ('USB-C кабель 5м', 2, 'Длина 5 м, быстрая зарядка', 5, 590.00, NOW(), NOW()),
            ('Диван-кровать', 4, 'Регулировка наклона', 0, 12500.00, NOW(), NOW())
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM products');
        $this->addSql('DELETE FROM product_types');
    }
}
