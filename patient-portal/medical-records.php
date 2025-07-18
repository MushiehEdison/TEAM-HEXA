<?php
require_once 'includes/portal-auth.php';
requirePatientAuth();
require_once 'includes/portal-functions.php';

$patientId = $_SESSION['patient_id'];
$filter = $_GET['filter'] ?? 'all';
$medicalRecords = getPatientMedicalRecords($patientId, $filter === 'all' ? null : $filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - Medical Records</title>
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <div class="portal-container">
        <?php include 'portal-header.php'; ?>
        
        <main class="portal-main">
            <div class="portal-content">
                <h1>Medical Records</h1>
                
                <div class="record-filters">
                    <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All Records</a>
                    <a href="?filter=prescription" class="btn <?php echo $filter === 'prescription' ? 'btn-primary' : 'btn-secondary'; ?>">Prescriptions</a>
                    <a href="?filter=lab_result" class="btn <?php echo $filter === 'lab_result' ? 'btn-primary' : 'btn-secondary'; ?>">Lab Results</a>
                    <a href="?filter=progress_note" class="btn <?php echo $filter === 'progress_note' ? 'btn-primary' : 'btn-secondary'; ?>">Progress Notes</a>
                    <a href="?filter=imaging" class="btn <?php echo $filter === 'imaging' ? 'btn-primary' : 'btn-secondary'; ?>">Imaging</a>
                    <a href="?filter=vaccination" class="btn <?php echo $filter === 'vaccination' ? 'btn-primary' : 'btn-secondary'; ?>">Vaccinations</a>
                </div>
                
                <?php if (count($medicalRecords) > 0): ?>
                    <div class="record-list">
                        <?php foreach ($medicalRecords as $record): ?>
                        <div class="record-card record-type-<?php echo $record['record_type']; ?>">
                            <div class="record-header">
                                <h3><?php echo $record['title']; ?></h3>
                                <span class="record-date">
                                    <?php echo date('M j, Y', strtotime($record['date_recorded'])); ?>
                                </span>
                            </div>
                            
                            <div class="record-body">
                                <p><?php echo $record['description'] ?: 'No description provided.'; ?></p>
                                
                                <?php if ($record['file_path']): ?>
                                    <div class="record-attachment">
                                        <i class="icon-paperclip"></i>
                                        <a href="<?php echo $record['file_path']; ?>" target="_blank" class="btn btn-sm">
                                            View Attachment
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="record-footer">
                                <span class="record-type">
                                    <?php echo ucfirst(str_replace('_', ' ', $record['record_type'])); ?>
                                </span>
                                <span class="recorded-by">
                                    Recorded by: <?php echo $record['recorded_by_name']; ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No medical records found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>