<?php

namespace App\Controllers;

use Core\Validator;

/**
 * Controlador para el formulario de contacto de la landing page.
 *
 * Protecciones anti-spam:
 * 1. Honeypot: campo oculto "website" que los bots rellenan (humanos no lo ven)
 * 2. Timestamp: si el formulario se envía en menos de 3s, se descarta
 * 3. Rate limit: máximo 3 mensajes por hora desde la misma IP
 * 4. CSRF: token obligatorio como en todos los POST de la app
 */
class ContactoController extends BaseController
{
    /** Email destino para los mensajes de contacto */
    private const DESTINATARIO = 'info@martincarmona.com';

    /** Máximo de mensajes por IP por hora */
    private const MAX_MENSAJES_POR_HORA = 3;

    /** Tiempo mínimo en segundos entre carga del formulario y envío */
    private const MIN_SEGUNDOS_ENVIO = 3;

    public function enviar()
    {
        // Solo aceptar POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        // --- Honeypot: si el campo "website" tiene contenido, es un bot ---
        $honeypot = trim($_POST['website'] ?? '');
        if ($honeypot !== '') {
            // Respondemos "éxito" para que el bot no sepa que fue detectado
            $this->json(['success' => true]);
            return;
        }

        // --- Timestamp: comprobar que no se envió demasiado rápido ---
        $formLoadedAt = (int) ($_POST['_t'] ?? 0);
        if ($formLoadedAt > 0 && (time() - $formLoadedAt) < self::MIN_SEGUNDOS_ENVIO) {
            $this->json(['success' => true]); // Falso éxito para el bot
            return;
        }

        // --- Rate limiting por IP ---
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$this->checkRateLimit($ip)) {
            $this->json([
                'success' => false,
                'error'   => 'Has enviado demasiados mensajes. Inténtalo de nuevo más tarde.'
            ]);
            return;
        }

        // --- Validar campos ---
        $nombre  = trim($_POST['nombre'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        $validator = new Validator();
        $validator->validate(
            ['nombre' => $nombre, 'email' => $email, 'mensaje' => $mensaje],
            ['nombre' => 'required|min:2|max:100', 'email' => 'required|email|max:150', 'mensaje' => 'required|min:10|max:2000']
        );

        if ($validator->fails()) {
            $this->json([
                'success' => false,
                'error'   => 'Por favor, revisa los campos del formulario.',
                'errors'  => $validator->errors(),
            ]);
            return;
        }

        // --- Enviar email ---
        $enviado = $this->enviarEmail($nombre, $email, $mensaje);

        if ($enviado) {
            // Registrar envío para rate limiting
            $this->registrarEnvio($ip);

            $this->json(['success' => true]);
        } else {
            \Core\Logger::app()->error("Error enviando email de contacto desde {$email}");
            $this->json([
                'success' => false,
                'error'   => 'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.'
            ]);
        }
    }

    /**
     * Envía el email de contacto al destinatario configurado.
     */
    private function enviarEmail(string $nombre, string $email, string $mensaje): bool
    {
        $nombreSafe  = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $emailSafe   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $mensajeSafe = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

        $asunto = "Nuevo mensaje de contacto de {$nombreSafe} — MartinCarmona.com";

        $cuerpo  = "Has recibido un nuevo mensaje desde el formulario de contacto:\n\n";
        $cuerpo .= "Nombre: {$nombreSafe}\n";
        $cuerpo .= "Email: {$emailSafe}\n";
        $cuerpo .= "Fecha: " . date('d/m/Y H:i') . "\n";
        $cuerpo .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida') . "\n";
        $cuerpo .= "\n--- Mensaje ---\n\n";
        $cuerpo .= $mensajeSafe;
        $cuerpo .= "\n\n--- Fin del mensaje ---";

        $headers  = "From: noreply@martincarmona.com\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: MartinCarmona-Contact/1.0";

        return @mail(self::DESTINATARIO, $asunto, $cuerpo, $headers);
    }

    /**
     * Comprueba si la IP ha excedido el límite de mensajes por hora.
     * Usa la sesión para almacenar los timestamps de envíos recientes.
     */
    private function checkRateLimit(string $ip): bool
    {
        $key = 'contact_rate_' . md5($ip);
        $ahora = time();
        $hace1h = $ahora - 3600;

        // Obtener registros de envíos de esta IP
        $envios = $_SESSION[$key] ?? [];

        // Filtrar solo los de la última hora
        $envios = array_filter($envios, function ($ts) use ($hace1h) {
            return $ts > $hace1h;
        });

        return count($envios) < self::MAX_MENSAJES_POR_HORA;
    }

    /**
     * Registra un envío exitoso para el control de rate limiting.
     */
    private function registrarEnvio(string $ip): void
    {
        $key = 'contact_rate_' . md5($ip);
        $envios = $_SESSION[$key] ?? [];

        // Limpiar envíos de más de 1 hora
        $hace1h = time() - 3600;
        $envios = array_filter($envios, function ($ts) use ($hace1h) {
            return $ts > $hace1h;
        });

        $envios[] = time();
        $_SESSION[$key] = $envios;
    }
}
