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

            <!-- Dropdown pentru selectarea tabelului - Clienți -->
            <form method="post">
                <label for="tableSelect">Selectează tabelul:</label>
                <select id="tableSelect" name="selectedTable" onchange="this.form.submit()">
                    <option value="default" <?php if (!isset($_POST['selectedTable']) || $_POST['selectedTable'] === 'default')
                        echo 'selected'; ?>>Facturile Tale</option>
                    <option value="device_services" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'device_services')
                        echo 'selected'; ?>>Servicii - Dispozitive</option>
                    <option value="device_employee" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'device_employee')
                        echo 'selected'; ?>>Dispozitive - Angajat</option>
                    <!-- Interogări complexe -->
                    <option value="client_services_date" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'client_services_date')
                        echo 'selected'; ?>>Servicii in Functie de
                        Data</option>
                    <option value="top_clients" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'top_clients')
                        echo 'selected'; ?>>Top Clienti - Reparatii</option>
                </select>
            </form>


            <br>

            <?php
            $selectedTable = isset($_POST['selectedTable']) ? $_POST['selectedTable'] : 'default';

            echo "<table border='1' cellspacing='0' cellpadding='10'>";

            if ($selectedTable === 'default') {
                // Afișare tabel facturi (default)
                echo "<h3>Facturile Tale</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Data Emiterii</th><th>Total (RON)</th><th>Status</th><th>Detalii</th></tr></thead>";
                echo "<tbody>";

                if ($clientId && $result_invoices && $result_invoices->num_rows > 0) {
                    while ($row = $result_invoices->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Data_Emiterii']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Total']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "<td>";
                        echo "<button onclick=\"toggleDetails(" . $row['ID_Invoice'] . ")\">Extinde</button>";
                        echo "</td>";
                        echo "</tr>";

                        // Detalii pentru plăți
                        $sql_payments = "SELECT Suma, Data_Platii, Metoda_Platii FROM payments WHERE ID_Invoice = ?";
                        $stmt_payments = $conn->prepare($sql_payments);
                        $stmt_payments->bind_param("i", $row['ID_Invoice']);
                        $stmt_payments->execute();
                        $result_payments = $stmt_payments->get_result();

                        echo "<tr id='details-" . $row['ID_Invoice'] . "' class='details-row' style='display:none;'>";
                        echo "<td colspan='4'>";
                        echo "<table border='1' width='100%'>";
                        echo "<thead><tr><th>Suma</th><th>Data Plății</th><th>Metoda Plății</th></tr></thead>";
                        echo "<tbody>";
                        if ($result_payments->num_rows > 0) {
                            while ($payment = $result_payments->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($payment['Suma']) . "</td>";
                                echo "<td>" . htmlspecialchars($payment['Data_Platii']) . "</td>";
                                echo "<td>" . htmlspecialchars($payment['Metoda_Platii']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>Nu există plăți pentru această factură.</td></tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Nu există facturi disponibile.</td></tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } elseif ($selectedTable === 'device_services') {
                // Afișare tabel 1
                $sql_table1 = "
                SELECT 
                    i.Total AS TOTALUL,
                    d.Tip_Dispozitiv AS TIP_DISPOZITIV,
                    d.Marca,
                    d.Model,
                    s.Descriere AS DESCRIEREA_DEFECTIUNII
                FROM invoices i
                INNER JOIN devices d ON i.ID_Client = d.ID_Client
                INNER JOIN services s ON d.ID_Device = s.ID_Device
                WHERE i.ID_Client = ?";
                $stmt_table1 = $conn->prepare($sql_table1);
                $stmt_table1->bind_param("i", $clientId);
                $stmt_table1->execute();
                $result_table1 = $stmt_table1->get_result();

                echo "<h3>Serviciile atribuite dispozitivelor si costul lor</h3><br>";
                echo "<table border='1'>";
                echo "<thead><tr><th>Total (RON)</th><th>Tip Dispozitiv</th><th>Marca</th><th>Model</th><th>Descrierea Defecțiunii</th></tr></thead>";
                echo "<tbody>";
                if ($result_table1->num_rows > 0) {
                    while ($row = $result_table1->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['TOTALUL']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['TIP_DISPOZITIV']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Marca']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Model']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['DESCRIEREA_DEFECTIUNII']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nu există date pentru tabelul 1.</td></tr>";
                }
                echo "</tbody>";
            } elseif ($selectedTable === 'device_employee') {
                // Afișare tabel 2
                $sql_table2 = "
                SELECT 
                    d.Tip_Dispozitiv AS TIP_DISPOZITIV,
                    d.Marca,
                    d.Model,
                    CONCAT(e.Nume, ' ', e.Prenume) AS NUME_SI_PRENUME_ANGAJAT,
                    i.Data_Emiterii
                FROM devices d
                INNER JOIN services s ON d.ID_Device = s.ID_Device
                INNER JOIN service_employees se ON s.ID_Service = se.ID_Service
                INNER JOIN employees e ON se.ID_Employee = e.ID_Employee
                INNER JOIN invoices i ON d.ID_Client = i.ID_Client
                WHERE i.ID_Client = ?";
                $stmt_table2 = $conn->prepare($sql_table2);
                $stmt_table2->bind_param("i", $clientId);
                $stmt_table2->execute();
                $result_table2 = $stmt_table2->get_result();

                echo "<h3>Dispozitivele Mele si Angajatul care a reparat</h3><br>";
                echo "<table border='1'>";
                echo "<thead><tr><th>Tip Dispozitiv</th><th>Marca</th><th>Model</th><th>Nume Angajat</th><th>Data Emiterii</th></tr></thead>";
                echo "<tbody>";
                if ($result_table2->num_rows > 0) {
                    while ($row = $result_table2->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['TIP_DISPOZITIV']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Marca']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Model']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['NUME_SI_PRENUME_ANGAJAT']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Data_Emiterii']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nu există date pentru tabelul 2.</td></tr>";
                }
                echo "</tbody>";
            } elseif ($selectedTable === 'client_services_date') {
                $sql = "
        SELECT 
            d.Tip_Dispozitiv AS Tip_Dispozitiv,
            d.Marca AS Marca,
            d.Model AS Model,
            s.Descriere AS Descriere_Serviciu,
            s.Data_Inceperii AS Data_Inceperii,
            s.Data_Finalizarii AS Data_Finalizarii
        FROM devices d
        INNER JOIN services s ON d.ID_Device = s.ID_Device
        WHERE d.ID_Client = (
            SELECT ID_Client 
            FROM clients 
            WHERE ID_User = ?
        )
        ORDER BY s.Data_Finalizarii DESC, d.Tip_Dispozitiv";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $clientId);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Serviciile tale in functie de data cand s-au rezolvat</h3><br>";
                echo "<table border='1'>";
                echo "<tr><th>Tip Dispozitiv</th><th>Marca</th><th>Model</th><th>Descriere Serviciu</th><th>Data Începerii</th><th>Data Finalizării</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                <td>" . htmlspecialchars($row['Tip_Dispozitiv']) . "</td>
                <td>" . htmlspecialchars($row['Marca']) . "</td>
                <td>" . htmlspecialchars($row['Model']) . "</td>
                <td>" . htmlspecialchars($row['Descriere_Serviciu']) . "</td>
                <td>" . htmlspecialchars($row['Data_Inceperii']) . "</td>
                <td>" . htmlspecialchars($row['Data_Finalizarii']) . "</td>
            </tr>";
                }
                echo "</table>";
            } elseif ($selectedTable === 'top_clients') {
                $sql = "
        SELECT 
            c.Nume AS Nume_Client,
            c.Prenume AS Prenume_Client,
            COUNT(s.ID_Service) AS Numar_Reparatii,
            (SELECT GROUP_CONCAT(DISTINCT d.Tip_Dispozitiv SEPARATOR ', ')
            FROM devices d
            WHERE d.ID_Client = c.ID_Client) AS Tipuri_Dispozitive
        FROM clients c
        INNER JOIN devices d ON c.ID_Client = d.ID_Client
        INNER JOIN services s ON d.ID_Device = s.ID_Device
        GROUP BY c.ID_Client
        ORDER BY Numar_Reparatii DESC, Nume_Client ASC";

                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Top Clienți cu Cele Mai Multe Reparații</h3><br>";
                echo "<table border='1'>";
                echo "<tr><th>Nume Client</th><th>Prenume Client</th><th>Număr Reparații</th><th>Tipuri Dispozitive</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                <td>" . htmlspecialchars($row['Nume_Client']) . "</td>
                <td>" . htmlspecialchars($row['Prenume_Client']) . "</td>
                <td>" . htmlspecialchars($row['Numar_Reparatii']) . "</td>
                <td>" . htmlspecialchars($row['Tipuri_Dispozitive']) . "</td>
              </tr>";
                }
                echo "</table>";
            }



            echo "</table>";
            ?>

            <br>
            <a href="logout.php">Logout</a>
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
        <title>Profil Angajat</title>
        <style>
            .details-row {
                display: none;
            }
        </style>
        <script>
            // Afișarea formularului pentru adăugare
            function showAddServiceForm() {
                document.getElementById('addServiceButton').style.display = 'none';
                document.getElementById('addServiceForm').style.display = 'block';
            }

            function hideAddServiceForm() {
                document.getElementById('addServiceButton').style.display = 'block';
                document.getElementById('addServiceForm').style.display = 'none';
            }

            // Afișarea formularului pentru modificare
            function showEditServiceForm(serviceId, deviceId, description, startDate, endDate, cost) {
                // Setăm valorile ascunse pentru ID-uri
                document.getElementById('editServiceId').value = serviceId;
                document.getElementById('editDeviceIdHidden').value = deviceId;

                // Setăm celelalte câmpuri editabile
                document.getElementById('editDescription').value = description;
                document.getElementById('editStartDate').value = startDate;
                document.getElementById('editEndDate').value = endDate;
                document.getElementById('editCost').value = cost;

                // Afișăm formularul de editare
                document.getElementById('editServiceForm').style.display = 'block';
            }

            function hideEditServiceForm() {
                document.getElementById('editServiceForm').style.display = 'none';
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

            <!-- Dropdown pentru selectarea tabelului - Angajați -->
            <form method="post">
                <label for="tableSelect">Selectează tabelul:</label>
                <select id="tableSelect" name="selectedTable" onchange="this.form.submit()">
                    <option value="default" <?php if (!isset($_POST['selectedTable']) || $_POST['selectedTable'] === 'default')
                        echo 'selected'; ?>>Serviciile Tale</option>
                    <option value="employee_services" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'employee_services')
                        echo 'selected'; ?>>Istoric Angajați-Servicii
                    </option>
                    <option value="employee_clients" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'employee_clients')
                        echo 'selected'; ?>>Istoric Angajați-Clienți</option>
                    <option value="employee_devices" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'employee_devices')
                        echo 'selected'; ?>>Istoric Angajați-Dispozitive
                    </option>
                    <option value="clients_services" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'clients_services')
                        echo 'selected'; ?>>Clienții și Serviciile Lor
                    </option>
                    <option value="clients_payments" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'clients_payments')
                        echo 'selected'; ?>>Clienții și Plățile Lor</option>
                    <!-- Interogări complexe -->
                    <option value="employee_services_payments" <?php if (isset($_POST['selectedTable']) && $_POST['selectedTable'] === 'employee_services_payments')
                        echo 'selected'; ?>>Servicii și Plăți
                        Gestionate</option>
                </select>
            </form>


            <br>

            <?php
            $selectedTable = isset($_POST['selectedTable']) ? $_POST['selectedTable'] : 'default';

            if ($selectedTable === 'default') {
                echo "<h3>Serviciile tale</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Descriere</th><th>Data Începerii</th><th>Data Finalizării</th><th>Cost</th><th>Acțiuni</th></tr></thead>";
                echo "<tbody>";
                if ($result_services && $result_services->num_rows > 0) {
                    while ($row = $result_services->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Descriere']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Data_Inceperii']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Data_Finalizarii']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Cost']) . "</td>";
                        echo "<td>";
                        echo "<form method='post' style='display:inline;'>
            <input type='hidden' name='delete_service' value='" . $row['ID_Service'] . "'>
            <button type='submit' onclick=\"return confirm('Ești sigur că vrei să ștergi acest serviciu?')\">Delete</button><br><br>
        </form>";
                        echo "<button 
            onclick=\"showEditServiceForm('" . htmlspecialchars($row['ID_Service']) . "', '" . htmlspecialchars($row['ID_Device']) . "', '" . htmlspecialchars($row['Descriere']) . "', '" . htmlspecialchars($row['Data_Inceperii']) . "', '" . htmlspecialchars($row['Data_Finalizarii']) . "', '" . htmlspecialchars($row['Cost']) . "')\">
            Update
          </button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nu există servicii disponibile.</td></tr>";
                }
                echo "</tbody>";

                echo "</table>";

                // Formular pentru modificare
                echo "<div id='editServiceForm' style='display:none; margin-top:20px;'>
    <h3>Modificare Serviciu</h3>
    <form method='post'>
        <!-- ID-urile sunt trimise ca valori ascunse -->
        <input type='hidden' id='editServiceId' name='serviceId'>
        <input type='hidden' id='editDeviceIdHidden' name='deviceId'>

        <!-- Câmpuri editabile -->
        <label for='editDescription'>Descriere:</label>
        <input type='text' id='editDescription' name='description' required><br>

        <label for='editStartDate'>Data Începerii:</label>
        <input type='date' id='editStartDate' name='startDate' required><br>

        <label for='editEndDate'>Data Finalizării:</label>
        <input type='date' id='editEndDate' name='endDate'><br>

        <label for='editCost'>Cost:</label>
        <input type='number' id='editCost' name='cost' step='0.01' required><br><br>

        <button type='submit' name='updateService'>Actualizează Serviciu</button><br><br>
        <button type='button' onclick='hideEditServiceForm()'>Anulează</button><br><br>
    </form>
</div>";


                // Formular pentru adăugare
                echo "<br><button id='addServiceButton' onclick='showAddServiceForm()'>Add new service</button><br>";
                echo "<div id='addServiceForm' style='display:none;'>
        <br><h3>Adaugă Serviciu</h3>
        <form method='post'>
            <label for='deviceId'>ID Dispozitiv:</label>
            <input type='number' id='deviceId' name='deviceId' required><br>
            <label for='description'>Descriere:</label>
            <input type='text' id='description' name='description' required><br>
            <label for='startDate'>Data Începerii:</label>
            <input type='date' id='startDate' name='startDate' required><br>
            <label for='endDate'>Data Finalizării:</label>
            <input type='date' id='endDate' name='endDate'><br>
            <label for='cost'>Cost:</label>
            <input type='number' id='cost' name='cost' step='0.01' required><br>
            <button type='submit' name='addService'>Adaugă Serviciu</button><br><br>
            <button type='button' onclick='hideAddServiceForm()'>Anulează</button>
        </form>
    </div>";
            } elseif ($selectedTable === 'employee_services') {
                $sql = "
        SELECT 
            E.Nume AS Nume_Angajat, 
            E.Prenume AS Prenume_Angajat, 
            S.Descriere, 
            S.Data_Inceperii, 
            S.Data_Finalizarii
        FROM Employees E
        JOIN Service_Employees SE ON E.ID_Employee = SE.ID_Employee
        JOIN Services S ON SE.ID_Service = S.ID_Service
        WHERE E.ID_Employee = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Istoric Angajați-Servicii</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Nume Angajat</th><th>Prenume Angajat</th><th>Descriere</th><th>Data Începerii</th><th>Data Finalizării</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Nume_Angajat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Prenume_Angajat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Descriere']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Data_Inceperii']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Data_Finalizarii']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Nu există date disponibile.</td></tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } elseif ($selectedTable === 'employee_clients') {
                $sql = "
        SELECT 
            E.Nume AS Nume_Angajat,
            E.Prenume AS Prenume_Angajat,
            C.Nume AS Nume_Client,
            C.Prenume AS Prenume_Client,
            D.Tip_Dispozitiv,
            S.Descriere AS Serviciu
        FROM Employees E
        JOIN Service_Employees SE ON E.ID_Employee = SE.ID_Employee
        JOIN Services S ON SE.ID_Service = S.ID_Service
        JOIN Devices D ON S.ID_Device = D.ID_Device
        JOIN Clients C ON D.ID_Client = C.ID_Client
        WHERE E.ID_Employee = ?
        ORDER BY E.Nume, E.Prenume, C.Nume, C.Prenume";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Istoric Angajați-Clienți</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Nume Angajat</th><th>Prenume Angajat</th><th>Nume Client</th><th>Prenume Client</th><th>Tip Dispozitiv</th><th>Serviciu</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Nume_Angajat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Prenume_Angajat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Nume_Client']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Prenume_Client']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Tip_Dispozitiv']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Serviciu']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Nu există date disponibile.</td></tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } elseif ($selectedTable === 'employee_devices') {
                $sql = "
        SELECT 
            E.Nume AS Nume_Angajat, 
            E.Prenume AS Prenume_Angajat, 
            D.Tip_Dispozitiv, 
            D.Marca, 
            D.Model
        FROM Employees E
        JOIN Service_Employees SE ON E.ID_Employee = SE.ID_Employee
        JOIN Services S ON SE.ID_Service = S.ID_Service
        JOIN Devices D ON S.ID_Device = D.ID_Device
        WHERE E.ID_Employee = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Istoric Angajați-Dispozitive</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Nume Angajat</th><th>Prenume Angajat</th><th>Tip Dispozitiv</th><th>Marca</th><th>Model</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Nume_Angajat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Prenume_Angajat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Tip_Dispozitiv']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Marca']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Model']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nu există date disponibile.</td></tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } elseif ($selectedTable === 'clients_services') {
                $sql = "      
        SELECT 
            C.Nume AS Nume_Client, 
            C.Prenume AS Prenume_Client, 
            S.Descriere AS Descriere_Serviciu, 
            S.Cost AS Cost_Serviciu
        FROM Clients C
        JOIN Devices D ON C.ID_Client = D.ID_Client
        JOIN Services S ON D.ID_Device = S.ID_Device
        JOIN Service_Employees SE ON S.ID_Service = SE.ID_Service
        WHERE SE.ID_Employee = ?
        ORDER BY C.Nume, C.Prenume, S.Descriere;";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Clienții și Serviciile</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Nume Client</th><th>Prenume Client</th><th>Descriere Serviciu</th><th>Cost Serviciu</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Nume_Client']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Prenume_Client']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Descriere_Serviciu']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Cost_Serviciu']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Nu există date disponibile.</td></tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } elseif ($selectedTable === 'clients_payments') {
                $sql = "
        SELECT 
            C.Nume AS Nume_Client, 
            C.Prenume AS Prenume_Client, 
            P.Suma, 
            P.Metoda_Platii, 
            P.Data_Platii
        FROM Clients C
        JOIN Invoices I ON C.ID_Client = I.ID_Client
        JOIN Payments P ON I.ID_Invoice = P.ID_Invoice
        WHERE C.ID_Client IN (SELECT DISTINCT ID_Client 
        FROM Devices WHERE ID_Device IN 
        (SELECT ID_Device FROM Service_Employees WHERE ID_Employee = ?))";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h3>Clienții și Plățile Lor</h3><br>";
                echo "<table border='1' cellspacing='0' cellpadding='10'>";
                echo "<thead><tr><th>Nume Client</th><th>Prenume Client</th><th>Suma</th><th>Metoda Plății</th><th>Data Plății</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Nume_Client']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Prenume_Client']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Suma']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Metoda_Platii']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Data_Platii']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nu există date disponibile.</td></tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } elseif ($selectedTable === 'employee_services_payments') {
                $sql = "
                
        SELECT 
            s.Descriere AS Descriere_Serviciu,
            d.Tip_Dispozitiv AS Tip_Dispozitiv,
            d.Marca AS Marca,
            d.Model AS Model,
            s.Cost AS Cost_Serviciu,
            s.Data_Inceperii AS Data_Inceput
        FROM services s
        INNER JOIN devices d ON s.ID_Device = d.ID_Device
        WHERE s.ID_Service IN (
            SELECT se.ID_Service
            FROM service_employees se
            WHERE se.ID_Employee = ?
        )
        ORDER BY s.Data_Inceperii DESC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $result = $stmt->get_result();

                // Titlul tabelului
                echo "<h3>Istoric Servicii Gestionate</h3><br>";

                // Începutul tabelului HTML
                echo "<table border='1'>";
                echo "<tr><th>Tip Dispozitiv</th><th>Marca</th><th>Model</th><th>Descriere Serviciu</th><th>Cost Serviciu</th><th>Data Început</th></tr>";

                // Iterăm prin rezultatele query-ului
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
            <td>" . htmlspecialchars($row['Tip_Dispozitiv']) . "</td>
            <td>" . htmlspecialchars($row['Marca']) . "</td>
            <td>" . htmlspecialchars($row['Model']) . "</td>
            <td>" . htmlspecialchars($row['Descriere_Serviciu']) . "</td>
            <td>" . htmlspecialchars($row['Cost_Serviciu']) . "</td>
            <td>" . htmlspecialchars($row['Data_Inceput']) . "</td>
        </tr>";
                }

                // Închidem tabelul
                echo "</table>";
            }

            ?>
            <br>
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