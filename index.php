<?php

declare(strict_types=1);

require_once __DIR__ . '/connect.php';

redirect(isset($_SESSION['user_id']) ? 'dashboard.php' : 'Login.html');

