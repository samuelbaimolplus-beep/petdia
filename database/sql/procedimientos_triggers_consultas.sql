-- ==============================================================================
-- SISTEMA VETERINARIO "LA PETDIATRÍA"
-- Script SQL: Vistas, Consultas, Procedimientos Almacenados y Triggers
-- Motor de Base de Datos: PostgreSQL 14+
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 0. VISTAS DE COMPATIBILIDAD (Soporte de sintaxis PascalCase para consultas académicas)
-- ------------------------------------------------------------------------------
CREATE OR REPLACE VIEW "Persona" AS SELECT * FROM personas;
CREATE OR REPLACE VIEW "Propietario" AS SELECT * FROM propietarios;
CREATE OR REPLACE VIEW "Paciente" AS SELECT * FROM pacientes;
CREATE OR REPLACE VIEW "Servicio" AS SELECT * FROM servicios;
CREATE OR REPLACE VIEW "Raza" AS SELECT * FROM razas;
CREATE OR REPLACE VIEW "Cita" AS SELECT * FROM citas;
CREATE OR REPLACE VIEW "Profesional" AS SELECT * FROM profesionales;
CREATE OR REPLACE VIEW "Atencion" AS SELECT * FROM atenciones;
CREATE OR REPLACE VIEW "Producto" AS SELECT * FROM productos;
CREATE OR REPLACE VIEW "Compra" AS SELECT * FROM compras;
CREATE OR REPLACE VIEW "Cargo" AS SELECT * FROM cargos;
CREATE OR REPLACE VIEW "Cuenta" AS SELECT * FROM cuentas;
CREATE OR REPLACE VIEW "MovimientoInventario" AS SELECT * FROM movimiento_inventarios;
CREATE OR REPLACE VIEW "Recordatorio" AS SELECT * FROM recordatorios;
CREATE OR REPLACE VIEW "Pago" AS SELECT * FROM pagos;
CREATE OR REPLACE VIEW "DetallePago" AS SELECT * FROM detalle_pagos;
CREATE OR REPLACE VIEW "AplicacionPago" AS SELECT * FROM aplicacion_pagos;
CREATE OR REPLACE VIEW "DetalleCita" AS SELECT * FROM detalle_citas;
CREATE OR REPLACE VIEW "FichaMedica" AS SELECT * FROM ficha_medicas;
CREATE OR REPLACE VIEW "DetalleVenta" AS SELECT * FROM detalle_ventas;
CREATE OR REPLACE VIEW "UsoProductoAtencion" AS SELECT * FROM uso_producto_atencions;
CREATE OR REPLACE VIEW "DetalleCompra" AS SELECT * FROM detalle_compras;
CREATE OR REPLACE VIEW "Lote" AS SELECT * FROM lotes;

-- ------------------------------------------------------------------------------
-- 1. CONSULTAS DE EJEMPLO
-- ------------------------------------------------------------------------------

-- Consultas Simples
SELECT * FROM "Persona" P INNER JOIN "Propietario" PR ON P.id_persona = PR.id_persona;
SELECT * FROM "Paciente" WHERE estado = 'ACTIVO';
SELECT * FROM "Servicio" WHERE precio_base > 50;

-- Consultas Múltiples (Join múltiple)
SELECT P.nombre AS mascota, PE.nombre AS dueno, PE.apellido1 
FROM "Paciente" P 
INNER JOIN "Propietario" PR ON P.id_propietario = PR.id_persona 
INNER JOIN "Persona" PE ON PR.id_persona = PE.id_persona;

-- Subconsultas (Filtro por promedio)
SELECT * FROM "Servicio" 
WHERE precio_base > (SELECT AVG(precio_base) FROM "Servicio");

-- ------------------------------------------------------------------------------
-- 2. PROCEDIMIENTOS ALMACENADOS Y FUNCIONES (PL/pgSQL)
-- ------------------------------------------------------------------------------

-- 1. Listar mascotas por propietario
CREATE OR REPLACE FUNCTION sp_mascotas_por_propietario(p_id_propietario INT)
RETURNS TABLE (
    id_paciente INT,
    nombre VARCHAR,
    raza VARCHAR,
    sexo VARCHAR,
    estado VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT P.id_paciente::INT, P.nombre::VARCHAR, R.nombre::VARCHAR AS raza, P.sexo::VARCHAR, P.estado::VARCHAR
    FROM pacientes P
    INNER JOIN razas R ON P.id_raza = R.id_raza
    WHERE P.id_propietario = p_id_propietario;
END;
$$;
-- Uso: SELECT * FROM sp_mascotas_por_propietario(1);

-- 2. Listar citas por fecha
CREATE OR REPLACE FUNCTION sp_citas_por_fecha(p_fecha DATE)
RETURNS TABLE (
    id_cita INT,
    mascota VARCHAR,
    profesional VARCHAR,
    fecha_hora_inicio TIMESTAMP,
    estado VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT C.id_cita::INT, PA.nombre::VARCHAR AS mascota, PE.nombre::VARCHAR AS profesional, 
           C.fecha_hora_inicio::TIMESTAMP, C.estado::VARCHAR
    FROM citas C
    INNER JOIN pacientes PA ON C.id_paciente = PA.id_paciente
    LEFT JOIN profesionales PR ON C.id_profesional = PR.id_persona
    LEFT JOIN personas PE ON PR.id_persona = PE.id_persona
    WHERE CAST(C.fecha_hora_inicio AS DATE) = p_fecha;
END;
$$;
-- Uso: SELECT * FROM sp_citas_por_fecha('2026-01-20');

-- 3. Listar atenciones de una mascota
CREATE OR REPLACE FUNCTION sp_atenciones_mascota(p_id_paciente INT)
RETURNS TABLE (
    id_atencion INT,
    fecha_hora_inicio TIMESTAMP,
    fecha_hora_fin TIMESTAMP,
    total_final NUMERIC,
    estado VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT A.id_atencion::INT, A.fecha_hora_inicio::TIMESTAMP, A.fecha_hora_fin::TIMESTAMP, 
           A.total_final::NUMERIC, A.estado::VARCHAR
    FROM atenciones A
    WHERE A.id_paciente = p_id_paciente
    ORDER BY A.fecha_hora_inicio DESC;
END;
$$;
-- Uso: SELECT * FROM sp_atenciones_mascota(1);

-- 4. Consultar productos por categoría
CREATE OR REPLACE FUNCTION sp_productos_por_categoria(p_categoria VARCHAR)
RETURNS TABLE (
    id_producto INT,
    nombre VARCHAR,
    precio_venta NUMERIC,
    stock_minimo NUMERIC,
    estado VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT P.id_producto::INT, P.nombre::VARCHAR, P.precio_venta::NUMERIC, P.stock_minimo::NUMERIC, P.estado::VARCHAR
    FROM productos P
    WHERE P.categoria = p_categoria
    ORDER BY P.nombre;
END;
$$;
-- Uso: SELECT * FROM sp_productos_por_categoria('ESTETICA');

-- 5. Ver compras por proveedor
CREATE OR REPLACE FUNCTION sp_compras_por_proveedor(p_id_proveedor INT)
RETURNS TABLE (
    id_compra INT,
    fecha_hora TIMESTAMP,
    total NUMERIC,
    estado VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT C.id_compra::INT, C.fecha_hora::TIMESTAMP, C.total::NUMERIC, C.estado::VARCHAR
    FROM compras C
    WHERE C.id_proveedor = p_id_proveedor
    ORDER BY C.fecha_hora DESC;
END;
$$;
-- Uso: SELECT * FROM sp_compras_por_proveedor(1);

-- 6. Listar cargos pendientes de un propietario
CREATE OR REPLACE FUNCTION sp_cargos_pendientes(p_id_propietario INT)
RETURNS TABLE (
    id_cargo INT,
    concepto VARCHAR,
    monto NUMERIC,
    descuento NUMERIC,
    saldo NUMERIC
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT CA.id_cargo::INT, CA.concepto::VARCHAR, CA.monto::NUMERIC, CA.descuento::NUMERIC, 
           (CA.monto - COALESCE(CA.descuento, 0))::NUMERIC AS saldo
    FROM cargos CA
    INNER JOIN cuentas CU ON CA.id_cuenta = CU.id_cuenta
    WHERE CU.id_propietario = p_id_propietario 
      AND CA.estado != 'PAGADO';
END;
$$;
-- Uso: SELECT * FROM sp_cargos_pendientes(1);

-- 7. Consultar productos con stock bajo
CREATE OR REPLACE FUNCTION sp_productos_stock_bajo()
RETURNS TABLE (
    nombre VARCHAR,
    stock_actual NUMERIC,
    stock_minimo NUMERIC
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT P.nombre::VARCHAR, COALESCE(SUM(M.cantidad), 0)::NUMERIC AS stock_actual, P.stock_minimo::NUMERIC
    FROM productos P
    LEFT JOIN movimiento_inventarios M ON P.id_producto = M.id_producto
    GROUP BY P.id_producto, P.nombre, P.stock_minimo
    HAVING COALESCE(SUM(M.cantidad), 0) < P.stock_minimo;
END;
$$;
-- Uso: SELECT * FROM sp_productos_stock_bajo();

-- 8. Ver recordatorios pendientes por mascota
CREATE OR REPLACE FUNCTION sp_recordatorios_pendientes(p_id_paciente INT)
RETURNS TABLE (
    id_recordatorio INT,
    tipo VARCHAR,
    fecha_hora_programada TIMESTAMP,
    mensaje TEXT,
    estado VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    RETURN QUERY
    SELECT R.id_recordatorio::INT, R.tipo::VARCHAR, R.fecha_hora_programada::TIMESTAMP, 
           R.mensaje::TEXT, R.estado::VARCHAR
    FROM recordatorios R
    WHERE R.id_paciente = p_id_paciente 
      AND R.estado = 'PENDIENTE'
    ORDER BY R.fecha_hora_programada;
END;
$$;
-- Uso: SELECT * FROM sp_recordatorios_pendientes(1);

-- 9. Registrar nuevo propietario
CREATE OR REPLACE PROCEDURE sp_registrar_propietario(
    IN p_documento VARCHAR,
    IN p_nombre VARCHAR,
    IN p_apellido1 VARCHAR,
    IN p_apellido2 VARCHAR,
    IN p_telefono VARCHAR,
    IN p_email VARCHAR,
    IN p_direccion VARCHAR,
    INOUT p_id_persona INT DEFAULT NULL
) LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO personas (documento, nombre, apellido1, apellido2, telefono, email, direccion)
    VALUES (p_documento, p_nombre, p_apellido1, p_apellido2, p_telefono, p_email, p_direccion)
    RETURNING id_persona INTO p_id_persona;

    INSERT INTO propietarios (id_persona) VALUES (p_id_persona);
END;
$$;
-- Uso: CALL sp_registrar_propietario('55667788', 'Roberto', 'Mamani', 'Quispe', '71234590', 'roberto@email.com', 'Calle Santa Cruz N° 123');

-- 10. Agregar nueva mascota
CREATE OR REPLACE PROCEDURE sp_agregar_mascota(
    IN p_nombre VARCHAR,
    IN p_id_raza INT,
    IN p_id_propietario INT,
    IN p_sexo VARCHAR,
    IN p_color VARCHAR,
    IN p_tamanio VARCHAR,
    INOUT p_id_paciente INT DEFAULT NULL
) LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO pacientes (nombre, id_raza, id_propietario, sexo, color, tamanio, estado)
    VALUES (p_nombre, p_id_raza, p_id_propietario, p_sexo, p_color, p_tamanio, 'ACTIVO')
    RETURNING id_paciente INTO p_id_paciente;
END;
$$;
-- Uso: CALL sp_agregar_mascota('Firulais', 1, 1, 'MACHO', 'CAFE', 'GRANDE');

-- 11. Agendar cita
CREATE OR REPLACE PROCEDURE sp_agendar_cita(
    IN p_id_paciente INT,
    IN p_id_profesional INT,
    IN p_fecha_hora_inicio TIMESTAMP,
    IN p_fecha_hora_fin TIMESTAMP,
    INOUT p_id_cita INT DEFAULT NULL
) LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO citas (id_paciente, id_profesional, fecha_hora_inicio, fecha_hora_fin, estado, origen)
    VALUES (p_id_paciente, p_id_profesional, p_fecha_hora_inicio, p_fecha_hora_fin, 'CONFIRMADA', 'APP')
    RETURNING id_cita INTO p_id_cita;
END;
$$;
-- Uso: CALL sp_agendar_cita(1, 6, '2026-02-10 09:00:00', '2026-02-10 09:30:00');

-- 12. Actualizar estado de cita
CREATE OR REPLACE PROCEDURE sp_actualizar_estado_cita(
    IN p_id_cita INT,
    IN p_nuevo_estado VARCHAR,
    IN p_motivo VARCHAR
) LANGUAGE plpgsql AS $$
BEGIN
    UPDATE citas
    SET estado = p_nuevo_estado,
        motivo_cancelacion = p_motivo
    WHERE id_cita = p_id_cita;
END;
$$;
-- Uso: CALL sp_actualizar_estado_cita(1, 'CANCELADA', 'Dueño no confirmó');

-- 13. Registrar pago y actualizar cargo
CREATE OR REPLACE PROCEDURE sp_registrar_pago(
    IN p_id_cargo INT,
    IN p_monto NUMERIC,
    IN p_id_metodo_pago INT
) LANGUAGE plpgsql AS $$
DECLARE
    v_id_pago INT;
    v_id_cuenta INT;
BEGIN
    SELECT id_cuenta INTO v_id_cuenta FROM cargos WHERE id_cargo = p_id_cargo;

    INSERT INTO pagos (id_cuenta, fecha_hora, estado)
    VALUES (v_id_cuenta, CURRENT_TIMESTAMP, 'COMPLETADO')
    RETURNING id_pago INTO v_id_pago;

    INSERT INTO detalle_pagos (id_pago, id_metodo_pago, monto)
    VALUES (v_id_pago, p_id_metodo_pago, p_monto);

    INSERT INTO aplicacion_pagos (id_pago, id_cargo, monto_aplicado)
    VALUES (v_id_pago, p_id_cargo, p_monto);

    UPDATE cargos SET estado = 'PAGADO' WHERE id_cargo = p_id_cargo;
END;
$$;
-- Uso: CALL sp_registrar_pago(2, 130.00, 1);

-- 14. Desactivar producto
CREATE OR REPLACE PROCEDURE sp_desactivar_producto(IN p_id_producto INT)
LANGUAGE plpgsql AS $$
BEGIN
    UPDATE productos
    SET estado = 'INACTIVO'
    WHERE id_producto = p_id_producto;
END;
$$;
-- Uso: CALL sp_desactivar_producto(8);

-- 15. Cancelar cita y eliminar sus detalles
CREATE OR REPLACE PROCEDURE sp_cancelar_cita(IN p_id_cita INT)
LANGUAGE plpgsql AS $$
BEGIN
    DELETE FROM detalle_citas WHERE id_cita = p_id_cita;
    DELETE FROM citas WHERE id_cita = p_id_cita;
END;
$$;
-- Uso: CALL sp_cancelar_cita(4);

-- ------------------------------------------------------------------------------
-- 3. TRIGGERS (Disparadores en PostgreSQL)
-- ------------------------------------------------------------------------------

-- TRIGGER 1: Al insertar mascota, crear ficha médica
CREATE OR REPLACE FUNCTION fn_crear_ficha_medica()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO ficha_medicas (
        id_paciente, fecha_apertura, antecedentes, alergias, 
        enfermedades_previas, cirugias_previas, medicamentos_habituales, observaciones
    ) VALUES (
        NEW.id_paciente, CURRENT_DATE, 'Sin antecedentes', 'Ninguna', 
        'Ninguna', 'Ninguna', 'Ninguno', 'Paciente nuevo'
    )
    ON CONFLICT (id_paciente) DO NOTHING;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_crear_ficha_medica ON pacientes;
CREATE TRIGGER tr_crear_ficha_medica
AFTER INSERT ON pacientes
FOR EACH ROW
EXECUTE FUNCTION fn_crear_ficha_medica();


-- TRIGGER 2: Al insertar atención, actualizar cita a COMPLETADA
CREATE OR REPLACE FUNCTION fn_actualizar_cita_al_atender()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.id_cita IS NOT NULL THEN
        UPDATE citas SET estado = 'COMPLETADA' WHERE id_cita = NEW.id_cita;
    END IF;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_actualizar_cita_al_atender ON atenciones;
CREATE TRIGGER tr_actualizar_cita_al_atender
AFTER INSERT ON atenciones
FOR EACH ROW
EXECUTE FUNCTION fn_actualizar_cita_al_atender();


-- TRIGGER 3: Al insertar detalle de venta, descontar stock
CREATE OR REPLACE FUNCTION fn_actualizar_stock_venta()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_user_id BIGINT;
BEGIN
    SELECT id_usuario INTO v_user_id FROM usuarios LIMIT 1;
    IF v_user_id IS NULL THEN
        v_user_id := 1;
    END IF;

    INSERT INTO movimiento_inventarios (
        id_producto, id_usuario, id_detalle_venta, fecha_hora, tipo_movimiento, cantidad, motivo
    ) VALUES (
        NEW.id_producto, v_user_id, NEW.id_detalle_venta, CURRENT_TIMESTAMP, 'SALIDA', -NEW.cantidad, 'VENTA'
    );
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_actualizar_stock_venta ON detalle_ventas;
CREATE TRIGGER tr_actualizar_stock_venta
AFTER INSERT ON detalle_ventas
FOR EACH ROW
EXECUTE FUNCTION fn_actualizar_stock_venta();


-- TRIGGER 4: Al insertar uso de producto en atención, descontar inventario
CREATE OR REPLACE FUNCTION fn_descontar_inventario_atencion()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_user_id BIGINT;
BEGIN
    SELECT id_usuario INTO v_user_id FROM usuarios LIMIT 1;
    IF v_user_id IS NULL THEN
        v_user_id := 1;
    END IF;

    INSERT INTO movimiento_inventarios (
        id_producto, id_usuario, id_uso, fecha_hora, tipo_movimiento, cantidad, motivo
    ) VALUES (
        NEW.id_producto, v_user_id, NEW.id_uso, CURRENT_TIMESTAMP, 'SALIDA', -NEW.cantidad, 'USO_ATENCION'
    );
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_descontar_inventario_atencion ON uso_producto_atencions;
CREATE TRIGGER tr_descontar_inventario_atencion
AFTER INSERT ON uso_producto_atencions
FOR EACH ROW
EXECUTE FUNCTION fn_descontar_inventario_atencion();


-- TRIGGER 5: Al insertar detalle de compra, sumar inventario
CREATE OR REPLACE FUNCTION fn_agregar_inventario_compra()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_user_id BIGINT;
BEGIN
    SELECT id_usuario INTO v_user_id FROM usuarios LIMIT 1;
    IF v_user_id IS NULL THEN
        v_user_id := 1;
    END IF;

    INSERT INTO movimiento_inventarios (
        id_producto, id_usuario, id_detalle_compra, fecha_hora, tipo_movimiento, cantidad, motivo
    ) VALUES (
        NEW.id_producto, v_user_id, NEW.id_detalle_compra, CURRENT_TIMESTAMP, 'ENTRADA', NEW.cantidad, 'COMPRA'
    );
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_agregar_inventario_compra ON detalle_compras;
CREATE TRIGGER tr_agregar_inventario_compra
AFTER INSERT ON detalle_compras
FOR EACH ROW
EXECUTE FUNCTION fn_agregar_inventario_compra();


-- TRIGGER 6: Validar que el pago no supere el saldo del cargo
CREATE OR REPLACE FUNCTION fn_validar_monto_pago()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_monto_cargo NUMERIC;
    v_monto_pagado NUMERIC;
BEGIN
    SELECT (monto - COALESCE(descuento, 0)) INTO v_monto_cargo FROM cargos WHERE id_cargo = NEW.id_cargo;
    SELECT COALESCE(SUM(monto_aplicado), 0) INTO v_monto_pagado FROM aplicacion_pagos WHERE id_cargo = NEW.id_cargo;

    IF (v_monto_pagado + NEW.monto_aplicado) > v_monto_cargo THEN
        RAISE EXCEPTION 'El monto pagado supera el saldo del cargo';
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_validar_monto_pago ON aplicacion_pagos;
CREATE TRIGGER tr_validar_monto_pago
BEFORE INSERT ON aplicacion_pagos
FOR EACH ROW
EXECUTE FUNCTION fn_validar_monto_pago();


-- TRIGGER 7: Al insertar cita, crear recordatorio automático
CREATE OR REPLACE FUNCTION fn_crear_recordatorio_cita()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO recordatorios (
        id_paciente, id_cita, tipo, fecha_hora_programada, medio_envio, mensaje, estado
    ) VALUES (
        NEW.id_paciente, NEW.id_cita, 'CITA', NEW.fecha_hora_inicio, 'EMAIL', 
        'Recordatorio de cita para el ' || NEW.fecha_hora_inicio::TEXT, 'PENDIENTE'
    );
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_crear_recordatorio_cita ON citas;
CREATE TRIGGER tr_crear_recordatorio_cita
AFTER INSERT ON citas
FOR EACH ROW
EXECUTE FUNCTION fn_crear_recordatorio_cita();


-- TRIGGER 8: Al cancelar cita, liberar el profesional asignado
CREATE OR REPLACE FUNCTION fn_liberar_profesional()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.estado = 'CANCELADA' AND OLD.estado != 'CANCELADA' THEN
        NEW.id_profesional := NULL;
    END IF;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_liberar_profesional ON citas;
CREATE TRIGGER tr_liberar_profesional
BEFORE UPDATE ON citas
FOR EACH ROW
EXECUTE FUNCTION fn_liberar_profesional();


-- TRIGGER 9: Validar que la cita esté confirmada antes de atender
CREATE OR REPLACE FUNCTION fn_validar_cita_atencion()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_estado VARCHAR;
BEGIN
    IF NEW.id_cita IS NOT NULL THEN
        SELECT estado INTO v_estado FROM citas WHERE id_cita = NEW.id_cita;

        IF v_estado IS DISTINCT FROM 'CONFIRMADA' AND v_estado IS DISTINCT FROM 'EN_SALA' AND v_estado IS DISTINCT FROM 'AGENDADA' THEN
            RAISE EXCEPTION 'La cita debe estar confirmada para generar una atencion';
        END IF;
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_validar_cita_atencion ON atenciones;
CREATE TRIGGER tr_validar_cita_atencion
BEFORE INSERT ON atenciones
FOR EACH ROW
EXECUTE FUNCTION fn_validar_cita_atencion();


-- TRIGGER 10: Al insertar producto con control de lote, generar lote inicial
CREATE OR REPLACE FUNCTION fn_crear_lote_producto()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.control_lotes IS TRUE THEN
        INSERT INTO lotes (id_producto, numero_lote, fecha_vencimiento, estado)
        VALUES (
            NEW.id_producto, 
            'LOT-' || NEW.id_producto || '-INICIAL', 
            CURRENT_DATE + INTERVAL '1 year', 
            'ACTIVO'
        );
    END IF;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_crear_lote_producto ON productos;
CREATE TRIGGER tr_crear_lote_producto
AFTER INSERT ON productos
FOR EACH ROW
EXECUTE FUNCTION fn_crear_lote_producto();
