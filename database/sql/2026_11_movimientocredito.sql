-- Registro de movimientos de créditos del cliente (historial). Ejecutar una sola vez, ANTES de desplegar el código.
-- Cada cambio de saldo queda con fecha, usuario y saldo anterior/nuevo.
-- tipo: PLAN (pago de plan validado), RESERVA (descuento), CANCELACION (devolución),
--       CONGELAMIENTO (devolución al congelar), AJUSTE (cambio manual en el formulario del cliente)

CREATE TABLE `movimientocredito` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `cantidad` int NOT NULL COMMENT 'Positivo suma, negativo resta',
  `saldoanterior` int NOT NULL,
  `saldonuevo` int NOT NULL,
  `reserva_id` int DEFAULT NULL,
  `planpago_id` int UNSIGNED DEFAULT NULL,
  `usuario_id` int UNSIGNED DEFAULT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientocredito_cliente_id_index` (`cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
