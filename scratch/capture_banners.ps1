$edge = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
$artDir = "C:\Users\rashe\.gemini\antigravity\brain\2aa41e5f-5a0b-4839-8389-12aa50655bc5"
$destDir = "C:\Users\rashe\Local Sites\wp\app\public\wp-content\plugins\omnifywp-ecommerce\scratch\banners"

for ($i = 1; $i -le 6; $i++) {
    $url = "http://wp.local/wp-content/plugins/omnifywp-ecommerce/scratch/banners/banner$i.html"
    $outFile = "$artDir\banner$i.png"
    & $edge --headless --disable-gpu --window-size=1200,675 "--screenshot=$outFile" "$url"
    Start-Sleep -Milliseconds 800
    Copy-Item -Path $outFile -Destination "$destDir\banner$i.png" -Force
    Write-Host "Captured and copied banner$i.png"
}
