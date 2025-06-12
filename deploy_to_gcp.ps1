# PowerShell script to zip the finedica folder for Google Cloud VM upload
$source = "$PSScriptRoot"
$zipfile = "$PSScriptRoot\finedica_upload.zip"
if (Test-Path $zipfile) { Remove-Item $zipfile }
Compress-Archive -Path "$source\*" -DestinationPath $zipfile -Force
Write-Host "Zipped finedica folder to $zipfile. Upload this file to your Google Cloud VM."
