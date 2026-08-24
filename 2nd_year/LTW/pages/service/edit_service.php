<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: ../home/freelancer_homepage.php');
    exit();
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare('SELECT id, title, description, price, category_id FROM Service WHERE id = :id AND user_id = :user_id');
$stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
$service = $stmt->fetch();

if (!$service) {
    echo "Serviço não encontrado ou não autorizado.";
    exit();
}

$categories = $pdo->query("SELECT id, name FROM Category")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token CSRF inválido.";
    } else {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $category_id = (int) $_POST['category'];

        if (!empty($title) && !empty($description) && is_numeric($price)) {
            $update = $pdo->prepare('UPDATE Service SET title = :title, description = :description, price = :price, category_id = :category_id WHERE id = :id AND user_id = :user_id');
            $update->execute([
                ':title' => $title,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $category_id,
                ':id' => $id,
                ':user_id' => $_SESSION['user_id']
            ]);
            
            $service_id = $id;
            $image_dir = '../../assets/img/service_images/' . $service_id . '/';
            if (!file_exists($image_dir)) {
                mkdir($image_dir, 0777, true);
            }

            if (!empty($_FILES['service_images']['name'][0])) {
                foreach ($_FILES['service_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['service_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_ext = pathinfo($_FILES['service_images']['name'][$key], PATHINFO_EXTENSION);
                        $filename = uniqid('img_', true) . '.' . $file_ext;
                        $target_path = $image_dir . $filename;
                        $check = getimagesize($tmp_name);
                        if ($check !== false) {
                            move_uploaded_file($tmp_name, $target_path);
                        }
                    }
                }
            }

            if (!empty($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $imgName) {
                    $imgPath = $image_dir . basename($imgName);
                    if (file_exists($imgPath)) unlink($imgPath);
                }
            }

            $_SESSION['success_message'] = "Serviço atualizado com sucesso!";
            header('Location: ../home/freelancer_homepage.php');
            exit();
        } else {
            $error = "Por favor, preencha todos os campos obrigatórios corretamente.";
        }
    }
}
?>

<?php include('../../includes/header.php'); ?>

<style>
:root {
  --primary-color: #6a1b9a;
  --primary-hover: #7c43bd;
  --secondary-color: #f8f9fa;
  --text-color: #2d3748;
  --text-light: #718096;
  --border-color: #e2e8f0;
  --error-bg: #fff5f5;
  --error-text: #e53e3e;
  --success-bg: #f0fff4;
  --success-text: #38a169;
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease;
}

.edit-service-page {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.service-container {
  max-width: 800px;
  margin: 0 auto;
  background-color: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.service-header {
  background-color: var(--primary-color);
  color: white;
  padding: 1.5rem;
  text-align: center;
}

.service-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.service-form {
  padding: 1.5rem;
  display: grid;
  gap: 1.25rem;
}

.form-group {
  display: grid;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
  font-size: 0.875rem;
}

.form-control {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  font-size: 1rem;
  transition: var(--transition);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(106, 27, 154, 0.1);
}

textarea.form-control {
  min-height: 120px;
  resize: vertical;
}

select.form-control {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23718096' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 16px 12px;
}

.btn {
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  border: none;
  font-size: 1rem;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.alert-error {
  background-color: var(--error-bg);
  color: var(--error-text);
  border: 1px solid rgba(229, 62, 62, 0.2);
}

.alert-success {
  background-color: var(--success-bg);
  color: var(--success-text);
  border: 1px solid rgba(56, 161, 105, 0.2);
}

.image-upload {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.image-preview-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.image-preview-item {
  position: relative;
  border-radius: var(--radius-sm);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.image-preview-item img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  display: block;
}

.image-preview-actions {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  padding: 0.5rem;
  display: flex;
  justify-content: center;
}

.image-preview-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  font-size: 0.875rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 1.5rem 0 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--border-color);
}

@media (max-width: 640px) {
  .service-container {
    border-radius: 0;
  }
  
  .service-header {
    padding: 1.25rem;
  }
  
  .service-form {
    padding: 1.25rem;
  }
  
  .image-preview-container {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>

<main class="edit-service-page">
  <div class="service-container">
    <div class="service-header">
      <h2>Editar Serviço</h2>
    </div>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="service-form">
      <div class="form-group">
        <label for="title">Título do Serviço</label>
        <input type="text" id="title" name="title" class="form-control" 
               value="<?= htmlspecialchars($service['title']) ?>" required>
      </div>
      
      <div class="form-group">
        <label for="description">Descrição Detalhada</label>
        <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($service['description']) ?></textarea>
      </div>
      
      <div class="form-group">
        <label for="price">Preço (€)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" 
               class="form-control" value="<?= htmlspecialchars($service['price']) ?>" required>
      </div>
      
      <div class="form-group">
        <label for="category">Categoria</label>
        <select id="category" name="category" class="form-control" required>
          <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>" <?= $category['id'] == $service['category_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($category['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <h3 class="section-title">Imagens do Serviço</h3>
      
      <div class="image-upload">
        <div class="form-group">
          <label for="service_images">Adicionar Novas Imagens</label>
          <input type="file" id="service_images" name="service_images[]" multiple class="form-control">
        </div>
        
        <?php
        $imageDir = '../../assets/img/service_images/' . $service['id'] . '/';
        $images = file_exists($imageDir) ? glob($imageDir . '*') : [];
        ?>
        
        <?php if (!empty($images)): ?>
          <div class="image-preview-container" id="image-preview">
            <?php foreach ($images as $img): ?>
              <div class="image-preview-item">
                <img src="<?= htmlspecialchars($img) ?>" alt="Imagem do serviço">
                <div class="image-preview-actions">
                  <label class="image-preview-checkbox">
                    <input type="checkbox" name="delete_images[]" value="<?= basename($img) ?>">
                    Eliminar
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <button type="submit" class="btn btn-primary">Guardar Alterações</button>
    </form>
  </div>
</main>

<script>
document.getElementById('service_images').addEventListener('change', function(e) {
  const files = e.target.files;
  const previewContainer = document.getElementById('image-preview') || document.createElement('div');
  previewContainer.className = 'image-preview-container';
  
  if (!document.getElementById('image-preview')) {
    document.querySelector('.image-upload').appendChild(previewContainer);
    previewContainer.id = 'image-preview';
  }
  
  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (file.type.match('image.*')) {
      const reader = new FileReader();
      
      reader.onload = function(event) {
        const previewItem = document.createElement('div');
        previewItem.className = 'image-preview-item';
        previewItem.innerHTML = `
          <img src="${event.target.result}" alt="Pré-visualização">
          <div class="image-preview-actions">
            <span style="color: white; font-size: 0.875rem;">Nova imagem</span>
          </div>
        `;
        previewContainer.appendChild(previewItem);
      };
      
      reader.readAsDataURL(file);
    }
  }
});
</script>

<?php include('../../includes/footer.php'); ?>