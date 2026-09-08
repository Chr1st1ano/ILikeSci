<?php
$inputFile = isset($argv[1]) ? $argv[1] : __DIR__ . '/manuscript.docx';
$outputFile = isset($argv[2]) ? $argv[2] : __DIR__ . '/manuscript_paragraphs.txt';

if (!file_exists($inputFile)) {
    echo "File not found: $inputFile\n";
    exit(1);
}

$zip = new ZipArchive;
if ($zip->open($inputFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    if (!$xml) {
        echo "Could not find word/document.xml in $inputFile\n";
        exit(1);
    }

    $dom = new DOMDocument();
    @$dom->loadXML($xml);
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
    
    $paragraphs = $xpath->query('//w:p');
    $out = [];
    foreach ($paragraphs as $p) {
        $texts = $xpath->query('.//w:t', $p);
        $line = '';
        foreach ($texts as $t) {
            $line .= $t->nodeValue;
        }
        if (trim($line) !== '') {
            $out[] = $line;
        }
    }
    file_put_contents($outputFile, implode("\n\n", $out));
    echo "Successfully extracted " . count($out) . " paragraphs into " . basename($outputFile) . "\n";
} else {
    echo "Failed to open $inputFile as a zip/docx archive\n";
    exit(1);
}
