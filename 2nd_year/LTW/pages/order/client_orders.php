<?php
require_once('../../database/db.php');
require_once('../../includes/header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_client'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos | Freelance Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --warning-500: #f59e0b;
            --info-500: #3b82f6;
            --danger-500: #ef4444;
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
            max-width: 80rem;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, var(--primary-600), var(--primary-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            color: var(--gray-500);
            font-size: 1.125rem;
            max-width: 42rem;
            margin: 0 auto;
        }

        .orders-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(20rem, 1fr));
        }

        .order-card {
            background-color: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.2s ease;
            border: 1px solid var(--gray-200);
        }

        .order-card:hover {
            transform: translateY(-0.25rem);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-50);
        }

        .order-card-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .order-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0 0 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .order-freelancer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .freelancer-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gray-200);
        }

        .freelancer-info {
            flex: 1;
        }

        .freelancer-name {
            font-weight: 500;
            color: var(--gray-800);
            margin: 0;
            font-size: 0.875rem;
        }

        .freelancer-username {
            color: var(--gray-500);
            margin: 0;
            font-size: 0.75rem;
        }

        .order-card-body {
            padding: 1rem 1.5rem;
        }

        .order-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .order-status {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            text-transform: capitalize;
        }

        .status-pending { background-color: var(--warning-500); color: white; }
        .status-in-progress { background-color: var(--info-500); color: white; }
        .status-completed { background-color: var(--success-500); color: white; }
        .status-received { background-color: var(--primary-600); color: white; }
        .status-rejected { background-color: var(--danger-500); color: white; }

        .order-date {
            color: var(--gray-500);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .order-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            border: 1px solid transparent;
            max-width: 330px;
        }

        .btn-primary {
            background-color: var(--primary-600);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-700);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--gray-700);
            border-color: var(--gray-300);
        }

        .btn-secondary:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-block {
            width: 100%;
        }

        .btn-icon {
            margin-right: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            grid-column: 1 / -1;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-state-description {
            color: var(--gray-500);
            max-width: 28rem;
            margin: 0 auto 1.5rem;
        }

        .loading-spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 640px) {
            .container {
                padding: 1.5rem 1rem;
            }
            
            .header h1 {
                font-size: 1.75rem;
            }
            
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Meus Pedidos</h1>
            <p>Acompanhe todos os serviços que você contratou</p>
        </div>

        <div class="orders-grid" id="orders-list">
            <div class="empty-state">
                <div class="loading-spinner"></div>
                <p>A carregar os seus pedidos...</p>
            </div>
        </div>
    </div>

    <script>
        const userId = <?= json_encode($user_id) ?>;
        const CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token']) ?>;
        const ordersList = document.getElementById('orders-list');
        
        async function loadOrders() {
            try {
                ordersList.innerHTML = `
                    <div class="empty-state">
                        <div class="loading-spinner"></div>
                        <p>A carregar os seus pedidos...</p>
                    </div>
                `;
                
                const res = await fetch(`../../includes/fetch_orders.php?client_id=${userId}`);
                const orders = await res.json();
                
                if (orders.length === 0) {
                    ordersList.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <h3 class="empty-state-title">Nenhum pedido encontrado</h3>
                            <p class="empty-state-description">Você ainda não contratou nenhum serviço. Encontre freelancers incríveis para te ajudar!</p>
                            <a href="../services/" class="btn btn-primary">Explorar Serviços</a>
                        </div>
                    `;
                    return;
                }
                
                ordersList.innerHTML = '';
                orders.forEach(order => {
                    const statusClass = `status-${order.status.replace(' ', '-')}`;
                    const statusText = order.status.charAt(0).toUpperCase() + order.status.slice(1);
                    const formattedDate = new Date(order.created_at).toLocaleDateString('pt-PT', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    let actionButtons = '';
                    
                    if (['completed', 'received'].includes(order.status)) {
                        actionButtons += `
                            <a href="view_delivery.php?id=${order.id}" class="btn btn-secondary btn-sm btn-block">
                                Ver Entrega
                            </a>
                        `;
                    }
                    
                    if (order.status === 'completed') {
                        actionButtons += `
                            <button onclick="confirmReceived(${order.id})" class="btn btn-primary btn-sm btn-block">
                                Confirmar Receção
                            </button>
                        `;
                    }
                    
                    if (order.status === 'received' && !order.has_review) {
                        actionButtons += `
                            <a href="../review/review_freelancer.php?id=${order.id}" class="btn btn-primary btn-sm btn-block">
                                Avaliar Freelancer
                            </a>
                        `;
                    }
                    
                    const orderCard = document.createElement('div');
                    orderCard.className = 'order-card';
                    orderCard.innerHTML = `
                        <div class="order-card-header">
                            <h3 class="order-title">${order.title}</h3>
                            <div class="order-freelancer">
                                <img src="../${order.profile_picture || '../../assets/img/default-profile.png'}" 
                                     alt="${order.username}" 
                                     class="freelancer-avatar">
                                <div class="freelancer-info">
                                    <p class="freelancer-name">${order.name || order.username}</p>
                                    <p class="freelancer-username">@${order.username}</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-card-body">
                            <div class="order-meta">
                                <span class="order-status ${statusClass}">${statusText}</span>
                                <span class="order-date">${formattedDate}</span>
                            </div>
                            <div class="order-actions">
                                ${actionButtons}
                                <a href="../chat/chat.php?freelancer_id=${order.user_id}" class="btn btn-secondary btn-sm btn-block">
                                    Ver Conversa
                                </a>
                            </div>
                        </div>
                    `;
                    ordersList.appendChild(orderCard);
                });
            } catch (error) {
                ordersList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">⚠️</div>
                        <h3 class="empty-state-title">Ocorreu um erro</h3>
                        <p class="empty-state-description">Não foi possível carregar seus pedidos. Por favor, tente novamente mais tarde.</p>
                        <button onclick="loadOrders()" class="btn btn-primary">Recarregar</button>
                    </div>
                `;
                console.error('Error loading orders:', error);
            }
        }

        async function confirmReceived(orderId) {
            const confirmed = confirm('Tem certeza que deseja confirmar a receção deste serviço? Esta ação não pode ser desfeita.');
            if (!confirmed) return;

            try {
                const button = event.target;
                button.disabled = true;
                button.innerHTML = '<span class="loading-spinner"></span> Processando...';
                
                const res = await fetch('../../includes/confirm_receipt.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${orderId}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
                });

                if (res.ok) {
                    const result = await res.json();
                    if (result.success) {
                        loadOrders();
                    } else {
                        alert(result.message || 'Ocorreu um erro ao confirmar a receção.');
                    }
                } else {
                    throw new Error('Erro na resposta do servidor');
                }
            } catch (error) {
                alert('Ocorreu um erro ao confirmar a receção.');
                console.error('Error confirming receipt:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>
<?php require_once('../../includes/footer.php'); ?>
</html>