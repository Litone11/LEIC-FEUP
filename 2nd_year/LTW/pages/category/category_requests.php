<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';
include('../../includes/header.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!empty($_SESSION['is_admin'])):
  $requests = $pdo->query("SELECT r.id, r.name, u.username FROM CategoryRequest r JOIN User u ON r.user_id = u.id WHERE r.status = 'pending'")->fetchAll();
?>
<?php endif; ?>


<main style="max-width: 800px; margin: 40px auto; font-family: Arial, sans-serif;">
  <h2>Pedidos de Novas Categorias</h2>

  <?php if (!empty($_SESSION['is_admin'])): ?>
    <?php if (count($requests) > 0): ?>
      <ul style="list-style: none; padding-left: 0;">
        <?php foreach ($requests as $req): ?>
          <li style="background: #f9f9f9; margin-bottom: 12px; padding: 12px 16px; border-radius: 6px;">
            <p style="margin: 0;"><strong><?= htmlspecialchars($req['name']) ?></strong> solicitado por <?= htmlspecialchars($req['username']) ?></p>
            <div style="margin-top: 10px;">
              <form action="process_category_request.php" method="POST" style="display: inline;">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <input type="hidden" name="action" value="accept">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" style="padding: 6px 12px; background: #4CAF50; color: white; border: none; border-radius: 6px;">Aceitar</button>
              </form>
              <form action="process_category_request.php" method="POST" style="display: inline; margin-left: 10px;">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" style="padding: 6px 12px; background: #b00020; color: white; border: none; border-radius: 6px;">Rejeitar</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Não há pedidos pendentes.</p>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php include('../../includes/footer.php'); ?>