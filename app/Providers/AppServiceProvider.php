<?php

namespace App\Providers;

use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\KnowledgeBase\KnowledgeBasePolicyResolver;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use App\KnowledgeBase\KnowledgeBaseRetrievalPipeline;
use App\KnowledgeBase\KnowledgeBaseTopicResolver;
use App\KnowledgeBase\LexicalKnowledgeBaseRetriever;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            KnowledgeBaseRegistry::class,
            static fn (): KnowledgeBaseRegistry => new KnowledgeBaseRegistry(
                config('knowledge-base.registry_path'),
            ),
        );

        $this->app->singleton(
            KnowledgeBaseDocumentLoader::class,
            static fn (): KnowledgeBaseDocumentLoader => new KnowledgeBaseDocumentLoader(
                config('knowledge-base.processed_directory'),
            ),
        );

        $this->app->singleton(
            KnowledgeBaseRetrievalPipeline::class,
            static fn (Application $app): KnowledgeBaseRetrievalPipeline => new KnowledgeBaseRetrievalPipeline(
                registry: $app->make(KnowledgeBaseRegistry::class),
                documentLoader: $app->make(KnowledgeBaseDocumentLoader::class),
                chunker: $app->make(KnowledgeBaseChunker::class),
                retriever: $app->make(LexicalKnowledgeBaseRetriever::class),
                policyResolver: $app->make(KnowledgeBasePolicyResolver::class),
                topicResolver: $app->make(KnowledgeBaseTopicResolver::class),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
