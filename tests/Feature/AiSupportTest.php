<?php

namespace Tests\Feature;

use App\Livewire\Admin\Conversations;
use App\Livewire\Panel\Support as PanelSupport;
use App\Livewire\Support\Assistant;
use App\Models\Faq;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\TechPackage;
use App\Models\User;
use App\Services\Ai\AiException;
use App\Services\Ai\Client;
use App\Services\Ai\Message;
use App\Support\SupportAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_llm_client_throws_when_not_configured(): void
    {
        $client = new Client(['base_url' => '', 'api_key' => '', 'model' => 'test']);

        $this->assertFalse($client->isConfigured());

        $this->expectException(AiException::class);

        $client->chat([Message::user('hi')]);
    }

    public function test_llm_client_returns_content_when_configured(): void
    {
        Http::fake([
            'https://llm.test/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Hello AI']]],
            ], 200),
        ]);

        $client = new Client([
            'base_url' => 'https://llm.test/v1',
            'api_key' => 'test-key',
            'model' => 'gpt-test',
            'timeout' => 10,
            'max_tokens' => 100,
        ]);

        $this->assertTrue($client->isConfigured());
        $this->assertSame('Hello AI', $client->chat([Message::user('hi')]));
    }

    public function test_llm_client_throws_on_error_status(): void
    {
        Http::fake([
            'https://llm.test/v1/chat/completions' => Http::response([], 500),
        ]);

        $client = new Client([
            'base_url' => 'https://llm.test/v1',
            'api_key' => 'test-key',
            'model' => 'gpt-test',
        ]);

        $this->expectException(AiException::class);

        $client->chat([Message::user('hi')]);
    }

    public function test_assistant_persists_messages_and_returns_fallback_reply(): void
    {
        $user = User::factory()->create();
        $conversation = SupportConversation::query()->create([
            'user_id' => $user->id,
            'title' => 'Support Chat',
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'panel',
        ]);

        $reply = app(SupportAssistant::class)->answer($conversation, 'What is the pricing?');

        $this->assertStringContainsString('Pro tier', $reply);
        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'role' => SupportMessage::ROLE_USER,
            'body' => 'What is the pricing?',
        ]);
        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'role' => SupportMessage::ROLE_ASSISTANT,
        ]);
        $this->assertNotNull($conversation->fresh()->last_message_at);
    }

    public function test_knowledge_includes_services_packages_and_faqs(): void
    {
        $category = ServiceCategory::query()->create(['name' => 'Development', 'slug' => 'development']);
        ServiceTier::query()->create(['service_category_id' => $category->id, 'name' => 'Starter', 'price' => '99', 'billing' => 'month']);
        TechPackage::query()->create([
            'category' => 'web',
            'title' => 'Pro Package',
            'type' => 'full-stack',
            'description' => 'A package',
            'price' => '499',
            'billing' => 'one-time',
            'sort_order' => 1,
        ]);
        Faq::query()->create(['question' => 'Do you offer support?', 'answer' => 'Yes, 24/7.']);

        $knowledge = app(SupportAssistant::class)->knowledge();

        $this->assertStringContainsString('Site:', $knowledge);
        $this->assertStringContainsString('Services:', $knowledge);
        $this->assertStringContainsString('Packages:', $knowledge);
        $this->assertStringContainsString('FAQ:', $knowledge);
        $this->assertStringContainsString('Pro Package', $knowledge);
    }

    public function test_owned_by_scopes_guests_by_session(): void
    {
        $mine = SupportConversation::query()->create([
            'session_id' => 'session-a',
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'hud',
        ]);
        $other = SupportConversation::query()->create([
            'session_id' => 'session-b',
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'hud',
        ]);

        $ids = SupportConversation::ownedBy(null, 'session-a')->pluck('id');

        $this->assertTrue($ids->contains($mine->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_guest_must_provide_email_and_phone_before_chatting(): void
    {
        Livewire::test(Assistant::class)
            ->call('start')
            ->assertSee('Start the chat')
            ->assertSee('Email address')
            ->assertSee('Phone number')
            ->set('question', 'How much does it cost?')
            ->call('send')
            ->assertSet('open', true)
            ->assertSee('Start the chat');

        // No message may be sent until contact details are provided.
        $this->assertDatabaseMissing('support_messages', [
            'role' => SupportMessage::ROLE_USER,
            'body' => 'How much does it cost?',
        ]);

        // Empty submission is rejected.
        Livewire::test(Assistant::class)
            ->call('start')
            ->call('submitContact')
            ->assertHasErrors(['guestEmail', 'guestPhone']);

        $this->assertDatabaseHas('support_conversations', [
            'guest_email' => null,
            'status' => SupportConversation::STATUS_OPEN,
        ]);
    }

    public function test_guest_can_chat_after_providing_contact_details(): void
    {
        Livewire::test(Assistant::class)
            ->call('start')
            ->set('guestEmail', 'guest@example.com')
            ->set('guestPhone', '+880 1710 000 000')
            ->call('submitContact')
            ->assertHasNoErrors()
            ->assertDontSee('Start the chat')
            ->set('question', 'How much does it cost?')
            ->call('send')
            ->assertSet('question', '')
            ->assertSet('open', true)
            ->assertSee('Pro tier');

        $this->assertDatabaseHas('support_conversations', [
            'source' => 'hud',
            'status' => SupportConversation::STATUS_OPEN,
            'guest_email' => 'guest@example.com',
            'guest_phone' => '+880 1710 000 000',
        ]);
        $this->assertDatabaseHas('support_messages', [
            'role' => SupportMessage::ROLE_USER,
            'body' => 'How much does it cost?',
        ]);
        $this->assertDatabaseHas('support_messages', ['role' => SupportMessage::ROLE_ASSISTANT]);
    }

    public function test_assistant_quick_action_asks_pricing(): void
    {
        Livewire::test(Assistant::class)
            ->call('start')
            ->set('guestEmail', 'guest@example.com')
            ->set('guestPhone', '+880 1710 000 000')
            ->call('submitContact')
            ->call('sendQuick', 'pricing')
            ->assertSee('Pro tier');

        $this->assertDatabaseHas('support_messages', [
            'role' => SupportMessage::ROLE_USER,
            'body' => 'What are your pricing options?',
        ]);
    }

    public function test_customer_can_chat_in_panel(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($user)
            ->test(PanelSupport::class)
            ->call('newConversation')
            ->set('message', 'When will my order be delivered?')
            ->call('send')
            ->assertSet('message', '')
            ->assertSee('delivery');

        $this->assertDatabaseHas('support_conversations', [
            'user_id' => $user->id,
            'source' => 'panel',
        ]);
    }

    public function test_customer_cannot_view_another_customers_conversation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $conversation = SupportConversation::query()->create([
            'user_id' => $owner->id,
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'panel',
        ]);

        Livewire::actingAs($intruder)
            ->test(PanelSupport::class)
            ->call('openConversation', $conversation->id)
            ->assertStatus(404);
    }

    public function test_admin_can_reply_close_and_reopen_conversation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();

        $conversation = SupportConversation::query()->create([
            'user_id' => $user->id,
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'panel',
        ]);
        $conversation->messages()->create(['role' => SupportMessage::ROLE_USER, 'body' => 'help me']);

        Livewire::actingAs($admin)
            ->test(Conversations::class)
            ->call('select', $conversation->id)
            ->set('reply', 'We are on it.')
            ->call('sendReply')
            ->assertSet('reply', '');

        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'role' => SupportMessage::ROLE_ASSISTANT,
            'body' => 'We are on it.',
        ]);

        Livewire::actingAs($admin)->test(Conversations::class)->call('close', $conversation->id);
        $this->assertSame(SupportConversation::STATUS_CLOSED, $conversation->fresh()->status);

        Livewire::actingAs($admin)->test(Conversations::class)->call('reopen', $conversation->id);
        $this->assertSame(SupportConversation::STATUS_OPEN, $conversation->fresh()->status);
    }

    public function test_customer_cannot_access_another_customers_hud_conversation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $conversation = SupportConversation::query()->create([
            'user_id' => $owner->id,
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'hud',
        ]);

        Livewire::actingAs($intruder)
            ->test(Assistant::class)
            ->set('conversationId', $conversation->id)
            ->call('start');

        $this->assertDatabaseMissing('support_messages', ['support_conversation_id' => $conversation->id]);

        Livewire::actingAs($intruder)
            ->test(Assistant::class)
            ->set('conversationId', $conversation->id)
            ->set('question', 'Hello?')
            ->call('send');

        $this->assertDatabaseMissing('support_messages', [
            'support_conversation_id' => $conversation->id,
            'role' => SupportMessage::ROLE_USER,
            'body' => 'Hello?',
        ]);
        $this->assertSame(SupportConversation::STATUS_OPEN, $conversation->fresh()->status);
    }

    public function test_authenticated_customer_skips_contact_gate(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($user)
            ->test(Assistant::class)
            ->call('start')
            ->assertDontSee('Start the chat')
            ->set('question', 'How much does it cost?')
            ->call('send')
            ->assertSee('Pro tier');

        $this->assertDatabaseHas('support_conversations', [
            'user_id' => $user->id,
            'guest_email' => null,
            'source' => 'hud',
        ]);
    }

    public function test_admin_sees_guest_email_and_phone_in_conversations(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $conversation = SupportConversation::query()->create([
            'guest_email' => 'guest@example.com',
            'guest_phone' => '+880 1710 000 000',
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'hud',
        ]);
        $conversation->messages()->create(['role' => SupportMessage::ROLE_USER, 'body' => 'hello']);

        Livewire::actingAs($admin)
            ->test(Conversations::class)
            ->assertSee('guest@example.com')
            ->assertSee('+880 1710 000 000');
    }

    public function test_assistant_widget_hidden_when_ai_disabled(): void
    {
        Setting::set('ai_enabled', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('assistant-toggle');
    }
}
