<?php
// TARJETA DE UN PUESTO
// La incluyen sala.php y ocupados.php dentro de su foreach, con $puesto ya preparado.

// Al pulsar, script.js pide confirmación; data-tipo le da el color de la zona
?>
<a href="<?php echo $puesto['enlace']; ?>" title="<?php echo $puesto['accion'] . ' ' . htmlspecialchars($puesto['nombre']); ?>" data-tipo="<?php echo $puesto['tipo_sala']; ?>" class="tarjeta-puesto <?php echo $puesto['clase_estado']; ?>" onclick="return confirmarCambio(this)">
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
