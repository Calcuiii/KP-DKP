<?php

namespace App\Providers;

use App\KnowledgeBase\BudgetedGroqAnswerGenerator;
use App\KnowledgeBase\GroqKnowledgeBaseAnswerGenerator;
use App\KnowledgeBase\KnowledgeBaseAnswerGenerator;
use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\KnowledgeBase\KnowledgeBasePolicyResolver;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use App\KnowledgeBase\KnowledgeBaseRetrievalPipeline;
use App\KnowledgeBase\KnowledgeBaseTopicResolver;
use App\KnowledgeBase\LexicalKnowledgeBaseRetriever;
use App\Models\Infographic;
use App\Services\ChatbotRateLimits;
use App\Services\KnowledgeBaseDocumentIndexer;
use App\View\Composers\NotificationComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Smalot\PdfParser\Parser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            KnowledgeBaseAnswerGenerator::class,
            static fn (): KnowledgeBaseAnswerGenerator => new BudgetedGroqAnswerGenerator(new GroqKnowledgeBaseAnswerGenerator(
                enabled: (bool) config('services.groq.enabled'),
                apiKey: config('services.groq.api_key'),
                model: config('services.groq.model'),
                timeoutSeconds: min(20, max(5, (int) config('services.groq.timeout_seconds'))),
                maxCompletionTokens: min(1800, max(256, (int) config('services.groq.max_completion_tokens'))),
            )),
        );

        $this->app->singleton(
            KnowledgeBaseRegistry::class,
            static fn (): KnowledgeBaseRegistry => new KnowledgeBaseRegistry(
                config('knowledge-base.registry_path'),
                config('knowledge-base.uploaded_registry_path'),
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

        $this->app->singleton(
            KnowledgeBaseDocumentIndexer::class,
            static fn (Application $app): KnowledgeBaseDocumentIndexer => new KnowledgeBaseDocumentIndexer(
                registryPath: config('knowledge-base.uploaded_registry_path'),
                processedDirectory: config('knowledge-base.processed_directory'),
                documentLoader: $app->make(KnowledgeBaseDocumentLoader::class),
                chunker: $app->make(KnowledgeBaseChunker::class),
                pdfParser: new Parser,
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('chatbot-messages', fn (Request $request) => app(ChatbotRateLimits::class)->messages($request));
        RateLimiter::for('chatbot-read', fn (Request $request) => Limit::perMinute(120)->by('read:'.app(ChatbotRateLimits::class)->actor($request)));
        RateLimiter::for('chatbot-actions', fn (Request $request) => Limit::perMinute(20)->by('actions:'.app(ChatbotRateLimits::class)->actor($request)));
        View::composer(['layouts.admin', 'components.admin.topbar'], NotificationComposer::class);

        View::composer(
            ['pages.landing', 'pages.infographics'],
            static function ($view): void {
                $view->with('infographics', Infographic::query()->ordered()->get());
            },
        );
    }
}
