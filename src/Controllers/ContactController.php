<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class ContactController extends Controller
{
    public function index(Request $request, Response $response): string
    {
        return $this->render('contact/index', [
            'title' => 'Contacto — Martín Carmona',
        ]);
    }

    public function send(Request $request, Response $response): string
    {
        if (!$request->isPost()) {
            return $response->redirect('/contacto');
        }

        // Validación CSRF
        $token = $_POST['_token'] ?? '';
        if (!$this->validateCsrf($token)) {
            return $response->json(['success' => false, 'message' => 'Token inválido'], 403);
        }

        $name    = trim($_POST['name'] ?? '');
        $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message) {
            return $response->json(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        }

        if (strlen($message) > 2000) {
            return $response->json(['success' => false, 'message' => 'El mensaje es demasiado largo']);
        }

        // Envío de email
        $to      = 'hola@martincarmona.com'; // cambiar a tu email real
        $subject = "Contacto desde martincarmona.com — {$name}";
        $body    = "Nombre: {$name}\nEmail: {$email}\n\nMensaje:\n{$message}";
        $headers = "From: noreply@martincarmona.com\r\nReply-To: {$email}";

        $sent = mail($to, $subject, $body, $headers);

        return $response->json([
            'success' => $sent,
            'message' => $sent ? '¡Mensaje enviado! Te respondo en breve.' : 'Error al enviar. Inténtalo de nuevo.',
        ]);
    }

    private function validateCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
