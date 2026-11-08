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
    $status = normalizeCategoryStatusValue($_POST['status'] ?? 0);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $statusColumn = categoryStatusColumnName();
    
    try {
        $query = "INSERT INTO categories (name, " . $statusColumn . ") VALUES (:name, :status)";
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
    $status = normalizeCategoryStatusValue($_POST['status'] ?? 0);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $statusColumn = categoryStatusColumnName();

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
        $query = "UPDATE categories SET name = :name, " . $statusColumn . " = :status WHERE id = :id";
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
    $isAvailable = !empty($_POST['is_available']) ? 1 : 0;
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
    $isAvailable = !empty($_POST['is_available']) ? 1 : 0;
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

// Get menu stats
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_menu_stats') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    try {
        $totalItems = (int) $conn->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
        $availableItems = (int) $conn->query("SELECT COUNT(*) FROM menu_items WHERE is_available = 1")->fetchColumn();
        $unavailableItems = (int) $conn->query("SELECT COUNT(*) FROM menu_items WHERE is_available = 0")->fetchColumn();
        $categoryCount = (int) $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'stats' => [
                    'total' => $totalItems,
                    'available' => $availableItems,
                    'unavailable' => $unavailableItems,
                    'categories' => $categoryCount
                ]
            ]);
            exit;
        }
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unable to load menu stats.']);
            exit;
        }
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

function getCurrentUserId()
{
    return isset($_SESSION['user_data']['id']) ? (int) $_SESSION['user_data']['id'] : 0;
}

if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_addresses') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in to manage addresses.'];
        header('Location: ../login.php');
        exit;
    }

    try {
        $query = "SELECT id, type, address, contact_number, is_default
                  FROM addresses
                  WHERE user_id = :user_id
                  ORDER BY is_default DESC, id DESC";
        $statement = $conn->prepare($query);
        $statement->execute([':user_id' => $userId]);
        $addresses = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'addresses' => $addresses
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid address request.'];
        header("Location: ../gb/settings.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while loading addresses.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while loading addresses.'];
        header("Location: ../gb/settings.php");
        exit;
    }
}

if (isset($_POST['form_type']) && $_POST['form_type'] === 'add_address') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in to add an address.'];
        header('Location: ../login.php');
        exit;
    }

    $addressType = trim($_POST['type'] ?? 'Home');
    $address = trim($_POST['address'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $isDefault = !empty($_POST['is_default']);

    if ($address === '' || $contactNumber === '') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Address and contact number are required.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Address and contact number are required.'];
        header("Location: ../gb/settings.php");
        exit;
    }

    try {
        if ($isDefault) {
            $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id")
                ->execute([':user_id' => $userId]);
        }

        $query = "INSERT INTO addresses (user_id, type, address, contact_number, is_default)
                  VALUES (:user_id, :type, :address, :contact_number, :is_default)";
        $statement = $conn->prepare($query);
        $result = $statement->execute([
            ':user_id' => $userId,
            ':type' => $addressType,
            ':address' => $address,
            ':contact_number' => $contactNumber,
            ':is_default' => $isDefault ? 1 : 0,
        ]);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Address added successfully!',
                    'address' => [
                        'id' => $conn->lastInsertId(),
                        'type' => $addressType,
                        'address' => $address,
                        'contact_number' => $contactNumber,
                        'is_default' => $isDefault ? 1 : 0,
                    ]
                ]);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Address added successfully!'];
            header("Location: ../gb/settings.php");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to add address.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to add address.'];
        header("Location: ../gb/settings.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while saving the address.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while saving the address.'];
        header("Location: ../gb/settings.php");
        exit;
    }
}

if (isset($_POST['form_type']) && $_POST['form_type'] === 'edit_address') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();
    $addressId = isset($_POST['address_id']) ? (int) $_POST['address_id'] : 0;

    if (!$userId || !$addressId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid address data.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid address data.'];
        header("Location: ../gb/settings.php");
        exit;
    }

    $addressType = trim($_POST['type'] ?? 'Home');
    $address = trim($_POST['address'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $isDefault = !empty($_POST['is_default']);

    if ($address === '' || $contactNumber === '') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Address and contact number are required.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Address and contact number are required.'];
        header("Location: ../gb/settings.php");
        exit;
    }

    try {
        if ($isDefault) {
            $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id")
                ->execute([':user_id' => $userId]);
        }

        $query = "UPDATE addresses
                  SET type = :type,
                      address = :address,
                      contact_number = :contact_number,
                      is_default = :is_default
                  WHERE id = :id AND user_id = :user_id";

        $statement = $conn->prepare($query);
        $result = $statement->execute([
            ':type' => $addressType,
            ':address' => $address,
            ':contact_number' => $contactNumber,
            ':is_default' => $isDefault ? 1 : 0,
            ':id' => $addressId,
            ':user_id' => $userId,
        ]);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Address updated successfully!',
                    'address' => [
                        'id' => $addressId,
                        'type' => $addressType,
                        'address' => $address,
                        'contact_number' => $contactNumber,
                        'is_default' => $isDefault ? 1 : 0,
                    ]
                ]);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Address updated successfully!'];
            header("Location: ../gb/settings.php");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update address.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to update address.'];
        header("Location: ../gb/settings.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the address.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the address.'];
        header("Location: ../gb/settings.php");
        exit;
    }
}

if (isset($_POST['form_type']) && $_POST['form_type'] === 'delete_address') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();
    $addressId = isset($_POST['address_id']) ? (int) $_POST['address_id'] : 0;

    if (!$userId || !$addressId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid address ID.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid address data.'];
        header("Location: ../gb/settings.php");
        exit;
    }

    try {
        $current = $conn->prepare("SELECT is_default FROM addresses WHERE id = :id AND user_id = :user_id");
        $current->execute([':id' => $addressId, ':user_id' => $userId]);
        $row = $current->fetch(PDO::FETCH_ASSOC);

        $deleteStmt = $conn->prepare("DELETE FROM addresses WHERE id = :id AND user_id = :user_id");
        $result = $deleteStmt->execute([
            ':id' => $addressId,
            ':user_id' => $userId,
        ]);

        if ($result) {
            if (!empty($row['is_default'])) {
                $fallbackStmt = $conn->prepare("SELECT id FROM addresses WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
                $fallbackStmt->execute([':user_id' => $userId]);
                $fallback = $fallbackStmt->fetch(PDO::FETCH_ASSOC);

                if ($fallback) {
                    $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = :id AND user_id = :user_id")
                        ->execute([':id' => $fallback['id'], ':user_id' => $userId]);
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Address deleted successfully!']);
                exit;
            }

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Address deleted successfully!'];
            header("Location: ../gb/settings.php");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete address.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to delete address.'];
        header("Location: ../gb/settings.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while deleting the address.']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while deleting the address.'];
        header("Location: ../gb/settings.php");
        exit;
    }
}

// Place Order
if (isset($_POST['form_type']) && $_POST['form_type'] === 'place_order') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in to place an order.'];
        header('Location: ../login.php');
        exit;
    }

    $itemsJson = $_POST['items'] ?? '[]';
    $addressId = isset($_POST['address_id']) ? (int) $_POST['address_id'] : 0;
    $subtotal = isset($_POST['subtotal']) ? (float) $_POST['subtotal'] : 0;
    $deliveryFee = isset($_POST['delivery_fee']) ? (float) $_POST['delivery_fee'] : 0;
    $totalAmount = isset($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0;
    $orderType = isset($_POST['order_type']) ? (int) $_POST['order_type'] : 1;

    // For pickup, no address is required
    if ($orderType === 0) {
        $addressId = 0;
    }

    try {
        $items = json_decode($itemsJson, true);
        if (!is_array($items) || empty($items)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Your cart is empty.'];
            header("Location: ../gb/binalot_menus.php");
            exit;
        }

        if ($orderType === 1 && !$addressId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Delivery address is required.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please select a delivery address.'];
            header("Location: ../gb/binalot_menus.php");
            exit;
        }

        if ($orderType === 1) {
            // Verify address belongs to user
            $addrCheck = $conn->prepare("SELECT id FROM addresses WHERE id = :id AND user_id = :user_id");
            $addrCheck->execute([':id' => $addressId, ':user_id' => $userId]);
            if (!$addrCheck->fetch(PDO::FETCH_ASSOC)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid delivery address.']);
                    exit;
                }
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid delivery address.'];
                header("Location: ../gb/binalot_menus.php");
                exit;
            }
        }

        $conn->beginTransaction();

        $orderStmt = $conn->prepare("INSERT INTO orders (user_id, address_id, subtotal, delivery_fee, total_amount, order_type, order_status, created_at)
                                     VALUES (:user_id, :address_id, :subtotal, :delivery_fee, :total_amount, :order_type, 'Pending', NOW())");
        $orderStmt->execute([
            ':user_id' => $userId,
            ':address_id' => $addressId,
            ':subtotal' => $subtotal,
            ':delivery_fee' => $deliveryFee,
            ':total_amount' => $totalAmount,
            ':order_type' => $orderType,
        ]);
        $orderId = (int) $conn->lastInsertId();

        // Generate order_number: GB- + LPAD(id, 6, '0')
        $orderNumStmt = $conn->prepare("UPDATE orders SET order_number = CONCAT('GB-', LPAD(:id, 6, '0')) WHERE id = :id");
        $orderNumStmt->execute([':id' => $orderId]);
        $orderNumber = 'GB-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);

        // Insert order items
        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, item_name, unit_price, quantity, subtotal)
                                    VALUES (:order_id, :menu_item_id, :item_name, :unit_price, :quantity, :subtotal)");
        foreach ($items as $item) {
            $itemQty = isset($item['qty']) ? (int) $item['qty'] : 1;
            $itemPrice = isset($item['price']) ? (float) $item['price'] : 0;
            $itemSubtotal = $itemPrice * $itemQty;
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':menu_item_id' => (isset($item['menu_item_id']) && $item['menu_item_id'] !== null && $item['menu_item_id'] !== '') ? (int) $item['menu_item_id'] : 0,
                ':item_name' => $item['name'] ?? 'Menu Item',
                ':unit_price' => $itemPrice,
                ':quantity' => $itemQty,
                ':subtotal' => $itemSubtotal,
            ]);
        }

        // Insert order status history
        $historyStmt = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, remarks, created_at)
                                       VALUES (:order_id, :old_status, :new_status, :changed_by, :remarks, NOW())");
        $historyStmt->execute([
            ':order_id' => $orderId,
            ':old_status' => '',
            ':new_status' => 'Pending',
            ':changed_by' => $userId,
            ':remarks' => 'Order placed',
        ]);

        $conn->commit();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Order placed successfully!'];
        header("Location: ../gb/my_orders.php");
        exit;

    } catch (\PDOException $e) {
        $conn->rollBack();
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while placing the order.'];
        header("Location: ../gb/binalot_menus.php");
        exit;
    }
}

// Update Order Status
if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_order_status') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $newStatus = trim($_POST['new_status'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (!$orderId || !$newStatus) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order ID and new status are required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order status data.'];
        header("Location: ../gb/orders.php");
        exit;
    }

    try {
        $current = $conn->prepare("SELECT order_status FROM orders WHERE id = :id");
        $current->execute([':id' => $orderId]);
        $row = $current->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order not found.'];
            header("Location: ../gb/orders.php");
            exit;
        }

        $conn->beginTransaction();

        $updateStmt = $conn->prepare("UPDATE orders SET order_status = :new_status WHERE id = :id");
        $updateStmt->execute([
            ':new_status' => $newStatus,
            ':id' => $orderId,
        ]);

        $historyStmt = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, remarks, created_at)
                                       VALUES (:order_id, :old_status, :new_status, :changed_by, :remarks, NOW())");
        $historyStmt->execute([
            ':order_id' => $orderId,
            ':old_status' => $row['order_status'],
            ':new_status' => $newStatus,
            ':changed_by' => $userId,
            ':remarks' => $remarks,
        ]);

        $conn->commit();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Order status updated successfully!']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Order status updated successfully!'];
        header("Location: ../gb/orders.php");
        exit;

    } catch (\PDOException $e) {
        $conn->rollBack();
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the order status.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the order status.'];
        header("Location: ../gb/orders.php");
        exit;
    }
}

// Get Orders
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_orders') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    $filterStatus = $_POST['status'] ?? '';
    $forUser = isset($_POST['for_user']) ? (bool) $_POST['for_user'] : false;

    try {
        $sql = "SELECT o.id, o.order_number, o.user_id, u.first_name, u.last_name, u.email,
                       o.address_id, a.address, a.contact_number,
                       o.subtotal, o.delivery_fee, o.total_amount,
                       o.order_status, o.created_at,
                       GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary
                FROM orders o
                JOIN users u ON u.id = o.user_id
                LEFT JOIN addresses a ON a.id = o.address_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                WHERE 1=1";
        $params = [];

        if ($forUser) {
            $sql .= " AND o.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        if ($filterStatus !== '') {
            $sql .= " AND o.order_status = :status";
            $params[':status'] = $filterStatus;
        }

        $sql .= " GROUP BY o.id";
        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'orders' => $orders,
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid orders request.'];
        header("Location: ../gb/orders.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching orders.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while fetching orders.'];
        header("Location: ../gb/orders.php");
        exit;
    }
}

// Get Order Items
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_order_items') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

    if (!$orderId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order ID.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }

    $userRole = $_SESSION['user_data']['role'] ?? 'customer';
    $isAdmin = ($userRole === 'admin');

    try {
        if ($isAdmin) {
            $stmt = $conn->prepare("
                SELECT oi.item_name, oi.quantity, oi.unit_price, oi.subtotal
                FROM order_items oi
                WHERE oi.order_id = :order_id
                ORDER BY oi.id
            ");
            $stmt->execute([':order_id' => $orderId]);
        } else {
            $stmt = $conn->prepare("
                SELECT oi.item_name, oi.quantity, oi.unit_price, oi.subtotal
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE oi.order_id = :order_id AND o.user_id = :user_id
                ORDER BY oi.id
            ");
            $stmt->execute([
                ':order_id' => $orderId,
                ':user_id' => $userId,
            ]);
        }
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'items' => $items,
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order items request.'];
        header("Location: ../gb/my_orders.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching order items.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while fetching order items.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }
}

// Submit Payment (customer uploads GCash receipt)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'submit_payment') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;

    if (!$orderId || !$referenceNumber || $amount <= 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order, reference number, and amount are required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid payment data.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }

    // Verify order belongs to user and is pending payment
    $orderCheck = $conn->prepare("SELECT id, total_amount, order_status FROM orders WHERE id = :id AND user_id = :user_id");
    $orderCheck->execute([':id' => $orderId, ':user_id' => $userId]);
    $order = $orderCheck->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order not found.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }

    if ($order['order_status'] !== 'Pending') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order is not pending payment.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order is not pending payment.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }

    // Handle receipt upload
    $receiptPath = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../img/payments';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['receipt']['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WEBP images are allowed.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid receipt image type.'];
            header("Location: ../gb/my_orders.php");
            exit;
        }
        $extension = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        $safeName = 'receipt_' . $orderId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetPath)) {
            $receiptPath = $safeName;
        }
    }

    if (!$receiptPath) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Receipt image is required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Receipt image is required.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO payments (order_id, method, amount, reference_number, receipt, payment_status, verified_by)
            VALUES (:order_id, 'GCash', :amount, :reference_number, :receipt, 'pending', 0)
        ");
        $result = $stmt->execute([
            ':order_id' => $orderId,
            ':amount' => $amount,
            ':reference_number' => $referenceNumber,
            ':receipt' => $receiptPath,
        ]);

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment receipt submitted successfully. Awaiting admin verification.',
                    'payment_id' => $conn->lastInsertId(),
                ]);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Payment receipt submitted successfully.'];
            header("Location: ../gb/my_orders.php");
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to submit payment.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to submit payment.'];
        header("Location: ../gb/my_orders.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while submitting payment.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while submitting payment.'];
        header("Location: ../gb/my_orders.php");
        exit;
    }
}

// Verify Payment (admin approves/rejects)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'verify_payment') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    $paymentId = isset($_POST['payment_id']) ? (int) $_POST['payment_id'] : 0;
    $newStatus = trim($_POST['payment_status'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (!$paymentId || !$newStatus || !in_array($newStatus, ['approved', 'rejected'], true)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid payment verification data.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid payment data.'];
        header("Location: ../gb/payments.php");
        exit;
    }

    try {
        $conn->beginTransaction();

        $payment = $conn->prepare("SELECT order_id FROM payments WHERE id = :id");
        $payment->execute([':id' => $paymentId]);
        $paymentRow = $payment->fetch(PDO::FETCH_ASSOC);

        if (!$paymentRow) {
            $conn->rollBack();
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Payment not found.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Payment not found.'];
            header("Location: ../gb/payments.php");
            exit;
        }

        $updateStmt = $conn->prepare("
            UPDATE payments
            SET payment_status = :status, verified_by = :verified_by, verified_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status' => $newStatus,
            ':verified_by' => $userId,
            ':id' => $paymentId,
        ]);

        if ($newStatus === 'approved') {
            $orderStmt = $conn->prepare("UPDATE orders SET order_status = 'Paid' WHERE id = :id");
            $orderStmt->execute([':id' => $paymentRow['order_id']]);

            $historyStmt = $conn->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, remarks, created_at)
                VALUES (:order_id, :old_status, :new_status, :changed_by, :remarks, NOW())
            ");
            $historyStmt->execute([
                ':order_id' => $paymentRow['order_id'],
                ':old_status' => 'Pending',
                ':new_status' => 'Paid',
                ':changed_by' => $userId,
                ':remarks' => $remarks ?: 'Payment validated',
            ]);
        }

        $conn->commit();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Payment ' . $newStatus . ' successfully!']);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Payment ' . $newStatus . ' successfully!'];
        header("Location: ../gb/payments.php");
        exit;

    } catch (\PDOException $e) {
        $conn->rollBack();
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while verifying payment.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while verifying payment.'];
        header("Location: ../gb/payments.php");
        exit;
    }
}

// Get Payments (admin list)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_payments') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    try {
        $sql = "
            SELECT p.id, p.order_id, o.order_number, u.first_name, u.last_name,
                   p.method, p.amount, p.reference_number, p.receipt, p.payment_status,
                   p.verified_by, p.verified_at,
                   v.first_name AS verified_first_name, v.last_name AS verified_last_name
            FROM payments p
            JOIN orders o ON o.id = p.order_id
            JOIN users u ON u.id = o.user_id
            LEFT JOIN users v ON v.id = p.verified_by
            ORDER BY p.created_at DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'payments' => $payments,
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid payments request.'];
        header("Location: ../gb/payments.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching payments.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while fetching payments.'];
        header("Location: ../gb/payments.php");
        exit;
    }
}

// Get Order Payment (latest payment for an order, admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_order_payment') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please log in.'];
        header('Location: ../login.php');
        exit;
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

    if (!$orderId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order ID.'];
        header("Location: ../gb/orders.php");
        exit;
    }

    $userRole = $_SESSION['user_data']['role'] ?? 'customer';
    $isAdmin = ($userRole === 'admin');

    try {
        if ($isAdmin) {
            $stmt = $conn->prepare("
                SELECT pay.id, pay.order_id, pay.method, pay.amount, pay.reference_number, pay.receipt, pay.payment_status,
                       pay.verified_by, pay.verified_at, pay.created_at,
                       o.order_number, u.first_name, u.last_name
                FROM payments pay
                JOIN orders o ON o.id = pay.order_id
                JOIN users u ON u.id = o.user_id
                WHERE pay.order_id = :order_id
                ORDER BY pay.id DESC
                LIMIT 1
            ");
            $stmt->execute([':order_id' => $orderId]);
        } else {
            $stmt = $conn->prepare("
                SELECT pay.id, pay.order_id, pay.method, pay.amount, pay.reference_number, pay.receipt, pay.payment_status,
                       pay.verified_by, pay.verified_at, pay.created_at,
                       o.order_number, u.first_name, u.last_name
                FROM payments pay
                JOIN orders o ON o.id = pay.order_id
                JOIN users u ON u.id = o.user_id
                WHERE pay.order_id = :order_id AND o.user_id = :user_id
                ORDER BY pay.id DESC
                LIMIT 1
            ");
            $stmt->execute([':order_id' => $orderId, ':user_id' => $userId]);
        }
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'payment' => $payment ?: null,
            ]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order payment request.'];
        header("Location: ../gb/orders.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching payment.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while fetching payment.'];
        header("Location: ../gb/orders.php");
        exit;
    }
}

function tableHasColumn($tableName, $columnName)
{
    global $conn;

    if (!$conn instanceof PDO) {
        return false;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table_name AND column_name = :column_name");
    $stmt->execute([
        ':table_name' => $tableName,
        ':column_name' => $columnName,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function normalizeCategoryStatusValue($value)
{
    $normalized = is_string($value) ? trim(strtolower($value)) : $value;

    if (is_bool($normalized)) {
        return $normalized ? 1 : 0;
    }

    if (is_int($normalized) || is_float($normalized)) {
        return ((int) $normalized) === 1 ? 1 : 0;
    }

    if (is_string($normalized)) {
        return in_array($normalized, ['1', 'y', 'yes', 'true', 'active', 'enabled'], true) ? 1 : 0;
    }

    return 0;
}

function categoryStatusColumnName()
{
    return 'status';
}