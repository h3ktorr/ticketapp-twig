<?php
// router.php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use Kelvin\TwigApp\Controllers\AuthController;
use Kelvin\TwigApp\Controllers\TicketController;

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);

$authController = new AuthController();
$ticketController = new TicketController();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $authController->currentUser();

// ------------------ HOME ------------------
if ($uri === '/' || $uri === '/home') {
    echo $twig->render('pages/landing.twig', ['user' => $user]);
    exit;
}

// ------------------ LOGIN ------------------
if ($uri === '/auth/login') {
    $errors = ['email' => '', 'password' => '', 'general' => ''];
    $email = '';
    $password = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Basic validation
        if ($email === '') {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required';
        }

        if (!$errors['email'] && !$errors['password']) {
            $userLogin = $authController->login(['email' => $email, 'password' => $password]);
            if ($userLogin) {
                header('Location: /dashboard');
                exit;
            } else {
                $errors['general'] = 'Invalid email or password';
            }
        }
    }

    echo $twig->render('pages/login.twig', [
        'errors' => $errors,
        'email' => $email,
        'user' => $user
    ]);
    exit;
}

// ------------------ SIGNUP ------------------
if ($uri === '/auth/signup') {
    $errors = ['name' => '', 'email' => '', 'password' => '', 'general' => ''];
    $name = '';
    $email = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Field validation
        if ($name === '') {
            $errors['name'] = 'Full name is required';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if (!$errors['name'] && !$errors['email'] && !$errors['password']) {
            try {
                $newUser = $authController->signup([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password
                ]);
                header('Location: /dashboard');
                exit;
            } catch (\Exception $e) {
                $errors['general'] = $e->getMessage();
            }
        }
    }

    echo $twig->render('pages/signup.twig', [
        'errors' => $errors,
        'name' => $name,
        'email' => $email,
        'user' => $user
    ]);
    exit;
}

// ------------------ LOGOUT ------------------
if ($uri === '/auth/logout') {
    $authController->logout();
    header('Location: /');
    exit;
}

// ------------------ DASHBOARD ------------------
if ($uri === '/dashboard') {
    if (!$user) {
        header('Location: /auth/login');
        exit;
    }

    $tickets = $ticketController->all();
    $stats = $ticketController->stats();

    echo $twig->render('pages/dashboard.twig', [
        'user' => $user,
        'stats' => $stats
    ]);
    exit;
}

// ------------------ TICKETS LIST ------------------
if ($uri === '/tickets') {
    if (!$user) {
        header('Location: /auth/login');
        exit;
    }

    echo $twig->render('pages/tickets.twig', [
        'user' => $user,
        'tickets' => $ticketController->all()
    ]);
    exit;
}

// ------------------ DELETE TICKET ------------------
if (preg_match('#^/tickets/(?P<id>[0-9]+)/delete$#', $uri, $matches)) {
    if (!$user) {
        header('Location: /auth/login');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticketController->delete($matches['id']);
        header('Location: /tickets');
        exit;
    }

    echo $twig->render('pages/ticket_delete_confirm.twig', [
        'user' => $user,
        'ticketId' => $matches['id']
    ]);
    exit;
}

// ------------------ CREATE / EDIT TICKET ------------------
if (preg_match('#^/tickets/(?P<id>[a-zA-Z0-9_-]+)$#', $uri, $matches)) {
    if (!$user) {
        header('Location: /auth/login');
        exit;
    }

    $id = $matches['id'];
    $isNew = $id === 'new';
    $errors = ['title' => '', 'description' => '', 'status' => '', 'general' => ''];

    // Default form data
    $form = ['title' => '', 'description' => '', 'status' => 'open'];

    // Load existing ticket if editing
    if (!$isNew) {
        $ticket = $ticketController->find($id);
        if (!$ticket) {
            http_response_code(404);
            echo $twig->render('pages/404.twig', [
                'message' => 'Ticket not found',
                'user' => $user
            ]);
            exit;
        }
        $form = $ticket;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form['title'] = trim($_POST['title'] ?? '');
        $form['description'] = trim($_POST['description'] ?? '');
        $form['status'] = $_POST['status'] ?? 'open';

        // Validation (mirroring React)
        if ($form['title'] === '' || strlen($form['title']) < 3) {
            $errors['title'] = 'Title is required (min 3 characters)';
        }

        if ($form['description'] !== '' && strlen($form['description']) < 6) {
            $errors['description'] = 'Description must be at least 6 characters';
        }

        if (!in_array($form['status'], ['open', 'in_progress', 'closed'])) {
            $errors['status'] = 'Invalid status';
        }

        // If no validation errors
        if (!$errors['title'] && !$errors['description'] && !$errors['status']) {
            try {
                if ($isNew) {
                    $ticketController->create($form);
                } else {
                    $ticketController->update($id, $form);
                }
                header('Location: /tickets');
                exit;
            } catch (\Exception $e) {
                $errors['general'] = 'Save failed. Try again.';
            }
        }
    }

    echo $twig->render('pages/ticket_edit.twig', [
        'user' => $user,
        'ticket' => $form,
        'isNew' => $isNew,
        'errors' => $errors
    ]);
    exit;
}

// ------------------ 404 NOT FOUND ------------------
http_response_code(404);
echo $twig->render('pages/404.twig', [
    'message' => 'Page not found',
    'user' => $user
]);
