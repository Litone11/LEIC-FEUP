DROP SCHEMA IF EXISTS lbaw25136 CASCADE;
CREATE SCHEMA lbaw25136;
SET search_path TO lbaw25136;


-----------------------------------------
-- Types

CREATE TYPE roles AS ENUM ('normal', 'coordinator');
CREATE TYPE task_effort AS ENUM ('High', 'Medium','Low');
CREATE TYPE task_status AS ENUM ('Untouched', 'InProgress','Done');
CREATE TYPE task_priority AS ENUM ('Urgent','High', 'Medium','Low');
CREATE TYPE user_status AS ENUM ('disponível', 'offline', 'customizável');


-----------------------------------------
--  Tables
-----------------------------------------

CREATE TABLE users (
   user_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
   email TEXT NOT NULL UNIQUE,
   username TEXT NOT NULL,
   password TEXT ,
   created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
   google_id VARCHAR,
   is_deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE regular_user (
   user_id INT PRIMARY KEY  REFERENCES users(user_id),
   profile_pic TEXT,
    status user_status NOT NULL DEFAULT 'offline',
   custom_status VARCHAR(60)
);

CREATE TABLE notification (
    notification_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    title varchar(80) NOT NULL,
    message_ varchar(250) NOT NULL,
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    is_read BOOLEAN DEFAULT FALSE NOT NULL,
    receiver_id INT, FOREIGN KEY (receiver_id) REFERENCES regular_user(user_id),
    link TEXT
);

CREATE TABLE invitation_notification (
    notification_id INT PRIMARY KEY, FOREIGN KEY (notification_id) REFERENCES notification(notification_id) ON DELETE CASCADE
);

CREATE TABLE accepted_invitation_notification (
    notification_id INT PRIMARY KEY,
    user_id INT, FOREIGN KEY (user_id)  REFERENCES regular_user(user_id),
    FOREIGN KEY (notification_id) REFERENCES invitation_notification(notification_id) ON DELETE CASCADE
);
CREATE TABLE project (
    project_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name varchar(80) NOT NULL,
    description varchar(256) NOT NULL,
    is_archived BOOL NOT NULL DEFAULT FALSE,
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    color VARCHAR(7) NOT NULL DEFAULT '#3b82f6'
        CHECK (color ~ '^#[0-9A-Fa-f]{6}$')
    --notification_id INT
);

CREATE TABLE sent_invitation_notification (
    notification_id INT PRIMARY KEY,
    project_id INT, FOREIGN KEY (project_id) REFERENCES project(project_id),
    FOREIGN KEY (notification_id) REFERENCES invitation_notification(notification_id) ON DELETE CASCADE

);
CREATE TABLE task (
    task_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name varchar(80) NOT NULL,
    description varchar(256) NOT NULL,
    status task_status NOT NULL DEFAULT 'Untouched',
    priority task_priority NOT NULL DEFAULT 'Low',
    effort task_effort NOT NULL DEFAULT 'Low',
    nr_comment INT NOT NULL DEFAULT 0 CHECK(nr_comment >= 0),
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    completed_at DATE CHECK (completed_at IS NULL OR created_at <= completed_at),
    due_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= due_at),
    project_id INT,
    task_list_id INT,
    task_responsible_id INT,
    assignee_id INT
    --notification_id INT
);

CREATE TABLE task_dependency (
    task_dependency_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    predecessor_task_id INT NOT NULL REFERENCES task(task_id) ON DELETE CASCADE,
    successor_task_id INT NOT NULL REFERENCES task(task_id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT task_dependency_unique UNIQUE (predecessor_task_id, successor_task_id)
);

CREATE INDEX task_dependency_successor_idx
    ON task_dependency (successor_task_id);

CREATE TABLE task_group (
    task_group_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name varchar(80) NOT NULL,
    description varchar(256),
    label varchar(80),
    project_id INT NOT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (project_id) REFERENCES project(project_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE task_list (
    task_list_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name varchar(80) NOT NULL,
    description varchar(256),
    task_group_id INT ,
    created_by INT NOT NULL,
    FOREIGN KEY (task_group_id) REFERENCES task_group(task_group_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE task_notification (
    notification_id INT PRIMARY KEY,
 FOREIGN KEY (notification_id) REFERENCES notification(notification_id) ON DELETE CASCADE
);

CREATE TABLE complete_notification (
    notification_id INT PRIMARY KEY,
    task_id INT, FOREIGN KEY(task_id) REFERENCES task(task_id),
    FOREIGN KEY (notification_id) REFERENCES task_notification(notification_id) ON DELETE CASCADE
);


CREATE TABLE thread (
    thread_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    message_ varchar(512) NOT NULL,
    likes INT NOT NULL DEFAULT 0 CHECK(likes >= 0),
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    task_id INT,
    user_id INT ,
    FOREIGN KEY (user_id) REFERENCES regular_user(user_id),
    FOREIGN KEY (task_id) REFERENCES task(task_id) ON DELETE CASCADE

);



CREATE TABLE commentary (
    comment_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    message_ varchar(512) NOT NULL,
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    thread_id INT,
    user_id INT NOT NULL,
    taskId_id INT,
    FOREIGN KEY (thread_id) REFERENCES thread(thread_id),
    FOREIGN KEY (user_id) REFERENCES regular_user(user_id),
    FOREIGN KEY (taskId_id) REFERENCES regular_user(user_id)
);

CREATE TABLE tag (
    tag_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    project_id INT , 
    FOREIGN KEY (project_id) REFERENCES project(project_id),
    name varchar(50) NOT NULL 
);

CREATE TABLE has_tags (
    tag_id INT,
    task_id INT,
    FOREIGN KEY (tag_id) REFERENCES tag(tag_id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES task(task_id) ON DELETE CASCADE,
    PRIMARY KEY (tag_id, task_id)
);

CREATE TABLE assigned_notification (
    notification_id INT PRIMARY KEY,
    task_id INT, FOREIGN KEY(task_id) REFERENCES task(task_id),
    FOREIGN KEY (notification_id) REFERENCES task_notification(notification_id) ON DELETE CASCADE

);

CREATE TABLE coordinate_change_notification (
    notification_id INT PRIMARY KEY,
    project_id INT ,FOREIGN KEY(project_id) REFERENCES project(project_id),
     FOREIGN KEY (notification_id) REFERENCES notification(notification_id) ON DELETE CASCADE
);

CREATE TABLE comment_notification (
    notification_id INT PRIMARY KEY,
    FOREIGN KEY (notification_id) REFERENCES task_notification(notification_id) ON DELETE CASCADE

);

CREATE TABLE comment_received_notification (
    notification_id INT PRIMARY KEY,
    thread_id INT ,FOREIGN KEY (thread_id) REFERENCES thread(thread_id),
    FOREIGN KEY (notification_id) REFERENCES comment_notification(notification_id) ON DELETE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES thread(thread_id) ON DELETE CASCADE
);

CREATE TABLE user_tag_notification (
    notification_id INT PRIMARY KEY,
    comment_id INT,
    FOREIGN KEY (notification_id) REFERENCES comment_notification(notification_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES commentary(comment_id) ON DELETE CASCADE
);

CREATE TABLE like_thread (
    user_id INT,
    thread_id INT,
    liked_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(liked_at <= CURRENT_DATE),
    PRIMARY KEY (user_id, thread_id),
    FOREIGN KEY (user_id) REFERENCES regular_user(user_id),
    FOREIGN KEY (thread_id) REFERENCES thread(thread_id)
);

CREATE TABLE like_notification (
    notification_id INT PRIMARY KEY,
    user_id INT ,
    thread_id INT ,
    FOREIGN KEY (user_id, thread_id) REFERENCES like_thread(user_id, thread_id),
    FOREIGN KEY (notification_id) REFERENCES comment_notification(notification_id) ON DELETE CASCADE
);

CREATE TABLE forum_topic (
    forum_topic_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    title varchar(120) NOT NULL,
    body varchar(2000) NOT NULL,
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    task_id INT,
    FOREIGN KEY (project_id) REFERENCES project(project_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (task_id) REFERENCES task(task_id)
);

CREATE TABLE forum_reply (
    forum_reply_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    body varchar(2000) NOT NULL,
    created_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(created_at <= CURRENT_DATE),
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (topic_id) REFERENCES forum_topic(forum_topic_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE forum_like (
    user_id INT,
    topic_id INT,
    liked_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(liked_at <= CURRENT_DATE),
    PRIMARY KEY (user_id, topic_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (topic_id) REFERENCES forum_topic(forum_topic_id) ON DELETE CASCADE
);

CREATE TABLE admins (
   user_id INT PRIMARY KEY , FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE related_to (
  user_id INT,
  project_id INT,
  user_role roles NOT NULL DEFAULT 'normal',
  is_favorite BOOL NOT NULL DEFAULT FALSE,
  PRIMARY KEY(user_id, project_id),
  FOREIGN KEY (user_id) REFERENCES regular_user(user_id),
  FOREIGN KEY (project_id) REFERENCES project(project_id)
);



CREATE TABLE block_user (
    block_user_id SERIAL PRIMARY KEY,
    reason varchar(250) NOT NULL,
    blocked_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(blocked_at <= CURRENT_DATE),
    unblocked_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(unblocked_at <= CURRENT_DATE AND blocked_at <= unblocked_at),
    admin_id INT,
    user_id INT,
    FOREIGN KEY (admin_id) REFERENCES admins(user_id),
    FOREIGN KEY (user_id) REFERENCES regular_user(user_id)
);



CREATE TABLE suspend_project (
    suspend_project_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    reason varchar(250) NOT NULL,
    suspended_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(suspended_at <= CURRENT_DATE),
    unsuspended_at DATE NOT NULL DEFAULT CURRENT_DATE CHECK(unsuspended_at <= CURRENT_DATE AND suspended_at <= unsuspended_at),
    admin_id INT,
    project_id INT,
    FOREIGN KEY (admin_id) REFERENCES admins(user_id),
    FOREIGN KEY (project_id) REFERENCES project(project_id)
);

CREATE TABLE invitation (
    invitation_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    project_id INT NOT NULL,
    is_accepted BOOL NOT NULL DEFAULT FALSE,
    CONSTRAINT ck_invitation CHECK(sender_id <> receiver_id),
    FOREIGN KEY (sender_id) REFERENCES regular_user(user_id),
    FOREIGN KEY (receiver_id) REFERENCES regular_user(user_id),
    FOREIGN KEY (project_id) REFERENCES project(project_id)
);




ALTER TABLE regular_user
ADD CONSTRAINT chk_custom_status
CHECK (
    (status = 'customizável' AND custom_status IS NOT NULL AND length(trim(custom_status)) > 0)
 OR (status <> 'customizável' AND custom_status IS NULL)
);

CREATE TABLE password_reset_tokens (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    CONSTRAINT password_reset_tokens_email_index UNIQUE (email)
);






ALTER TABLE task
ADD FOREIGN KEY (project_id) REFERENCES project(project_id),
ADD FOREIGN KEY (task_list_id) REFERENCES task_list(task_list_id),
ADD FOREIGN KEY (task_responsible_id) REFERENCES regular_user(user_id),
ADD FOREIGN KEY (assignee_id) REFERENCES regular_user(user_id);



---INDEX 
ALTER TABLE project
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION idx_ffs_project() RETURNS TRIGGER AS $$
BEGIN
 IF TG_OP = 'INSERT' THEN
        NEW.tsvectors = (
         setweight(to_tsvector('english',  coalesce(NEW.name, '')), 'A') ||
         setweight(to_tsvector('english',  coalesce(NEW.description, '')),  'B')
        );
 END IF;
 IF TG_OP = 'UPDATE' THEN
         IF (NEW.name <> OLD.name OR NEW.description <> OLD.description) THEN
           NEW.tsvectors = (
             setweight(to_tsvector('english',   coalesce(NEW.name, '')), 'A') ||
             setweight(to_tsvector('english',  coalesce(NEW.description, '')),'B')
           );
         END IF;
 END IF;
 RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER idx_ffs_project
 BEFORE INSERT OR UPDATE ON project
 FOR EACH ROW
 EXECUTE PROCEDURE idx_ffs_project();


CREATE INDEX project_search_idx ON project USING GIN (tsvectors);

ALTER TABLE task
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION idx_ffs_task() RETURNS TRIGGER AS $$
BEGIN
 IF TG_OP = 'INSERT' THEN
        NEW.tsvectors = (
        setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(NEW.description, '')), 'B')
        );
 END IF;
 IF TG_OP = 'UPDATE' THEN
         IF (NEW.name <> OLD.name OR NEW.description <> OLD.description) THEN
           NEW.tsvectors = (
        setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(NEW.description, '')), 'B')
           );
         END IF;
 END IF;
 RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER idx_ffs_task
 BEFORE INSERT OR UPDATE ON task
 FOR EACH ROW
 EXECUTE PROCEDURE idx_ffs_task();


CREATE INDEX task_search_idx ON task USING GIN (tsvectors);

--TRIGGER

CREATE OR REPLACE FUNCTION trg_task_completed_at() RETURNS trigger AS $$
BEGIN
  IF NEW.status = 'Done' AND (OLD.status IS DISTINCT FROM 'Done') THEN
    NEW.completed_at := CURRENT_DATE;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER task_completed_at
BEFORE UPDATE ON task
FOR EACH ROW
EXECUTE FUNCTION trg_task_completed_at();
CREATE OR REPLACE FUNCTION trg_prevent_duplicate_like() RETURNS trigger AS $$
BEGIN
  IF EXISTS (
    SELECT 1 FROM like_thread
    WHERE user_id = NEW.user_id AND thread_id = NEW.thread_id
  ) THEN
    RAISE EXCEPTION 'User cannot like the same thread twice';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER prevent_duplicate_like
BEFORE INSERT ON like_thread
FOR EACH ROW
EXECUTE FUNCTION trg_prevent_duplicate_like();
CREATE OR REPLACE FUNCTION trg_tag_lower() RETURNS trigger AS $$
BEGIN
  NEW.name := lower(NEW.name);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tag_lower
BEFORE INSERT OR UPDATE ON tag
FOR EACH ROW
EXECUTE FUNCTION trg_tag_lower();
CREATE OR REPLACE FUNCTION trg_regular_user_default_pic() RETURNS trigger AS $$
BEGIN
  IF NEW.profile_pic IS NULL OR NEW.profile_pic = '' THEN
    NEW.profile_pic := 'default-profile.svg';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER regular_user_default_pic
BEFORE INSERT ON regular_user
FOR EACH ROW
EXECUTE FUNCTION trg_regular_user_default_pic();
CREATE OR REPLACE FUNCTION trg_block_self() RETURNS trigger AS $$
BEGIN
    IF NEW.admin_id = NEW.user_id THEN
    RAISE EXCEPTION 'An admin cannot block themselves';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER block_self_chk
BEFORE INSERT OR UPDATE ON block_user
FOR EACH ROW
EXECUTE FUNCTION trg_block_self();
CREATE OR REPLACE FUNCTION trg_cascade_delete_thread() RETURNS trigger AS $$
BEGIN
  DELETE FROM commentary WHERE thread_id = OLD.thread_id;
  DELETE FROM like_thread WHERE thread_id = OLD.thread_id;
  RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER cascade_delete_thread
BEFORE DELETE ON thread
FOR EACH ROW
EXECUTE FUNCTION trg_cascade_delete_thread();

CREATE OR REPLACE PROCEDURE proc_tran01(
    p_project_id INT,
    p_user_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_notification_id INT;
    v_project_name TEXT;
BEGIN
    -- Get the project name for the message
    SELECT name INTO v_project_name
    FROM project
    WHERE project_id = p_project_id;

    -- Insert base notification
    INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
    VALUES (
        'Novo Coordenador',
        'O projeto "' || v_project_name || '"tem um novo coordenador!',
        CURRENT_DATE,
        FALSE,
        p_user_id
    )
    RETURNING notification_id INTO v_notification_id;

    -- Insert into subclass
    INSERT INTO coordinate_change_notification (notification_id, project_id)
    VALUES (v_notification_id, p_project_id);
END;
$$;
CREATE OR REPLACE PROCEDURE proc_tran02(
    p_project_id INT,
    p_user_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_project_name TEXT;
    v_notification_id INT;
BEGIN
    SELECT name INTO v_project_name
    FROM project
    WHERE project_id = p_project_id;

    RAISE NOTICE 'project_id = %, user_id = %, project_name = %', p_project_id, p_user_id, v_project_name;

    INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
    VALUES (
        'Novo Convite ',
        'Foste convidado para : "' || v_project_name || '"!',
        CURRENT_DATE,
        FALSE,
        p_user_id
    )
    RETURNING notification_id INTO v_notification_id;

    RAISE NOTICE 'notification_id = %', v_notification_id;

    INSERT INTO invitation_notification (notification_id)
    VALUES (v_notification_id);

    INSERT INTO sent_invitation_notification (notification_id, project_id)
    VALUES (v_notification_id, p_project_id);
END;
$$;




CREATE OR REPLACE PROCEDURE proc_tran03(
    p_invitation_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_project_id INT;
    v_coordinator_id INT;
    v_notification_id INT;
    v_member_name TEXT;
    v_project_name TEXT;
BEGIN
    UPDATE invitation
    SET is_accepted = TRUE
    WHERE invitation_id = p_invitation_id
    RETURNING project_id, receiver_id INTO v_project_id, v_coordinator_id;

    IF NOT FOUND THEN
        RETURN;
    END IF;

    -- Obter o nome do membro que aceitou
    SELECT username INTO v_member_name
    FROM users
    WHERE user_id = (SELECT receiver_id FROM invitation WHERE invitation_id = p_invitation_id);

    -- Obter o nome do projeto
    SELECT name INTO v_project_name
    FROM project
    WHERE project_id = v_project_id;

    -- Obter o coordenador do projeto
    SELECT user_id INTO v_coordinator_id
    FROM related_to
    WHERE project_id = v_project_id
      AND user_role = 'coordinator';

    -- Inserir a notificação
    INSERT INTO notification (
        title, message_, created_at, is_read, receiver_id
    )
    VALUES (
        'Convite Aceite',
        'O/A "' || v_member_name || '" aceitou o convite para o projeto "' || v_project_name || '"!',
        NOW(),
        FALSE,
        v_coordinator_id
    )
    RETURNING notification_id INTO v_notification_id;

  
    INSERT INTO invitation_notification (notification_id)
    VALUES (v_notification_id);

    INSERT INTO accepted_invitation_notification (notification_id, user_id)
    VALUES (v_notification_id, v_coordinator_id);

END;
$$;


CREATE OR REPLACE PROCEDURE proc_tran04(p_task_id INT)
LANGUAGE plpgsql
AS $$
DECLARE
    v_task_name        TEXT;
    v_assignee_id      INT;
    v_project_id       INT;
    v_notification_id  INT;
    v_coordinator_id   INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;

    -- Marca a task como Done
    UPDATE task
    SET status = 'Done',
        completed_at = CURRENT_DATE
    WHERE task_id = p_task_id
      AND status <> 'Done'
    RETURNING name, assignee_id, project_id
    INTO v_task_name, v_assignee_id, v_project_id;

    IF FOUND THEN

        -- Notificação para o assignee
        IF v_assignee_id IS NOT NULL THEN
            INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
            VALUES (
                'Tarefa Concluída',
                'A tarefa :"' || v_task_name ||'" foi marcada como concluída !',
                CURRENT_DATE,
                FALSE,
                v_assignee_id
            )
            RETURNING notification_id INTO v_notification_id;

            INSERT INTO task_notification (notification_id)
            VALUES (v_notification_id);

            INSERT INTO complete_notification (notification_id, task_id)
            VALUES (v_notification_id, p_task_id);
        END IF;

        -- Obter o coordenador do projeto
        SELECT user_id INTO v_coordinator_id
        FROM related_to
        WHERE project_id = v_project_id
          AND user_role = 'coordinator'
        LIMIT 1;

        -- Notificação para o coordenador
        INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
        VALUES (
            'Tarefa Concluída',
            'Tarefa concluída: ' || v_task_name,
            CURRENT_DATE,
            FALSE,
            v_coordinator_id
        )
        RETURNING notification_id INTO v_notification_id;

        INSERT INTO task_notification (notification_id)
        VALUES (v_notification_id);

        INSERT INTO complete_notification (notification_id, task_id)
        VALUES (v_notification_id, p_task_id);

    END IF;
END;
$$;




CREATE OR REPLACE PROCEDURE proc_tran05(
    p_task_id INT,
    p_assignee_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_task_name TEXT;
    v_task_responsible_id INT;
    v_notification_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;

    -- Get task name and responsible
    SELECT name, task_responsible_id
    INTO v_task_name, v_task_responsible_id
    FROM task
    WHERE task_id = p_task_id;

    -- -------------------------------------------------
    -- ASSIGNEE
    -- -------------------------------------------------
    IF p_assignee_id IS NOT NULL THEN
        INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
        VALUES (
            'Tarefa Atribuída',
            'Foste atribuido para : ' || v_task_name,
            CURRENT_DATE,
            FALSE,
            p_assignee_id
        )
        RETURNING notification_id INTO v_notification_id;

        INSERT INTO task_notification (notification_id)
        VALUES (v_notification_id);

        INSERT INTO assigned_notification (notification_id, task_id)
        VALUES (v_notification_id, p_task_id);
    END IF;

    -- -------------------------------------------------
    -- TASK RESPONSIBLE
    -- -------------------------------------------------
    IF v_task_responsible_id IS NOT NULL
       AND v_task_responsible_id <> p_assignee_id THEN

        INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
        VALUES (
            'Responsável por Tarefa',
            'És responsável por :' || v_task_name,
            CURRENT_DATE,
            FALSE,
            v_task_responsible_id
        )
        RETURNING notification_id INTO v_notification_id;

        INSERT INTO task_notification (notification_id)
        VALUES (v_notification_id);

        INSERT INTO assigned_notification (notification_id, task_id)
        VALUES (v_notification_id, p_task_id);
    END IF;

END;
$$;



CREATE OR REPLACE PROCEDURE proc_tran06(
    p_message TEXT,
    p_thread_id INT,
    p_user_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_comment_id INT;
    v_notification_id INT;
    v_thread_owner_id INT;
BEGIN
    -- Insert the comment
    INSERT INTO commentary (message_, thread_id, user_id, created_at)
    VALUES (p_message, p_thread_id, p_user_id, CURRENT_DATE)
    RETURNING comment_id INTO v_comment_id;

    -- Get thread owner
    SELECT user_id INTO v_thread_owner_id
    FROM thread
    WHERE thread_id = p_thread_id;

    -- Insert notification
    INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
    VALUES ('Novo comentário numa Thread', 'Um novo comentário foi adicionado', CURRENT_DATE, FALSE, v_thread_owner_id)
    RETURNING notification_id INTO v_notification_id;

    -- Insert into task_notification first (because comment_notification depends on it)
    INSERT INTO task_notification (notification_id)
    VALUES (v_notification_id);

    -- Insert into comment_notification subclass
    INSERT INTO comment_notification (notification_id)
    VALUES (v_notification_id);

    -- Insert into comment_received_notification
    INSERT INTO comment_received_notification (notification_id, thread_id)
    VALUES (v_notification_id, p_thread_id);

END;
$$;



CREATE OR REPLACE PROCEDURE proc_tran07(
    p_user_id INT,
    p_thread_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_notification_id INT;
BEGIN
    -- Insert the like
    INSERT INTO like_thread (user_id, thread_id, liked_at)
    VALUES (p_user_id, p_thread_id, CURRENT_DATE);

    -- Increment the thread's like count
    UPDATE thread
    SET likes = likes + 1
    WHERE thread_id = p_thread_id;

    -- Step 1: Insert into base notification
    INSERT INTO notification (title, message_, created_at, is_read, receiver_id)
    SELECT
        'New Like',
        'Your thread received a new like!',
        CURRENT_DATE,
        FALSE,
        t.user_id
    FROM thread t
    WHERE t.thread_id = p_thread_id
    RETURNING notification_id INTO v_notification_id;

    -- Step 2: Insert into task_notification (needed before comment_notification)
    INSERT INTO task_notification (notification_id)
    VALUES (v_notification_id);

    -- Step 3: Insert into comment_notification
    INSERT INTO comment_notification (notification_id)
    VALUES (v_notification_id);

    -- Step 4: Insert into like_notification
    INSERT INTO like_notification (notification_id, user_id, thread_id)
    VALUES (v_notification_id, p_user_id, p_thread_id);

END;
$$;



CREATE OR REPLACE PROCEDURE proc_tran08(p_project_id INT)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Delete notifications associated with the project
    DELETE FROM like_notification ln
    USING comment_notification cn
    JOIN task_notification tn ON cn.notification_id = tn.notification_id
    JOIN notification n ON tn.notification_id = n.notification_id
    WHERE ln.notification_id = cn.notification_id
      AND n.receiver_id IN (SELECT user_id FROM related_to WHERE project_id = p_project_id);

    DELETE FROM comment_notification cn
    USING task_notification tn
    WHERE cn.notification_id = tn.notification_id
      AND tn.notification_id IN (SELECT notification_id FROM notification n
                                 JOIN related_to r ON n.receiver_id = r.user_id
                                 WHERE r.project_id = p_project_id);

    DELETE FROM task_notification tn
    USING notification n
    WHERE tn.notification_id = n.notification_id
      AND n.receiver_id IN (SELECT user_id FROM related_to WHERE project_id = p_project_id);

    DELETE FROM notification n
    WHERE n.receiver_id IN (SELECT user_id FROM related_to WHERE project_id = p_project_id);
    DELETE FROM thread
    WHERE task_id IN (SELECT task_id FROM task WHERE project_id = p_project_id);
    DELETE FROM task WHERE project_id = p_project_id;
    DELETE FROM invitation WHERE project_id = p_project_id;
    DELETE FROM sent_invitation_notification WHERE project_id = p_project_id;
    DELETE FROM coordinate_change_notification WHERE project_id = p_project_id;
    DELETE FROM suspend_project WHERE project_id = p_project_id;
    DELETE FROM related_to WHERE project_id = p_project_id;

    -- Finally delete the project
    DELETE FROM project WHERE project_id = p_project_id;

END;
$$;



CREATE OR REPLACE PROCEDURE proc_anonymize_user(p_user_id INT)
LANGUAGE plpgsql
AS $$
DECLARE
  anonymous_email TEXT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    anonymous_email := 'anonymous' || p_user_id ||'@anonymous.com';
    -- Anonymize the user
    UPDATE users
    SET
        username = 'Anonymous',
        email = anonymous_email
    WHERE user_id = p_user_id;

    -- Anonymize user's comments
    UPDATE commentary
    SET user_id = NULL
    WHERE user_id = p_user_id;

    -- Anonymize user's threads
    UPDATE thread
    SET user_id = NULL, 
        message_ = '[removed]'
    WHERE user_id = p_user_id;

    UPDATE task
    SET task_responsible_id = NULL,
        assignee_id = NULL
    WHERE task_responsible_id = p_user_id OR assignee_id = p_user_id;

END;
$$;


INSERT INTO users (email, username, password, created_at) VALUES
  ('admin@example.com', 'Admin', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_DATE),
  ('alice@example.com', 'alice', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_DATE),
  ('bruno@example.com', 'bruno', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_DATE),
  ('carla@example.com', 'carla', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', CURRENT_DATE);

INSERT INTO regular_user (user_id, profile_pic, status, custom_status) VALUES
  (2, 'default-profile.svg', 'offline', NULL),
  (3, NULL, 'offline', NULL),
  (4, 'default-profile.svg', 'customizável', 'working remotely');


INSERT INTO admins (user_id) VALUES (1);

INSERT INTO project (name, description, is_archived, created_at) VALUES
  ('Orion',  'Internal task manager revamp', FALSE, CURRENT_DATE),
  ('Apollo', 'Mobile companion app',         FALSE, CURRENT_DATE);


INSERT INTO task_group (name, description, label, project_id, created_by) VALUES
  ('Estrutura Orion', 'Grupo por omissão do projeto Orion', 'general', 1, 2),
  ('Estrutura Apollo', 'Grupo por omissão do projeto Apollo', 'general', 2, 4);

INSERT INTO task_list (name, description, task_group_id, created_by) VALUES
  ('Backlog', 'Itens por fazer', 1, 2),
  ('Sprint',  'Em curso',        1, 2),
  ('Mobile Backlog', 'Tarefas mobile', 2, 4);


INSERT INTO related_to (user_id, project_id, user_role, is_favorite) VALUES
  (2, 1, 'coordinator', TRUE),
  (3, 1, 'normal',      FALSE),
  (4, 2, 'coordinator', TRUE);





INSERT INTO invitation (sender_id, receiver_id, project_id, is_accepted) VALUES
  (2, 3, 1, TRUE),
  (3, 4, 2, FALSE);

INSERT INTO task
  (name, description, status, priority, effort, nr_comment, created_at, completed_at, due_at,
   project_id, task_list_id, task_responsible_id, assignee_id)
VALUES
  ('Design DB indexes', 'Rever e otimizar índices', 'InProgress', 'High',   'Medium', 0,
    CURRENT_DATE, CURRENT_DATE, CURRENT_DATE, 1, 2, 2, 3),
  ('Implement login',   'OAuth2 com PKCE',         'Untouched',  'Urgent', 'High',   0,
    CURRENT_DATE, CURRENT_DATE, CURRENT_DATE, 1, 1, 2, 3),
  ('React shell',       'Navegação e layout',      'InProgress', 'Medium', 'Medium', 0,
    CURRENT_DATE, CURRENT_DATE, CURRENT_DATE, 2, 3, 3, 4),
  ('CI pipeline',       'Tests & coverage',        'Done',       'Low',    'Low',    0,
    CURRENT_DATE, CURRENT_DATE, CURRENT_DATE, 2, 3, 3, 4);

INSERT INTO tag (name,project_id) VALUES ('backend',1), ('frontend',1), ('mobile',2), ('urgent',2);
INSERT INTO has_tags (tag_id,task_id) VALUES (1,1), (2,2);

INSERT INTO task_dependency (predecessor_task_id, successor_task_id, created_at) VALUES
  (1, 2, CURRENT_TIMESTAMP),
  (3, 4, CURRENT_TIMESTAMP);

INSERT INTO thread (message_, likes, created_at, task_id, user_id) VALUES
  ('Analisar EXPLAIN plans.', 0, CURRENT_DATE, 1, 2),
  ('Precisamos de social login?', 0, CURRENT_DATE, 2, 3),
  ('Header apertado em mobile.', 0, CURRENT_DATE, 3, 4);

INSERT INTO like_thread (user_id, thread_id, liked_at) VALUES
  (2, 1, CURRENT_DATE);
