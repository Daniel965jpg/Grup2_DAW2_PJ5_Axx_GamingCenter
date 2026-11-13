<?php
// TROZO COMÚN: TARJETA DE UN PUESTO
// La incluyen sala.php y ocupados.php dentro de su foreach, así no repetimos este HTML.
// Usa la variable $puesto del foreach, que cada vista deja preparada con:
// enlace, accion, tipo_sala, clase_estado, texto_estado, imagen y nombre
// (y en ocupados.php también sala y ocupado_por).
?>
<a href="<?php echo $puesto['enlace']; ?>" title="<?php echo $puesto['accion'] . ' ' . htmlspecialchars($puesto['nombre']); ?>" data-tipo="<?php echo $puesto['tipo_sala']; ?>" class="tarjeta-puesto <?php echo $puesto['clase_estado']; ?>">
    <div class="indicador-estado">
        <span class="icono-pantalla"></span>
        <span class="texto-estado"><?php echo $puesto['texto_estado']; ?></span>
    </div>
    <div class="contenedor-imagen">
        <img src="<?php echo $puesto['imagen']; ?>" alt="<?php echo htmlspecialchars($puesto['nombre']); ?>" class="imagen-puesto">
    </div>
    <div class="nombre-puesto"><?php echo htmlspecialchars($puesto['nombre']); ?></div>
    <?php if (isset($puesto['sala'])) { ?>
        <div class="dato-puesto"><?php echo htmlspecialchars($puesto['sala']); ?></div>
    <?php } ?>
    <?php if (!empty($puesto['ocupado_por'])) { ?>
        <div class="dato-puesto">Ocupado por <?php echo htmlspecialchars($puesto['ocupado_por']); ?></div>
    <?php } ?>
</a>
