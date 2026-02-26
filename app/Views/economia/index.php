<?php $title = 'Economía — Dashboard'; ?>

<style>
.eco-tabs { display:flex; gap:0; border-bottom:2px solid #333; margin-bottom:24px; }
.eco-tabs a {
    padding:10px 20px; text-decoration:none; color:#aaa; font-size:.9rem;
    border-bottom:3px solid transparent; margin-bottom:-2px; transition:color .2s;
}
.eco-tabs a.active { color:#fff; border-bottom-color:#4caf50; }
.eco-tabs a:hover  { color:#fff; }

.eco-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:32px; }
.eco-card {
    background:#2a2a2a; border-radius:10px; padding:20px 24px;
    border-left:4px solid #555;
}
.eco-card.banco    { border-left-color:#2196f3; }
.eco-card.efectivo { border-left-color:#4caf50; }
.eco-card.deuda    { border-left-color:#f44336; }
.eco-card .label { font-size:.8rem; color:#888; margin-bottom:6px; text-transform:uppercase; letter-spacing:.05em; }
.eco-card .valor { font-size:1.6rem; font-weight:700; color:#fff; }
.eco-card .valor.neg { color:#f44336; }
.eco-card .sub   { font-size:.8rem; color:#666; margin-top:4px; }

.eco-section-title { font-size:1rem; font-weight:600; color:#ccc; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; }
.eco-section-title a { font-size:.8rem; color:#4caf50; text-decoration:none; font-weight:400; }
.eco-section-title a:hover { text-decoration:underline; }

.eco-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.eco-table th { background:#222; color:#888; font-weight:500; text-align:left; padding:10px 12px; border-bottom:1px solid #333; font-size:.8rem; text-transform:uppercase; }
.eco-table td { padding:10px 12px; border-bottom:1px solid #2a2a2a; color:#ccc; vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tr:hover td { background:#1e1e1e; }

.badge-cuenta { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; }
.badge-banco    { background:#1565c0; color:#90caf9; }
.badge-efectivo { background:#2e7d32; color:#a5d6a7; }

.importe-gasto   { color:#f44336; font-weight:600; }
.importe-ingreso { color:#4caf50; font-weight:600; }

.eco-two-col { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
@media(max-width:700px){ .eco-two-col { grid-template-columns:1fr; } }
.eco-empty { color:#666; font-size:.875rem; padding:16px 0; }
</style>

<div class="container">

    <nav class="eco-tabs">
        <a href="<?= $this->url('/economia') ?>" class="active">Dashboard</a>
        <a href="<?= $this->url('/economia/gastos') ?>">Gastos</a>
        <a href="<?= $this->url('/economia/ingresos') ?>">Ingresos</a>
        <a href="<?= $this->url('/economia/deudas') ?>">Deudas trabajadores</a>
    </nav>

    <!-- Cards resumen -->
    <div class="eco-cards">
        <?php
        $sb = (float)($resumen['saldo_banco']    ?? 0);
        $se = (float)($resumen['saldo_efectivo'] ?? 0);
        ?>
        <div class="eco-card banco">
            <div class="label">Saldo banco</div>
            <div class="valor <?= $sb < 0 ? 'neg' : '' ?>"><?= number_format($sb, 2, ',', '.') ?> €</div>
            <div class="sub">Ingresos − gastos en banco</div>
        </div>
        <div class="eco-card efectivo">
            <div class="label">Saldo efectivo</div>
            <div class="valor <?= $se < 0 ? 'neg' : '' ?>"><?= number_format($se, 2, ',', '.') ?> €</div>
            <div class="sub">Ingresos − gastos en efectivo</div>
        </div>
        <div class="eco-card deuda">
            <div class="label">Deuda trabajadores</div>
            <div class="valor neg"><?= number_format($deudaPendiente, 2, ',', '.') ?> €</div>
            <div class="sub">Pagos mensuales pendientes</div>
        </div>
    </div>

    <!-- Últimos movimientos -->
    <div class="eco-two-col">
        <div>
            <div class="eco-section-title">
                Últimos gastos
                <a href="<?= $this->url('/economia/gastos') ?>">Ver todos</a>
            </div>
            <?php if (empty($ultimosGastos)): ?>
                <p class="eco-empty">Sin gastos registrados aún.</p>
            <?php else: ?>
            <table class="eco-table">
                <thead><tr><th>Fecha</th><th>Concepto</th><th>Importe</th><th>Cuenta</th></tr></thead>
                <tbody>
                <?php foreach ($ultimosGastos as $g): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($g['fecha'])) ?></td>
                    <td><?= htmlspecialchars($g['concepto']) ?></td>
                    <td class="importe-gasto">−<?= number_format($g['importe'], 2, ',', '.') ?> €</td>
                    <td><span class="badge-cuenta badge-<?= $g['cuenta'] ?? 'banco' ?>"><?= ucfirst($g['cuenta'] ?? 'banco') ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div>
            <div class="eco-section-title">
                Últimos ingresos
                <a href="<?= $this->url('/economia/ingresos') ?>">Ver todos</a>
            </div>
            <?php if (empty($ultimosIngresos)): ?>
                <p class="eco-empty">Sin ingresos registrados aún.</p>
            <?php else: ?>
            <table class="eco-table">
                <thead><tr><th>Fecha</th><th>Concepto</th><th>Importe</th><th>Cuenta</th></tr></thead>
                <tbody>
                <?php foreach ($ultimosIngresos as $i): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($i['fecha'])) ?></td>
                    <td><?= htmlspecialchars($i['concepto']) ?></td>
                    <td class="importe-ingreso">+<?= number_format($i['importe'], 2, ',', '.') ?> €</td>
                    <td><span class="badge-cuenta badge-<?= $i['cuenta'] ?? 'banco' ?>"><?= ucfirst($i['cuenta'] ?? 'banco') ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>
