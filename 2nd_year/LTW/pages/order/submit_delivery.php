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

$freelancerId = $_SESSION['user_id'];
$transactionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT t.*, s.user_id, s.title, c.username as client_name 
                      FROM ServiceTransaction t 
                      JOIN Service s ON s.id = t.service_id 
                      JOIN User c ON t.client_id = c.id
                      WHERE t.id = ? AND s.user_id = ?");
$stmt->execute([$transactionId, $freelancerId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    echo '<div class="error-message">Transação inválida.</div>';
    include('../../includes/footer.php');
    exit();
}

// Handle form submission (file processing, message, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo '<div class="error-message">Token CSRF inválido.</div>';
        include('../../includes/footer.php');
        exit();
    }

    // Get the message and process file uploads
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Create delivery directory if not exists and save uploaded files
    $deliveryDir = "../../assets/deliveries/$transactionId/";
    if (!is_dir($deliveryDir)) {
        mkdir($deliveryDir, 0777, true);
    }

    $savedFiles = [];

    if (isset($_FILES['attachments']) && isset($_FILES['attachments']['tmp_name']) && is_array($_FILES['attachments']['tmp_name'])) {
        foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['attachments']['error'][$index] === UPLOAD_ERR_OK) {
                $originalName = basename($_FILES['attachments']['name'][$index]);
                $uniqueName = time() . '_' . $index . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $targetPath = $deliveryDir . $uniqueName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $savedFiles[] = $uniqueName;
                }
            }
        }
    }

    $fileList = json_encode($savedFiles);

    $insert = $pdo->prepare("INSERT INTO Delivery (transaction_id, message, files, submitted_at) VALUES (?, ?, ?, datetime('now'))");
    $insert->execute([$transactionId, $message, $fileList]);

    // For demonstration, let's assume processing is always successful.
    // If you have error conditions, only redirect on success.
    $update = $pdo->prepare("UPDATE ServiceTransaction SET status = 'completed', completed_at = datetime('now') WHERE id = ? AND EXISTS (SELECT 1 FROM Service WHERE id = service_id AND user_id = ?)");
    $update->execute([$transactionId, $freelancerId]);
    header('Location: in_progress.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submeter Entrega | Freelance Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-600: #1E88E5;
            --primary-700: #1565C0;
            --primary-50: #f5f3ff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --success-500: #10b981;
            --error-500: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px -1px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
            --radius-sm: 0.125rem;
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-2xl: 1rem;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 48rem;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .delivery-card {
            background-color: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin: 0 auto;
        }

        .delivery-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .delivery-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-600);
            margin: 0 0 0.5rem;
        }

        .delivery-info {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .delivery-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.875rem;
            transition: all 0.15s;
            min-height: 8rem;
            resize: vertical;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-600);
            box-shadow: 0 0 0 3px var(--primary-50);
        }

        .file-upload {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-md);
            background-color: var(--gray-50);
            cursor: pointer;
            transition: all 0.15s;
        }

        .file-upload-label:hover {
            border-color: var(--primary-600);
            background-color: var(--primary-50);
        }

        .file-upload-label svg {
            width: 2rem;
            height: 2rem;
            color: var(--gray-400);
            margin-bottom: 0.5rem;
        }

        .file-upload-label span {
            font-weight: 500;
            color: var(--gray-600);
            text-align: center;
        }

        .file-upload-label small {
            color: var(--gray-500);
            font-size: 0.75rem;
        }

        .file-upload-input {
            display: none;
        }

        .file-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .file-preview-item {
            background-color: var(--gray-100);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .file-preview-item svg {
            width: 0.75rem;
            height: 0.75rem;
            color: var(--gray-500);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-600);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-700);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-block {
            width: 100%;
        }

        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-500);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--error-500);
        }

        .service-info {
            background-color: var(--gray-50);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
        }

        .service-info p {
            margin: 0.25rem 0;
            font-size: 0.875rem;
        }

        .service-info strong {
            color: var(--gray-700);
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            
            .delivery-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="delivery-card">
            <div class="delivery-header">
                <h2>Submeter Entrega Final</h2>
                <div class="delivery-info">
                    Por favor, envie os arquivos finais e uma mensagem para o cliente
                </div>
            </div>

            <div class="service-info">
                <p><strong>Serviço:</strong> <?= htmlspecialchars($transaction['title']) ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($transaction['client_name']) ?></p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="delivery-form">
                <div class="form-group">
                    <label for="message">Mensagem para o cliente</label>
                    <textarea name="message" id="message" placeholder="Descreva sua entrega ou inclua instruções importantes..."></textarea>
                </div>

                <div class="form-group">
                    <label>Anexar arquivos</label>
                    <div class="file-upload">
                        <label for="attachments" class="file-upload-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span>Arraste arquivos ou clique para selecionar</span>
                            <small>Formatos aceitos: PDF, DOC, ZIP, etc. (Máx. 10MB cada)</small>
                        </label>
                        <input type="file" name="attachments[]" id="attachments" multiple class="file-upload-input">
                        <div class="file-preview" id="filePreview"></div>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <button type="submit" class="btn btn-primary btn-block">
                    Entregar Serviço
                </button>
            </form>
        </div>
    </div>

    <script>
        // File upload preview
        const fileInput = document.getElementById('attachments');
        const filePreview = document.getElementById('filePreview');
        
        fileInput.addEventListener('change', function() {
            filePreview.innerHTML = '';
            
            if (this.files.length > 0) {
                Array.from(this.files).forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-preview-item';
                    fileItem.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)
                    `;
                    filePreview.appendChild(fileItem);
                });
            }
        });
    </script>
</body>
</html>