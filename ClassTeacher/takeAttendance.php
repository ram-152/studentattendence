<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch class and class arm information
$query = "SELECT tblclass.className, tblclassarms.classArmName 
    FROM tblclassteacher
    INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
    INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
    WHERE tblclassteacher.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

// Get active session and term
$querey = mysqli_query($conn, "SELECT * FROM tblsessionterm WHERE isActive = '1'");
$rwws = mysqli_fetch_array($querey);
$sessionTermId = $rwws['Id'];

// Get today's date
$dateTaken = date("Y-m-d");

// Check if attendance has already been taken for today
$qurty = mysqli_query($conn, "SELECT * FROM tblattendance 
    WHERE classId = '$_SESSION[classId]' AND classArmId = '$_SESSION[classArmId]' AND dateTimeTaken='$dateTaken'");
$count = mysqli_num_rows($qurty);

// If no attendance record exists, insert records for all students in the class
if ($count == 0) {
    $qus = mysqli_query($conn, "SELECT * FROM tblstudents 
        WHERE classId = '$_SESSION[classId]' AND classArmId = '$_SESSION[classArmId]'");
    while ($ros = $qus->fetch_assoc()) {
        $qquery = mysqli_query($conn, "INSERT INTO tblattendance (admissionNo, classId, classArmId, sessionTermId, status, dateTimeTaken) 
            VALUES ('$ros[admissionNumber]', '$_SESSION[classId]', '$_SESSION[classArmId]', '$sessionTermId', '0', '$dateTaken')");
    }
}

// Handling attendance submission
if (isset($_POST['save'])) {
    $admissionNo = $_POST['admissionNo'];
    $check = $_POST['check'];
    $N = count($admissionNo);

    // Check if attendance has already been taken (status = 1)
    $qurty = mysqli_query($conn, "SELECT * FROM tblattendance 
        WHERE classId = '$_SESSION[classId]' AND classArmId = '$_SESSION[classArmId]' AND dateTimeTaken='$dateTaken' AND status = '1'");
    $count = mysqli_num_rows($qurty);

    if ($count > 0) {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Attendance has been taken for today!</div>";
    } else {
        // Update attendance status for selected students
        for ($i = 0; $i < $N; $i++) {
            if (isset($check[$i])) {
                $qquery = mysqli_query($conn, "UPDATE tblattendance SET status='1' WHERE admissionNo = '$check[$i]'");
                if ($qquery) {
                    $statusMsg = "<div class='alert alert-success'  style='margin-right:700px;'>Attendance Taken Successfully!</div>";
                } else {
                    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred!</div>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>Dashboard</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
    <script>
        function matchValueToCheckboxes() {
            var textboxValue = document.getElementById("textInput").value;
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = (checkbox.value === textboxValue);
            });
        }
    </script>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include "Includes/sidebar.php";?>
        <!-- Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?php include "Includes/topbar.php";?>
                <!-- Topbar -->

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Take Attendance (Today's Date: <?php echo date("m-d-Y"); ?>)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">All Students in Class</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <form method="post">
                                <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h6 class="m-0 font-weight-bold text-primary">All Students in (<?php echo $rrw['className'].' - '.$rrw['classArmName'];?>) Class</h6>
                                    </div>
                                    <div class="table-responsive p-3">
                                        <?php echo $statusMsg; ?>
                                        <table class="table align-items-center table-flush table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Other Name</th>
                                                    <th>Admission No</th>
                                                    <th>Class</th>
                                                    <th>Section</th>
                                                    <th>Check</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <input type="textbox" id="textInput" name="textInput" onkeyup="matchValueToCheckboxes()" placeholder="Enter PIN">
                                                <?php
                                                    $query = "SELECT tblstudents.Id, tblstudents.admissionNumber, tblclass.className, tblclass.Id AS classId, 
                                                                tblclassarms.classArmName, tblclassarms.Id AS classArmId, tblstudents.firstName, 
                                                                tblstudents.lastName, tblstudents.otherName, tblstudents.admissionNumber, tblstudents.dateCreated 
                                                            FROM tblstudents
                                                            INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                                                            INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
                                                            WHERE tblstudents.classId = '$_SESSION[classId]' AND tblstudents.classArmId = '$_SESSION[classArmId]'";
                                                    $rs = $conn->query($query);
                                                    $sn = 0;

                                                    if ($rs->num_rows > 0) {
                                                        while ($rows = $rs->fetch_assoc()) {
                                                            $sn++;
                                                            echo "
                                                            <tr>
                                                                <td>$sn</td>
                                                                <td>{$rows['firstName']}</td>
                                                                <td>{$rows['lastName']}</td>
                                                                <td>{$rows['otherName']}</td>
                                                                <td>{$rows['admissionNumber']}</td>
                                                                <td>{$rows['className']}</td>
                                                                <td>{$rows['classArmName']}</td>
                                                                <td><input type='checkbox' name='check[]' value='{$rows['admissionNumber']}' class='form-control'></td>
                                                            </tr>
                                                            <input type='hidden' name='admissionNo[]' value='{$rows['admissionNumber']}' />";
                                                        }
                                                    } else {
                                                        echo "<div class='alert alert-danger' role='alert'>No Record Found!</div>";
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                        <br>
                                        <button type="submit" name="save" class="btn btn-primary">Take Attendance</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <?php include "Includes/footer.php"; ?>
                <!-- Footer -->
            </div>
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
</body>

</html>
