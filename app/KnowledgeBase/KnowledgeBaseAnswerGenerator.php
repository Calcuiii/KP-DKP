<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

interface KnowledgeBaseAnswerGenerator
{
    public function generate(
        KnowledgeBaseGroundedContext $context,
    ): KnowledgeBaseAnswerGenerationResult;
}
