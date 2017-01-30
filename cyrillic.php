<?php

$arrCyrillicLatinMapping = [

  'a' => [ 'small' => 'а', 'caps' => 'А' ],
  'b' => [ 'small' => 'б', 'caps' => 'Б' ],
  'c' => [ 'small' => 'ц', 'caps' => 'Ц' ],
  'd' => [ 'small' => 'д', 'caps' => 'Д' ],
  'e' => [ 'small' => 'е', 'caps' => 'Е' ],
  'f' => [ 'small' => 'ф', 'caps' => 'Ф' ],
  'g' => [ 'small' => 'г', 'caps' => 'Г' ],
  'h' => [ 'small' => 'х', 'caps' => 'Х' ],
  'i' => [ 'small' => 'и', 'caps' => 'И' ],
  'j' => [ 'small' => 'й', 'caps' => 'Й' ],
  'k' => [ 'small' => 'к', 'caps' => 'К' ],
  'l' => [ 'small' => 'л', 'caps' => 'Л' ],
  'm' => [ 'small' => 'м', 'caps' => 'М' ],
  'n' => [ 'small' => 'н', 'caps' => 'Н' ],
  'o' => [ 'small' => 'о', 'caps' => 'О' ],
  'p' => [ 'small' => 'п', 'caps' => 'П' ],
  'r' => [ 'small' => 'р', 'caps' => 'Р' ],
  's' => [ 'small' => 'с', 'caps' => 'С' ],
  't' => [ 'small' => 'т', 'caps' => 'Т' ],
  'u' => [ 'small' => 'у', 'caps' => 'У' ],
  'v' => [ 'small' => 'в', 'caps' => 'В' ],
  'w' => [ 'small' => 'в', 'caps' => 'В' ],
  'x' => [ 'small' => 'х', 'caps' => 'X' ],
  'y' => [ 'small' => 'ъ', 'caps' => 'Ь' ],
  'z' => [ 'small' => 'з', 'caps' => 'З' ],

  'zh' => [ 'small' => 'ж', 'caps' => 'Ж' ],
  'ch' => [ 'small' => 'ч', 'caps' => 'Ч' ],
  'sh' => [ 'small' => 'ш', 'caps' => 'Ш' ],
  'sht'=> [ 'small' => 'щ', 'caps' => 'Щ' ],
  'ja' => [ 'small' => 'я', 'caps' => 'Я' ],
  'ju' => [ 'small' => 'ю', 'caps' => 'Ю' ]

];

while(true) {

  $strLatinLetter = array_rand($arrCyrillicLatinMapping);
  $arrCyrillicLetter = $arrCyrillicLatinMapping[$strLatinLetter];

  print "\n\nmember this letter?\n\n";
  print $arrCyrillicLetter['small'];
  print "\n\n";

  $strInput = trim(fgets(STDIN, 8));

  if(':q' === $strInput) {

    print "\nдовиждане\n\n";
    exit;

  }

  print $strInput === $strLatinLetter ? 'oh, I member!' : 'wrong: ' . $arrCyrillicLetter['small'] . ' = ' . $strLatinLetter;

}
