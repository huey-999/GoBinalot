<?php

include_once 'config.php';
include_once 'auth.php';

if (isset($conn) && $conn instanceof PDO) {
    ensureDeliveryTables($conn);
}


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
            } elseif ($_SESSION['user_data']['role'] == 'rider') {
                header("Location: ../gb/my_deliveries.php");
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
        if ($conn->inTransaction()) { $conn->rollBack(); }
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

    // Delivery-related statuses for delivery orders are managed by delivery progress.

    try {
        $current = $conn->prepare("SELECT order_status, order_type FROM orders WHERE id = :id");
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

        if ((int) ($row['order_type'] ?? 0) === 1 && in_array($newStatus, ['Out for Delivery', 'Completed'], true)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Delivery and completion statuses for delivery orders are managed on the Delivery page.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Delivery and completion statuses are managed on the Delivery page.'];
            header("Location: ../gb/orders.php");
            exit;
        }

        ensureDeliveryTables($conn);
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

        if ((int) ($row['order_type'] ?? 0) === 1 && $newStatus === 'Preparing') {
    ensureDeliveryRowForOrder($conn, $orderId, $userId, $remarks ?: 'Order moved to Preparing');
}

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
        if ($conn->inTransaction()) { $conn->rollBack(); }
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

// Confirm Delivery (customer acknowledges receipt)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'confirm_delivery') {
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
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid order.'];
        header('Location: ../gb/my_orders.php');
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, order_type, order_status FROM orders WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $orderId, ':user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order not found.'];
            header('Location: ../gb/my_orders.php');
            exit;
        }

        if ((int) ($order['order_type'] ?? 0) !== 1) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Only delivery orders can be confirmed.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Only delivery orders can be confirmed.'];
            header('Location: ../gb/my_orders.php');
            exit;
        }

        if (($order['order_status'] ?? '') !== 'Delivered') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Order is not awaiting confirmation.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order is not awaiting confirmation.'];
            header('Location: ../gb/my_orders.php');
            exit;
        }

        $conn->beginTransaction();
        $upd = $conn->prepare("UPDATE orders SET order_status = 'Completed' WHERE id = :id");
        $upd->execute([':id' => $orderId]);

        $hist = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, remarks, created_at) VALUES (:order_id, :old_status, :new_status, :changed_by, :remarks, NOW())");
        $hist->execute([
            ':order_id' => $orderId,
            ':old_status' => 'Delivered',
            ':new_status' => 'Completed',
            ':changed_by' => $userId,
            ':remarks' => 'Delivery confirmed by customer',
        ]);
        $conn->commit();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Delivery confirmed. Thank you!']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Delivery confirmed. Thank you!'];
        header('Location: ../gb/my_orders.php');
        exit;
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while confirming delivery.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while confirming delivery.'];
        header('Location: ../gb/my_orders.php');
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

    // Admin-only access for payment verification
    if (($_SESSION['user_data']['role'] ?? '') !== 'admin') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/dashboard.php');
        exit;
    }

    // CSRF protection
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid security token.'];
        header('Location: ../gb/payments.php');
        exit;
    }

    $paymentId = isset($_POST['payment_id']) ? (int) $_POST['payment_id'] : 0;
    $newStatus = trim($_POST['payment_status'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $paymentNotes = trim($_POST['payment_notes'] ?? '');

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
            if ($conn->inTransaction()) { $conn->rollBack(); }
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
            SET payment_status = :status, verified_by = :verified_by, verified_at = NOW(), payment_notes = :payment_notes
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status' => $newStatus,
            ':verified_by' => $userId,
            ':payment_notes' => $paymentNotes,
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
        if ($conn->inTransaction()) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while verifying payment: ' . $e->getMessage()]);
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

    // Admin-only access
    if (($_SESSION['user_data']['role'] ?? '') !== 'admin') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/dashboard.php');
        exit;
    }

    try {
        $orderFilter = trim($_POST['order_number'] ?? $_POST['order'] ?? '');

        $sql = "
             SELECT p.id, p.order_id, o.order_number, u.first_name, u.last_name,
                   p.method, p.amount, p.reference_number, p.receipt, p.payment_status,
                   p.verified_by, p.verified_at, o.created_at AS order_created_at,
                   v.first_name AS verified_first_name, v.last_name AS verified_last_name
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             JOIN users u ON u.id = o.user_id
             LEFT JOIN users v ON v.id = p.verified_by
        ";
        $params = [];
        if ($orderFilter !== '') {
            $sql .= " WHERE o.order_number = :order_number";
            $params[':order_number'] = $orderFilter;
        }
        $sql .= " ORDER BY p.verified_at DESC, p.id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
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

// Get all system settings (admin only)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_settings') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userRole = $_SESSION['user_data']['role'] ?? 'customer';

    if ($userRole !== 'admin') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/settings.php');
        exit;
    }

    try {
        $statement = $conn->prepare("SELECT setting_key, setting_value, setting_type, description FROM system_settings ORDER BY setting_key");
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'type' => $row['setting_type'],
                'description' => $row['description'],
            ];
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'settings' => $settings]);
            exit;
        }

        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid settings request.'];
        header("Location: ../gb/settings.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while loading settings.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while loading settings.'];
        header("Location: ../gb/settings.php");
        exit;
    }
}

// Update system settings (admin only) - bulk update from single form
if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_setting') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userRole = $_SESSION['user_data']['role'] ?? 'customer';
    $userId = $_SESSION['user_data']['id'] ?? 0;

    if ($userRole !== 'admin') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/settings.php');
        exit;
    }

    try {
        // Load existing settings to know types
        $typeStmt = $conn->prepare("SELECT setting_key, setting_type FROM system_settings");
        $typeStmt->execute();
        $settingTypes = [];
        foreach ($typeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settingTypes[$row['setting_key']] = $row['setting_type'];
        }

        $uploadDir = __DIR__ . '/../img/settings';
        $updated = [];
        $failed = [];

        // Process file uploads first (inject into $_POST as value)
        foreach ($_FILES as $fieldName => $fileInfo) {
            if (!isset($settingTypes[$fieldName])) continue;
            if ($fileInfo['error'] === UPLOAD_ERR_OK) {
                $imageFileName = resolveUploadedMenuImage($fileInfo['tmp_name'], $fileInfo['name'], $uploadDir);
                if ($imageFileName) {
                    $_POST[$fieldName] = '../img/settings/' . $imageFileName;
                }
            }
        }

        // Process all POST fields as settings
        foreach ($_POST as $key => $value) {
            if ($key === 'form_type') continue;
            if (!isset($settingTypes[$key])) continue;

            $settingType = $settingTypes[$key];
            $settingValue = $value;

            // Handle booleans: unchecked checkboxes won't be in POST
            if ($settingType === 'boolean') {
                $settingValue = (in_array($value, ['1', 'on', 'true'], true)) ? '1' : '0';
            }

            $upsert = $conn->prepare("
                INSERT INTO system_settings (setting_key, setting_value, setting_type, updated_by, updated_at)
                VALUES (:setting_key, :setting_value, :setting_type, :updated_by, NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), updated_by = VALUES(updated_by), updated_at = NOW()
            ");
            $result = $upsert->execute([
                ':setting_key' => $key,
                ':setting_value' => $settingValue,
                ':setting_type' => $settingType,
                ':updated_by' => $userId,
            ]);

            if ($result) {
                $updated[] = $key;
            } else {
                $failed[] = $key;
            }
        }

        // Handle unchecked boolean fields (default to 0)
        foreach ($settingTypes as $key => $settingType) {
            if ($settingType === 'boolean' && !isset($_POST[$key])) {
                $upsert = $conn->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_type, updated_by, updated_at)
                    VALUES (:setting_key, :setting_value, :setting_type, :updated_by, NOW())
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = NOW()
                ");
                $upsert->execute([
                    ':setting_key' => $key,
                    ':setting_value' => '0',
                    ':setting_type' => $settingType,
                    ':updated_by' => $userId,
                ]);
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => empty($failed),
                'message' => count($updated) > 0 ? 'Settings updated successfully!' : 'No settings were updated.',
                'updated' => $updated,
                'failed' => $failed,
            ]);
            exit;
        }

        if (count($updated) > 0) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Settings updated successfully!'];
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'No settings were updated.'];
        }
        header("Location: ../gb/settings.php");
        exit;

    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while saving the settings.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while saving the settings.'];
        header("Location: ../gb/settings.php");
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

// ----- Delivery management helpers and handlers -----

function ensureDeliveryTables($conn)
{
    if (!empty($GLOBALS['delivery_tables_ensured'])) {
        return;
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS deliveries (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        delivery_person INT NULL,
        delivery_status VARCHAR(50) NOT NULL DEFAULT 'pending_dispatch',
        dispatched_at DATETIME NULL,
        picked_up_at DATETIME NULL,
        out_for_delivery_at DATETIME NULL,
        delivered_at DATETIME NULL,
        eta DATETIME NULL,
        failure_reason VARCHAR(255) NULL,
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_deliveries_order (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS delivery_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        delivery_id INT NOT NULL,
        old_status VARCHAR(50) NULL,
        new_status VARCHAR(50) NOT NULL,
        changed_by INT NULL,
        remarks VARCHAR(255) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $existingColumns = [];
    foreach ($conn->query("SHOW COLUMNS FROM deliveries")->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $existingColumns[$column['Field']] = true;
    }

    $alterations = [];
    if (!isset($existingColumns['delivery_person'])) {
        $alterations[] = "ADD COLUMN delivery_person INT NULL";
    }
    if (isset($existingColumns['delivery_person'])) {
        $alterations[] = "MODIFY COLUMN delivery_person INT NULL";
    }
    if (!isset($existingColumns['picked_up_at'])) {
        $alterations[] = "ADD COLUMN picked_up_at DATETIME NULL";
    }
    if (!isset($existingColumns['out_for_delivery_at'])) {
        $alterations[] = "ADD COLUMN out_for_delivery_at DATETIME NULL";
    }
    if (!isset($existingColumns['eta'])) {
        $alterations[] = "ADD COLUMN eta DATETIME NULL";
    }
    if (!isset($existingColumns['failure_reason'])) {
        $alterations[] = "ADD COLUMN failure_reason VARCHAR(255) NULL";
    }
    if (!isset($existingColumns['notes'])) {
        $alterations[] = "ADD COLUMN notes TEXT NULL";
    }
    if (!isset($existingColumns['created_at'])) {
        $alterations[] = "ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP";
    }
    if (!isset($existingColumns['updated_at'])) {
        $alterations[] = "ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }

    // Map legacy dispatched_at if present; keep it as the assignment timestamp.
    if (!isset($existingColumns['dispatched_at'])) {
        $alterations[] = "ADD COLUMN dispatched_at DATETIME NULL";
    }

    if (!empty($alterations)) {
        $conn->exec("ALTER TABLE deliveries " . implode(", ", $alterations));
    }

    $col = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'")->fetch(PDO::FETCH_ASSOC);
    if (!$col) {
        $conn->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
    }

    $GLOBALS['delivery_tables_ensured'] = true;
}

function ensureDeliveryRowForOrder($conn, $orderId, $changedBy, $remarks)
{
    ensureDeliveryTables($conn);

    $stmt = $conn->prepare("SELECT id, delivery_status, delivery_person FROM deliveries WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $orderId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return $row;
    }

    $insert = $conn->prepare("INSERT INTO deliveries (order_id, delivery_status, created_at, updated_at) VALUES (:order_id, 'pending_dispatch', NOW(), NOW())");
    $insert->execute([':order_id' => $orderId]);
    $deliveryId = (int) $conn->lastInsertId();

    $history = $conn->prepare("INSERT INTO delivery_status_history (delivery_id, old_status, new_status, changed_by, remarks, created_at)
                               VALUES (:delivery_id, '', 'pending_dispatch', :changed_by, :remarks, NOW())");
    $history->execute([
        ':delivery_id' => $deliveryId,
        ':changed_by' => $changedBy,
        ':remarks' => $remarks,
    ]);

    return ['id' => $deliveryId, 'delivery_status' => 'pending_dispatch', 'delivery_person' => null];
}

function isAdminUser()
{
    return (($_SESSION['user_data']['role'] ?? '') === 'admin');
}

// Get riders (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_riders') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || !isAdminUser()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, is_active, created_at FROM users WHERE role = 'rider' ORDER BY first_name, last_name");
        $stmt->execute();
        $riders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'riders' => $riders]);
            exit;
        }
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching riders.']);
            exit;
        }
    }
}

// Add rider (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'add_rider') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || !isAdminUser()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'First name, last name, email, and password are required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid rider data.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $check = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Email is already in use.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, role, is_active, created_at)
                                VALUES (:first_name, :last_name, :email, :password, :phone, 'rider', 1, NOW())");
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password' => $hash,
            ':phone' => $phone,
        ]);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rider created successfully.', 'rider_id' => $conn->lastInsertId()]);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Rider created successfully.'];
        header('Location: ../gb/deliveries.php');
        exit;
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while creating the rider.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while creating the rider.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }
}

// Update rider (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_rider') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || !isAdminUser()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    $riderId = isset($_POST['rider_id']) ? (int) $_POST['rider_id'] : 0;
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$riderId || $firstName === '' || $lastName === '' || $email === '') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Rider, first name, last name, and email are required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid rider data.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $stmt = $conn->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE id = :id AND role = 'rider'");
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':phone' => $phone,
            ':id' => $riderId,
        ]);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rider updated successfully.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Rider updated successfully.'];
        header('Location: ../gb/deliveries.php');
        exit;
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the rider.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the rider.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }
}

// Toggle rider active state (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'toggle_rider') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || !isAdminUser()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    $riderId = isset($_POST['rider_id']) ? (int) $_POST['rider_id'] : 0;
    $isActive = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 0;

    if (!$riderId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Rider ID is required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid rider.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $stmt = $conn->prepare("UPDATE users SET is_active = :is_active WHERE id = :id AND role = 'rider'");
        $stmt->execute([':is_active' => $isActive ? 1 : 0, ':id' => $riderId]);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rider status updated.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Rider status updated.'];
        header('Location: ../gb/deliveries.php');
        exit;
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the rider.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the rider.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }
}

// Get deliveries (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_deliveries') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || !isAdminUser()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $sql = "SELECT d.id, d.order_id, d.delivery_person, d.delivery_status, d.dispatched_at, d.picked_up_at,
                       d.out_for_delivery_at, d.delivered_at, d.eta, d.failure_reason, d.notes, d.created_at,
                       o.order_number, o.order_type, o.order_status, o.total_amount, o.address_id,
                        u.first_name AS customer_first_name, u.last_name AS customer_last_name, a.contact_number AS customer_phone,
                       r.first_name AS rider_first_name, r.last_name AS rider_last_name, r.phone AS rider_phone,
                       a.address AS delivery_address
                FROM deliveries d
                JOIN orders o ON o.id = d.order_id
                JOIN users u ON u.id = o.user_id
                LEFT JOIN users r ON r.id = d.delivery_person
                LEFT JOIN addresses a ON a.id = o.address_id
                ORDER BY d.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'deliveries' => $deliveries]);
            exit;
        }
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching deliveries.']);
            exit;
        }
    }
}

// Get delivery details (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'get_delivery') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || (!isAdminUser() && (($_SESSION['user_data']['role'] ?? '') !== 'rider'))) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    $deliveryId = isset($_POST['delivery_id']) ? (int) $_POST['delivery_id'] : 0;
    if (!$deliveryId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Delivery ID is required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid delivery.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $sql = "SELECT d.*, o.order_number, o.order_type, o.order_status, o.total_amount, o.subtotal, o.delivery_fee,
                        u.first_name AS customer_first_name, u.last_name AS customer_last_name, a.contact_number AS customer_phone,
                       r.first_name AS rider_first_name, r.last_name AS rider_last_name, r.phone AS rider_phone,
                       a.address AS delivery_address
                FROM deliveries d
                JOIN orders o ON o.id = d.order_id
                JOIN users u ON u.id = o.user_id
                LEFT JOIN users r ON r.id = d.delivery_person
                LEFT JOIN addresses a ON a.id = o.address_id
                WHERE d.id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $deliveryId]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$delivery) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Delivery not found.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Delivery not found.'];
            header('Location: ../gb/my_deliveries.php');
            exit;
        }

        $userRole = $_SESSION['user_data']['role'] ?? '';
        if ($userRole === 'rider' && (int) ($delivery['delivery_person'] ?? 0) !== (int) $userId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'You can only view your assigned deliveries.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Access denied.'];
            header('Location: ../gb/my_deliveries.php');
            exit;
        }

        $itemsStmt = $conn->prepare("SELECT item_name, quantity, unit_price, subtotal FROM order_items WHERE order_id = :order_id ORDER BY id");
        $itemsStmt->execute([':order_id' => $delivery['order_id']]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'delivery' => $delivery, 'items' => $items]);
            exit;
        }
    } catch (\PDOException $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while fetching delivery details.']);
            exit;
        }
    }
}

// Assign rider to delivery (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'assign_delivery') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || !isAdminUser()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $riderId = isset($_POST['rider_id']) ? (int) $_POST['rider_id'] : 0;
    $eta = trim($_POST['eta'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$orderId || !$riderId) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order and rider are required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid assignment data.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $conn->beginTransaction();

        $orderStmt = $conn->prepare("SELECT id, order_type, order_status FROM orders WHERE id = :id");
        $orderStmt->execute([':id' => $orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || (int) $order['order_type'] !== 1) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Order is not a delivery order.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order is not a delivery order.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        $allowedOrderStatuses = ['Paid', 'Preparing', 'Out for Delivery'];
        if (!in_array($order['order_status'] ?? '', $allowedOrderStatuses, true)) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Order is not ready for delivery assignment.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Order is not ready for delivery assignment.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        $riderStmt = $conn->prepare("SELECT id FROM users WHERE id = :id AND role = 'rider' AND is_active = 1");
        $riderStmt->execute([':id' => $riderId]);
        if (!$riderStmt->fetch()) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Rider is not available.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Rider is not available.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        $existingStmt = $conn->prepare("SELECT id, delivery_status FROM deliveries WHERE order_id = :order_id");
        $existingStmt->execute([':order_id' => $orderId]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $oldStatus = $existing['delivery_status'];
            $update = $conn->prepare("UPDATE deliveries
                                      SET delivery_person = :rider_id,
                                          delivery_status = 'assigned',
                                          dispatched_at = NOW(),
                                          eta = :eta,
                                          notes = :notes,
                                          updated_at = NOW()
                                      WHERE id = :id");
            $update->execute([
                ':rider_id' => $riderId,
                ':eta' => $eta !== '' ? $eta : null,
                ':notes' => $notes,
                ':id' => $existing['id'],
            ]);
            $deliveryId = (int) $existing['id'];
        } else {
            $oldStatus = '';
            $insert = $conn->prepare("INSERT INTO deliveries (order_id, delivery_person, delivery_status, dispatched_at, eta, notes, created_at, updated_at)
                                      VALUES (:order_id, :rider_id, 'assigned', NOW(), :eta, :notes, NOW(), NOW())");
            $insert->execute([
                ':order_id' => $orderId,
                ':rider_id' => $riderId,
                ':eta' => $eta !== '' ? $eta : null,
                ':notes' => $notes,
            ]);
            $deliveryId = (int) $conn->lastInsertId();
        }

        $hist = $conn->prepare("INSERT INTO delivery_status_history (delivery_id, old_status, new_status, changed_by, remarks, created_at)
                                VALUES (:delivery_id, :old_status, 'assigned', :changed_by, :remarks, NOW())");
        $hist->execute([
            ':delivery_id' => $deliveryId,
            ':old_status' => $oldStatus,
            ':changed_by' => $userId,
            ':remarks' => $notes !== '' ? $notes : 'Rider assigned',
        ]);

        if ($order['order_status'] !== 'Out for Delivery') {
            $updOrder = $conn->prepare("UPDATE orders SET order_status = 'Assigned' WHERE id = :id");
            $updOrder->execute([':id' => $orderId]);

            $orderHist = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, remarks, created_at)
                                         VALUES (:order_id, :old_status, 'Assigned', :changed_by, :remarks, NOW())");
            $orderHist->execute([
                ':order_id' => $orderId,
                ':old_status' => $order['order_status'],
                ':changed_by' => $userId,
                ':remarks' => $notes !== '' ? $notes : 'Rider assigned',
            ]);
        }

        $conn->commit();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rider assigned successfully.', 'delivery_id' => $deliveryId]);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Rider assigned successfully.'];
        header('Location: ../gb/deliveries.php');
        exit;
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) { $conn->rollBack(); }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while assigning the rider.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while assigning the rider.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }
}

// Update delivery status (admin)
if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_delivery_status') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $userId = getCurrentUserId();

    if (!$userId || (!isAdminUser() && (($_SESSION['user_data']['role'] ?? '') !== 'rider'))) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Admin access required.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    $deliveryId = isset($_POST['delivery_id']) ? (int) $_POST['delivery_id'] : 0;
    $newStatus = trim($_POST['delivery_status'] ?? '');
    $failureReason = trim($_POST['failure_reason'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $allowedStatuses = ['pending_dispatch', 'assigned', 'picked_up', 'out_for_delivery', 'delivered', 'cancelled'];
    $transitions = [
        'pending_dispatch' => ['assigned', 'cancelled'],
        'assigned' => ['picked_up', 'cancelled'],
        'picked_up' => ['out_for_delivery', 'cancelled'],
        'out_for_delivery' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];

    if (!$deliveryId || !in_array($newStatus, $allowedStatuses, true)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid delivery status data.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid delivery status data.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }

    try {
        ensureDeliveryTables($conn);
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT d.id, d.order_id, d.delivery_person, d.delivery_status, o.order_status FROM deliveries d JOIN orders o ON o.id = d.order_id WHERE d.id = :id");
        $stmt->execute([':id' => $deliveryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Delivery not found.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Delivery not found.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        $userRole = $_SESSION['user_data']['role'] ?? '';
        if ($userRole === 'rider' && (int) ($row['delivery_person'] ?? 0) !== (int) $userId) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'You can only update your assigned deliveries.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Access denied.'];
            header('Location: ../gb/my_deliveries.php');
            exit;
        }

        $oldStatus = $row['delivery_status'];
        if ($oldStatus === $newStatus) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Delivery status unchanged.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Delivery status unchanged.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        if (!in_array($newStatus, $transitions[$oldStatus] ?? [], true)) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid delivery status transition.']);
                exit;
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid delivery status transition.'];
            header('Location: ../gb/deliveries.php');
            exit;
        }

        $timestampField = null;
        if ($newStatus === 'picked_up') {
            $timestampField = 'picked_up_at';
        } elseif ($newStatus === 'out_for_delivery') {
            $timestampField = 'out_for_delivery_at';
        } elseif ($newStatus === 'delivered') {
            $timestampField = 'delivered_at';
        }

        $sql = "UPDATE deliveries SET delivery_status = :status, failure_reason = :failure_reason, notes = COALESCE(NULLIF(:notes, ''), notes), updated_at = NOW()";
        if ($timestampField) {
            $sql .= ", {$timestampField} = NOW()";
        }
        $sql .= " WHERE id = :id";
        $update = $conn->prepare($sql);
        $update->execute([
            ':status' => $newStatus,
            ':failure_reason' => $newStatus === 'cancelled' ? ($failureReason !== '' ? $failureReason : 'Cancelled') : $failureReason,
            ':notes' => $notes,
            ':id' => $deliveryId,
        ]);

        $hist = $conn->prepare("INSERT INTO delivery_status_history (delivery_id, old_status, new_status, changed_by, remarks, created_at)
                                VALUES (:delivery_id, :old_status, :new_status, :changed_by, :remarks, NOW())");
        $hist->execute([
            ':delivery_id' => $deliveryId,
            ':old_status' => $oldStatus,
            ':new_status' => $newStatus,
            ':changed_by' => $userId,
            ':remarks' => $notes !== '' ? $notes : $failureReason,
        ]);

        $orderStatus = $row['order_status'];
        $targetOrderStatus = null;
        if ($newStatus === 'assigned') {
            $targetOrderStatus = 'Assigned';
        } elseif ($newStatus === 'picked_up') {
            $targetOrderStatus = 'Picked Up';
        } elseif ($newStatus === 'out_for_delivery') {
            $targetOrderStatus = 'Out for Delivery';
        } elseif ($newStatus === 'delivered') {
            $targetOrderStatus = 'Delivered';
        } elseif ($newStatus === 'cancelled') {
            $targetOrderStatus = 'Cancelled';
        }

        if ($targetOrderStatus && $orderStatus !== $targetOrderStatus) {
            $updOrder = $conn->prepare("UPDATE orders SET order_status = :status WHERE id = :id");
            $updOrder->execute([':status' => $targetOrderStatus, ':id' => $row['order_id']]);

            $orderHist = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, remarks, created_at)
                                         VALUES (:order_id, :old_status, :new_status, :changed_by, :remarks, NOW())");
            $orderHist->execute([
                ':order_id' => $row['order_id'],
                ':old_status' => $orderStatus,
                ':new_status' => $targetOrderStatus,
                ':changed_by' => $userId,
                ':remarks' => $notes !== '' ? $notes : 'Delivery status updated',
            ]);
        }

        $conn->commit();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Delivery status updated successfully.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Delivery status updated successfully.'];
        header('Location: ../gb/deliveries.php');
        exit;
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) { $conn->rollBack(); }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the delivery status.']);
            exit;
        }
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Something went wrong while updating the delivery status.'];
        header('Location: ../gb/deliveries.php');
        exit;
    }
}