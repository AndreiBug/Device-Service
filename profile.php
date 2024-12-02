<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Conexiune la baza de date
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "service";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Preluăm ID_User din sesiune
$userId = $_SESSION['id'];

// Obținem rolul utilizatorului
$sql_role = "SELECT tip_utilizator FROM users WHERE ID_User = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $userId);
$stmt_role->execute();
$result_role = $stmt_role->get_result();

if ($result_role->num_rows > 0) {
    $user = $result_role->fetch_assoc();
    $role = $user['tip_utilizator']; // Exemplu valori: "client" sau "employee"
} else {
    die("Eroare: Nu s-a putut identifica rolul utilizatorului.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_invoice'])) {
    $invoiceId = intval($_POST['delete_invoice']);

    // Ștergem plățile asociate facturii
    $sql_delete_payments = "DELETE FROM payments WHERE ID_Invoice = ?";
    $stmt_delete_payments = $conn->prepare($sql_delete_payments);
    $stmt_delete_payments->bind_param("i", $invoiceId);
    $stmt_delete_payments->execute();

    // Ștergem factura
    $sql_delete_invoice = "DELETE FROM invoices WHERE ID_Invoice = ?";
    $stmt_delete_invoice = $conn->prepare($sql_delete_invoice);
    $stmt_delete_invoice->bind_param("i", $invoiceId);
    $stmt_delete_invoice->execute();

    if ($stmt_delete_invoice->affected_rows > 0) {
        echo "<p style='color:green;'>Factura și plățile asociate au fost șterse cu succes.</p>";
    } else {
        echo "<p style='color:red;'>Eroare: Nu s-a putut șterge factura.</p>";
    }

    // Închidem statement-urile
    $stmt_delete_payments->close();
    $stmt_delete_invoice->close();
}

// Logica de ștergere pentru servicii (angajați)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
    $serviceId = intval($_POST['delete_service']);

    // Ștergerea serviciului
    $sql_delete_service = "DELETE FROM services WHERE ID_Service = ?";
    $stmt_delete_service = $conn->prepare($sql_delete_service);
    $stmt_delete_service->bind_param("i", $serviceId);
    $stmt_delete_service->execute();

    if ($stmt_delete_service->affected_rows > 0) {
        echo "<p style='color:green;'>Serviciul a fost șters cu succes.</p>";
    } else {
        echo "<p style='color:red;'>Eroare: Nu s-a putut șterge serviciul.</p>";
    }

    $stmt_delete_service->close();
}

// Logica de adugare pentru servicii (angajați)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addService'])) {
    $deviceId = intval($_POST['deviceId']);
    $description = $_POST['description'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $cost = floatval($_POST['cost']);

    // Adăugăm serviciul în baza de date
    $sql_add_service = "INSERT INTO services (ID_Device, Descriere, Data_Inceperii, Data_Finalizarii, Cost) 
                        VALUES (?, ?, ?, ?, ?)";
    $stmt_add_service = $conn->prepare($sql_add_service);
    $stmt_add_service->bind_param("isssd", $deviceId, $description, $startDate, $endDate, $cost);

    if ($stmt_add_service->execute()) {
        echo "<p style='color:green;'>Serviciul a fost adăugat cu succes!</p>";
    } else {
        echo "<p style='color:red;'>Eroare: Nu s-a putut adăuga serviciul.</p>";
    }

    $stmt_add_service->close();
}

// Update services
if (isset($_POST['updateService'])) {
    $serviceId = $_POST['serviceId'];
    $deviceId = $_POST['deviceId'];
    $description = $_POST['description'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $cost = $_POST['cost'];

    $sql_update = "UPDATE services 
                   SET ID_Device = ?, Descriere = ?, Data_Inceperii = ?, Data_Finalizarii = ?, Cost = ? 
                   WHERE ID_Service = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("isssdi", $deviceId, $description, $startDate, $endDate, $cost, $serviceId);

    if ($stmt_update->execute()) {
        echo "<script>alert('Serviciul a fost actualizat cu succes.'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Eroare la actualizarea serviciului.');</script>";
    }

    $stmt_update->close();
}


// Afișare interfață bazată pe rol
if ($role === 'Client') {
    // Preluăm numele și prenumele utilizatorului
    $sql_user = "SELECT Nume, Prenume FROM users WHERE ID_User = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $userId);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        $nume = $user['Nume'];
        $prenume = $user['Prenume'];
    } else {
        $nume = "Nume necunoscut";
        $prenume = "Prenume necunoscut";
    }

    // Obținem ID_Client asociat utilizatorului logat
    $sql_client = "SELECT ID_Client FROM clients WHERE ID_User = ?";
    $stmt_client = $conn->prepare($sql_client);
    $stmt_client->bind_param("i", $userId);
    $stmt_client->execute();
    $result_client = $stmt_client->get_result();

    if ($result_client->num_rows > 0) {
        $client = $result_client->fetch_assoc();
        $clientId = $client['ID_Client'];

        // Query pentru preluarea facturilor utilizatorului logat
        $sql_invoices = "SELECT ID_Invoice, Data_Emiterii, Total, Status FROM invoices WHERE ID_Client = ?";
        $stmt_invoices = $conn->prepare($sql_invoices);
        $stmt_invoices->bind_param("i", $clientId);
        $stmt_invoices->execute();
        $result_invoices = $stmt_invoices->get_result();
    } else {
        $clientId = null;
        $result_invoices = null;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Facturile mele</title>
        <style>
            .details-row {
                display: none;
            }
        </style>
        <script>
            function toggleDetails(invoiceId) {
                const detailsRow = document.getElementById('details-' + invoiceId);
                detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
            }
        </script>
    </head>

    <body>
        <div class="nav-spacer">
            <div class="nav-bar">
                <div class="nav-left">
                    <button onclick="location.href='index.php'">Home</button>
                    <button onclick="location.href='services.php'">Services</button>
                    <button onclick="location.href='about.php'">About</button>
                </div>
                <h1 class="nav-title">TechFix</h1>
                <div class="nav-right">
                    <?php if (isset($_SESSION['id'])): ?>
                        <button onclick="location.href='profile.php'">Profile</button>
                        <button onclick="location.href='logout.php'">Logout</button>
                    <?php else: ?>
                        <button onclick="location.href='login.php'">Login</button>
                        <button onclick="location.href='register.php'">Register</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <h2>Bun venit, <?php echo htmlspecialchars($nume . ' ' . $prenume); ?>!</h2>
            <h3>Facturile tale</h3><br>

            <?php if ($clientId && $result_invoices && $result_invoices->num_rows > 0): ?>
                <table border="1" cellspacing="0" cellpadding="10">
                    <thead>
                        <tr>
                            <th>ID Factură</th>
                            <th>Data Emiterii</th>
                            <th>Total (RON)</th>
                            <th>Status</th>
                            <th>Detalii</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_invoices->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['ID_Invoice']); ?></td>
                                <td><?php echo htmlspecialchars($row['Data_Emiterii']); ?></td>
                                <td><?php echo htmlspecialchars($row['Total']); ?></td>
                                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                                <td>
                                    <button onclick="toggleDetails(<?php echo $row['ID_Invoice']; ?>)">Extinde</button>
                                    <br><br>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="delete_invoice" value="<?php echo $row['ID_Invoice']; ?>">
                                        <button type="submit"
                                            onclick="return confirm('Ești sigur că vrei să ștergi această factură?')">Șterge</button>
                                    </form>
                                </td>
                            </tr>

                            <?php
                            $sql_payments = "SELECT ID_Payment, Suma, Data_Platii, Metoda_Platii FROM payments WHERE ID_Invoice = ?";
                            $stmt_payments = $conn->prepare($sql_payments);
                            $stmt_payments->bind_param("i", $row['ID_Invoice']);
                            $stmt_payments->execute();
                            $result_payments = $stmt_payments->get_result();
                            ?>
                            <tr id="details-<?php echo $row['ID_Invoice']; ?>" class="details-row">
                                <td colspan="5">
                                    <table border="1" width="100%">
                                        <thead>
                                            <tr>
                                                <th>ID Plată</th>
                                                <th>Suma</th>
                                                <th>Data Plății</th>
                                                <th>Metoda Plății</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result_payments->num_rows > 0): ?>
                                                <?php while ($payment = $result_payments->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($payment['ID_Payment']); ?></td>
                                                        <td><?php echo htmlspecialchars($payment['Suma']); ?></td>
                                                        <td><?php echo htmlspecialchars($payment['Data_Platii']); ?></td>
                                                        <td><?php echo htmlspecialchars($payment['Metoda_Platii']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4">Nu există plăți pentru această factură.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nu există facturi disponibile pentru acest utilizator.</p>
            <?php endif; ?> <br>
            <a href="logout.php" style="float: center; font-size: 20px;"><b>Logout</b></a>
        </div>
    </body>

    </html>
    <?php
} elseif ($role === 'Employee') {
    // Obținem ID_Employee asociat utilizatorului logat
    $sql_employee = "SELECT ID_Employee, Nume, Prenume FROM employees WHERE ID_User = ?";
    $stmt_employee = $conn->prepare($sql_employee);
    $stmt_employee->bind_param("i", $userId);
    $stmt_employee->execute();
    $result_employee = $stmt_employee->get_result();

    if ($result_employee->num_rows > 0) {
        $employee = $result_employee->fetch_assoc();
        $employeeId = $employee['ID_Employee'];
        $nume = $employee['Nume'];
        $prenume = $employee['Prenume'];

        // Obținem serviciile asociate acestui angajat
        $sql_services = "
            SELECT s.ID_Service, s.ID_Device, s.Descriere, s.Data_Inceperii, s.Data_Finalizarii, s.Cost
            FROM services s
            INNER JOIN service_employees se ON s.ID_Service = se.ID_Service
            WHERE se.ID_Employee = ?";
        $stmt_services = $conn->prepare($sql_services);
        $stmt_services->bind_param("i", $employeeId);
        $stmt_services->execute();
        $result_services = $stmt_services->get_result();
    } else {
        $employeeId = null;
        $result_services = null;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Serviciile mele</title>
        <style>
            .details-row {
                display: none;
            }
        </style>
        <script>
            function toggleDetails(serviceId) {
                const detailsRow = document.getElementById('details-' + serviceId);
                detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
            }
        </script>
    </head>

    <body>
        <div class="nav-spacer">
            <div class="nav-bar">
                <div class="nav-left">
                    <button onclick="location.href='index.php'">Home</button>
                    <button onclick="location.href='services.php'">Services</button>
                    <button onclick="location.href='about.php'">About</button>
                </div>
                <h1 class="nav-title">TechFix</h1>
                <div class="nav-right">
                    <?php if (isset($_SESSION['id'])): ?>
                        <button onclick="location.href='profile.php'">Profile</button>
                        <button onclick="location.href='logout.php'">Logout</button>
                    <?php else: ?>
                        <button onclick="location.href='login.php'">Login</button>
                        <button onclick="location.href='register.php'">Register</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <h2>Bun venit, <?php echo htmlspecialchars($nume . ' ' . $prenume); ?>!</h2>
            <h3>Serviciile tale</h3><br>

            <button id="addServiceButton" onclick="showAddServiceForm()">Adaugă Serviciu Nou</button><br><br>
            <h3>Add service</h3>
            <!-- Formularul de adăugare a unui serviciu nou -->
            <div id="addServiceForm" style="display:none; margin-top:20px;">
                <form method="post">
                    <label for="deviceId">ID Dispozitiv:</label>
                    <input type="number" id="deviceId" name="deviceId" required><br>

                    <label for="description">Descriere:</label>
                    <input type="text" id="description" name="description" required><br>

                    <label for="startDate">Data Începerii:</label>
                    <input type="date" id="startDate" name="startDate" required><br>

                    <label for="endDate">Data Finalizării:</label>
                    <input type="date" id="endDate" name="endDate" required><br>

                    <label for="cost">Cost:</label>
                    <input type="number" id="cost" name="cost" step="0.01" required><br><br>

                    <button type="submit" name="addService">Adaugă Serviciu</button>
                    <br><br><button type="button" onclick="hideAddServiceForm()">Anulează</button><br><br>
                </form>
            </div>

            <script>
                // Funcție pentru afișarea formularului și ascunderea butonului
                function showAddServiceForm() {
                    document.getElementById('addServiceButton').style.display = 'none';
                    document.getElementById('addServiceForm').style.display = 'block';
                }

                // Funcție pentru ascunderea formularului și afișarea butonului
                function hideAddServiceForm() {
                    document.getElementById('addServiceButton').style.display = 'block';
                    document.getElementById('addServiceForm').style.display = 'none';
                }

                function showEditServiceForm(serviceId, deviceId, description, startDate, endDate, cost) {
                    document.getElementById('editServiceId').value = serviceId;
                    document.getElementById('editDeviceId').value = deviceId;
                    document.getElementById('editDescription').value = description;
                    document.getElementById('editStartDate').value = startDate;
                    document.getElementById('editEndDate').value = endDate;
                    document.getElementById('editCost').value = cost;

                    document.getElementById('editServiceForm').style.display = 'block';
                }

                function hideEditServiceForm() {
                    document.getElementById('editServiceForm').style.display = 'none';
                }
            </script>

            <?php if (isset($result_services) && $result_services->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Serviciu</th>
                            <th>ID Dispozitiv</th>
                            <th>Descriere</th>
                            <th>Data Începerii</th>
                            <th>Data Finalizării</th>
                            <th>Cost</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($service = $result_services->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['ID_Service']); ?></td>
                                <td><?php echo htmlspecialchars($service['ID_Device']); ?></td>
                                <td><?php echo htmlspecialchars($service['Descriere']); ?></td>
                                <td><?php echo htmlspecialchars($service['Data_Inceperii']); ?></td>
                                <td><?php echo htmlspecialchars($service['Data_Finalizarii']); ?></td>
                                <td><?php echo htmlspecialchars($service['Cost']); ?></td>
                                <td>
                                    <button onclick="deleteService(<?php echo $service['ID_Service']; ?>)">Șterge</button><br><br>
                                    <button
                                        onclick="showEditServiceForm(<?php echo $service['ID_Service']; ?>, '<?php echo htmlspecialchars($service['ID_Device']); ?>', '<?php echo htmlspecialchars($service['Descriere']); ?>', '<?php echo htmlspecialchars($service['Data_Inceperii']); ?>', '<?php echo htmlspecialchars($service['Data_Finalizarii']); ?>', '<?php echo htmlspecialchars($service['Cost']); ?>')">
                                        Modifică
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div id="editServiceForm" style="display:none; margin-top:20px;">
                    <h3>Modificare services</h3>
                    <form method="post">
                        <input type="hidden" id="editServiceId" name="serviceId">

                        <label for="editDeviceId">ID Dispozitiv:</label>
                        <input type="number" id="editDeviceId" name="deviceId" required><br>

                        <label for="editDescription">Descriere:</label>
                        <input type="text" id="editDescription" name="description" required><br>

                        <label for="editStartDate">Data Începerii:</label>
                        <input type="date" id="editStartDate" name="startDate" required><br>

                        <label for="editEndDate">Data Finalizării:</label>
                        <input type="date" id="editEndDate" name="endDate" required><br>

                        <label for="editCost">Cost:</label>
                        <input type="number" id="editCost" name="cost" step="0.01" required><br><br>

                        <button type="submit" name="updateService">Actualizează Serviciu</button><br><br>
                        <button type="button" onclick="hideEditServiceForm()">Anulează</button><br><br>
                    </form>
                </div>
            <?php else: ?>
                <p>Nu există servicii disponibile.</p>
            <?php endif; ?><br>
            <a href="logout.php" style="float: center; font-size: 20px;"><b>Logout</b></a>
        </div>
    </body>

    </html>
    <?php
} else {
    echo "Eroare: Rol necunoscut.";
    exit();
}

// Închidere conexiuni și statement-uri
if (isset($stmt_role))
    $stmt_role->close();
if (isset($stmt_user))
    $stmt_user->close();
if (isset($stmt_payments))
    $stmt_payments->close();
if (isset($stmt_invoices))
    $stmt_invoices->close();
$conn->close();
?>