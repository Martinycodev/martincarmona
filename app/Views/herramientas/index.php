<?php 
$title = 'Listado de Herramientas';
include BASE_PATH . '/app/Views/layouts/header.php'; 
?>
        <h1>🔧 Herramientas</h1>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($herramientas as $herramienta): ?>
                <tr>
                    <td><?= htmlspecialchars($herramienta['id']) ?></td>
                    <td><?= htmlspecialchars($herramienta['nombre']) ?></td>
                    <td><?= htmlspecialchars($herramienta['tipo']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= $this->url('/datos') ?>" class="btn">Volver</a>
    </div>
</body>
</html>
