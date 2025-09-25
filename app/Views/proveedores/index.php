<?php 
$title = 'Listado de Proveedores';
?>
<div class="container">
        <h1>🚚 Proveedores</h1>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Contacto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proveedores as $proveedor): ?>
                <tr>
                    <td><?= htmlspecialchars($proveedor['id']) ?></td>
                    <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                    <td><?= htmlspecialchars($proveedor['contacto']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= $this->url('/datos') ?>" class="btn">Volver</a>
    </div>
</body>
</html>
