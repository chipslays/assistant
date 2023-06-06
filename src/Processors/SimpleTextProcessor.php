<?php

namespace Chatbot\Processors;

class SimpleTextProcessor implements TextProcessorInterface
{
    public function process(string $text): array
    {
        $words = $this->tokenize($text);

        return $words;
    }

    protected function tokenize(string $text): array
    {
        return array_filter(preg_split('/\s+/', preg_replace('~[^a-zа-яё0-9]~iu', ' ', str_replace('ё', 'е', mb_strtolower($text)))));
    }
}