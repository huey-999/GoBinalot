<?php

include 'config.php';
include 'auth.php';

function resolveUploadedMenuImage($fileTmp, $fileName, $uploadDir)
{
    if (empty($fileTmp) || !is_file($fileTmp)) {
        return null;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($fileTmp);

    if (!in_array($fileType, $allowedTypes, true)) {
        return null;
    }

    $fileHash = hash_file('sha1', $fileTmp);
    $existingFiles = scandir($uploadDir);

    foreach ($existingFiles as $existingFile) {
        if ($existingFile === '.' || $existingFile === '..') {
            continue;
        }

        $existingPath = $uploadDir . DIRECTORY_SEPARATOR . $existingFile;

        if (is_file($existingPath) && hash_file('sha1', $existingPath) === $fileHash) {
            return $existingFile;
        }
    }

    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($extension === '') {
        $extensionMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensionMap[$fileType] ?? 'jpg';
    }

    $safeName = 'menu_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

    if (move_uploaded_file($fileTmp, $targetPath)) {
        return $safeName;
    }

    return null;
}

function cleanupMenuImageIfUnused($menuId, $imageName)
{
    if (empty($imageName)) {
        return;
    }

    global $conn;

    if (!$conn instanceof PDO) {
        return;
    }

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM menu_items WHERE image = :image AND id != :id");
    $checkStmt->execute([
        ':image' => $imageName,
        ':id' => $menuId,
    ]);

    if ((int) $checkStmt->fetchColumn() > 0) {
        return;
    }

    $filePath = __DIR__ . '/../img/menu/' . basename($imageName);
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

// Register Customer
if (isset($_POST['form_type']) && $_POST['form_type'] === 'register') {

    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['regEmail'] ?? '');
    $password = $_POST['regPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $agreeTerms = isset($_POST['agreeTerms']) ? 'Yes' : 'No';

    try {

            $query = "INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (:first_name, :last_name, :email, :password, :role, NOW())";
            $statement = $conn->prepare($query);
            $data = [
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'customer',
            ];

            $result = $statement->execute($data);

        if ($result) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Registration successful! Please log in.'
            ];
            header("Location: ../login.php");
            exit;
        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Registration failed. Please try again.'
            ];
            header("Location: ../register.php");
            exit;
        }

    } catch (\PDOException $e) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Something went wrong while registering.'
        ];
        header("Location: ../register.php");
        exit;
    }
}


// Login Customer
if (isset($_POST['form_type']) && $_POST['form_type'] === 'login') {

    $email = trim($_POST['loginEmail'] ?? '');
    $password = $_POST['loginPassword'] ?? '';

    try {

        $query = "SELECT * FROM users WHERE email = :email";
        $statement = $conn->prepare($query);
        $data = [':email' => $email];

        $statement->execute($data);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            
            $_SESSION['user_data'] = [
                'id' => $result['id'],
                'first_name' => $result['first_name'],
                'last_name' => $result['last_name'],
                'email' => $result['email'],
                'phone' => $result['phone'],
                'role' => $result['role'],
            ];

            if ($_SESSION['user_data']['role'] == 'admin') {
                header("Location: ../gb/dashboard.php");
                exit;
            } else {
                header("Location: ../gb/my_orders.php");
                exit;
            }

        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Invalid email or password.',
            ];
            header("Location: ../login.php");
            exit;
        }

    } catch (\PDOException $e) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Unable to login right now.'
        ];
        header("Location: ../login.php");
        exit;
    }
}

// Add Category
if (isset($_POST['form_type']) && $_POST['form_type'] === 'add_category') {

    $categoryName = trim($_POST['category_name'] ?? '');
    $status = !empty($_POST['status']) ? 'active' : 'inactive';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    
    try {
        $query = "INSERT INTO categories (name, status) VALUES (:name, :status)";
        $statement = $conn->prepare($query);
        $data = [
            ':name' => $categoryName,
            ':status' => $status
        ];

        $result = $statement->execute($data);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Category added successfully!',
                    'category' => [
                        'id' => $conn->lastInsertId(),
                        'name' => $categoryName,
                        'status' => $status
                    ]
                ]);
                exit;
            }

            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Category added successfully!'
            ];
            header("Location: ../gb/menus.php#categories");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add category.'
            ]);
            exit;
        }

        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Failed to add category.'
        ];
        header("Location: ../gb/menus.php#categories");
        exit;
        
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Something went wrong while adding the category.'
            ]);
            exit;
        }

        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Something went wrong while adding the category.'
        ];
        header("Location: ../gb/menus.php#categories");
        exit;
    }
}

// Edit Category
if (isset($_POST['form_type']) && $_POST['form_type'] === 'edit_category') {

    $categoryId = trim($_POST['category_id'] ?? '');
    $categoryName = trim($_POST['category_name'] ?? '');
    $status = !empty($_POST['status']) ? 'active' : 'inactive';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if (!$categoryId || !$categoryName) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category ID and name are required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid category data.'];
        header("Location: ../gb/menus.php#categories");
        exit;
    }

    try {
        $query = "UPDATE categories SET name = :name, status = :status WHERE id = :id";
        $statement = $conn->prepare($query);
        $data = [
            ':name' => $categoryName,
            ':status' => $status,
            ':id' => $categoryId
        ];
        $result = $statement->execute($data);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Category updated successfully!',
                    'category' => ['id' => $categoryId, 'name' => $categoryName, 'status' => $status]
                ]);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Category updated successfully!'];
            header("Location: ../gb/menus.php#categories");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update category.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to update category.'];
        header("Location: ../gb/menus.php#categories");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the category.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the category.'];
        header("Location: ../gb/menus.php#categories");
        exit;
    }
}

// Delete Category
if (isset($_POST['form_type']) && $_POST['form_type'] === 'delete_category') {

    $categoryId = trim($_POST['category_id'] ?? '');
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if (!$categoryId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category ID is required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid category data.'];
        header("Location: ../gb/menus.php#categories");
        exit;
    }

    try {
        $query = "DELETE FROM categories WHERE id = :id";
        $statement = $conn->prepare($query);
        $result = $statement->execute([':id' => $categoryId]);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully!']);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Category deleted successfully!'];
            header("Location: ../gb/menus.php#categories");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete category.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to delete category.'];
        header("Location: ../gb/menus.php#categories");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while deleting the category.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while deleting the category.'];
        header("Location: ../gb/menus.php#categories");
        exit;
    }
}

// Update User Profile
if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_profile') {
    
    $id = $_POST['id'] ?? '';
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');

    try {

        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name WHERE id = :id";
        $statement = $conn->prepare($query);
        $data = [
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':id' => $id,
        ];

        $result = $statement->execute($data);

        if ($result) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Profile updated successfully!'
            ];
            $_SESSION['user_data']['first_name'] = $firstName;
            $_SESSION['user_data']['last_name'] = $lastName;
            header("Location: ../gb/profile.php");
            exit;
        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Failed to update profile.'
            ];
            header("Location: ../gb/profile.php");
            exit;
        }

    } catch (\PDOException $e) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Something went wrong while updating your profile.'
        ];
        header("Location: ../gb/profile.php");
        exit;
    }
}

// Add Menu Item
if (isset($_POST['form_type']) && $_POST['form_type'] === 'add_menu_item') {

    $menuName = trim($_POST['menu_name'] ?? '');
    $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $description = trim($_POST['description'] ?? '');
    $isAvailable = !empty($_POST['is_available']) ? 'Y' : 'N';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    $imageFileName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../img/menu';
        $imageFileName = resolveUploadedMenuImage($_FILES['image']['tmp_name'], $_FILES['image']['name'], $uploadDir);
    }

    if (!$menuName || !$categoryId || $price < 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Menu name, category, and price are required.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please complete the required menu item fields.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }

    try {
        $query = "INSERT INTO menu_items (category_id, name, description, price, image, is_available)
                  VALUES (:category_id, :name, :description, :price, :image, :is_available)";
        $statement = $conn->prepare($query);
        $data = [
            ':category_id' => $categoryId,
            ':name' => $menuName,
            ':description' => $description,
            ':price' => $price,
            ':image' => $imageFileName,
            ':is_available' => $isAvailable,
        ];

        $result = $statement->execute($data);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Menu item added successfully!',
                    'menu_item' => [
                        'id' => $conn->lastInsertId(),
                        'name' => $menuName,
                        'category_id' => $categoryId,
                        'price' => $price,
                        'description' => $description,
                        'image' => $imageFileName,
                        'is_available' => $isAvailable,
                    ]
                ]);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Menu item added successfully!'];
            header("Location: ../gb/menus.php#menu-items-tab-pane");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to add menu item.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to add menu item.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while adding the menu item.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while adding the menu item.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }
}

// Edit Menu Item
if (isset($_POST['form_type']) && $_POST['form_type'] === 'edit_menu_item') {

    $menuId = isset($_POST['menu_id']) ? (int) $_POST['menu_id'] : 0;
    $menuName = trim($_POST['menu_name'] ?? '');
    $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $description = trim($_POST['description'] ?? '');
    $isAvailable = !empty($_POST['is_available']) ? 'Y' : 'N';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    $imageFileName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../img/menu';
        $imageFileName = resolveUploadedMenuImage($_FILES['image']['tmp_name'], $_FILES['image']['name'], $uploadDir);

        if ($imageFileName !== null) {
            $currentImageStmt = $conn->prepare("SELECT image FROM menu_items WHERE id = :id");
            $currentImageStmt->execute([':id' => $menuId]);
            $currentImage = $currentImageStmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($currentImage['image']) && $currentImage['image'] !== $imageFileName) {
                cleanupMenuImageIfUnused($menuId, $currentImage['image']);
            }
        }
    }

    if ($imageFileName === null && !empty($_POST['keep_existing_image'])) {
        $imageFileName = $_POST['keep_existing_image'];
    }

    if ($imageFileName === null) {
        $currentImageStmt = $conn->prepare("SELECT image FROM menu_items WHERE id = :id");
        $currentImageStmt->execute([':id' => $menuId]);
        $currentImage = $currentImageStmt->fetch(PDO::FETCH_ASSOC);
        $imageFileName = $currentImage['image'] ?? null;
    }

    if (!$menuId || !$menuName || !$categoryId || $price < 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Menu item ID, name, category, and price are required.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid menu item data.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }

    try {
        $query = "UPDATE menu_items
                  SET name = :name,
                      category_id = :category_id,
                      price = :price,
                      description = :description,
                      image = :image,
                      is_available = :is_available
                  WHERE id = :id";

        if ($imageFileName === null) {
            $query = "UPDATE menu_items
                      SET name = :name,
                          category_id = :category_id,
                          price = :price,
                          description = :description,
                          is_available = :is_available
                      WHERE id = :id";
        }

        $statement = $conn->prepare($query);
        $data = [
            ':name' => $menuName,
            ':category_id' => $categoryId,
            ':price' => $price,
            ':description' => $description,
            ':is_available' => $isAvailable,
            ':id' => $menuId,
        ];

        if ($imageFileName !== null) {
            $data[':image'] = $imageFileName;
        }

        $result = $statement->execute($data);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Menu item updated successfully!',
                    'menu_item' => [
                        'id' => $menuId,
                        'name' => $menuName,
                        'category_id' => $categoryId,
                        'price' => $price,
                        'description' => $description,
                        'image' => $imageFileName,
                        'is_available' => $isAvailable,
                    ]
                ]);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Menu item updated successfully!'];
            header("Location: ../gb/menus.php#menu-items-tab-pane");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update menu item.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to update menu item.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the menu item.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the menu item.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }
}

// Delete Menu Item
if (isset($_POST['form_type']) && $_POST['form_type'] === 'delete_menu_item') {

    $menuId = isset($_POST['menu_id']) ? (int) $_POST['menu_id'] : 0;
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if (!$menuId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Menu item ID is required.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid menu item data.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }

    try {
        $query = "DELETE FROM menu_items WHERE id = :id";
        $statement = $conn->prepare($query);

        $currentImageStmt = $conn->prepare("SELECT image FROM menu_items WHERE id = :id");
        $currentImageStmt->execute([':id' => $menuId]);
        $currentImage = $currentImageStmt->fetch(PDO::FETCH_ASSOC);

        $result = $statement->execute([':id' => $menuId]);

        if ($result) {
            cleanupMenuImageIfUnused($menuId, $currentImage['image'] ?? null);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Menu item deleted successfully!']);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Menu item deleted successfully!'];
            header("Location: ../gb/menus.php#menu-items-tab-pane");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete menu item.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to delete menu item.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while deleting the menu item.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while deleting the menu item.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }
}

// Get menu stats
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_menu_stats') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    try {
        $totalItems = (int) $conn->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
        $availableItems = (int) $conn->query("SELECT COUNT(*) FROM menu_items WHERE is_available = 'Y'")->fetchColumn();
        $unavailableItems = (int) $conn->query("SELECT COUNT(*) FROM menu_items WHERE is_available = 'N'")->fetchColumn();
        $categoryCount = (int) $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'stats' => [
                    'total_items' => $totalItems,
                    'available_items' => $availableItems,
                    'unavailable_items' => $unavailableItems,
                    'categories' => $categoryCount
                ]
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid stats request.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching menu stats.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while fetching menu stats.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }
}

// Get categories
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_categories') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    try {
        $query = "SELECT id, name, status FROM categories ORDER BY name ASC";
        $statement = $conn->prepare($query);
        $statement->execute();
        $categories = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'categories' => $categories
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid categories request.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching categories.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while fetching categories.'];
        header("Location: ../gb/menus.php#menu-items-tab-pane");
        exit;
    }
}
?>