<?php

declare(strict_types=1);

require_once __DIR__ . '/connect.php';

if (!isset($_SESSION['user_id'])) {
    redirect('Login.html', ['error' => 'session']);
}

$statement = db()->prepare(
    'SELECT student_id, first_name, last_name, email, department, faculty, course, level,
            admission_year, registration_date, created_at
     FROM users WHERE id = :id LIMIT 1'
);
$statement->execute([':id' => (int) $_SESSION['user_id']]);
$user = $statement->fetch();

if (!is_array($user)) {
    $_SESSION = [];
    session_destroy();
    redirect('Login.html', ['error' => 'session']);
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <title>Dashboard | Student Portal</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <main class="page-shell">
      <header class="page-header">
        <a class="brand" href="dashboard.php">
          <span class="brand-mark" aria-hidden="true">S</span>
          <span>Student Portal</span>
        </a>
        <form class="logout-form" action="logout.php" method="post">
          <button class="button button--secondary button--small" type="submit">Log out</button>
        </form>
      </header>

      <section class="dashboard-hero">
        <span class="eyebrow">Student dashboard</span>
        <h1>Welcome, <?= e($user['first_name']) ?>.</h1>
        <p>Your registration is complete and your account is active.</p>
      </section>

      <section class="profile-card" aria-labelledby="profile-title">
        <div class="profile-heading">
          <div>
            <span class="eyebrow">Account overview</span>
            <h2 id="profile-title"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h2>
          </div>
          <span class="status-pill">Active</span>
        </div>

        <dl class="detail-grid">
          <div><dt>Student ID</dt><dd><?= e($user['student_id']) ?></dd></div>
          <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
          <div><dt>Faculty</dt><dd><?= e($user['faculty']) ?></dd></div>
          <div><dt>Department</dt><dd><?= e($user['department']) ?></dd></div>
          <div><dt>Course</dt><dd><?= e($user['course']) ?></dd></div>
          <div><dt>Level</dt><dd><?= e($user['level']) ?> Level</dd></div>
          <div><dt>Admission year</dt><dd><?= e((string) $user['admission_year']) ?></dd></div>
          <div><dt>Registered on</dt><dd><?= e($user['registration_date']) ?></dd></div>
        </dl>
      </section>
    </main>
  </body>
</html>

