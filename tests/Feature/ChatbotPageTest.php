<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatbotPageTest extends TestCase
{
    public function test_the_chatbot_page_renders_successfully(): void
    {
        $response = $this->get(route('chatbot'));

        $response
            ->assertOk()
            ->assertSee('data-chatbot-app', false)
            ->assertSee('data-chat-message-list', false)
            ->assertDontSee('fixed bottom-6 left-1/2', false);
    }

    public function test_the_chat_message_endpoint_uses_its_form_request(): void
    {
        $response = $this->postJson(route('chatbot.api.messages.send'));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'session_key',
                'message',
            ]);
    }
}
