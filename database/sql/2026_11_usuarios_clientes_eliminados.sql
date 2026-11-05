-- Usuarios de clientes que ya estaban eliminados: se eliminan también (deleted_at) para que no puedan iniciar sesión.
-- Desplegar primero el código que agrega SoftDeletes al modelo User. Se puede ejecutar más de una vez.

-- Revisar antes cuáles son:
SELECT u.id, u.login, u.cliente_id, c.deleted_at AS cliente_eliminado
FROM `user` u
JOIN `cliente` c ON c.id = u.cliente_id
WHERE c.deleted_at IS NOT NULL AND u.deleted_at IS NULL;

UPDATE `user` u
JOIN `cliente` c ON c.id = u.cliente_id
SET u.deleted_at = NOW()
WHERE c.deleted_at IS NOT NULL AND u.deleted_at IS NULL;
