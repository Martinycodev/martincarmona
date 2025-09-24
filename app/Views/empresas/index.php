<?php 
$title = 'Listado de Empresas';
include BASE_PATH . '/app/Views/layouts/header.php'; 
?>
        <h1>🏢 Empresas</h1>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>CIF</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empresas as $empresa): ?>
                <tr>
                    <td><?= htmlspecialchars($empresa['id']) ?></td>
                    <td><?= htmlspecialchars($empresa['nombre']) ?></td>
                    <td><?= htmlspecialchars($empresa['cif']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= $this->url('/datos') ?>" class="btn">Volver</a>
    </div>
</body>
</html>
