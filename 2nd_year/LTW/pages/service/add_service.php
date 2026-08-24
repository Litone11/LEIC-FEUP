<?php
require_once '../../database/db.php';
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = (int) $_POST['category'];
    $delivery_time = intval($_POST['delivery_time']);
    $user_id = $_SESSION['user_id'];

    if (empty($title)) $errors[] = "Título é obrigatório";
    if (empty($description)) $errors[] = "Descrição é obrigatória";
    if ($price <= 0) $errors[] = "Preço deve ser maior que zero";
    if ($delivery_time <= 0) $errors[] = "Prazo de entrega inválido";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO Service (user_id, title, description, price, category_id, delivery_time, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'active')");
        if ($stmt->execute([$user_id, $title, $description, $price, $category, $delivery_time])) {
            $success = "Serviço criado com sucesso!";
            $service_id = $pdo->lastInsertId();
            
            if (!empty($_FILES['service_images']['name'][0])) {
                $image_dir = '../../assets/img/service_images/' . $service_id . '/';
                if (!file_exists($image_dir)) mkdir($image_dir, 0777, true);
                
                foreach ($_FILES['service_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['service_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $filename = uniqid() . '_' . basename($_FILES['service_images']['name'][$key]);
                        move_uploaded_file($tmp_name, $image_dir . $filename);
                    }
                }
            }
        } else {
            $errors[] = "Erro ao criar serviço";
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<style>
.add-service {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.add-service h2 {
    text-align: center;
    color: #1565C0;
    margin-bottom: 25px;
}

.add-service form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.add-service label {
    font-weight: 600;
    color: #4b5563;
}

.add-service input, 
.add-service select {
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 16px;
}

.add-service button {
    background: #1E88E5;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
}

.add-service button:hover {
    background: #1565C0;
}

.error {
    color: #ef4444;
    padding: 10px;
    background: #fee2e2;
    border-radius: 6px;
    margin-bottom: 15px;
}

.success {
    color: #059669;
    padding: 10px;
    background: #d1fae5;
    border-radius: 6px;
    margin-bottom: 15px;
}

.category-row {
    display: flex;
    gap: 10px;
}

.category-row a {
    padding: 10px;
    background: #1565C0;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
}
</style>

<main class="add-service">
    <h2>Adicionar Novo Serviço</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Título</label>
        <input type="text" name="title" required>
        
        <label>Descrição</label>
        <input type="text" name="description" required>
        
        <label>Preço (€)</label>
        <input type="number" name="price" step="0.01" min="0.01" required>
        
        <label>Categoria</label>
        <div class="category-row">
            <select name="category" required>
                <?php 
                $categories = $pdo->query("SELECT id, name FROM Category")->fetchAll();
                foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <a href="../category/request_category.php">+ Categoria</a>
        </div>
        
        <label>Prazo de Entrega (dias)</label>
        <input type="number" name="delivery_time" min="1" required>
        
        <label>Imagens (opcional)</label>
        <input type="file" name="service_images[]" multiple accept="image/*">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <button type="submit">Publicar Serviço</button>
    </form>
</main>

<?php include '../../includes/footer.php'; ?>