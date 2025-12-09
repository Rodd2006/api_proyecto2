<?php
require_once __DIR__ . '/../core/Model.php';

class Carrito extends Model {

    /* ================================
       OBTENER CARRITO
    ================================= */
    public function obtener(int $idUsuario): array {

        $carritoId = $this->asegurarCarritoAbierto($idUsuario);

        $sql = "SELECT 
                    dc.id_detalle_carrito,
                    dc.id_producto,
                    dc.cantidad,
                    dc.precio_unitario,
                    (dc.cantidad * dc.precio_unitario) AS subtotal,
                    p.nombre,
                    p.descripcion,
                    p.imagen
                FROM detalles_carrito dc
                INNER JOIN products p ON p.id = dc.id_producto
                WHERE dc.id_carrito = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$carritoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ================================
       AGREGAR PRODUCTO
    ================================= */
    /* 
    public function agregar(int $idUsuario, int $idProducto, int $cantidad, float $precioUnit): bool {

        $carritoId = $this->asegurarCarritoAbierto($idUsuario);

        // 🔥 SANEAR PRECIO
        $precioUnit = floatval($precioUnit);
        $precioUnit = number_format($precioUnit, 2, '.', '');

        // ¿Ya existe?
        $check = $this->db->prepare("
            SELECT id_detalle_carrito, cantidad 
            FROM detalles_carrito 
            WHERE id_carrito = ? AND id_producto = ?
        ");

        $check->execute([$carritoId, $idProducto]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nuevaCantidad = $row['cantidad'] + $cantidad;

            $upd = $this->db->prepare("
                UPDATE detalles_carrito
                SET cantidad = ?, precio_unitario = ?
                WHERE id_detalle_carrito = ?
            ");

            return $upd->execute([$nuevaCantidad, $precioUnit, $row['id_detalle_carrito']]);
        }

        // 🔥 INSERT NUEVO REGISTRO
        $ins = $this->db->prepare("
            INSERT INTO detalles_carrito (id_carrito, id_producto, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)
        ");

        $ok = $ins->execute([$carritoId, $idProducto, $cantidad, $precioUnit]);

        if (!$ok) {
            error_log("ERROR INSERTANDO DETALLE CARRITO: " . print_r($ins->errorInfo(), true));
        }

        return $ok;
    }
 */
    public function agregar(int $idUsuario, int $idProducto, int $cantidad, float $precioUnit): bool {

        // Obtiene o crea el carrito del usuario.
        $carritoId = $this->asegurarCarritoAbierto($idUsuario);

        // Primero revisa si ya existe ese producto dentro del carrito.
        $check = $this->db->prepare("
            SELECT id_detalle_carrito, cantidad 
            FROM detalles_carrito 
            WHERE id_carrito = ? AND id_producto = ?
        ");
        $check->execute([$carritoId, $idProducto]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        // Si ya existe, se actualiza la cantidad sumando la nueva.
        if ($row) {
            $nueva = (int)$row['cantidad'] + $cantidad;

            // Se actualiza cantidad y precio unitario.
            $upd = $this->db->prepare("
                UPDATE detalles_carrito 
                SET cantidad = ?, precio_unitario = ? 
                WHERE id_detalle_carrito = ?
            ");
            return $upd->execute([$nueva, $precioUnit, $row['id_detalle_carrito']]);
        } 
        
        // Si no existe, se inserta una nueva fila.
        $ins = $this->db->prepare("
            INSERT INTO detalles_carrito (id_carrito, id_producto, cantidad, precio_unitario) 
            VALUES (?, ?, ?, ?)
        ");
        return $ins->execute([$carritoId, $idProducto, $cantidad, $precioUnit]);
    }


    /* ================================
       ACTUALIZAR CANTIDAD DIRECTO
    ================================= */
    public function actualizarDetalle(int $idDetalleCarrito, int $cantidad): bool {

        if ($cantidad <= 0) {
            $del = $this->db->prepare("
                DELETE FROM detalles_carrito 
                WHERE id_detalle_carrito = ?
            ");
            return $del->execute([$idDetalleCarrito]);
        }

        $upd = $this->db->prepare("
            UPDATE detalles_carrito
            SET cantidad = ?
            WHERE id_detalle_carrito = ?
        ");

        return $upd->execute([$cantidad, $idDetalleCarrito]);
    }

    /* ================================
       ELIMINAR ITEM
    ================================= */
    public function eliminarDetalle(int $idDetalleCarrito): bool {
        $del = $this->db->prepare("
            DELETE FROM detalles_carrito 
            WHERE id_detalle_carrito = ?
        ");
        return $del->execute([$idDetalleCarrito]);
    }

    /* ================================
       VACIAR CARRITO
    ================================= */
    public function vaciar(int $idUsuario): bool {

        $carritoId = $this->asegurarCarritoAbierto($idUsuario);

        $del = $this->db->prepare("
            DELETE FROM detalles_carrito 
            WHERE id_carrito = ?
        ");

        return $del->execute([$carritoId]);
    }

    /* ================================
       ASEGURAR CARRITO ABIERTO
    ================================= */
    private function asegurarCarritoAbierto(int $idUsuario): int {

        $sel = $this->db->prepare("
            SELECT id_carrito
            FROM carrito
            WHERE id_usuario = ? AND estado = 'abierto'
            LIMIT 1
        ");

        $sel->execute([$idUsuario]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);

        if ($row) return $row['id_carrito'];

        $ins = $this->db->prepare("
            INSERT INTO carrito (id_usuario, estado)
            VALUES (?, 'abierto')
        ");

        $ins->execute([$idUsuario]);

        return (int)$this->db->lastInsertId();
    }
}
