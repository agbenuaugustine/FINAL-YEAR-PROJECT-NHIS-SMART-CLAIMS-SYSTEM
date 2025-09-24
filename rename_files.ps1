# PowerShell script to rename PHP files and update internal links
# Removes .php extension from user-facing files while keeping utility files

Write-Host "Starting PHP file renaming process..." -ForegroundColor Green

# Define directories to keep .php extensions (utility/library files)
$excludeDirs = @(
    "config",
    "controllers", 
    "models",
    "utils",
    "PHPMailer"
)

# Function to check if a file should be excluded
function Should-Exclude-File {
    param($filePath)
    foreach ($excludeDir in $excludeDirs) {
        if ($filePath -like "*\$excludeDir\*") {
            return $true
        }
    }
    return $false
}

# Get all PHP files
$phpFiles = Get-ChildItem -Path "." -Filter "*.php" -Recurse

# Separate files to rename and files to keep
$filesToRename = @()
$filesToKeep = @()

foreach ($file in $phpFiles) {
    if (Should-Exclude-File $file.FullName) {
        $filesToKeep += $file
    } else {
        $filesToRename += $file
    }
}

Write-Host "`nFiles to rename (remove .php):" -ForegroundColor Yellow
$filesToRename | ForEach-Object { Write-Host "  $_" }

Write-Host "`nFiles to keep (.php extension):" -ForegroundColor Cyan
$filesToKeep | ForEach-Object { Write-Host "  $_" }

# Create mapping for link updates
$linkMappings = @{}
foreach ($file in $filesToRename) {
    $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "").Replace("\", "/")
    $newPath = $relativePath -replace "\.php$", ""
    $linkMappings[$relativePath] = $newPath
}

Write-Host "`nLink mappings that will be updated:" -ForegroundColor Magenta
$linkMappings.GetEnumerator() | ForEach-Object { Write-Host "  $($_.Key) -> $($_.Value)" }

# Ask for confirmation
$confirmation = Read-Host "`nDo you want to proceed? (y/N)"
if ($confirmation -ne 'y' -and $confirmation -ne 'Y') {
    Write-Host "Operation cancelled." -ForegroundColor Red
    exit
}

# Step 1: Update all internal links in ALL PHP files (both renamed and kept)
Write-Host "`nStep 1: Updating internal links..." -ForegroundColor Green

$allPhpFiles = Get-ChildItem -Path "." -Filter "*.php" -Recurse
$totalUpdates = 0

foreach ($file in $allPhpFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Update each mapping
    foreach ($mapping in $linkMappings.GetEnumerator()) {
        $oldLink = $mapping.Key
        $newLink = $mapping.Value
        
        # Various patterns to catch different link formats
        $patterns = @(
            # href="path.php"
            "href=[`"']([^`"']*/)?" + [regex]::Escape($oldLink.Split('/')[-1]) + "[`"']",
            # action="path.php"  
            "action=[`"']([^`"']*/)?" + [regex]::Escape($oldLink.Split('/')[-1]) + "[`"']",
            # location.href = "path.php"
            "location\.href\s*=\s*[`"']([^`"']*/)?" + [regex]::Escape($oldLink.Split('/')[-1]) + "[`"']",
            # window.location = "path.php"
            "window\.location\s*=\s*[`"']([^`"']*/)?" + [regex]::Escape($oldLink.Split('/')[-1]) + "[`"']",
            # include "path.php" or require "path.php"
            "(include|require)(_once)?\s*[`"']([^`"']*/)?" + [regex]::Escape($oldLink.Split('/')[-1]) + "[`"']",
            # Simple quoted references
            "[`"']([^`"']*/)?" + [regex]::Escape($oldLink.Split('/')[-1]) + "[`"']"
        )
        
        foreach ($pattern in $patterns) {
            if ($content -match $pattern) {
                $fileName = $oldLink.Split('/')[-1]
                $newFileName = $newLink.Split('/')[-1]
                $content = $content -replace $fileName, $newFileName
            }
        }
    }
    
    # Save if content changed
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "  Updated links in: $($file.Name)" -ForegroundColor Yellow
        $totalUpdates++
    }
}

Write-Host "Updated $totalUpdates files with new links." -ForegroundColor Green

# Step 2: Rename the actual files
Write-Host "`nStep 2: Renaming files..." -ForegroundColor Green

$renamedCount = 0
foreach ($file in $filesToRename) {
    $newName = $file.FullName -replace "\.php$", ""
    
    try {
        Rename-Item -Path $file.FullName -NewName ([System.IO.Path]::GetFileName($newName))
        Write-Host "  Renamed: $($file.Name) -> $([System.IO.Path]::GetFileName($newName))" -ForegroundColor Green
        $renamedCount++
    }
    catch {
        Write-Host "  Error renaming $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nProcess completed!" -ForegroundColor Green
Write-Host "Renamed $renamedCount files" -ForegroundColor Yellow
Write-Host "Updated links in $totalUpdates files" -ForegroundColor Yellow

Write-Host "`nYour .htaccess is already configured to handle extensionless PHP files." -ForegroundColor Cyan
Write-Host "You can now access your pages without .php extensions!" -ForegroundColor Cyan

# Create a summary report
$reportContent = @"
PHP File Renaming Report
Generated: $(Get-Date)

Files Renamed ($renamedCount):
$($filesToRename | ForEach-Object { "- $($_.Name) -> $($_.Name -replace '\.php$', '')" } | Out-String)

Files Kept with .php extension ($($filesToKeep.Count)):
$($filesToKeep | ForEach-Object { "- $($_.Name)" } | Out-String)

Link Updates Made: $totalUpdates files updated

Next Steps:
1. Test your application to ensure all links work
2. Update any external bookmarks or links
3. Consider adding redirects for old .php URLs if needed
"@

Set-Content -Path "rename_report.txt" -Value $reportContent
Write-Host "`nDetailed report saved to: rename_report.txt" -ForegroundColor Cyan