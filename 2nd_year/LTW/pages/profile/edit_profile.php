<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (
    !isset($_SESSION['user_id']) ||
    (!isset($_SESSION['is_client']) && !isset($_SESSION['is_freelancer']) && !isset($_SESSION['is_admin']))
) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM User WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("Erro: Utilizador não encontrado.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token CSRF inválido.";
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $name = trim($_POST['name']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $profile_picture = $user['profile_picture'];

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../../assets/img/uploads/";
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    if ($profile_picture && !str_contains($profile_picture, 'default-profile.png')) {
                        @unlink($profile_picture);
                    }
                    $profile_picture = $target_file;
                }
            } else {
                $error = "O ficheiro enviado não é uma imagem válida.";
            }
        }

        if (empty($error)) {
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    $error = "As passwords não coincidem.";
                } elseif (strlen($password) < 8) {
                    $error = "A password deve ter pelo menos 8 caracteres.";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE User SET username = ?, email = ?, name = ?, password_hash = ?, profile_picture = ? WHERE id = ?");
                    $updated = $stmt->execute([$username, $email, $name, $password_hash, $profile_picture, $user_id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE User SET username = ?, email = ?, name = ?, profile_picture = ? WHERE id = ?");
                $updated = $stmt->execute([$username, $email, $name, $profile_picture, $user_id]);
            }

            if (empty($error)) {
                if ($updated) {
                    $success = "Perfil atualizado com sucesso!";
                    $_SESSION['username'] = $username;
                    $_SESSION['profile_picture'] = $profile_picture;
                } else {
                    $error = "Erro ao atualizar o perfil. Por favor, tente novamente.";
                }
            }
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

.edit-profile-page {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.profile-container {
  max-width: 600px;
  margin: 0 auto;
  background-color: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.profile-header {
  background-color: var(--primary-color);
  color: white;
  padding: 1.5rem;
  text-align: center;
}

.profile-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.profile-form {
  padding: 1.5rem;
  display: grid;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
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

.avatar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.avatar-preview {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.password-note {
  font-size: 0.75rem;
  color: var(--text-light);
  margin-top: -0.5rem;
}

@media (max-width: 640px) {
  .profile-container {
    border-radius: 0;
  }
  
  .profile-header {
    padding: 1.25rem;
  }
  
  .profile-form {
    padding: 1.25rem;
  }
}
</style>

<main class="edit-profile-page">
  <div class="profile-container">
    <div class="profile-header">
      <h2>Editar Perfil</h2>
    </div>
    
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="profile-form">
      <div class="avatar-container">
        <img src="<?= htmlspecialchars($user['profile_picture'] ?? '../../assets/img/default-profile.png') ?>" 
             alt="Foto de perfil" 
             class="avatar-preview" 
             id="avatar-preview">
        <input type="file" name="profile_picture" id="profile-picture" accept="image/*" class="form-control">
      </div>
      
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control" 
               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control" 
               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" class="form-control" 
               value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="password">Nova Password</label>
        <input type="password" id="password" name="password" class="form-control">
        <p class="password-note">Deixe em branco para manter a password atual</p>
      </div>
      
      <div class="form-group">
        <label for="confirm_password">Confirmar Nova Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
      </div>
      
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      
      <button type="submit" class="btn btn-primary">Guardar Alterações</button>
    </form>
  </div>
</main>

<script>
document.getElementById('profile-picture').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(event) {
      document.getElementById('avatar-preview').src = event.target.result;
    };
    reader.readAsDataURL(file);
  }
});

document.querySelector('form').addEventListener('submit', function(e) {
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirm_password').value;
  
  if (password && password !== confirmPassword) {
    e.preventDefault();
    alert('As passwords não coincidem!');
  }
  
  if (password && password.length < 8) {
    e.preventDefault();
    alert('A password deve ter pelo menos 8 caracteres!');
  }
});
</script>

<?php include('../../includes/footer.php'); ?>