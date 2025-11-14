<?php 
$title = 'Listado de Empresas';
?>
<div class="container">
        <h1>🏢 Empresas</h1>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($empresas)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px;">
                        No hay empresas registradas
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($empresas as $empresa): ?>
                    <tr>
                        <td><?= htmlspecialchars($empresa['id']) ?></td>
                        <td><?= htmlspecialchars($empresa['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($empresa['dni'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="<?= $this->url('/datos') ?>" class="btn">Volver</a>
    </div>
