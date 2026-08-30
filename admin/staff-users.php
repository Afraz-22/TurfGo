<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Staff & Users";
$active = "staff-users";
$theme = "admin";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "turf-pricing", "href" => "turf-pricing.php", "icon" => "🏟️", "label" => "Turf & Pricing"],
    ["key" => "staff-users", "href" => "staff-users.php", "icon" => "👥", "label" => "Staff & Users"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "payments", "href" => "payments.php", "icon" => "💳", "label" => "Payments"],
    ["key" => "reports", "href" => "reports.php", "icon" => "📈", "label" => "Reports"],
    ["key" => "settings", "href" => "settings.php", "icon" => "⚙️", "label" => "Settings"],
];

$error = "";
$success = "";

// Add a new staff (manager) account
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_user"])) {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];
    $password = password_hash("welcome123", PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "A user with this email already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        if ($stmt->execute()) {
            $success = "User added. Default password: welcome123";
        } else {
            $error = "Could not add user.";
        }
        $stmt->close();
    }
    $check->close();
}

// Toggle status
if (isset($_GET["toggle"])) {
    $id = (int)$_GET["toggle"];
    $conn->query("UPDATE users SET status = IF(status='active','inactive','active') WHERE id = $id");
    header("Location: staff-users.php");
    exit();
}

// Delete user
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: staff-users.php");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Staff & Users</h1>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="card">
    <div class="card-title">Add Staff / Admin User</div>
    <form method="POST" action="staff-users.php" class="filter-bar">
        <input type="text" name="name" placeholder="Full name" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="role" required>
            <option value="manager">Turf Manager</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" name="add_user" class="btn">+ Add User</button>
    </form>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($u["name"]); ?></td>
                <td><?php echo h(ucfirst($u["role"])); ?></td>
                <td><?php echo h($u["email"]); ?></td>
                <td><span class="badge badge-<?php echo h($u["status"]); ?>"><?php echo h(ucfirst($u["status"])); ?></span></td>
                <td>
                    <a class="icon-btn" href="staff-users.php?toggle=<?php echo (int)$u['id']; ?>" title="Toggle status">🔁</a>
                    <?php if ($u["id"] != $_SESSION["user_id"]): ?>
                        <a class="icon-btn" href="staff-users.php?delete=<?php echo (int)$u['id']; ?>" title="Delete" data-confirm="Delete this user?">🗑️</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
