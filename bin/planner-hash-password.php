<?php
/**
 * planner-hash-password.php
 * -------------------------
 * Genera un hash bcrypt para PLANNER_PASSWORD_HASH del archivo .env.
 *
 * Uso (desde la raíz del proyecto):
 *   php bin/planner-hash-password.php
 *
 * El script pide la contraseña por consola SIN mostrarla en pantalla
 * (cuando es posible) e imprime el hash listo para copiar a .env.
 *
 * Importante: NO escribe nada en .env automáticamente. Tú decides
 * cuándo y dónde pegarlo.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse desde la línea de comandos.\n");
    exit(1);
}

echo "Generador de hash de contraseña para el módulo Planner\n";
echo "------------------------------------------------------\n";

// Intento ocultar la entrada en sistemas POSIX. En Windows el "stty"
// no existe, así que la contraseña se verá al teclear (limitación del
// terminal nativo de Windows).
$hideInput = function (bool $hide): void {
    if (DIRECTORY_SEPARATOR !== '\\' && function_exists('shell_exec')) {
        shell_exec($hide ? 'stty -echo' : 'stty echo');
    }
};

echo "Introduce la contraseña: ";
$hideInput(true);
$password = trim((string) fgets(STDIN));
$hideInput(false);
echo "\n";

if ($password === '') {
    fwrite(STDERR, "ERROR: contraseña vacía. Abortando.\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "ERROR: usa al menos 8 caracteres.\n");
    exit(1);
}

echo "Repite la contraseña:   ";
$hideInput(true);
$confirm = trim((string) fgets(STDIN));
$hideInput(false);
echo "\n";

if (!hash_equals($password, $confirm)) {
    fwrite(STDERR, "ERROR: las contraseñas no coinciden.\n");
    exit(1);
}

// PASSWORD_DEFAULT delega en PHP la elección del algoritmo más seguro
// disponible (bcrypt hoy, posiblemente argon2 mañana). Es la recomendación
// oficial. password_verify() sabe leer cualquier formato producido aquí.
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\nHash generado correctamente. Pega esta línea en tu archivo .env:\n\n";
echo "PLANNER_PASSWORD_HASH={$hash}\n\n";
echo "Recuerda definir también PLANNER_USERNAME en .env.\n";
