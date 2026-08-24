<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if ($pdo->query("SELECT COUNT(*) FROM User WHERE is_admin = 1")->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO User (name, username, email, password_hash, is_admin, is_client, is_freelancer) 
                          VALUES (?, ?, ?, ?, 1, 1, 1)");
    $stmt->execute(['Administrador', 'admin', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT)]);
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include('../../includes/header.php');
?>

<style>
.admin-panel {
    max-width: 1000px;
    margin: 30px auto;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.admin-panel h2 {
    text-align: center;
    color: #1565C0;
    margin-bottom: 30px;
}

.section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.section h3 {
    color: #1565C0;
    margin-bottom: 15px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: #f9f5ff;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.stat-card h4 {
    margin: 0 0 5px;
    color: #1565C0;
    font-size: 16px;
}

.stat-card p {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input, 
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.btn {
    background: #1E88E5;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.btn:hover {
    background: #1565C0;
}

.btn-danger {
    background: #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
}

.service-list {
    list-style: none;
    padding: 0;
}

.service-item {
    background: #f9f5ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.service-info {
    flex: 1;
}

.service-actions {
    margin-left: 15px;
}

.role-toggle {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 20px 0;
}

.role-toggle label {
    background: #f3e8ff;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
}
</style>

<main class="admin-panel">
    <h2>Painel de Administração</h2>

    <form method="POST" action="../auth/toggle_roles.php" class="section">
        <h3>Função Atual</h3>
        <div class="role-toggle">
            <label>
                <input type="radio" name="role" value="client" <?= !empty($_SESSION['is_client']) ? 'checked' : '' ?>> 
                Cliente
            </label>
            <label>
                <input type="radio" name="role" value="freelancer" <?= !empty($_SESSION['is_freelancer']) ? 'checked' : '' ?>> 
                Freelancer
            </label>
        </div>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <button type="submit" class="btn">Atualizar Função</button>
    </form>

    <div class="section">
        <h3>Estatísticas do Sistema</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Serviços Publicados</h4>
                <p><?= $pdo->query("SELECT COUNT(*) FROM Service")->fetchColumn() ?></p>
            </div>
            <div class="stat-card">
                <h4>Clientes</h4>
                <p><?= $pdo->query("SELECT COUNT(*) FROM User WHERE is_client = 1")->fetchColumn() ?></p>
            </div>
            <div class="stat-card">
                <h4>Freelancers</h4>
                <p><?= $pdo->query("SELECT COUNT(*) FROM User WHERE is_freelancer = 1")->fetchColumn() ?></p>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Elevar a Administrador</h3>
        <form action="../auth/make_admin.php" method="POST">
            <div class="form-group">
                <label for="username">Nome de utilizador:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn">Tornar Admin</button>
        </form>
        <div style="margin-top: 20px;">
            <h4>Administradores Atuais</h4>
            <ul class="service-list">
                <?php foreach ($pdo->query("SELECT username FROM User WHERE is_admin = 1 ORDER BY username") as $admin): ?>
                    <li class="service-item">
                        <div class="service-info">
                            <strong><?= htmlspecialchars($admin['username']) ?></strong>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="section">
        <h3 style="display: flex; justify-content: space-between;">
            <span>Categorias</span>
            <a href="../category/category_requests.php" class="btn">Ver Pedidos</a>
        </h3>
        <form action="../category/add_category.php" method="POST">
            <div class="form-group">
                <label for="category">Nova categoria:</label>
                <input type="text" name="category" id="category" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn">Adicionar</button>
        </form>
        <ul class="service-list">
            <?php foreach ($pdo->query("SELECT id, name FROM Category ORDER BY name") as $category): ?>
                <li class="service-item">
                    <div class="service-info">
                        <strong><?= htmlspecialchars($category['name']) ?></strong>
                    </div>
                    <div class="service-actions">
                        <form action="../category/delete_category.php" method="POST">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="section">
        <h3>Gerir Serviços</h3>
        <ul class="service-list">
            <?php foreach ($pdo->query("SELECT s.id, s.title, s.price, u.username FROM Service s JOIN User u ON s.user_id = u.id ORDER BY s.created_at DESC") as $s): ?>
                <li class="service-item">
                    <div class="service-info">
                        <strong><?= htmlspecialchars($s['title']) ?></strong>
                        <div>
                            <small>Freelancer: <?= htmlspecialchars($s['username']) ?></small> | 
                            <small>Preço: €<?= number_format($s['price'], 2) ?></small>
                        </div>
                    </div>
                    <div class="service-actions">
                        <form action="../service/delete_service.php" method="POST">
                            <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

<?php include('../../includes/footer.php'); ?>