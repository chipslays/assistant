<?php

namespace Chatbot\Processors;

interface TextProcessorInterface
{
    public function process(string $text): array;
}