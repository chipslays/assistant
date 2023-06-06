<?php

namespace Chatbot\Processors;

use phpMorphy;
use cijic\phpMorphy\Morphy;

class MorphyTextProcessor implements TextProcessorInterface
{
    protected ?phpMorphy $morphy;

    // protected array $stopWords = ['и', 'в', 'на', 'с', 'по', 'а', 'о', 'не', 'что', 'как', 'это', 'уже', 'для', 'от'];
    protected array $stopWords = [];

    protected string $mode = 'getBaseForm';

    public function __construct(phpMorphy|string $morphy = 'ru')
    {
        $this->morphy = is_string($morphy) ? new Morphy($morphy) : $morphy;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function process(string $text): array
    {
        $words = $this->tokenize($text);
        $words = $this->removeStopWords($words);
        $words = array_map([$this, 'stem'], $words);

        return $words;
    }

    protected function tokenize(string $text): array
    {
        // return preg_split('/\s+/', mb_strtolower($text));
        return array_filter(preg_split('/\s+/', preg_replace('~[^a-zа-яё0-9]~iu', ' ', mb_strtolower($text))));;
    }

    protected function stem(string $word): string
    {
        $baseForm = $this->morphy?->{$this->mode}(mb_strtoupper($word));

        if (!$baseForm) {
            return $word;
        }

        $baseForm = array_filter($baseForm);

        return count($baseForm) > 0 ? mb_strtolower(end($baseForm)) : $word;
    }

    protected function removeStopWords(array $words): array
    {
        return array_filter($words, function ($word) {
            return !in_array($word, $this->stopWords);
        });
    }

    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    public function setStopWords(array $stopWords): void
    {
        $this->stopWords = $stopWords;
    }

    public function addStopWord(string $stopWord): void
    {
        $this->stopWords[] = $stopWord;
    }

    public function removeStopWord(string $stopWord): void
    {
        $this->stopWords = array_values(array_diff($this->stopWords, [$stopWord]));
    }
}