<?php
if (!function_exists('renderFormularioPersonaAmpliado')) {
    function renderFormularioPersonaAmpliado($nombreBoton = 'crear', $textoBoton = 'Registrar persona') {
        $sacramentos = [
            'Bautizo' => ['titulo' => 'Bautizo', 'icono' => 'bi-droplet-fill', 'documento' => 'Certificado de Bautizo'],
            'PrimeraComunion' => ['titulo' => 'Primera Comunión', 'icono' => 'bi-award-fill', 'documento' => 'Recuerdo de Primera Comunión (opcional)'],
            'Confirmacion' => ['titulo' => 'Confirmación', 'icono' => 'bi-send-fill', 'documento' => 'Certificado de Confirmación'],
            'Matrimonio' => ['titulo' => 'Matrimonio', 'icono' => 'bi-link-45deg', 'documento' => 'Certificado de Matrimonio']
        ];
        ?>
        <form method="POST" enctype="multipart/form-data" class="persona-form" data-persona-form novalidate>
          <section class="persona-seccion">
            <h6 class="persona-seccion-titulo"><i class="bi bi-person-fill"></i> Datos personales</h6>
            <div class="row g-3">
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> Tipo de documento:</label>
                <select class="form-select" name="TipoDocumento" data-tipo-documento required>
                  <option value="CI boliviano">CI boliviano</option>
                  <option value="Pasaporte">Pasaporte</option>
                  <option value="Documento extranjero">Documento extranjero</option>
                  <option value="Sin documento">Sin documento</option>
                </select>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger" data-documento-obligatorio>*</span> Número de documento:</label>
                <input type="text" class="form-control" name="NumeroDocumento" data-numero-documento maxlength="30" placeholder="Número de documento" required>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> Nombre:</label>
                <input type="text" class="form-control" name="Nombre" maxlength="25" required>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label">Apellido paterno:</label>
                <input type="text" class="form-control" name="ApPaterno" maxlength="20">
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label">Apellido materno:</label>
                <input type="text" class="form-control" name="ApMaterno" maxlength="20">
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> Sexo:</label>
                <select class="form-select" name="Sexo" required>
                  <option value="">Seleccionar...</option><option value="Varon">Varón</option><option value="Mujer">Mujer</option>
                </select>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> Fecha de nacimiento:</label>
                <input type="date" class="form-control" name="FechaNac" data-fecha-nacimiento required>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> Estado civil:</label>
                <select name="Estado_per" class="form-select" required>
                  <option value="">Seleccionar...</option><option>Soltero</option><option>Soltera</option><option>Casado</option><option>Casada</option><option>Viudo</option><option>Viuda</option>
                </select>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> Nacionalidad:</label>
                <input type="text" class="form-control" name="Nacionalidad" value="Boliviana" maxlength="60" list="nacionalidadesPersona" required>
                <datalist id="nacionalidadesPersona"><option value="Boliviana"><option value="Argentina"><option value="Brasilera"><option value="Chilena"><option value="Peruana"><option value="Paraguaya"></datalist>
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label"><span class="text-danger">*</span> ¿Qué solicita?</label>
                <select name="SacramentoSolicita" class="form-select" data-sacramento-solicita required>
                  <option value="">Seleccionar...</option>
                  <optgroup label="Reserva">
                    <option value="Reserva de Bautizo">Bautizo</option>
                    <option value="Reserva de Matrimonio">Matrimonio</option>
                    <option value="Reserva de misa">Misa</option>
                  </optgroup>
                  <optgroup label="Certificado">
                    <option value="Certificado de Bautizo">Bautizo</option>
                    <option value="Certificado de Confirmación">Confirmación</option>
                    <option value="Certificado de Matrimonio">Matrimonio</option>
                  </optgroup>
                  <option value="Registro pastoral">Registro pastoral</option>
                </select>
              </div>
              <div class="col-lg-3 col-md-6"><label class="form-label">Zona / Sector:</label><input type="text" class="form-control" name="ZonaSector" maxlength="80" placeholder="Ej.: San Pedro Alto"></div>
              <div class="col-lg-3 col-md-6"><label class="form-label">Dirección:</label><input type="text" class="form-control" name="Direccion" maxlength="40" placeholder="Referencia del domicilio"></div>
              <div class="col-lg-3 col-md-6"><label class="form-label"><span class="text-danger">*</span> Contacto:</label><input type="text" class="form-control" name="Contacto" maxlength="15" required></div>
              <div class="col-lg-3 col-md-6"><label class="form-label">Teléfono alternativo:</label><input type="text" class="form-control" name="TelefonoAlternativo" maxlength="15"></div>
              <div class="col-lg-3 col-md-6"><label class="form-label">Correo electrónico:</label><input type="email" class="form-control" name="Correo" maxlength="50"></div>
            </div>
          </section>

          <section class="persona-seccion mt-3">
            <h6 class="persona-seccion-titulo"><i class="bi bi-award-fill"></i> Antecedentes sacramentales</h6>
            <div class="row g-3">
            <?php foreach ($sacramentos as $clave => $config): ?>
              <div class="col-xl-3 col-md-6">
                <article class="antecedente-card" data-antecedente-card>
                  <header><span><i class="bi <?= $config['icono'] ?>"></i> <?= htmlspecialchars($config['titulo']) ?></span><i class="bi bi-chevron-down" data-chevron></i></header>
                  <div class="antecedente-pregunta">
                    <strong>¿Realizó este sacramento?</strong>
                    <label><input type="radio" name="Antecedentes[<?= $clave ?>][Realizado]" value="Si"> Sí</label>
                    <label><input type="radio" name="Antecedentes[<?= $clave ?>][Realizado]" value="No" checked> No</label>
                  </div>
                  <?php if ($clave === 'Matrimonio'): ?><p class="antecedente-ayuda">Registro de antecedente; no crea una reserva.</p><?php endif; ?>
                  <div class="antecedente-detalle d-none" data-antecedente-detalle>
                    <div class="row g-2">
                      <div class="col-12">
                        <label class="form-label">País *</label>
                        <select class="form-select form-select-sm" data-pais-selector><option value="Bolivia">Bolivia</option><option value="Otro">Otro país</option></select>
                        <input type="hidden" name="Antecedentes[<?= $clave ?>][Pais]" value="Bolivia" data-pais-valor>
                        <input type="text" class="form-control form-control-sm mt-2 d-none" data-pais-manual maxlength="60" placeholder="Escriba el país">
                      </div>
                      <div class="col-12" data-ubicacion-bolivia>
                        <label class="form-label">Departamento *</label>
                        <select class="form-select form-select-sm" name="Antecedentes[<?= $clave ?>][Departamento]" data-campo-detalle>
                          <option value="">Seleccionar...</option><option>Beni</option><option>Chuquisaca</option><option>Cochabamba</option><option>La Paz</option><option>Oruro</option><option>Pando</option><option>Potosí</option><option>Santa Cruz</option><option>Tarija</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <label class="form-label">Provincia</label>
                        <select class="form-select form-select-sm" name="Antecedentes[<?= $clave ?>][Provincia]" data-provincia data-campo-detalle><option value="">Seleccionar...</option></select>
                        <label class="form-check-label small mt-1"><input type="checkbox" class="form-check-input" data-provincia-manual-check> Ingresar manualmente</label>
                        <input type="text" class="form-control form-control-sm mt-1 d-none" name="Antecedentes[<?= $clave ?>][ProvinciaManual]" data-provincia-manual maxlength="80" placeholder="Escriba la provincia">
                      </div>
                      <div class="col-6">
                        <label class="form-label">Municipio</label>
                        <select class="form-select form-select-sm" name="Antecedentes[<?= $clave ?>][Municipio]" data-municipio data-campo-detalle><option value="">Seleccionar...</option></select>
                        <label class="form-check-label small mt-1"><input type="checkbox" class="form-check-input" data-municipio-manual-check> Ingresar manualmente</label>
                        <input type="text" class="form-control form-control-sm mt-1 d-none" name="Antecedentes[<?= $clave ?>][MunicipioManual]" data-municipio-manual maxlength="100" placeholder="Escriba el municipio">
                      </div>
                      <div class="col-12"><label class="form-label">Parroquia *</label><input type="text" class="form-control form-control-sm" name="Antecedentes[<?= $clave ?>][Parroquia]" maxlength="150" data-campo-detalle></div>
                      <div class="col-12"><label class="form-label">Fecha</label><input type="date" class="form-control form-control-sm" name="Antecedentes[<?= $clave ?>][Fecha]" data-fecha-sacramento data-campo-detalle></div>
                      <?php if ($clave !== 'PrimeraComunion'): ?>
                        <div class="col-4"><label class="form-label">Libro</label><input type="text" class="form-control form-control-sm" name="Antecedentes[<?= $clave ?>][Libro]" maxlength="40"></div>
                        <div class="col-4"><label class="form-label">Folio</label><input type="text" class="form-control form-control-sm" name="Antecedentes[<?= $clave ?>][Pagina]" maxlength="40"></div>
                        <div class="col-4"><label class="form-label">Partida</label><input type="text" class="form-control form-control-sm" name="Antecedentes[<?= $clave ?>][Partida]" maxlength="40"></div>
                      <?php endif; ?>
                      <?php if ($clave === 'Matrimonio'): ?>
                        <div class="col-12"><label class="form-label">Nombre del cónyuge</label><input type="text" class="form-control form-control-sm" name="Antecedentes[<?= $clave ?>][NombreConyuge]" maxlength="120"></div>
                      <?php endif; ?>
                      <div class="col-12"><label class="form-label"><?= htmlspecialchars($config['documento']) ?></label><input type="file" class="form-control form-control-sm" name="DocumentoAntecedente[<?= $clave ?>]" accept=".jpg,.jpeg,.png,.pdf"></div>
                    </div>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
            </div>
            <div class="alert alert-primary-soft mt-3 mb-0 py-2"><i class="bi bi-info-circle-fill me-2"></i>Los detalles se solicitan únicamente cuando la persona indica que ya realizó el sacramento.</div>
            <div class="alert alert-warning mt-2 mb-0 py-2 d-none" data-alerta-matrimonio><i class="bi bi-exclamation-triangle-fill me-2"></i>Esta persona registra un matrimonio religioso anterior. Requiere revisión del párroco antes de continuar.</div>
          </section>

          <section class="persona-seccion mt-3">
            <h6 class="persona-seccion-titulo"><i class="bi bi-file-text-fill"></i> Observaciones pastorales</h6>
            <textarea class="form-control" name="Observaciones" rows="2" maxlength="1000" placeholder="Notas internas del párroco o secretaría"></textarea>
          </section>
          <div class="d-flex align-items-center justify-content-between mt-3">
            <small class="text-muted"><span class="text-danger">*</span> Campos obligatorios</small>
            <div><button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="<?= htmlspecialchars($nombreBoton) ?>" class="btn btn-success-soft"><?= htmlspecialchars($textoBoton) ?></button></div>
          </div>
        </form>
        <?php
    }
}
?>
