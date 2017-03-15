<?php

#namespace easyslavic; TODO

#
# workflow
#
# 1. e.g. file or database holding translations
# 2. 2 arrays, one holding available languages, the other holds the translations
# 3. translations needs to be converted to category object
# 3. get instance of WordsManager
# 3.1. initiated with categories and languages
# 
#

#
# [
#   'numbers' => [
#     0 => [ 'eins', 'one' ],
#     1 => [ 'zwei', 'two' ]
#   ],
#   'animals' => [
#     0 => [ '', '' ],
#     1 => [ '', '' ]
#   ]
# ]
#
#
# possible config
# [
#   'ui_language' => '',
#   'translate_from' => '',
#   'translate_to' => [], # support for multiple languages, e.g. *from* bulgarian cyrillic *to* bulgarian latin *to* english
#   'categories' => []
# ]
#
#
# - there must be some kind of configuration, config file, config through cli params, statis config array or something else
# - it should hold the ui language, but this is maybe by default english
# - it should tell which language we want to learn
# - should know about 'special' languages like bulgarian, that uses different letters, so there should be an option to enable/disable
#   the translation from cyrillic to latin before asked for the actual translation
# - optional (yagni) different modes of how we want to be asked, but this is undefined yet
# - maybe we can define here categories of words already, that we will ask about, so there isn't a ui required for this
#
# - there must be an interface that takes the user input and knows whether the given answer is correct or not

function convertToCategories(array $arrWords, array $arrLanguages) : array {

  if(empty($arrWords))
    return [];
    
  $strCategory  = '';
  $intCounter = -1;
  $arrCategories = [];
  
  foreach($arrWords AS $intKey => $arrWord) {

    # first detect whether empty row or not
    # get key for catageory
    # get all words until next empty row and add them to category
  
    if(empty(array_filter($arrWord))) {
      
      $intCounter++;
      continue;
    
    }
    
    if(0 === $intCounter % 2) {
      $strCategory = $arrWord[1];
      continue;
    }
    
    $objWord = new Word($arrWord, $arrLanguages);
    $objTranslation = new Translation($objWord);
    
    if(array_key_exists($strCategory, $arrCategories)) {
      $arrCategories[$strCategory]->add($objTranslation);
    }
    else {
      $objCategory = new WordCategory($strCategory);
      $objCategory->add($objTranslation);
      $arrCategories[$strCategory] = $objCategory;
    }
  
  }

  return array_values($arrCategories);

}

class EasySlavic {

  public static function getInstance(array $arrWords, array $arrLanguages, array $arrConfig) : WordsManager { 
  
    return WordsManager::create($arrWords, $arrLanguages, $arrConfig);
  
  }

}

class WordCategory implements Iterator {

  private $strCategory;
  
  private $intPosition = 0;
  
  private $arrWords = [];
  
  public function __construct(string $strCategory) { $this->strCategory = $strCategory; }
  
  public function getName() { return $this->strCategory; }
  
  public function add(Translation $objWord) {
    
  
    $this->arrWords[] = $objWord;
  
  }
  
  public function rewind() {
  
    $this->intPosition = 0;
  
  }
  
  public function next() {
    
    do
      $intNextValue = mt_rand(0, sizeof($this->arrWords) - 1);
    while($intNextValue === $this->intPosition);
  
  
    $this->intPosition = $intNextValue;
  
  }
  
  public function current() {
  
    return $this->arrWords[$this->intPosition];
  
  }
  
  public function valid() {
  
    return array_key_exists($this->intPosition, $this->arrWords);
  
  }
  
  public function key() {
  
    return $this->intPosition;
  
  }

}

interface IWordsManager {} # maybe not neccessary

/**
  * - should know whether every single word has been asked or not
  * - should decide and vary the wrong answered words to avoid loop between the first two wrong answered words
  * - randomly words answered without failures should be asked too
  *
  **/
class WordsManager {
  
  private $arrConfig = [];
  
  # all categories from the file/database/...
  private $arrCategories = [];
  
  # only the categories that are selected by $arrConfig
  private $arrSelectedCategories = [];
  
  private $arrLanguages = [];
  
  private $objCurrentWord;
  
  private $objCurrentCategory;
  
  # variable that holds information about the last asked word or even wordS and/or category/index
  
  # variable that holds (references to) words that have been answered invalid
  
  public function __construct(array $arrCategories, array $arrLanguages, array $arrConfig) {
  
    $this->arrCategories = $arrCategories;
    $this->arrLanguages  = $arrLanguages;
    $this->arrConfig     = $arrConfig;
    
    foreach($this->arrCategories AS $objCategory)
      if(in_array($objCategory->getName(), $this->arrConfig['categories'])) # TODO key really exists...
        $this->arrSelectedCategories[] = $objCategory;
    
    $this->objCurrentWord = $this->getNextWord();
  
  }
  
  public function ask() {
    
    #ar_dump('objCurrentWord key: ' . $this->objCurrentWord->key());
    
    #var_dump('before' . $this->objCurrentWord->key());
    #$this->objCurrentWord->next();
    #var_dump('after' . $this->objCurrentWord->key());
    
    if(!$this->objCurrentWord->valid($this->arrConfig['translate_from'])) {
    
      $this->objCurrentWord->rewind();
      $this->objCurrentWord = $this->getNextWord();
    
    }

    if(array_key_exists('foo', $this->arrConfig) && is_callable($this->arrConfig['foo'])) {
    
      #$bar = $this->arrConfig['foo']($this->objCurrentWord->getByLanguage($this->arrConfig['translate_from']));
      $bar = $this->arrConfig['foo']($this->objCurrentWord, $this->arrConfig);
      
      #var_dump($bar);

      #$this->objCurrentWord->next();
      print "\n" . 'you know what it means? ' . $bar['ask'][0] . ' = '; 
    
      return;
    
    }
    
    #print "\n" . 'you know what it means? ' . $this->objCurrentWord->getByLanguage($this->arrConfig['translate_from']) . ' = ';
  
  }
  
  public function answer(string $strInput) {

    #$strAnswer = array_key_exists('foo', $this->arrConfig) && is_callable($this->arrConfig['foo'])
      #? $this->arrConfig['foo']($this->objCurrentWord->getByLanguage($this->arrConfig['translate_from']))['answer']
      #: $this->objCurrentWord->getByLanguage($this->arrConfig['translate_to']);

    $strAnswer = $this->arrConfig['foo']($this->objCurrentWord, $this->arrConfig)['answer'][0];

    $strSuccessMessage = array_key_exists('bar', $this->arrConfig)
      ? ''
      : "\n" . 'correct!';
    
    $strFailureMessage = array_key_exists('baz', $this->arrConfig)
      ? ''
      : "\n" . 'false! it\'s not: "' . $strInput . '" but ' . $strAnswer;

    $this->objCurrentWord->next();

    if($strInput === $strAnswer) {
    
      print $strSuccessMessage;

      return;
    
    }
    
    print $strFailureMessage;

  }
  
  private function getQuestion() : array {
  
    return [
    
      'success_message' => '',
      'error_message'   => ''
    
    ];
  
  }
  
  public function getNextWord() : IWord {
  
    $objCategory = 1 === sizeof($this->arrSelectedCategories)
      ? $this->arrSelectedCategories[0]
      : []; # TODO support more than one category
    
    #var_dump($this->objCurrentWord);
    
    if(!$objCategory->valid())
      $objCategory->rewind();
    
    while($objCategory->valid()) {
    
      # TODO skip if no translation provided
      $objTranslation = $objCategory->current();
      $objCategory->next();

      return $objTranslation->getWord();

    }
  
  
  }
  
  public function validateAnswer(string $userInput) {}
  
  public function getCategories() {}
  
  # maybe this belongs somewhere else. validateAnswer maybe shoult return a
  # data structure that knows whether the answer was correct or not and also know the e.g. from german to bulgarian
  # e.g. "correct. eins means edno"
  public function display() : string {}
  
  # ? maybe this should be in a factory or smthng
  # or maybe I should use __constructor drectly
  # it seems that it is not needed, since easyslavic creates the wordsmanager instance
  public static function create(array $arrQuestions, array $arrLanguages, array $arrConfig) : WordsManager {
  
    return new WordsManager($arrQuestions, $arrLanguages, $arrConfig);
  
  }
  
}

/**
  *
  * this class knows how many times one word has been asked for translation
  * so it holds the actual word and knows about how many times this word has been answered correctly
  *
  **/
class Translation {

  private $objWord;

  private $intAsked = 0;
  
  private $intAnsweredCorrectly = 0;

  public function __construct(IWord $objWord) {
    
    $this->objWord = $objWord;
    
  }

  public function getFailurePercentage() {}
  
  public function increaseAskedCounter() {}
  
  public function increaseAnsweredCorrecltyCounter() {}
  
  
  
  public function getWord() : IWord { return $this->objWord; }

}

interface IWord {

  public function getByLanguage(string $strLanguage) : string;

}

/**
  *
  * this class purpose is to abstract a word and its meaning in several different languages
  *
  * e.g. [ 'german' => 'eins', 'english' => 'one', 'bulgarian-cyrillic' => '', 'bulgarian-latin' => 'edno', 'croatian' => 'jedan' ]
  *
  **/
class Word implements IWord {

  private $arrWord = [];
  
  private $arrFoo = [];
  
  private $intPosition = 0;

  public function __construct(array $arrWord, array $arrLanguages) {
    
    if(empty($arrWord))
      throw new Exception('empty translation provided');
    
    if(empty($arrLanguages))
      throw new Exception('no languages provided');
    
    if(sizeof($arrWord) !== sizeof($arrLanguages))
      throw new Exception('size of available languages and translations differ'); # TODO display any word if available
  
    #var_dump($arrLanguages);
    #var_dump($arrWord);
  
    foreach($arrLanguages AS $intLanguageKey => $strLanguage)
      foreach(explode('-', $arrWord[$intLanguageKey]) AS $intWordKey => $strWord)
        $this->arrWord[$strLanguage][$intWordKey] = $strWord;
      
    #var_dump($this->arrWord['german']);
  
  }
  
  public function valid(string $strLanguage) { 
  
    #var_dump($this->arrFoo, $this->intPosition);
    return array_key_exists($strLanguage, $this->arrWord) && array_key_exists($this->intPosition, $this->arrWord[$strLanguage]);
  
  }
  
  public function current($strLanguage) { return $this->arrWord[$strLanguage][$this->key()]; }
  
  public function next() { $this->intPosition++; }
  
  public function prev() { $this->intPosition--; }
  
  public function rewind() { $this->intPosition = 0; }
  
  public function key() { return $this->intPosition;  }
  
  public function getByLanguage(string $strLanguage) : string {
  
    if(empty($strLanguage))
      return '';
      
      #var_dump($this->current($strLanguage));
      
      #var_dump('getByLanguage key' . $this->key());
#var_dump('is valid: ' . $this->valid($strLanguage));
    if($this->valid($strLanguage)) {
      
      #if(0 < $this->key())
        #$this->prev();
      
      $current = $this->current($strLanguage);
      #var_dump('current: ' . $current);
      #$this->next();
      return $current;
    }
    
    $this->rewind();
    
    #var_dump('here');
    
    return array_key_exists($strLanguage, $this->arrWord)
      ? empty($this->arrWord[$strLanguage])
        ? 'no translation provided'
        : $this->arrWord[$strLanguage]
      : '';
  
  }

}

/*use PHPUnit\Framework\TestCase;

final class GetNewTickerItemsTest extends TestCase {

  public function testEmptyConstructor() {}

  public function testConstructor() {
  
    $arrLanguages = [ 'german', 'english', 'bulgarian-cyrillic', 'bulgarian-latin', 'croatian' ];
    $arrWord      = [ 'foo',    'bar',     'baz',                'qux',             'foobar',  ];
  
    $objWord = new Word($arrWord, $arrLanguages);
  
    $this->assertEquals($objWord->getWordByLanguage('german'), 'foo');
    $this->assertEquals($objWord->getWordByLanguage('english'), 'bar');
    $this->assertEquals($objWord->getWordByLanguage('bulgarian-cyrillic'), 'baz');
    $this->assertEquals($objWord->getWordByLanguage('bulgarian-latin'), 'qux');
    $this->assertEquals($objWord->getWordByLanguage('croatian'), 'foobar');
  
  }
  
  public function testNoTranslationProvided() {
  
    $arrLanguages = [ 'german' ];
    $arrWord      = [ '' ];
    
    $objWord = new Word($arrWord, $arrLanguages);
    
    $this->assertEquals($objWord->getWordByLanguage('german'), 'no translation provided');
  
  }
  
  public function testLanguageDoesNotExist() {
  
    $arrLanguages = [ 'german' ];
    $arrWord      = [ '' ];
    
    $objWord = new Word($arrWord, $arrLanguages);
  
    $this->assertEquals($objWord->getWordByLanguage('chinese'), '');
  
  }

}*/

#############################

$arrWords = [ 
  ['','','',''], 
  ['zahlen', 'numbers', '', '', ''], 
  ['','','',''], 
  [ 'eins', 'one', 'едно', 'edno', 'jedan' ],
  [ 'zwei', 'two', 'две', 'dve', 'dva' ] ,
  ['','','',''], 
  ['tiere', 'animals', '', ''], 
  ['','','',''], 
  [ 'vogel', 'bird', 'птица', 'ptitsa', 'ptica' ],
  ['','','',''], 
  ['verabschiedungen', 'goodbyes', '', ''],
  ['','','',''], 
  [ 'tschüss', 'bye', 'чао', 'chao', 'bok' ],
  ['','','',''],
  ['lektion1', 'unit1', '', ''],
  ['','','',''],
  [ 'Stewardess', 'stewardess', 'стюардеса', 'stjuardesa', '' ],
  [ 'Guten Tag', 'hello', 'добър ден', 'dobyr den', '' ],
  [ 'Sie', 'you', 'Вие', 'Vie', '' ],
  [ 'Was wollen Sie?', 'What do you want?', 'какво искате?', 'kakvo iskate?', '' ],
  [ 'Kaffe', 'coffe', 'кафе', 'kafe', '' ],
  [ 'Tee', 'tea', 'чаи', 'chai', '' ],
  [ 'Wein', 'wine', 'вино', 'vino', '' ],
  [ 'Bier', 'beer', 'бира', 'bira', '' ],
  [ 'Wasser', 'water', 'вода', 'voda', '' ],
  [ 'bitte', 'please', 'моля', 'molja', '' ],
  [ 'Ach!', 'oh!', 'ах!', 'ax!', '' ],
  [ 'schrecklich', 'awful', 'ужас', 'uzhas', '' ],
  [ 'Was ist das?', 'What is it?', 'какво е това?', 'kakvo e tova?', '' ],
  [ 'Entschuldigung', 'sorry', 'исвинете', 'isvinete', '' ],
  [ 'Geschäftsman', 'businessman', 'бизнесмен', 'biznesmen', '' ],
  [ 'Kein Problem', 'no problem', 'няма проблем', 'njama problem', '' ],
  [ 'Das ist nur Wasser', 'It is just water', 'само вода е', 'samo voda e', '' ],
  [ 'ich habe', 'I have', 'имам', 'imam', '' ],
  [ 'Serviette', 'napkin', 'салфетка', 'salfetka', '' ],
  [ 'danke', 'thanks', 'благодаря', 'blagodarja', 'hvala' ],
  [ 'Woher sind Sie?', 'Where are you from?', 'откъде сте?', 'otkyde ste?', '' ],
  [ 'Ich bin', 'I am', 'аз съм', 'az sym', '' ],
  [ 'aus', 'from', 'от', 'ot', '' ],
  [ 'Warum?', 'Why?', 'защо?', 'zashto?', '' ],
  [ 'aber', 'but', 'но', 'no', '' ],
  [ 'Wohnung', 'flat', 'апартамент', 'apartament', '' ],
  [ 'Firma', 'company', 'фирма', 'firma', '' ],
  [ 'und', 'and', 'и', 'i', '' ],
  [ 'Verpflichtungen', 'obligations', 'ангажименти', 'angazhimenti', '' ],
  [ 'sehr/viel', 'very/much', 'миого', 'miogo', '' ],
  [ 'interessant', 'interesting', 'интересно', 'interesno', '' ],
  ['','','',''],
  ['Unbestimmte-Artikel', 'indefinite-articles', '', ''],
  ['','','',''],
  ['ein-Tisch','a-table', 'един-маса', 'edin-masa', ''],
  ['eine-Serviette','a-napkin', 'една-салфетка', 'edna-salfetka', ''],
  ['eine-Wohnung','a-flat', 'една-апартамент', 'edna-apartament', ''],
  ['eine-Firma','a-company', 'една-фирма', 'edna-firma', ''],
  ['','','',''],
  ['Bestimmte-Artikel', 'definite-articles', '', ''],
  ['','','',''],
  ['Der-Tisch','the-table', 'маса-та', 'masa-ta', ''],
  ['Die-Serviette','the-napkin', 'салфетка-та', 'salfetka-ta', ''],
  ['Die-Wohnung','the-flat', 'апартамент-а', 'apartament-a', ''],
  ['eine-Firma','a-company', 'фирма-та', 'firma-ta', ''],
  ['','','',''],
  ['konjunktionen', 'conjunctions', '', ''],
  ['','','',''],
  [
    'ich bin-du bist-er ist-sie ist-es ist-wir sind-ihr seid-sie sind',
    'I am-you are-he is-she is-it is-we are-you are-they are',
    'аз съм-ти си-той е-тя е-то е-ние сме-вие сте-те са',
    'az sym-ti si-toj e-tja e-to e-nie sme-vie ste-te sa',
    'ja sam-ti si-on je-ona je-ono je-mi smo-vi ste-oni/one/ona su'
  ],
  [
    'ich habe-du hast-er hat-sie hat-es hat-wir haben-ihr habt-sie haben',
    'I have-you have-he has-she has-it has-we have-you have-they have',
    'аз има[м]-ти има[ш]-той има-тя има-то има-ние има[ме]-вие има[те]-те има[т]',
    'az imam-ti imash-toj ima-tja ima-to ima-nie imame-vie imate-te imat',
    'ja imam-ti imaš-on ima-ona ima-ono ima-mi imamo-vi imate-oni/one/ona imaju'
  ],
  [
    'ich komme an-du kommst an-er kommt an-sie kommt an-es kommt an-wir kommen an-ihr kommt an-sie kommen an',
    'I arrive-you arrive-he arrives-she arrives-it arrives-we arrive-you arrive-they arrive',
    'аз пристига[м]-ти пристинга[ш]-той пристига-тя пристига-то пристига-ние пристига[ме]-вие пристига[те]-те пристига[т]',
    '',
    ''
  ],
  /*[
    'ich lüge-du lügst-er lügt-sie lügt-es lügt-wir lügen-ihr lügt-sie lügen',
    'I lie-you lie-he lies-she lies-it lies-we lie-you lie-they lie',
    '',
    '',
    ''
  ],*/
  [
    'ich will-du willst-er will-sie will-es will-wir wollen-ihr wollt-sie wollen',
    'I want-you want-he wants-she wants-it wants-we want-you want-they want',
    'аз иска[м]-ти иска[ш]-той иска-тя иска-то иска-ние иска[ме]-вие иска[те]-те иска[т]',
    'az iskam-ti iskash-toj iska-tja iska-to iska-nie iskame-vie iskate-te iskat',
    ''
  ],
  [
    'ich lerne-du lernst-er lernt-sie lernt-es lernt-wir lernen-ihr lernt-sie lernen',
    'I learn-you learn-he learns-she learns-it learns-we learn-you learn-they learn',
    'аз уч[а]-ти уч[иш]-той учи-тя учи-то учи-ние уч[им]-вие уч[ите]-те уч[ат]',
    'az ucha-ti uchish-toj uchi-tja uchi-to uchi-nie uchim-vie uchite-te uchat',
    'ja učim-ti učiš-on uči-ona uči-ono uči-mi učimo-vi učite-oni/one/ona uče'
  ],
  [
    'ich gehe-du gehst-er geht-sie geht-es geht-wir gehen-ihr geht-sie gehen',
    'I go,you go-he goes-she goes-it goes-we go-you go-they go',
    'аз отива[м]-ти отива[ш]-той отива-тя отива-то отива-ние отива[ме]-вие отива[те]-те отива[т]',
    'az otivam-ti otivash-toj otiva-tja otiva-to otiva-nie otivame-vie otivate-te otivat',
    'ja idem-ti ideš-on ide-ona ide-ono ide-mi idemo-vi idete-oni/one/ona idu'
  ],
  [
    'ich frühstücke-du frühstückst-er frühstückt-sie frühstückt-es frühstückt-wir frühstücken-ihr frühstückt-sie frühstücken',
    '',
    'аз закусва[м]-ти закусва[ш]-той закусва-тя закусва-то закусва-ние закусва[ме]-вие закусва[те]-те закусва[т]',
    'az zakusvam-ti zakusvash-toj zakusva-tja zakusva-to zakusva-nie zakusvame-vie zakusvate-te zakusvat',
    ''
  ],
  ['','','',''],
  ['nema-problema-lektion1', 'nema-problema-unit1', '', ''],
  ['','','',''],
  [ 'rechts', 'right', '', '', 'desno' ],
  [ 'gerade(aus)', 'straight', '', '', 'ravno' ],
  [ 'Küche', 'kitchen', '', '', 'kuhinja' ],
  [ 'Zimmer', 'room', '', '', 'soba' ],
  [ 'Wohnzimmer', 'living room', '', '', 'dnevna soba' ],
  [ 'Tisch', 'table', 'маса', 'masa', 'stol' ],
  [ 'Küche', 'kitchen', '', '', 'kuhinja' ],
  [ 'Rose', 'rose', '', '', 'ruža' ],
  [ 'Garten', 'garden', '', '', 'vrt' ],
  [ 'Sessel', 'chair???', '', '', 'naslonjač' ],
  [ 'Stuhl', 'chair', '', '', 'stolica' ],
  [ 'Schlafzimmer', 'bed room', '', '', 'spavaća soba' ],
  [ 'Bett', 'bed', '', '', 'krevet' ],
  [ 'Fernseher', 'tv', '', '', 'televizor' ],
  [ 'Badezimmer', 'bathroom', '', '', 'kupaonica' ],
  [ 'Keller', 'basement', '', '', 'podrum' ],
  [ 'Schrank', 'cabinet', '', '', 'ormar' ]
];

#
# nominativ => wer oder was?
# genitiv => wessen?
# dativ => wem?
# akkusativ => wen oder was?
# --------------------------
# lokativ => wo? über wen? über was?
#
#
#
# AKK - ja ne želim ići u spavaću sobu - ich moechte nicht ins schlafzimmer gehen
# LOK - moja majka želi kuhati u kuhinji - meine mutter moechte in der kueche kochen
# AKK - stavi kluč u kuhinju - leg den schluessel in die kueche 
# vino je u podrumu - (bring den ?) wein in den keller
# stavi vino i vodu u podrum, molim te! - bring den wein in den keller, bitte
# moj novi šef ne spava u krevutu - mein neuer chef schlaeft nicht im bett
# on ne želi ići u krevet - er er moechte nicht ins bett gehen
# moja stara kravata se nalazi u ormaru - meine alte krawatte ist im schrank
# stavi tvoj sako u ormar - leg dein sacko in den schrank
# mi smo sad(a) u hotelu na moru - wir sind im hotel am meer
# AKK - vi idete u augustu na more - sie fahren im august ans meer
#

#
# [ 'ask' => [ 'ich bin', 'du bist' ], 'answer' => [ 'az sym', 'ti si' ] ];
#
# var_dump(sizeof($arrWords));

$cloConjunctions = function(IWord $objWord, array $arrConfig) {

  $strWord = $objWord->getByLanguage($arrConfig['translate_from']);
  $strWordTo = str_replace('[', '', str_replace(']', '', $objWord->getByLanguage($arrConfig['translate_to'])));
  
  $arrExplodedFrom = explode('-', $strWord);
  $arrExplodedTo = explode('-', $strWordTo);

  return [ 'answer' => explode('-', $strWordTo), 'ask' => explode('-', $strWord) ]  ;

};

$arrConfig = [

  'translate_from' => 'german',
  'translate_to' => 'croatian',
  'categories' => [ 'nema-problema-unit1' ],
  #'categories' => [ 'conjunctions' ],
  #'foo' => function(string $strWord) { return array_combine([ 'answer', 'ask' ], explode('-', $strWord)); }
  'foo' => $cloConjunctions

];

# they can be filled from database, file, etc.
$arrLanguages = [ 'german', 'english', 'bulgarian-cyrillic', 'bulgarian-latin', 'croatian' ];

$objWord = new Word($arrWords[64], $arrLanguages);
$cloConjunctions($objWord, $arrConfig);

$arrCategories   = convertToCategories($arrWords, $arrLanguages);
$objWordsManager = EasySlavic::getInstance($arrCategories, $arrLanguages, $arrConfig);

##########
# "main" #
##########
while(true) {

  $objWordsManager->ask();
  
  $strInput = trim(fgets(STDIN, 1024));

  #var_dump(mb_detect_encoding($strInput));
  #var_dump(iconv('utf-8', 'utf-8//IGNORE', $strInput));

  #var_dump(ord($strInput[0]));
  #var_dump(ord($strInput[1]));
  #var_dump(ord($strInput[2]));
  #var_dump(ord($strInput[3]));
  #var_dump(ord($strInput[4]));
  #var_dump(ord($strInput[5]));
  
  #$strInput = mb_convert_encoding($strInput, 'UTF-8', 'ASCII');

  if(':q' === $strInput)
    break;

  $objWordsManager->answer($strInput);

}
