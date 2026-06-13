<?php
require_once __DIR__ . '/../includes/db_helper.php';

global $pdo;

try {
    echo "Starting database migration...\n";

    // 1. Check if books table still has the 'category' column
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'category'");
    $categoryColumn = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($categoryColumn) {
        echo "Found legacy 'category' column in 'books' table. Proceeding with migration...\n";

        // 2. Create categories table if it does not exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "Table 'categories' created or verified.\n";

        // 3. Extract unique categories from current books table and insert them into categories table
        $pdo->exec("
            INSERT IGNORE INTO categories (name) 
            SELECT DISTINCT category FROM books 
            WHERE category IS NOT NULL AND category != '';
        ");
        echo "Existing category strings imported into 'categories' table.\n";

        // 4. Ensure some standard categories exist in the database even if they weren't in the books
        $standard_categories = [
            'Tâm lý - Kỹ năng sống' => 'Sách hướng dẫn về kỹ năng sống, giao tiếp, và phát triển bản thân.',
            'Tiểu thuyết' => 'Các tác phẩm văn học hư cấu mang tính chất tiểu thuyết.',
            'Công nghệ thông tin' => 'Sách về lập trình, thiết kế web, hệ quản trị cơ sở dữ liệu và công nghệ.',
            'Văn học Việt Nam' => 'Các tác phẩm văn học nổi tiếng của các tác giả Việt Nam.',
            'Khoa học vũ trụ' => 'Sách nghiên cứu và phổ biến kiến thức về thiên văn và vũ trụ.',
            'Tài chính cá nhân' => 'Sách hướng dẫn quản lý tài chính, đầu tư và tư duy làm giàu.'
        ];

        $stmt_insert_cat = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (:name, :description)");
        foreach ($standard_categories as $name => $desc) {
            $stmt_insert_cat->execute(['name' => $name, 'description' => $desc]);
        }
        echo "Standard categories populated.\n";

        // 5. Add category_id column to books table
        $pdo->exec("ALTER TABLE books ADD COLUMN category_id INT NULL");
        echo "Column 'category_id' added to 'books' table.\n";

        // 6. Update books category_id based on matching name in categories table
        $pdo->exec("
            UPDATE books b 
            JOIN categories c ON b.category = c.name 
            SET b.category_id = c.id
        ");
        echo "Mapped existing books to their new category IDs.\n";

        // 7. For any book that has no category or didn't match, assign a default category or first category
        $stmt_default_cat = $pdo->query("SELECT id FROM categories LIMIT 1");
        $default_cat_id = $stmt_default_cat->fetchColumn();
        
        if ($default_cat_id) {
            $pdo->exec("UPDATE books SET category_id = $default_cat_id WHERE category_id IS NULL");
        }

        // 8. Make category_id NOT NULL and add foreign key constraint
        $pdo->exec("ALTER TABLE books MODIFY COLUMN category_id INT NOT NULL");
        $pdo->exec("ALTER TABLE books ADD CONSTRAINT fk_books_categories FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT");
        echo "Foreign key constraint added.\n";

        // 9. Drop the old category string column
        $pdo->exec("ALTER TABLE books DROP COLUMN category");
        echo "Legacy 'category' column dropped from 'books' table.\n";

        echo "Migration completed successfully!\n";
    } else {
        echo "Database already migrated. Legacy 'category' column not found in 'books' table.\n";
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Migration failed: " . $e->getMessage() . "\n");
}
