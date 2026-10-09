$file1 = "app/Http/Controllers/ApprovalPendapatanController.php"
if (Test-Path $file1) {
    $c = Get-Content $file1 -Raw
    $c = $c -replace 'trackingoutstanding::', 'trackingOutstanding::'
    Set-Content $file1 $c
}

$file2 = "app/Http/Controllers/Crm/ApprovalPendapatanSalesController.php"
if (Test-Path $file2) {
    $c = Get-Content $file2 -Raw
    $c = $c -replace '\bRkm::', 'RKM::'
    Set-Content $file2 $c
}

$file3 = "app/Http/Controllers/Crm/checklistRKMController.php"
if (Test-Path $file3) {
    $c = Get-Content $file3 -Raw
    $c = $c -replace '\bChecklistRKM::', 'checklistRKM::'
    Set-Content $file3 $c
}

$file4 = "app/Http/Controllers/DaftarTugasController.php"
if (Test-Path $file4) {
    $c = Get-Content $file4 -Raw
    $c = $c -replace '\bAbsensikaryawan::', 'AbsensiKaryawan::'
    Set-Content $file4 $c
}

$file5 = "app/Http/Controllers/ExpenseHubController.php"
if (Test-Path $file5) {
    $c = Get-Content $file5 -Raw
    $c = $c -replace '\bdetailexpenseHub::', 'detailExpenseHub::'
    Set-Content $file5 $c
}

echo "Replace done."
