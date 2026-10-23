<?php

namespace Database\Seeders;

use App\Actions\CompleteOrder;
use App\Actions\CreateOrder;
use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use App\Models\Page;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seller = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $studio = User::factory()->create([
            'name' => 'Ada Sterling',
            'email' => 'ada@example.com',
        ]);

        $buyer = User::factory()->create([
            'name' => 'Sam Rivera',
            'email' => 'sam@example.com',
        ]);

        $admin = User::factory()->create([
            'name' => 'Site Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $invoxa = $this->createProduct($seller, [
            'name' => 'Invoxa',
            'tagline' => 'Beautiful, lightning fast invoicing for freelancers.',
            'description' => "Invoxa turns time tracking into beautiful, on-brand invoices.\n\nCreate a quote, log your hours, and send a branded invoice in seconds. Clients can pay online, and every invoice syncs with your accounting tool.\n\nThis is the full desktop-class product, not a demo.",
            'platform' => AppPlatform::Web,
            'price' => 4900,
            'version' => '2.4.1',
            'requirements' => 'Any modern browser, internet connection',
            'demo_url' => 'https://example.com/invoxa/demo',
            'download_url' => 'https://example.com/invoxa/download',
            'repository_url' => 'https://github.com/example/invoxa',
            'features' => [
                ['title' => 'Branded invoices', 'description' => 'Upload a logo once and every invoice, quote and receipt inherits your colours and fonts.'],
                ['title' => 'Time tracking', 'description' => 'One click timers per client, with automatic rounding and overtime rules.'],
                ['title' => 'Online payments', 'description' => 'Card, bank transfer and PayPal checkout with automatic reconciliation.'],
                ['title' => 'Recurring billing', 'description' => 'Retainers, subscriptions and usage based plans billed on your schedule.'],
            ],
            'tags' => ['billing', 'productivity', 'freelance'],
        ]);

        $this->createProduct($seller, [
            'name' => 'Pocket Ledger',
            'tagline' => 'Track every expense the moment it happens.',
            'description' => "Pocket Ledger is a fast, offline first expense tracker built for Android.\n\nPhotograph a receipt and the amount, tax and category are filled in for you. Everything syncs the moment you are back online.",
            'platform' => AppPlatform::Android,
            'price' => 1900,
            'version' => '3.1.0',
            'requirements' => 'Android 9.0 (Pie) or later',
            'demo_url' => 'https://example.com/pocket-ledger/demo',
            'download_url' => 'https://example.com/pocket-ledger/download',
            'features' => [
                ['title' => 'Receipt scanning', 'description' => 'Point, shoot and the receipt is parsed into a tidy expense entry.'],
                ['title' => 'Offline first', 'description' => 'Everything works without a connection and syncs when you are back.'],
                ['title' => 'Shared budgets', 'description' => 'Invite your household and split shared expenses automatically.'],
                ['title' => 'Monthly reports', 'description' => 'See where the money went with clear, exportable summaries.'],
            ],
            'tags' => ['finance', 'android'],
        ]);

        $this->createProduct($seller, [
            'name' => 'Focus Timer',
            'tagline' => 'A distraction free pomodoro timer for Windows.',
            'description' => "Focus Timer hides everything else. One clean window, one timer, one task.\n\nIt blocks distracting sites during a session, plays gentle sounds when the timer ends, and keeps a daily history of how you spent your attention.",
            'platform' => AppPlatform::Windows,
            'price' => 2900,
            'version' => '1.8.2',
            'requirements' => 'Windows 10 version 1809 or later, 2 GB RAM',
            'download_url' => 'https://example.com/focus-timer/download',
            'features' => [
                ['title' => 'Distraction blocking', 'description' => 'Blacklist distracting sites and apps while a session is running.'],
                ['title' => 'Session history', 'description' => 'A simple timeline of every session so you can see what actually got done.'],
                ['title' => 'Ambient sounds', 'description' => 'Built in focus soundscapes that fade in and out with the timer.'],
                ['title' => 'Keyboard driven', 'description' => 'Start, pause and reset without ever leaving the keyboard.'],
            ],
            'tags' => ['productivity', 'windows'],
        ]);

        $recipe = $this->createProduct($seller, [
            'name' => 'Recipe Vault',
            'tagline' => 'Your recipes, organised the way you actually cook.',
            'description' => 'Recipe Vault is still in early access. Expect import tools, shopping lists and a meal planner.',
            'platform' => AppPlatform::Web,
            'price' => 0,
            'version' => '0.4.0',
            'features' => [
                ['title' => 'Recipe import', 'description' => 'Bring your recipes across from anywhere.'],
            ],
            'tags' => ['cooking', 'free'],
        ]);

        $recipe->update(['status' => ProductStatus::Draft, 'published_at' => null]);

        $shiftboard = $this->createProduct($studio, [
            'name' => 'ShiftBoard',
            'tagline' => 'Plan shift rotas your team actually follows.',
            'description' => "ShiftBoard takes the pain out of rotas for small teams.\n\nDrag a shift onto the calendar, publish it, and everyone gets a push notification with the details. Swaps are handled in the app, so nobody has to chase anyone in a group chat.",
            'platform' => AppPlatform::Ios,
            'price' => 1400,
            'version' => '5.0.3',
            'requirements' => 'iOS 16.0 or later, iPhone or iPad',
            'demo_url' => 'https://example.com/shiftboard/demo',
            'download_url' => 'https://example.com/shiftboard/download',
            'features' => [
                ['title' => 'Drag and drop rotas', 'description' => 'Build a week of shifts in seconds and copy it forward.'],
                ['title' => 'Push notifications', 'description' => 'Everyone is notified the moment a rota is published.'],
                ['title' => 'Swap requests', 'description' => 'Let staff volunteer for shifts and approve swaps with one tap.'],
            ],
            'tags' => ['teams', 'productivity'],
        ]);

        $pixelpress = $this->createProduct($studio, [
            'name' => 'PixelPress',
            'tagline' => 'A delightful image compressor for macOS.',
            'description' => "PixelPress batch compresses images without leaving the Finder.\n\nSelect a folder, drag it onto the window, and every image is resized, re-encoded and queued for upload in one go.",
            'platform' => AppPlatform::MacOs,
            'price' => 3500,
            'version' => '2.2.0',
            'requirements' => 'macOS 13 Ventura or later',
            'download_url' => 'https://example.com/pixelpress/download',
            'features' => [
                ['title' => 'Batch compression', 'description' => 'Compress hundreds of images in one pass with sensible presets.'],
                ['title' => 'Finder integration', 'description' => 'Right click any folder and compress it without opening the app.'],
                ['title' => 'Format presets', 'description' => 'Save reusable presets for web, print and social platforms.'],
            ],
            'tags' => ['design', 'macos'],
        ]);

        $this->createProduct($studio, [
            'name' => 'Termvault',
            'tagline' => 'Encrypted secrets for your terminal workflows.',
            'description' => "Termvault keeps your API keys, tokens and passwords in an encrypted vault that lives right in your terminal.\n\nPipe commands in and out, share secrets with your team, and never paste a credential into a chat again.",
            'platform' => AppPlatform::Linux,
            'price' => 2400,
            'version' => '1.3.7',
            'requirements' => 'Any modern Linux distribution, bash or zsh',
            'download_url' => 'https://example.com/termvault/download',
            'features' => [
                ['title' => 'End to end encryption', 'description' => 'Secrets are encrypted on your machine with a master passphrase.'],
                ['title' => 'Shell integration', 'description' => 'Read secrets straight from a script with a single function call.'],
                ['title' => 'Team sharing', 'description' => 'Share a vault with teammates without ever seeing their secrets.'],
            ],
            'tags' => ['developer', 'security'],
        ]);

        $this->seedPurchase($buyer, $pixelpress);
        $this->seedPurchase($seller, $shiftboard);

        $this->seedPages();
    }

    /**
     * Full text of the terms of service.
     */
    protected function termsContent(): string
    {
        return <<<'HTML'
<p><strong>Effective date:</strong> 23 October 2026</p>
<p>These Terms of Service ("Terms") govern your use of the DevNest marketplace (the "Platform"), operated by the platform owner ("we", "us"). By creating an account or using the Platform you agree to these Terms. If you do not agree, do not use the Platform.</p>
<h2>1. The Platform</h2>
<p>DevNest is a marketplace that lets independent developers ("Sellers") list, showcase and sell software applications ("Apps") to end users ("Buyers"). We provide the storefront, checkout, licensing and delivery infrastructure. We are not a party to the sale between a Seller and a Buyer; each transaction is concluded directly between them, with us acting as the payment and delivery agent.</p>
<h2>2. Accounts</h2>
<p>You must be at least 16 years old to create an account. You are responsible for keeping your credentials secure and for all activity under your account. Notify us immediately of any unauthorised access. We may suspend or close accounts that breach these Terms.</p>
<h2>3. Seller obligations</h2>
<p>Sellers warrant that they own or have the right to distribute every App they list, that the App does not infringe any third-party rights, and that its description, screenshots and metadata are accurate. Sellers must deliver a working product and honour the licence terms stated on the listing. Sellers may not list malware, deceptive software or content that is illegal in the jurisdictions where the Platform is offered.</p>
<h2>4. Buyer obligations</h2>
<p>Buyers receive a personal, non-exclusive, non-transferable licence to use the purchased App, subject to the licence terms stated on the listing. Buyers may not redistribute, resell or reverse engineer the App beyond what those terms permit. Buyers are responsible for checking that an App meets their requirements before purchase; where a demo is offered it should be tried first.</p>
<h2>5. Pricing and payment</h2>
<p>Prices are set by Sellers in the currency configured for the Platform. Payment is processed by PayPal. By completing a purchase you authorise us to charge the stated amount. Free Apps are delivered without charge. Taxes, where applicable, are the responsibility of the Buyer.</p>
<h2>6. Refunds</h2>
<p>Refund requests are handled case by case and should be raised within 14 days of purchase. We may issue a full or partial refund, or decline the request, at our discretion, taking into account whether the App was downloaded, activated or used. On refund the licence is revoked.</p>
<h2>7. Licences and downloads</h2>
<p>Each purchase issues a single licence key tied to the Buyer's account. Sellers may update an App at any time; Buyers receive updates within the same major version at no extra cost. We do not guarantee that an App will remain available forever, but a purchased licence remains valid for the version acquired.</p>
<h2>8. Reviews</h2>
<p>Buyers may leave reviews on Apps they have purchased. Reviews must be honest, relevant and free of abusive or unlawful content. We may remove reviews that breach these Terms.</p>
<h2>9. Prohibited conduct</h2>
<p>You may not use the Platform to spam, harass, defraud, scrape data, circumvent security, or interfere with its operation. You may not use automated tools to create accounts or to purchase Apps.</p>
<h2>10. Intellectual property</h2>
<p>The Platform, its branding and code are owned by us. App content is owned by its Seller. Nothing in these Terms transfers ownership of either.</p>
<h2>11. Disclaimer</h2>
<p>The Platform is provided "as is" without warranty of any kind. We do not warrant that any App will be error free, secure or fit for a particular purpose. Sellers are solely responsible for their Apps.</p>
<h2>12. Limitation of liability</h2>
<p>To the fullest extent permitted by law, we are not liable for any indirect, incidental, special or consequential damages arising out of your use of the Platform or any App. Our total liability for any claim is limited to the amount you paid us in the 12 months preceding the claim.</p>
<h2>13. Termination</h2>
<p>You may close your account at any time. We may suspend or terminate access for breach of these Terms. On termination, licences you have purchased remain valid for the versions acquired.</p>
<h2>14. Changes to these Terms</h2>
<p>We may update these Terms from time to time. Material changes will be announced on the Platform. Continued use after a change constitutes acceptance.</p>
<h2>15. Governing law</h2>
<p>These Terms are governed by the laws of the jurisdiction in which the platform owner is established, without regard to conflict of law principles.</p>
<p>If you have questions, contact us through the support email listed on the Platform.</p>
HTML;

    }

    /**
     * Full text of the privacy policy.
     */
    protected function privacyContent(): string
    {
        return <<<'HTML'
<p><strong>Effective date:</strong> 23 October 2026</p>
<p>This Privacy Policy explains how we collect, use, store and share personal information when you use the DevNest marketplace (the "Platform").</p>
<h2>1. Who we are</h2>
<p>The Platform is operated by the platform owner ("we", "us"). Questions about this Policy can be sent to the support email listed on the Platform.</p>
<h2>2. Information we collect</h2>
<p><strong>Account information:</strong> name, email address, password (stored hashed), and optional two factor credentials.</p>
<p><strong>Transaction information:</strong> order references, amounts, currency, payment status, PayPal order and capture identifiers, and licence keys.</p>
<p><strong>Usage information:</strong> page views, search queries, and the device and browser you use, collected through standard server logs.</p>
<p><strong>Content you provide:</strong> app listings, descriptions, screenshots, reviews and support messages.</p>
<h2>3. How we use your information</h2>
<p>We use your information to operate your account, process purchases and issue licences, send transactional emails such as verification and password reset links, prevent fraud and abuse, improve the Platform, and comply with legal obligations. We do not sell your personal information.</p>
<h2>4. Payment processing</h2>
<p>Payments are processed by PayPal. We do not store your card or bank details. PayPal's own privacy policy governs that data.</p>
<h2>5. Cookies</h2>
<p>We use essential cookies to keep you signed in and to remember your preferences such as theme and sidebar state. We do not use tracking cookies for advertising.</p>
<h2>6. Sharing</h2>
<p>We share information only with the service providers that help us run the Platform (hosting, email delivery, payment processing) and when required by law. Sellers can see the name and email of Buyers who purchase their Apps, for support and licensing purposes.</p>
<h2>7. Retention</h2>
<p>We keep your account information for as long as your account exists. Order and licence records are kept for as long as needed to honour the purchase and for legal and accounting purposes, even after an account is closed.</p>
<h2>8. Your rights</h2>
<p>Depending on your location you may have the right to access, correct, delete or export your personal information, and to object to or restrict certain processing. You can update most of your details from your profile page. For anything else, contact us through the support email.</p>
<h2>9. Security</h2>
<p>We use HTTPS, hashed passwords, rate limiting and access controls to protect your information. No system is completely secure; please use a strong, unique password.</p>
<h2>10. Children</h2>
<p>The Platform is not directed at children under 16 and we do not knowingly collect their information.</p>
<h2>11. Changes to this Policy</h2>
<p>We may update this Policy from time to time. Material changes will be announced on the Platform.</p>
HTML;

    }

    /**
     * Publish the standard legal pages.
     */
    protected function seedPages(): void
    {
        Page::query()->firstOrCreate(['slug' => 'terms'], [
            'title' => 'Terms of service',
            'content' => $this->termsContent(),
            'location' => 'footer',
            'sort_order' => 10,
            'published_at' => now(),
        ]);

        Page::query()->firstOrCreate(['slug' => 'privacy'], [
            'title' => 'Privacy policy',
            'content' => $this->privacyContent(),
            'location' => 'footer',
            'sort_order' => 20,
            'published_at' => now(),
        ]);
    }

    /**
     * Create a product together with its features and tags.
     *
     * @param  array{
     *     name: string,
     *     tagline: string,
     *     description: string,
     *     platform: AppPlatform,
     *     price: int,
     *     version?: string|null,
     *     requirements?: string|null,
     *     demo_url?: string|null,
     *     download_url?: string|null,
     *     repository_url?: string|null,
     *     features?: array<int, array{title: string, description?: string|null}>,
     *     tags?: array<int, string>,
     * }  $definition
     */
    protected function createProduct(User $owner, array $definition): Product
    {
        $product = $owner->products()->create([
            'name' => $definition['name'],
            'slug' => Str::slug($definition['name']),
            'tagline' => $definition['tagline'],
            'description' => $definition['description'],
            'platform' => $definition['platform'],
            'status' => ProductStatus::Published,
            'price' => $definition['price'],
            'currency' => 'USD',
            'version' => $definition['version'] ?? null,
            'requirements' => $definition['requirements'] ?? null,
            'demo_url' => $definition['demo_url'] ?? null,
            'download_url' => $definition['download_url'] ?? null,
            'repository_url' => $definition['repository_url'] ?? null,
            'published_at' => now(),
        ]);

        foreach ($definition['features'] ?? [] as $position => $feature) {
            $product->features()->create([
                'title' => $feature['title'],
                'description' => $feature['description'] ?? null,
                'position' => $position,
            ]);
        }

        $product->tags()->sync(
            collect($definition['tags'] ?? [])
                ->map(function (string $name): int {
                    $slug = Str::slug($name);

                    return Tag::query()->firstOrCreate(['slug' => $slug], ['name' => $name])->id;
                })
                ->all()
        );

        return $product;
    }

    /**
     * Place and settle an order so the sales views have data.
     */
    protected function seedPurchase(User $buyer, Product $product): void
    {
        $order = app(CreateOrder::class)->handle($buyer, $product);

        app(CompleteOrder::class)->handle($order);
    }
}
