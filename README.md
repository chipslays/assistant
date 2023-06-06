# Assistant

Эта библиотека была создана для внедрения простых чат-ботов на сайтах и любых других проектах.

Assistant - это простое API, в которое вы заносите варианты сообщений и ответов на них. Результат ответа может быть абсолютно разным: строка, массив, результат выполнения если это callable или совершенно любой другой тип данных. Это делает библиотеку гибкой в использовании.

## Установка

```bash
composer require chipslays/assistant
```

## Просто пример

```php
use Chatbot\Assistant;

$assistant = new Assistant;

$assistant->add(['Как связаться со службой поддержки', ...], 'Наш телефон: 8 800 555-35-35');
$assistant->add(['Какой-то другой вопрос от пользователя', ...], 'Другой ответ');

$answer = $assistant->run('Какой способ связи с вами есть?'); // Наш телефон: 8 800 555-35-35
```

## API

### `Assistant(TextProcessorInterface $textProcessor = new SimpleTextProcessor)`

```php
use Chatbot\Assistant;
use Chatbot\Processors\TextProcessor;

// по умолчанию Assistant использует SimpleTextProcessor
// но можно воспользоваться MorphyTextProcessor
$assistant = new Assistant(new MorphyTextProcessor);
```

```php
use Chatbot\Assistant;
use Chatbot\Processors\TextProcessor;

// процессор MorphyTextProcessor поддерживает другие языки из phpMorphy
// но SimpleTextProcessor поддерживает все (?) языки, которые попдают под ~[^a-zа-яё0-9]~iu
$assistant = new Assistant(new MorphyTextProcessor('en'));
```

```php
use Chatbot\Assistant;
use Chatbot\Processors\TextProcessor;
use cijic\phpMorphy\Morphy;

// или можно передать объект Morphy в MorphyTextProcessor
$assistant = new Assistant(new MorphyTextProcessor(new Morphy('en')));
```

### `add(array $questions, mixed $answer): self`

```php
$assistant->add([...], 'Ответ в виде строки');

$answer = $assistant->run('...'); // ответ в виде строки
```

```php
$assistant->add([...], [
    'text' => 'Ответ в виде массива',
    'buttons' => [...],
    'yetAnotherKey' => 'Еще какое-то значение'
]);

$answer = $assistant->run('...'); // [array]
```

```php
$assistant->add([...], function () {
    $user = fetchUser(...); // что-нибудь делаем
    return 'Ответ в виде строки которая вернет функция'; // или любой другой тип данных
});

$answer = $assistant->run('...'); // string
```

```php
$assistant->add([...], 'Ответ в виде строки 1');
$assistant->add([...], 'Ответ в виде строки 2');
$assistant->add([...], 'Ответ в виде строки 3');

// получаем ответ в виде массива в котором содержит score (float), answer (mixed) и default (bool)
$answer = $assistant->run('...', asArray: true);

// элементы в массиве отсортированы по ключу score (по убыванию), содержит только ответы где score > 0
// если ответы не найдены и не указан ответ по умолчанию, то вернет null
// если ответы не найдены и указан ответ по умолчанию, вернут один элемент где ключ default == true
//
// ^ array:1 [
//   0 => array:3 [
//     "score" => 0.21693045781866 // точность ответа
//     "answer" => "Ответ в виде строки 1" // сам ответ который мы указали, если это функция и т.п., ее нужно выполнить самостоятельно
//     "default" => false // true - это ответ по умолчанию если не найден, false - соответственно если ответ был найден
//   ]
//   ...
// ]
```

```php
$assistant->add([...], fn () => 'Ответ в виде функции');
$assistant->add([...], 'Ответ в виде строки');

$answers = $assistant->run('...', asArray: true);

// можем воспользоваться методом processAnswer
$answer = $assistant->processAnswer($answers[0]['answer']); // Ответ в виде функции

// или обработать самостоятельно
$answer = $answers[0]['answer'];
$answer = is_callable($answer) ? call_user_func($answer) : $answer;
```

### `setDataset(array $dataset): self`

```php
// массовое добавление (под капотом цикл с методом add)
$dataset = [
    [
        'questions' => [
            '...',
        ],
        'answer' => '...',
    ],
    [
        'questions' => [
            '...',
        ],
        'answer' => [...],
    ],
    [
        'questions' => [
            '...',
        ],
        'answer' => fn () => ...,
    ],
];

$assistant->setDataset($dataset);
```

### `setDefaultAnswer(mixed $answer): void`

```php
// ответ по умолчанию, если не были найдены ответы
// поддерживает так же любые типы ответа
$assistant->setDefaultAnswer('...');
$assistant->setDefaultAnswer([...]);
$assistant->setDefaultAnswer(fn () => ...);
```

