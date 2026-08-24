<?php // services.php ?>
<?php include('includes/header.php'); ?>
require_once '../database/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
<main>
  <h2>Available Services</h2>
  <?php if (!empty($_SESSION['is_freelancer'])): ?>
    <div style="text-align: right; margin: 1em 0;">
      <a href="add_service.php" class="btn-primary">+ Adicionar Serviço</a>
    </div>
  <?php endif; ?>
  <div class="service-list">
    <?php
    if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
        echo "<p class='error'>Apenas freelancers autenticados podem ver os seus serviços.</p>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Service WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($services) > 0) {
            foreach ($services as $service) {
                echo "<div class='service-card'>";
                echo "<h3>" . htmlspecialchars($service['title']) . "</h3>";
                echo "<p>A partir de €" . htmlspecialchars($service['price']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Não tens serviços publicados ainda.</p>";
        }
    }
    ?>
  </div>
</main>
<?php include('includes/footer.php'); ?>
