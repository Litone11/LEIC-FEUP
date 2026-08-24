DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS Inquiry;
DROP TABLE IF EXISTS ServiceTransaction;
DROP TABLE IF EXISTS Service;
DROP TABLE IF EXISTS Category;
DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS AdminAction;

CREATE TABLE User (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  name TEXT NOT NULL,
  is_client BOOLEAN DEFAULT 1,
  is_freelancer BOOLEAN DEFAULT 0,
  is_admin BOOLEAN DEFAULT 0,
  bio TEXT,
  profile_picture TEXT
);

CREATE TABLE Category (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT UNIQUE NOT NULL
);

CREATE TABLE CategoryRequest (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  status TEXT DEFAULT 'pending',
  requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES User(id)
);

CREATE TABLE Service (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  price REAL NOT NULL,
  category_id INTEGER,
  status TEXT DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  delivery_time INTEGER,
  main_image TEXT,
  FOREIGN KEY (user_id) REFERENCES User(id),
  FOREIGN KEY (category_id) REFERENCES Category(id)
);

CREATE TABLE ServiceTransaction (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  client_id INTEGER NOT NULL,
  service_id INTEGER NOT NULL,
  status TEXT DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME,
  FOREIGN KEY (client_id) REFERENCES User(id),
  FOREIGN KEY (service_id) REFERENCES Service(id)
);

CREATE TABLE Inquiry (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  sender_id INTEGER NOT NULL,
  receiver_id INTEGER NOT NULL,
  service_id INTEGER,
  content TEXT NOT NULL,
  is_custom_offer BOOLEAN DEFAULT 0,
  archived_by TEXT,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES User(id),
  FOREIGN KEY (receiver_id) REFERENCES User(id),
  FOREIGN KEY (service_id) REFERENCES Service(id)
);

CREATE TABLE Review (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  transaction_id INTEGER NOT NULL,
  rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (transaction_id) REFERENCES ServiceTransaction(id)
);

CREATE TABLE AdminAction (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  admin_id INTEGER NOT NULL,
  action_type TEXT NOT NULL,
  target_user_id INTEGER,
  details TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES User(id),
  FOREIGN KEY (target_user_id) REFERENCES User(id)
);

CREATE TABLE Delivery (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  transaction_id INTEGER NOT NULL,
  message TEXT,
  files TEXT,
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (transaction_id) REFERENCES ServiceTransaction(id)
);

-- Users (password: 123456)
INSERT INTO User (username, email, password_hash, name, is_client, is_freelancer, is_admin, profile_picture) VALUES
('cliente1', 'cliente1@example.com', '$2y$10$uS9SIkCmbbR7eM5mEmPRxePTXq6K0b0.Cys2Z4m3pZ9SzZo1YeV16', 'Cliente Um', 1, 0, 0, '../../assets/img/profiles/cliente1.png'),
('freelancer1', 'freelancer1@example.com', '$2y$10$uS9SIkCmbbR7eM5mEmPRxePTXq6K0b0.Cys2Z4m3pZ9SzZo1YeV16', 'Freelancer Um', 0, 1, 0, '../../assets/img/profiles/freelancer1.png'),
('cliente2', 'cliente2@example.com', '$2y$10$uS9SIkCmbbR7eM5mEmPRxePTXq6K0b0.Cys2Z4m3pZ9SzZo1YeV16', 'Cliente Dois', 1, 0, 0, '../../assets/img/profiles/cliente1.png'),
('freelancer2', 'freelancer2@example.com', '$2y$10$uS9SIkCmbbR7eM5mEmPRxePTXq6K0b0.Cys2Z4m3pZ9SzZo1YeV16', 'Freelancer Dois', 0, 1, 0, '../../assets/img/profiles/freelancer1.png'),
('admin',	'admin@a.a',	'$2y$12$dads4x1bUWZRKrqcDt0xMuQQNYVCLQxpOcddJ/GaLZLm/RIzhQv1a',	'admin',	1,	0,	1,'');		


-- Categories
INSERT INTO Category (name) VALUES
('Design'), ('Programação'), ('Escrita'), ('Consultoria'), ('Tradução');


-- Services
INSERT INTO Service (user_id, title, description, price, category_id, delivery_time, status, created_at, main_image) VALUES
(2, 'Design de Logotipo', 'Criação de logotipos únicos.', 70.0, 1, 2, 'active', datetime('now'), '../../assets/img/service_images/1/service1.png'),
(2, 'Website Profissional', 'Website completo em WordPress.', 300.0, 2, 5, 'active', datetime('now'), '../../assets/img/service_images/2/service2.png'),
(4, 'Consultoria Financeira', 'Ajudo a organizar finanças pessoais e empresariais.', 100.0, 4, 3, 'active', datetime('now'), '../../assets/img/service_images/3/service3.png'),
(4, 'Tradução Técnica', 'Tradução de documentos técnicos para várias línguas.', 80.0, 5, 4, 'active', datetime('now'), '../../assets/img/service_images/4/service4.jpg');


-- Transactions
INSERT INTO ServiceTransaction (client_id, service_id, status, created_at) VALUES
(1, 1, 'completed', datetime('now')),
(1, 2, 'received', datetime('now')),
(1, 3, 'completed', datetime('now')),
(1, 4, 'completed', datetime('now'));


-- Reviews
INSERT INTO Review (transaction_id, rating, comment, created_at) VALUES
(1, 5, 'Excelente trabalho!', datetime('now')),
(2, 4, 'Bom trabalho, mas podia ser mais rápido.', datetime('now')),
(3, 5, 'Ajudou muito com meu planejamento!', datetime('now')),
(4, 5, 'Tradução impecável, muito profissional.', datetime('now'));


-- Inquiries
INSERT INTO Inquiry (sender_id, receiver_id, service_id, content, is_custom_offer, sent_at) VALUES
(1, 2, 1, 'Olá, posso pedir um logotipo com cores específicas?', 0, datetime('now')),
(2, 1, 1, 'Sim, claro! Posso fazer isso.', 0, datetime('now')),
(1, 4, 3, 'Pode me ajudar com um plano de negócios?', 0, datetime('now')),
(4, 1, 3, 'Claro! Posso montar algo personalizado.', 1, datetime('now'));


-- Deliveries
INSERT INTO Delivery (transaction_id, message, files, submitted_at) VALUES
(1, 'Aqui está o seu logotipo finalizado.', 'img/deliveries/logotipo-final.png', datetime('now')),
(3, 'Plano financeiro entregue com projeções.', 'img/deliveries/financeiro.pdf', datetime('now')),
(4, 'Documento traduzido e revisado.', 'img/deliveries/traducao.pdf', datetime('now'));

-- Category Requests
INSERT INTO CategoryRequest (user_id, name, status, requested_at) VALUES
(2, 'Consultoria SEO', 'pending', datetime('now')),
(2, 'Vídeo Edição', 'pending', datetime('now')),
(4, 'Edição de Vídeo', 'pending', datetime('now')),
(4, 'Mentoria Pessoal', 'pending', datetime('now'));






