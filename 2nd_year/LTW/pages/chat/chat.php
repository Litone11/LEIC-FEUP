<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$freelancer_id = isset($_GET['freelancer_id']) ? (int)$_GET['freelancer_id'] : null;
$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;

$sender_id = $_SESSION['user_id'];
if (!empty($_SESSION['is_client'])) {
    $receiver_id = $freelancer_id;
} elseif (!empty($_SESSION['is_freelancer'])) {
    $receiver_id = $client_id;
} else {
    echo "Utilizador não autorizado.";
    exit();
}

if (!$receiver_id) {
    echo "ID do destinatário em falta.";
    exit();
}

$stmtUser = $pdo->prepare("SELECT username FROM User WHERE id = ?");
$stmtUser->execute([$receiver_id]);
$receiver = $stmtUser->fetch();
$receiver_name = $receiver ? $receiver['username'] : 'Utilizador';
?>

<?php include('../../includes/header.php'); ?>
<style>
  :root {
    --primary-color: #6a1b9a;
    --primary-hover: #7c43bd;
    --background-color: #f8f9fa;
    --chat-background: #ffffff;
    --border-color: #e1e1e1;
    --text-color: #333333;
    --text-light: #666666;
    --message-sent: #e3f2fd;
    --message-received: #f1f1f1;
  }

  main.chat-page {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    background-color: var(--background-color);
    min-height: calc(100vh - 120px);
  }

  .chat-container {
    background-color: var(--chat-background);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    max-width: 800px;
    width: 100%;
    display: flex;
    flex-direction: column;
    height: 70vh;
    max-height: 700px;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .chat-header {
    padding: 18px 24px;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .chat-header h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 500;
    letter-spacing: 0.5px;
  }

  .chat-body {
    flex-grow: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: var(--chat-background);
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .chat-form {
    display: flex;
    padding: 16px;
    gap: 12px;
    background-color: var(--chat-background);
    border-top: 1px solid var(--border-color);
  }

  .chat-form textarea {
    flex-grow: 1;
    resize: none;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 1rem;
    border: 1px solid var(--border-color);
    min-height: 50px;
    max-height: 120px;
    transition: border 0.3s, box-shadow 0.3s;
    outline: none;
  }

  .chat-form textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(106, 27, 154, 0.2);
  }

  .chat-form button {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    font-weight: 500;
    align-self: flex-end;
  }

  .chat-form button:hover {
    background-color: var(--primary-hover);
  }

  .chat-form button:active {
    transform: scale(0.98);
  }

  @media (max-width: 768px) {
    main.chat-page {
      padding: 10px;
      min-height: calc(100vh - 80px);
    }
    
    .chat-container {
      height: 85vh;
      border-radius: 0;
    }
    
    .chat-header {
      padding: 14px 16px;
    }
    
    .chat-body {
      padding: 12px;
    }
    
    .chat-form {
      padding: 12px;
      gap: 8px;
    }
  }
</style>
<link rel="stylesheet" href="../../assets/css/chat.css">
<main class="chat-page">
  <div class="chat-container">
    <div class="chat-header">
      <h3><?= htmlentities($receiver_name) ?></h3>
    </div>
    <div class="chat-body" id="chat-messages"></div>
    <form id="chat-form" class="chat-form">
      <textarea id="message-input" rows="1" placeholder="Escreve a tua mensagem..." required></textarea>
      <button type="submit">Enviar</button>
    </form>
  </div>
</main>
<script src="../../assets/js/chat.js"></script>
<script>
  const SENDER_ID = <?= json_encode($sender_id) ?>;
  const RECEIVER_ID = <?= json_encode($receiver_id) ?>;
  const CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token']) ?>;
</script>
<?php include('../../includes/footer.php'); ?>