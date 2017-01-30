<?php

$resWords = fopen('./words.csv', 'r');
$arrWords = array_map('str_getcsv', file('./words.csv'));
array_shift($arrWords);

fclose($resWords);

while(true) {

  $intRandomRow = array_rand($arrWords);

  if(empty($arrWords[$intRandomRow][0]))
    continue;

  print "\n\ntranslate: " . $arrWords[$intRandomRow][2] . "\n\n";

  $strInput = trim(fgets(STDIN, 1024));

  if(':q' === $strInput) {

    print "\nдовиждане\n\n";
    exit;

  }

  print ($strInput === $arrWords[$intRandomRow][3] ? 'correct' : 'wrong!') . ' - ' . $arrWords[$intRandomRow][2] . ' = ' . $arrWords[$intRandomRow][3] . ' = ' . $arrWords[$intRandomRow][1];

}
