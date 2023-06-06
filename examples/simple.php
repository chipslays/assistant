<?php

use Chatbot\Assistant;
use Chatbot\Processors\MorphyTextProcessor;

require __DIR__ . '/../vendor/autoload.php';

$assistant = new Assistant(new MorphyTextProcessor);

$assistant->add(
    [
        'Как оплатить счет за электроэнергию?',
        'Какие способы оплаты счетов доступны?',
        'Как оплатить электричество в Acme?',
        'Какие платежные системы принимаются?',
        'Как оплатить счет за электроэнергию онлайн?',
    ],
    'Оплатить счет за электроэнергию можно через банк, почту, терминалы, интернет-банкинг и мобильные приложения.',
);

$assistant->setDefaultAnswer('Я тебя не понял...');

$answer = $assistant->run('Оплатить как?');

dd($answer);

