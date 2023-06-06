<?php

namespace Chatbot;

use Chatbot\Processors\MorphyTextProcessor;
use Chatbot\Processors\SimpleTextProcessor;
use Chatbot\Processors\TextProcessorInterface;

class Assistant
{
    protected array $questionsAndAnswers = [];

    protected TextProcessorInterface $textProcessor;

    protected mixed $defaultAnswer = null;

    public function __construct(TextProcessorInterface $textProcessor = new SimpleTextProcessor)
    {
        $this->textProcessor = $textProcessor;
    }

    public function setTextProcessor(TextProcessorInterface $textProcessor)
    {
        $this->textProcessor = $textProcessor;
    }

    public function add(array $questions, mixed $answer): self
    {
        $this->questionsAndAnswers[] = [
            'questions' => $questions,
            'answer' => $answer,
        ];

        return $this;
    }

    public function setDataset(array $dataset): self
    {
        foreach ($dataset as $data) {
            $this->add($data['questions'], $data['answer']);
        }

        return $this;
    }

    public function run(string $inputQuestion, bool $asArray = false): mixed
    {
        if ($this->textProcessor instanceof MorphyTextProcessor) {
            $this->textProcessor->setMode('getBaseForm');
        }

        $answers = $this->getAnswer($inputQuestion);

        if ($answers == null && $this->textProcessor instanceof MorphyTextProcessor) {
            $this->textProcessor->setMode('getPseudoRoot');
            $answers = $this->getAnswer($inputQuestion);
        }

        if ($answers === null && $this->defaultAnswer) {
            return $asArray
                ? [['score' => 1, 'answer' => $this->defaultAnswer, 'default' => true]]
                : $this->processAnswer($this->defaultAnswer);
        }

        return $asArray ? $answers : ($answers ? $this->processAnswer($answers[0]['answer']) : null);
    }

    public function setDefaultAnswer(mixed $answer): void
    {
        $this->defaultAnswer = $answer;
    }

    protected function getAnswer(string $inputQuestion): ?array
    {
        $inputWords = array_count_values($this->textProcessor->process($inputQuestion));
        $maxScore = 0;

        $answers = [];

        foreach ($this->questionsAndAnswers as $qa) {
            foreach ($qa['questions'] as $question) {
                $questionWords = array_count_values($this->textProcessor->process($question));
                $score = $this->cosineSimilarity($inputWords, $questionWords);

                if ($score > $maxScore) {
                    $maxScore = $score;
                }

                $answers[] = ['score' => $score, 'answer' => $qa['answer'], 'default' => false];
            }
        }

        usort($answers, fn($a, $b) => $b['score'] <=> $a['score']);
        $answers = array_filter($answers, fn ($item) => $item['score'] > 0);

        return count($answers) > 0 ? $answers : null;
    }

    public function processAnswer(mixed $answer): mixed
    {
        return is_callable($answer) ? call_user_func($answer) : $answer;
    }

    private function cosineSimilarity(array $inputWords, array $questionWords): float
    {
        $dotProduct = 0;
        $inputMagnitude = 0;
        $questionMagnitude = 0;

        foreach ($inputWords as $word => $count) {
            $inputMagnitude += $count * $count;
            if (isset($questionWords[$word])) {
                $dotProduct += $count * $questionWords[$word];
            }
        }

        foreach ($questionWords as $count) {
            $questionMagnitude += $count * $count;
        }

        return $dotProduct / (sqrt($inputMagnitude) * sqrt($questionMagnitude));
    }
}
