// ====== CONFIGURACIÓN DE CARGA DE ARCHIVOS ======
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');
  const fileList = document.getElementById('fileList');
  const totalFilesInput = document.getElementById('totalFiles');
  const allowedFormats = ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/jpeg', 'image/png'];
  const maxFileSize = 10 * 1024 * 1024; // 10MB
  let uploadedFiles = [];

  // ====== EVENTOS DE DRAG & DROP ======
  dropZone.addEventListener('click', () => {
    fileInput.click();
  });

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.backgroundColor = '#e8f5e9';
    dropZone.style.borderColor = '#4caf50';
    dropZone.classList.add('shadow');
  });

  dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.backgroundColor = 'transparent';
    dropZone.style.borderColor = '#ffc107';
    dropZone.classList.remove('shadow');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.backgroundColor = 'transparent';
    dropZone.style.borderColor = '#ffc107';
    dropZone.classList.remove('shadow');
    handleFiles(e.dataTransfer.files);
  });

  fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
  });

  // ====== MANEJO DE ARCHIVOS ======
  function handleFiles(files) {
    let validFiles = [];
    let invalidFiles = [];

    for (let file of files) {
      // Validar formato
      if (!allowedFormats.includes(file.type)) {
        invalidFiles.push(`${file.name} (formato no permitido)`);
        continue;
      }

      // Validar tamaño
      if (file.size > maxFileSize) {
        invalidFiles.push(`${file.name} (superior a 10MB)`);
        continue;
      }

      validFiles.push(file);
    }

    if (invalidFiles.length > 0) {
      let mensaje = 'Los siguientes archivos no se pueden agregar:\n\n';
      invalidFiles.forEach(f => {
        mensaje += '• ' + f + '\n';
      });
      alert(mensaje);
    }

    if (validFiles.length > 0) {
      uploadedFiles = validFiles;
      displayFiles(validFiles);
      totalFilesInput.value = validFiles.length;
      updateSummary();
    }
  }

  function displayFiles(files) {
    fileList.innerHTML = '';

    if (files.length === 0) {
      fileList.innerHTML = '<p class="text-muted text-center"><i class="fas fa-inbox mr-2"></i>Ningún archivo seleccionado</p>';
      return;
    }

    let html = '<div class="alert alert-success mb-3"><strong><i class="fas fa-check-circle mr-2"></i>' + files.length + ' archivo(s) seleccionado(s)</strong></div>';

    files.forEach((file, index) => {
      const fileSize = (file.size / 1024).toFixed(2);
      const fileIcon = getFileIcon(file.type);
      const fileNameTruncated = file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name;

      html += `
      <div class="alert alert-light border border-left-success mb-2 d-flex justify-content-between align-items-center">
        <div class="flex-grow-1">
          <div>
            <i class="fas ${fileIcon} mr-2 text-success"></i>
            <strong title="${file.name}">${fileNameTruncated}</strong>
          </div>
          <small class="text-muted d-block ml-4">${fileSize} KB</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    });

    fileList.innerHTML += html;
  }

  function getFileIcon(fileType) {
    const icons = {
      'application/pdf': 'fa-file-pdf',
      'application/vnd.ms-excel': 'fa-file-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fa-file-excel',
      'image/jpeg': 'fa-file-image',
      'image/png': 'fa-file-image'
    };
    return icons[fileType] || 'fa-file';
  }

  function removeFile(index) {
    uploadedFiles.splice(index, 1);
    totalFilesInput.value = uploadedFiles.length;

    // Actualizar el input file
    const dt = new DataTransfer();
    uploadedFiles.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;

    displayFiles(uploadedFiles);
    updateSummary();
  }

  // ====== VALIDACIÓN DEL FORMULARIO ======
  function validarFormulario(e) {
    const form = document.getElementById('formRadicacion');
    if (!form) return false;

    if (form.checkValidity() === false) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
      return false;
    }

    form.classList.add('was-validated');
    return true;
  }

  // ====== ACTUALIZACIÓN DE RESUMEN EN TIEMPO REAL ======
  function updateSummary() {
    const nombreSolicitante = document.getElementById('numeroDocumento').value + ' - ' + document.getElementById('nombreSolicitante').value;
    const dependencia = document.querySelector('#dependenciaDestino option:checked').text;
    const serie = document.querySelector('#serieDocumental option:checked').text;
    const subserie = document.querySelector('#subserieDocumental option:checked').text;
    const asunto = document.getElementById('asuntoDocumento').value;
    const adjuntos = totalFilesInput.value;
    const prioridad = document.querySelector('#prioridad option:checked').text;
    const fechaLimite = document.getElementById('fechaLimite').value;

    // Actualizar resumen
    document.querySelector('.resumen-solicitante').textContent = nombreSolicitante && nombreSolicitante !== ' - ' ? nombreSolicitante : '—';
    document.querySelector('.resumen-dependencia').textContent = dependencia !== 'Seleccione una dependencia...' ? dependencia : '—';
    document.querySelector('.resumen-serie').textContent = serie !== 'Seleccione una serie...' ? serie : '—';
    document.querySelector('.resumen-subserie').textContent = subserie !== 'Seleccione una subserie...' ? subserie : '—';
    document.querySelector('.resumen-asunto').textContent = asunto || '—';
    document.querySelector('.resumen-adjuntos').textContent = adjuntos;
    document.querySelector('.resumen-prioridad').textContent = prioridad !== 'Seleccione...' ? prioridad : '—';
    document.querySelector('.resumen-fechaLimite').textContent = fechaLimite || '—';
  }

  // ====== LIMPIAR FORMULARIO ======
  function limpiarFormulario() {
    if (confirm('¿Desea limpiar todos los datos del formulario?')) {
      document.getElementById('formRadicacion').reset();
      document.getElementById('formRadicacion').classList.remove('was-validated');
      uploadedFiles = [];
      totalFilesInput.value = 0;
      fileList.innerHTML = '<p class="text-muted text-center"><i class="fas fa-inbox mr-2"></i>Ningún archivo seleccionado</p>';
      updateSummary();
    }
  }

  // ====== ENVÍO DEL FORMULARIO ======
  const formRadicacion = document.getElementById('formRadicacion');
  if (formRadicacion) {
    formRadicacion.addEventListener('submit', function(e) {
      e.preventDefault();

      if (!validarFormulario(e)) {
        Swal.fire('Error', 'Por favor, complete todos los campos requeridos', 'error');
        return;
      }

    const btnRadicar = document.getElementById('btnRadicar');
    const textoOriginal = btnRadicar.innerHTML;
    btnRadicar.disabled = true;
    btnRadicar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Procesando...';

    // Aquí se enviaría el formulario al servidor
    console.log('Formulario válido. Enviando datos...');

    // Simular envío (reemplazar con AJAX real)
    setTimeout(() => {
      btnRadicar.disabled = false;
      btnRadicar.innerHTML = textoOriginal;
      Swal.fire('Éxito', 'Radicación realizada correctamente', 'success');
      document.getElementById('formRadicacion').reset();
    }, 2000);
  });

  // ====== INICIALIZACIÓN ======
  document.addEventListener('DOMContentLoaded', function() {
    // Agregar listeners a los campos para actualizar el resumen
    const camposResumen = ['numeroDocumento', 'nombreSolicitante', 'dependenciaDestino', 'serieDocumental', 'subserieDocumental', 'asuntoDocumento', 'prioridad', 'fechaLimite'];

    camposResumen.forEach(id => {
      const elemento = document.getElementById(id);
      if (elemento) {
        elemento.addEventListener('change', updateSummary);
        elemento.addEventListener('keyup', updateSummary);
      }
    });

    // Establecer fecha mínima como hoy
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fechaDocumento').setAttribute('min', hoy);
    document.getElementById('fechaLimite').setAttribute('min', hoy);

    // Cargar datos iniciales del resumen
    updateSummary();
  });

  // ====== CLASE PARA VALIDACIÓN ======
  (function() {
    'use strict';
    window.addEventListener('load', function() {
      const forms = document.getElementsByClassName('needs-validation');
      let validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
          if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    }, false);
  })();
