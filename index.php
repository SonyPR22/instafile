<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = rtrim("./descarga/" . $carpetaNombre, '/');

try {
    if (!file_exists($carpetaRuta)) {
        mkdir($carpetaRuta, 0755, true);
        $mensaje = "Carpeta '$carpetaNombre' creada con éxito.";
    } else {
        $mensaje = "La carpeta '$carpetaNombre' ya existe.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Manejo de subida de archivo
        if (isset($_FILES['archivo']) && !empty($_FILES['archivo']['name'][0])) {
            foreach ($_FILES['archivo']['name'] as $key => $name) {
                $tmpName = $_FILES['archivo']['tmp_name'][$key];
                $name = str_replace(' ', '_', basename($name));
                if (move_uploaded_file($tmpName, "$carpetaRuta/$name")) {
                    $mensaje = "Archivo '$name' subido con éxito.";
                } else {
                    $mensaje = "Error al subir el archivo '$name'.";
                }
            }
        }

        // Manejo de eliminación de archivos
        if (isset($_POST['eliminarArchivos'])) {
            $archivoAEliminar = basename($_POST['eliminarArchivos']);
            $archivoRutaAEliminar = "$carpetaRuta/$archivoAEliminar";

            if (file_exists($archivoRutaAEliminar)) {
                if (unlink($archivoRutaAEliminar)) {
                    $mensaje = "Archivos '$archivoAEliminar' eliminado con éxito.";
                } else {
                    $mensaje = "Error al eliminar el archivos '$archivoAEliminar'.";
                }
            } else {
                $mensaje = "El archivos '$archivoAEliminar' no existe.";
            }
        }
    }
} catch (Exception $e) {
    $mensaje = "Error: " . htmlspecialchars($e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir archivos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Compartir archivos <sup class="beta">BETA</sup></h1>
    </header>
    <main class="content">
        <div class="initial-message">
            <h3>Sube tus archivos y comparte este enlace temporal: <span>ibu.pe/<?php echo htmlspecialchars($carpetaNombre);?></span></h3>
        </div>
        <div class="container">
            <div class="drop-area" id="drop-area">
                <form action="" id="form" method="POST" enctype="multipart/form-data">
                    <input type="file" class="file-input" name="archivos[]" id="archivo" multiple>
                    <label for="archivo">Arrastra tus archivos aquí o <strong>Abre el explorador</strong></label>
                </form>
                <div id="progress-container" class="progress-container">
                    <div id="progress-bar" class="progress-bar"></div>
                </div>
                <div id="upload-message" class="upload-message"></div>
            </div>

            <div class="container2">
                <div id="file-list" class="pila">
                    <?php
                    $targetDir = $carpetaRuta;

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (isset($_FILES['archivos'])) {
                            foreach ($_FILES['archivos']['name'] as $key => $name) {
                                $tmpName = $_FILES['archivos']['tmp_name'][$key];
                                $name = str_replace(' ', '_', basename($name));
                                if (move_uploaded_file($tmpName, "$targetDir/$name")) {
                                    echo "<p class='success'>Archivo '$name' subido exitosamente.</p>";
                                } else {
                                    echo "<p class='error'>Error al subir el archivo '$name'.</p>";
                                }
                            }
                        }

                        if (isset($_POST['eliminarArchivo'])) {
                            $archivoAEliminar = basename($_POST['eliminarArchivo']);
                            if (unlink("$targetDir/$archivoAEliminar")) {
                                echo "<p class='success'>Archivo '$archivoAEliminar' eliminado exitosamente.</p>";
                            } else {
                                echo "<p class='error'>Error al eliminar el archivo '$archivoAEliminar'.</p>";
                            }
                        }
                    }

                    $files = array_diff(scandir($targetDir), array('.', '..'));

                    if (count($files) > 0) {
                        echo "<h3>Archivos Subidos:</h3>";
                        foreach ($files as $file) {
                            echo "<div class='archivos_subidos'>
                                <div><a href='$carpetaRuta/$file' download class='boton-descargar'>$file</a></div>
                                <div>
                                    <form action='' method='POST' style='display:inline;'>
                                        <input type='hidden' name='eliminarArchivo' value='$file'>
                                        <button type='submit' class='btn_delete'>
                                            <svg xmlns='http://www.w3.org/2000/svg' class='icon icon-tabler icon-tabler-trash' width='24' height='24' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor' fill='none' stroke-linecap='round' stroke-linejoin='round'>
                                                <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                                <path d='M4 7l16 0' />
                                                <path d='M10 11l0 6' />
                                                <path d='M14 11l0 6' />
                                                <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12' />
                                                <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3' />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<p>No se han subido archivos.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('archivo');
        const progressBar = document.getElementById('progress-bar');
        const uploadMessage = document.getElementById('upload-message');
        const fileList = document.getElementById('file-list');
        const initialMessage = document.querySelector('.initial-message');

        dropArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.add('highlight');
        });

        dropArea.addEventListener('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('highlight');
        });

        dropArea.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('highlight');

            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', function () {
            const files = fileInput.files;
            handleFiles(files);
        });

        function handleFiles(files) {
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('archivos[]', files[i]);
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = Math.round(percentComplete) + '%';
                }
            });

            xhr.addEventListener('load', function () {
                if (xhr.status === 200) {
                    updateFileList();
                    uploadMessage.innerHTML = '<p class="success">Archivos subidos exitosamente.</p>';
                } else {
                    uploadMessage.innerHTML = '<p class="error">Error al subir los archivos.</p>';
                }
            });

            xhr.addEventListener('error', function () {
                uploadMessage.innerHTML = '<p class="error">Error de red.</p>';
            });

            xhr.send(formData);
        }

        function updateFileList() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '', true);

            xhr.addEventListener('load', function () {
                if (xhr.status === 200) {
                    fileList.innerHTML = xhr.responseText;
                }
            });

            xhr.send();
        }
    });
    </script>
</body>
</html>
