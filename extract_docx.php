<?php
$zip = new ZipArchive;
if ($zip->open('manuscript.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
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
    file_put_contents('manuscript_paragraphs.txt', implode("\n\n", $out));
    echo "Successfully extracted " . count($out) . " paragraphs.\n";
} else {
    echo "Failed to open manuscript.docx\n";
}
