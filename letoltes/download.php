<?php
// Állítsd be: ezt a mappát csomagoljuk
$folderPath = __DIR__ . DIRECTORY_SEPARATOR . 'letoltesre';

// Biztonsági ellenőrzés
if (!is_dir($folderPath)) {
  http_response_code(404);
  exit("A mappa nem található.");
}

$zip = new ZipArchive();

// Ide készítjük ideiglenesen a zip-et
$tmpZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mappa_letoltes_' . uniqid() . '.zip';

if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
  http_response_code(500);
  exit("Nem sikerült létrehozni a ZIP fájlt.");
}

// Rekurzív bejárás és hozzáadás
$files = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($folderPath, FilesystemIterator::SKIP_DOTS),
  RecursiveIteratorIterator::LEAVES_ONLY
);

$baseLen = strlen($folderPath) + 1;

foreach ($files as $file) {
  if ($file->isFile()) {
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, $baseLen); // zipen belüli útvonal
    $zip->addFile($filePath, $relativePath);
  }
}

$zip->close();

// Letöltés fejlécek
$downloadName = 'letoltesre_mappa.zip';

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($tmpZipPath));
header('Pragma: public');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

// Kiírás a kimenetre
readfile($tmpZipPath);

// Takarítás
@unlink($tmpZipPath);
exit;
