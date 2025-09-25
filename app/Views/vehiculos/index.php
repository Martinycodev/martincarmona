<?php 
$title = 'Listado de Vehículos';
?>
<div class="container">
        <h1>🚗 Vehículos</h1>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Matrícula</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiculos as $vehiculo): ?>
                <tr>
                    <td><?= htmlspecialchars($vehiculo['id']) ?></td>
                    <td><?= htmlspecialchars($vehiculo['marca']) ?></td>
                    <td><?= htmlspecialchars($vehiculo['modelo']) ?></td>
                    <td><?= htmlspecialchars($vehiculo['matricula']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= $this->url('/datos') ?>" class="btn">Volver</a>
    </div>
</body>
</html>
