# Script to find and update any remaining .php links to extensionless versions
Write-Host "Searching for .php references in files..." -ForegroundColor Green

# Get all files that might contain links (excluding binary files)
$searchFiles = Get-ChildItem -Path "." -Recurse | Where-Object { 
    $_.Extension -in @('.html', '.htm', '.js', '.css', '.php', '') -and
    !$_.PSIsContainer -and
    $_.FullName -notlike "*PHPMailer*" -and
    $_.FullName -notlike "*language*"
}

# Define the files that were renamed (without the directory paths)
$renamedFiles = @(
    'index', 'register', 'login', 'logout', 'dashboard', 'patients', 'users', 'visits', 
    'vital-signs', 'claims', 'reports', 'patient-registration', 'service-requisition',
    'hospital-management', 'user-management', 'claims-dashboard', 'finance-dashboard',
    'lab-dashboard', 'pharmacy-dashboard', 'opd-dashboard', 'records-dashboard',
    'diagnosis-medication', 'claims-processing', 'client-registration', 'auth',
    'unauthorized', 'secure_auth', 'apply_security', 'test_security', 'header', 
    'footer', 'department_dashboard_template', 'hospital-register', 'services-api',
    'vital-signs-api', 'validate-token', 'test-email', 'test'
)

$totalUpdates = 0
$filesToFix = @()

foreach ($file in $searchFiles) {
    try {
        $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
        if ($content) {
            $originalContent = $content
            $fileUpdated = $false
            
            # Check for .php references to renamed files
            foreach ($fileName in $renamedFiles) {
                # Simple replacement - look for filename.php and replace with filename
                if ($content -match "$fileName\.php") {
                    $content = $content -replace "$fileName\.php", $fileName
                    $fileUpdated = $true
                }
            }
            
            # Save if content changed
            if ($fileUpdated -and $content -ne $originalContent) {
                Set-Content -Path $file.FullName -Value $content -NoNewline
                Write-Host "Updated: $($file.Name)" -ForegroundColor Yellow
                $filesToFix += $file.Name
                $totalUpdates++
            }
        }
    }
    catch {
        Write-Host "Error processing $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nUpdate complete!" -ForegroundColor Green
Write-Host "Files updated: $totalUpdates" -ForegroundColor Yellow

if ($filesToFix.Count -gt 0) {
    Write-Host "`nFiles that were updated:" -ForegroundColor Cyan
    $filesToFix | ForEach-Object { Write-Host "  - $_" }
} else {
    Write-Host "No .php references found that needed updating." -ForegroundColor Green
}

Write-Host "`nYour files are now ready for extensionless URLs!" -ForegroundColor Green