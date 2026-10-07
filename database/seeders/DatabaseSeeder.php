<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\ClientProject;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\FeedComment;
use App\Models\FeedLike;
use App\Models\FeedMedia;
use App\Models\FeedPost;
use App\Models\GroupPackage;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\TechPackage;
use App\Models\TimelinePhase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->seedSettings();
        $this->seedSkills();
        $this->seedProjects();
        $this->seedServices();
        $this->seedPackages();
        $this->seedGroupPackages();
        $this->seedBlog();
        $this->seedFaqs();
        $this->seedTimeline();
        $this->seedEngagement();
        $this->seedInvoices();
        $this->seedSupport();
        $this->seedFeed();
        $this->seedClientProjects();
    }

    private function seedSettings(): void
    {
        $settings = [
            'site_name' => 'MD SHAMIM HOSSAIN',
            'site_tagline' => 'Advanced Sci-Fi HUD Portfolio',
            'hero_kicker' => 'Online // Portfolio',
            'hero_title' => 'MD SHAMIM HOSSAIN',
            'hero_subtitle' => 'Senior Laravel & PHP Specialist',
            'hero_text' => 'Senior Laravel and PHP Specialist, Software Architect Engineer delivering high-performance systems with futuristic design precision.',
            'about_text' => 'I am MD SHAMIM HOSSAIN, a Senior Laravel and PHP Specialist and Software Architect Engineer focused on resilient backend architecture, scalable API design, and polished user-first digital systems.',
            'email' => 'deendayalan.pv@example.com',
            'phone' => '+91 90000 00000',
            'facebook' => 'https://www.facebook.com/shamimwhois',
            'github' => 'https://github.com/shamimwhois',
            'telegram' => 'https://t.me/shamimwhois',
            'whatsapp' => 'https://wa.me/919000000000',
            'typewriter_lines' => [
                'Senior Laravel and PHP Specialist',
                'Software Architect Engineer',
                'Backend System Strategist',
            ],
            'stats' => [
                ['value' => '8+', 'label' => 'Years online'],
                ['value' => '150+', 'label' => 'Projects shipped'],
                ['value' => '24/7', 'label' => 'Support mindset'],
                ['value' => '100%', 'label' => 'Mission focus'],
            ],
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function seedSkills(): void
    {
        $skills = [
            ['name' => 'Frontend Architecture', 'level' => 95, 'icon' => 'UI', 'sort_order' => 1],
            ['name' => 'JavaScript / TypeScript', 'level' => 92, 'icon' => 'JS', 'sort_order' => 2],
            ['name' => 'PHP Ecosystem', 'level' => 88, 'icon' => 'PHP', 'sort_order' => 3],
            ['name' => 'Python Workflows', 'level' => 84, 'icon' => 'PY', 'sort_order' => 4],
            ['name' => 'SEO Strategy', 'level' => 86, 'icon' => 'SEO', 'sort_order' => 5],
            ['name' => 'Digital Marketing', 'level' => 81, 'icon' => 'DM', 'sort_order' => 6],
        ];

        foreach ($skills as $skill) {
            Skill::query()->updateOrCreate(['name' => $skill['name']], $skill);
        }
    }

    private function seedProjects(): void
    {
        $projects = [
            [
                'title' => 'Neon Commerce Dashboard',
                'description' => 'A conversion-focused analytics and campaign dashboard with motion-first UX.',
                'link' => 'https://example.com/demo1',
                'thumbnail' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=900&q=80',
                'tech' => ['React', 'Charts', 'Tailwind'],
                'status' => 'Live',
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Cinematic Product Landing',
                'description' => 'A premium launch microsite blending 3D-like depth and interactive storytelling.',
                'link' => 'https://example.com/demo2',
                'thumbnail' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=900&q=80',
                'tech' => ['GSAP', 'WebGL', 'Landing'],
                'status' => 'Beta',
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Realtime Ops Panel',
                'description' => 'An operations HUD interface with status feeds and service monitoring widgets.',
                'link' => 'https://example.com/demo3',
                'thumbnail' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=900&q=80',
                'tech' => ['Vue', 'Realtime', 'Socket'],
                'status' => 'Live',
                'featured' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(['title' => $project['title']], $project);
        }
    }

    private function seedServices(): void
    {
        $categories = [
            [
                'name' => 'Web Design',
                'slug' => 'web-design',
                'tiers' => [
                    ['name' => 'Starter', 'price' => '$199', 'billing' => 'one-time', 'features' => ['UX audit & page goals', 'Wireframes (core screens)', 'Style guide starter'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 1],
                    ['name' => 'Pro', 'price' => '$499', 'billing' => 'one-time', 'features' => ['Full UI system + components', 'Hi-fi mockups & prototype', 'Responsive layout specs'], 'popular' => true, 'cta' => 'gradient', 'sort_order' => 2],
                    ['name' => 'Elite', 'price' => '$999', 'billing' => 'one-time', 'features' => ['Brand narrative + art direction', 'Motion & micro-interaction specs', 'Dev handoff & QA checklist'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Web Dev',
                'slug' => 'web-dev',
                'tiers' => [
                    ['name' => 'Starter', 'price' => '$299', 'billing' => 'one-time', 'features' => ['Landing or marketing site build', 'CMS-friendly sections', 'Analytics hookup'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 1],
                    ['name' => 'Pro', 'price' => '$799', 'billing' => 'one-time', 'features' => ['Laravel / API integration', 'Auth, roles & admin basics', 'Staging + production deploy'], 'popular' => true, 'cta' => 'gradient', 'sort_order' => 2],
                    ['name' => 'Elite', 'price' => '$1,499', 'billing' => 'one-time', 'features' => ['Multi-service architecture', 'Queues, jobs & integrations', 'Performance + security hardening'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'tiers' => [
                    ['name' => 'Starter', 'price' => '$149', 'billing' => 'month', 'features' => ['Channel audit', 'One campaign setup', 'Monthly KPI snapshot'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 1],
                    ['name' => 'Pro', 'price' => '$399', 'billing' => 'month', 'features' => ['Funnel + landing iteration', 'A/B test loops', 'Creative refresh cadence'], 'popular' => true, 'cta' => 'gradient', 'sort_order' => 2],
                    ['name' => 'Elite', 'price' => '$899', 'billing' => 'month', 'features' => ['Multi-channel orchestration', 'CRO workshop blocks', 'Executive reporting'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'SEO',
                'slug' => 'seo',
                'tiers' => [
                    ['name' => 'Starter', 'price' => '$129', 'billing' => 'month', 'features' => ['Technical crawl audit', 'Core on-page fixes', 'Baseline rankings'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 1],
                    ['name' => 'Pro', 'price' => '$349', 'billing' => 'month', 'features' => ['Schema & internal linking plan', 'Content briefs (monthly)', 'Search console monitoring'], 'popular' => true, 'cta' => 'gradient', 'sort_order' => 2],
                    ['name' => 'Elite', 'price' => '$699', 'billing' => 'month', 'features' => ['Authority roadmap & outreach', 'Advanced structured data', 'Competitive gap analysis'], 'popular' => false, 'cta' => 'outline', 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($categories as $index => $categoryData) {
            $category = ServiceCategory::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                ['name' => $categoryData['name'], 'sort_order' => $index + 1]
            );

            foreach ($categoryData['tiers'] as $tier) {
                ServiceTier::query()->updateOrCreate(
                    [
                        'service_category_id' => $category->id,
                        'name' => $tier['name'],
                    ],
                    [...$tier, 'service_category_id' => $category->id]
                );
            }
        }
    }

    private function seedPackages(): void
    {
        $packages = [
            ['category' => 'javascript', 'icon' => 'JS', 'title' => 'React', 'version' => '18.x', 'type' => 'UI Library', 'description' => 'Component-based frontend system for scalable interfaces.', 'price' => 49, 'billing' => 'one-time', 'command' => 'npm install react react-dom', 'features' => ['Reusable components', 'Large ecosystem', 'Performance tooling'], 'install_commands' => ['npm install react react-dom', 'pnpm add react react-dom'], 'repo_url' => 'https://github.com/facebook/react', 'docs_url' => 'https://react.dev/', 'sort_order' => 1],
            ['category' => 'javascript', 'icon' => 'TW', 'title' => 'Tailwind CSS', 'version' => '3.x', 'type' => 'Utility CSS', 'description' => 'Utility-first CSS framework for rapid, consistent styling.', 'price' => 29, 'billing' => 'one-time', 'command' => 'npm install -D tailwindcss', 'features' => ['Utility classes', 'Theme customization', 'Responsive variants'], 'install_commands' => ['npm install -D tailwindcss', 'npx tailwindcss init -p'], 'repo_url' => 'https://github.com/tailwindlabs/tailwindcss', 'docs_url' => 'https://tailwindcss.com/docs', 'sort_order' => 2],
            ['category' => 'php', 'icon' => 'LV', 'title' => 'Laravel', 'version' => '13.x', 'type' => 'Framework', 'description' => 'Full-stack PHP framework for modern web applications.', 'price' => 79, 'billing' => 'one-time', 'command' => 'composer create-project laravel/laravel app', 'features' => ['Routing + ORM', 'Queue + jobs', 'Blade templates'], 'install_commands' => ['composer create-project laravel/laravel app'], 'repo_url' => 'https://github.com/laravel/laravel', 'docs_url' => 'https://laravel.com/docs', 'sort_order' => 1],
            ['category' => 'php', 'icon' => 'SM', 'title' => 'Symfony', 'version' => '7.x', 'type' => 'Framework', 'description' => 'Flexible components and robust architecture for enterprise apps.', 'price' => 69, 'billing' => 'one-time', 'command' => 'composer create-project symfony/skeleton app', 'features' => ['Reusable components', 'DI container', 'CLI tooling'], 'install_commands' => ['composer create-project symfony/skeleton app'], 'repo_url' => 'https://github.com/symfony/symfony', 'docs_url' => 'https://symfony.com/doc', 'sort_order' => 2],
            ['category' => 'python', 'icon' => 'FA', 'title' => 'FastAPI', 'version' => '0.11+', 'type' => 'API Framework', 'description' => 'High-performance APIs with modern Python typing support.', 'price' => 59, 'billing' => 'one-time', 'command' => 'pip install fastapi uvicorn', 'features' => ['Async support', 'OpenAPI docs', 'Type-safe validation'], 'install_commands' => ['pip install fastapi uvicorn', 'pip install pydantic-settings'], 'repo_url' => 'https://github.com/fastapi/fastapi', 'docs_url' => 'https://fastapi.tiangolo.com/', 'sort_order' => 1],
            ['category' => 'python', 'icon' => 'DJ', 'title' => 'Django', 'version' => '5.x', 'type' => 'Framework', 'description' => 'Batteries-included framework for secure web projects.', 'price' => 64, 'billing' => 'one-time', 'command' => 'pip install django', 'features' => ['Admin UI', 'ORM', 'Authentication'], 'install_commands' => ['pip install django', 'django-admin startproject core'], 'repo_url' => 'https://github.com/django/django', 'docs_url' => 'https://docs.djangoproject.com/', 'sort_order' => 2],
            ['category' => 'managers', 'icon' => 'NM', 'title' => 'NPM', 'version' => '10.x', 'type' => 'Package Manager', 'description' => 'JavaScript package manager and script runner.', 'command' => 'npm install <package-name>', 'features' => ['Dependency graph', 'Scripts', 'Version lock files'], 'install_commands' => ['npm install <package-name>', 'npm uninstall <package-name>'], 'repo_url' => 'https://github.com/npm/cli', 'docs_url' => 'https://docs.npmjs.com/', 'sort_order' => 1],
            ['category' => 'managers', 'icon' => 'PP', 'title' => 'PyPI', 'version' => 'pip', 'type' => 'Package Index', 'description' => 'Python package repository and installer ecosystem.', 'command' => 'pip install package-name', 'features' => ['Huge package index', 'Virtual env support', 'Simple installs'], 'install_commands' => ['pip install package-name', 'pip freeze > requirements.txt'], 'repo_url' => 'https://github.com/pypa/pip', 'docs_url' => 'https://packaging.python.org/', 'sort_order' => 2],
            ['category' => 'managers', 'icon' => 'PK', 'title' => 'Packagist', 'version' => 'Composer', 'type' => 'Package Index', 'description' => 'Default package repository for Composer dependencies.', 'command' => 'composer require vendor/package', 'features' => ['Version constraints', 'Autoload integration', 'PHP ecosystem standard'], 'install_commands' => ['composer require vendor/package', 'composer update'], 'repo_url' => 'https://github.com/composer/composer', 'docs_url' => 'https://getcomposer.org/doc/', 'sort_order' => 3],
        ];

        foreach ($packages as $package) {
            TechPackage::query()->updateOrCreate(
                ['category' => $package['category'], 'title' => $package['title']],
                $package
            );
        }
    }

    private function seedGroupPackages(): void
    {
        $combos = [
            ['title' => 'Content-Writing Package', 'icon' => 'CW', 'features' => ['Grammarly Premium', 'QuillBot', 'Wordtune', 'WordAi', 'Jasper'], 'price' => 299, 'sort_order' => 1],
            ['title' => 'WordPress Package', 'icon' => 'WP', 'features' => ['Elementor Pro', 'WP Rocket', 'GeneratePress Premium', 'Yoast SEO Premium', 'WPForms Pro'], 'price' => 299, 'sort_order' => 2],
            ['title' => 'SEO Package', 'icon' => 'SE', 'features' => ['Ahrefs', 'SEMrush', 'SERanking', 'Moz Pro', 'Ubersuggest'], 'price' => 299, 'sort_order' => 3],
            ['title' => 'Content-Writing Package 2', 'icon' => 'C2', 'features' => ['Copy.ai', 'Writesonic', 'Rytr', 'Frase', 'Surfer SEO'], 'price' => 299, 'sort_order' => 4],
            ['title' => 'Graphic Combo', 'icon' => 'GC', 'features' => ['Canva Pro', 'Adobe Express', 'Figma', 'Envato Elements'], 'price' => 299, 'sort_order' => 5],
            ['title' => 'Ultra SEO Package', 'icon' => 'US', 'features' => ['Ahrefs', 'SEMrush', 'Majestic', 'Screaming Frog', 'DeepCrawl'], 'price' => 299, 'sort_order' => 6],
            ['title' => 'OpenAI / Mid-Chat-GPT', 'icon' => 'AI', 'features' => ['ChatGPT Plus', 'Claude Pro', 'Gemini Advanced'], 'price' => 299, 'sort_order' => 7],
            ['title' => 'AI Image/Video Combo', 'icon' => 'IV', 'features' => ['Midjourney', 'DALL·E', 'Runway', 'CapCut Pro'], 'price' => 299, 'sort_order' => 8],
            ['title' => 'Stealth Audio', 'icon' => 'SA', 'features' => ['Murf.ai', 'ElevenLabs', 'Speechify', 'NaturalReader'], 'price' => 299, 'sort_order' => 9],
            ['title' => 'AI Multifunctional Package', 'icon' => 'AM', 'features' => ['ChatGPT Plus', 'Claude Pro', 'Perplexity', 'Copilot Pro'], 'price' => 299, 'sort_order' => 10],
            ['title' => 'Essential Kit', 'icon' => 'EK', 'features' => ['Canva Pro', 'Grammarly Premium', 'QuillBot', 'VPN Access'], 'price' => 299, 'sort_order' => 11],
            ['title' => 'Research Tool', 'icon' => 'RT', 'features' => ['Perplexity', 'Scite', 'Zotero', 'Connected Papers'], 'price' => 299, 'sort_order' => 12],
            ['title' => 'Win Depends Offer Package', 'icon' => 'WD', 'features' => ['Ahrefs', 'SEMrush', 'Surfer SEO', 'ChatGPT Plus'], 'price' => 299, 'sort_order' => 13],
            ['title' => 'ChatGPT Idle Secret Handle', 'icon' => 'CH', 'features' => ['ChatGPT Plus', 'Stealth activation guide', 'Priority support'], 'price' => 299, 'sort_order' => 14],
            ['title' => 'ChatGPT Plus Student Combo', 'icon' => 'SC', 'features' => ['ChatGPT Plus', 'Claude Pro', 'Gemini Advanced', 'Student discount'], 'price' => 299, 'sort_order' => 15],
        ];

        foreach ($combos as $index => $combo) {
            GroupPackage::query()->updateOrCreate(
                ['kind' => GroupPackage::KIND_COMBO, 'title' => $combo['title']],
                [...$combo, 'kind' => GroupPackage::KIND_COMBO, 'billing' => 'month', 'publish_status' => 'published']
            );
        }

        $tools = [
            ['Ahrefs', 350], ['SEMrush', 350], ['ChatGPT', 250], ['HikeAI', 250],
            ['Stealthwriter', 400], ['Voodoo / VEO 3', 300], ['Links Indexer', 250], ['Ubersuggest', 250],
            ['Storydoke', 350], ['Pencil', 350], ['SlideShare', 350], ['Qraok', 350],
            ['Murf.ai', 350], ['MyInsta', 350], ['Morningfame', 350], ['LinkedIn', 350],
            ['Pluralsight', 400], ['GetVocal', 350], ['MOZ', 350], ['Skillshare', 350],
            ['ChatGPT-GO', 350], ['Figma', 350], ['Xpensi', 350], ['Pages', 350],
            ['Canva Pro', 250], ['Payman / Payoneer', 350], ['Grammarly', 350], ['Video Express', 350],
            ['Lavable', 250], ['Duolingo', 350], ['Neuranker', 350], ['Boost / Poder', 350],
            ['SERanking', 350], ['Account Accessity', 350], ['Scite', 250], ['I Love PDF', 250],
            ['Pikawin', 250], ['CapCut', 350], ['Gamma', 450], ['Insta / Walk', 350],
            ['Writerly', 350], ['Idenogram', 450], ['Perplexity', 350], ['Copilot', 450],
            ['Artify', 350], ['Freepik', 350], ['Deepcrawl', 350], ['SEOptimer', 350],
            ['Zoho', 250], ['Brightlocal', 350], ['Envato Elements', 350], ['Turnitin', 450],
            ['Gemini', 450], ['QuillBot', 250], ['Jasper', 450], ['VidIQ', 250],
            ['Vanquish Expert', 400], ['Stockmedia', 350], ['LinkedIn Sales Navigator', 350], ['Wordtune', 350],
            ['SoftSummit', 299], ['Arena', 450],
        ];

        foreach ($tools as $index => [$title, $price]) {
            GroupPackage::query()->updateOrCreate(
                ['kind' => GroupPackage::KIND_TOOL, 'title' => $title],
                [
                    'kind' => GroupPackage::KIND_TOOL,
                    'title' => $title,
                    'price' => $price,
                    'billing' => 'month',
                    'sort_order' => $index + 1,
                    'publish_status' => 'published',
                ]
            );
        }
    }

    private function seedBlog(): void
    {
        $admin = User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

        $posts = [
            [
                'title' => 'Designing HUD Interfaces for Real Conversion',
                'excerpt' => 'A deep dive on balancing futuristic aesthetics with business outcomes and accessibility.',
                'body' => '<p>A head-up display (HUD) aesthetic is captivating, but it must never eclipse the job the interface is here to do: converting visitors into customers.</p><h2>Clarity beats decoration</h2><p>Every frame, glow, and scan line should have a purpose. Use motion to guide the eye toward primary actions, not to distract from them.</p><h2>Accessibility is non-negotiable</h2><p>Respect <code>prefers-reduced-motion</code>, maintain contrast ratios, and keep keyboard navigation intact. A futuristic interface is only impressive if everyone can use it.</p><h2>Measure what matters</h2><p>Instrument every call-to-action and iterate on the funnel. Beautiful UI that does not convert is just a poster.</p>',
                'category' => 'Tutorials',
                'tags' => ['UI', 'Frontend', 'SEO', 'Motion'],
                'views' => 3200,
                'featured' => true,
                'published' => true,
            ],
            [
                'title' => 'Micro-interactions that Convert',
                'excerpt' => 'Animation as guidance, not decoration.',
                'body' => '<p>Micro-interactions are the fine details that make an interface feel alive. When used deliberately, they reduce perceived latency and reinforce the brand.</p><ul><li>Button feedback within 100ms</li><li>Subtle state transitions</li><li>Progress indicators for async work</li></ul><p>Keep motion functional. If it does not communicate state or direction, cut it.</p>',
                'category' => 'Tips',
                'tags' => ['UX', 'Motion', 'Frontend'],
                'views' => 1580,
                'featured' => false,
                'published' => true,
            ],
            [
                'title' => 'SEO for Creative Portfolios',
                'excerpt' => 'How to rank visual-heavy websites.',
                'body' => '<p>Portfolio sites are visually rich but often crawl poorly. Structure your content so search engines understand it without sacrificing design.</p><p>Use semantic HTML, descriptive alt text, and a sensible heading hierarchy. Lazy-load imagery and keep the initial bundle lean for Core Web Vitals.</p>',
                'category' => 'Guides',
                'tags' => ['SEO', 'Marketing'],
                'views' => 1250,
                'featured' => false,
                'published' => true,
            ],
            [
                'title' => 'Service Packaging Strategy',
                'excerpt' => 'Offer architecture that improves close-rate.',
                'body' => '<p>A three-tier offer (Starter, Pro, Elite) anchors buyers. The middle tier is where most revenue lands, so make it the obvious choice.</p><p>Keep one-time builds separated from monthly retainers, and align each tier with a clear outcome the customer can envision.</p>',
                'category' => 'Guides',
                'tags' => ['Business', 'Strategy'],
                'views' => 890,
                'featured' => false,
                'published' => true,
            ],
            [
                'title' => 'Tailwind at Scale',
                'excerpt' => 'Token systems and maintainability tips.',
                'body' => '<p>Utility-first CSS scales when you treat configuration as a design system. Centralize colors, spacing, and typography into tokens rather than scattering arbitrary values.</p><p>Adopt a component layer for repeated patterns so markup stays readable while the utility classes remain the source of truth.</p>',
                'category' => 'Tutorials',
                'tags' => ['CSS', 'Frontend'],
                'views' => 2100,
                'featured' => false,
                'published' => true,
            ],
            [
                'title' => 'Cyberpunk Typography',
                'excerpt' => 'Readable futuristic type pairings.',
                'body' => '<p>Display fonts like Orbitron capture the sci-fi mood, but body copy needs a readable companion such as Rajdhani or a geometric sans.</p><p>Respect the classic hierarchy: one display face for headings, one workhorse for body text, and generous line-height for legibility.</p>',
                'category' => 'News',
                'tags' => ['Design', 'Typography'],
                'views' => 650,
                'featured' => false,
                'published' => true,
            ],
            [
                'title' => 'Audio UX in Interfaces',
                'excerpt' => 'When and how UI sound should be used.',
                'body' => '<p>Sound can reinforce interaction, but autoplaying audio is hostile. Arm audio only after a user gesture and let them opt out.</p><p>Keep cues short, throttled, and inaudible for those who prefer reduced motion or silent environments.</p>',
                'category' => 'Updates',
                'tags' => ['UX', 'Audio'],
                'views' => 0,
                'featured' => false,
                'published' => false,
            ],
        ];

        foreach ($posts as $index => $post) {
            BlogPost::query()->updateOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    ...$post,
                    'user_id' => $admin->id,
                    'slug' => Str::slug($post['title']),
                    'published_at' => now()->subDays(count($posts) - $index),
                ]
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'How quickly can a project start?', 'answer' => 'Most projects start within 48 hours after scope and payment confirmation.', 'sort_order' => 1],
            ['question' => 'Can I customize service tiers?', 'answer' => 'Yes, every package can be reconfigured based on business objectives and budget.', 'sort_order' => 2],
            ['question' => 'Do you provide maintenance?', 'answer' => 'Ongoing maintenance and growth retainers are available for all completed projects.', 'sort_order' => 3],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(['question' => $faq['question']], $faq);
        }
    }

    private function seedTimeline(): void
    {
        $phases = [
            ['days' => 'Days 1–2', 'title' => 'Discovery & strategy', 'description' => 'Align on goals, constraints, and success metrics. Lock scope and communication rhythm for the build.', 'sort_order' => 1],
            ['days' => 'Days 3–5', 'title' => 'Wireframe & visual direction', 'description' => 'Structure key flows, approve the HUD visual language, and validate layout before production work.', 'sort_order' => 2],
            ['days' => 'Days 6–10', 'title' => 'Build & integration', 'description' => 'Implement features, connect APIs and content, and keep staging aligned with approved milestones.', 'sort_order' => 3],
            ['days' => 'Days 11–12', 'title' => 'QA & revisions', 'description' => 'Cross-browser checks, accessibility pass, and focused revision cycles so nothing ships half-polished.', 'sort_order' => 4],
            ['days' => 'Days 13–14', 'title' => 'Launch protocol', 'description' => 'Production deploy, monitoring hooks, handoff docs, and a clean cutover so your signal stays live.', 'sort_order' => 5],
        ];

        foreach ($phases as $phase) {
            TimelinePhase::query()->updateOrCreate(['title' => $phase['title']], $phase);
        }
    }

    private function seedEngagement(): void
    {
        ContactMessage::query()->updateOrCreate(
            ['email' => 'lead@example.com'],
            [
                'name' => 'Enterprise Lead',
                'phone' => '+1 555 0100',
                'subject' => 'Enterprise Web Platform',
                'message' => 'We are looking for a full Laravel platform with an admin console and customer portal. Please share an estimate.',
                'is_read' => false,
            ]
        );
    }

    private function seedInvoices(): void
    {
        $customer = User::query()->where('email', 'client@scifi-hud.test')->first();

        if (! $customer) {
            return;
        }

        $tier = ServiceTier::query()->where('popular', true)->first();

        Invoice::query()->updateOrCreate(
            ['invoice_no' => 'INV-2026-0001'],
            [
                'user_id' => $customer->id,
                'service_tier_id' => $tier?->id,
                'amount' => $tier ? (float) str_replace([',', '$'], '', $tier->price) : 799,
                'currency' => config('payments.currency'),
                'status' => 'paid',
                'payment_method' => 'bkash',
                'purchased_at' => now()->subDays(9),
            ]
        );

        $package = TechPackage::query()->whereNotNull('price')->first();

        Invoice::query()->updateOrCreate(
            ['invoice_no' => 'INV-2026-0002'],
            [
                'user_id' => $customer->id,
                'tech_package_id' => $package?->id,
                'amount' => $package ? (float) $package->price : 49,
                'currency' => config('payments.currency'),
                'status' => 'paid',
                'payment_method' => 'paypal',
                'purchased_at' => now()->subDays(3),
            ]
        );
    }

    private function seedFeed(): void
    {
        $admin = User::query()->where('role', User::ROLE_ADMIN)->first();
        $customer = User::query()->where('email', 'client@scifi-hud.test')->first();
        $alex = User::query()->where('email', 'alex@scifi-hud.test')->first();

        if (! $admin || ! $customer || ! $alex) {
            return;
        }

        $posts = [
            [
                'user_id' => $admin->id,
                'body' => "Just shipped a full Laravel platform with an admin console, customer portal and realtime support. The glass UI + theme system is live. 🚀\n\nHappy to talk through the architecture if anyone is curious.",
                'created_at' => now()->subHours(5),
            ],
            [
                'user_id' => $alex->id,
                'body' => 'The new checkout flow is so smooth — placed an order in under 30 seconds. Great work on the payment steps and confirmation screen!',
                'created_at' => now()->subHours(3),
            ],
            [
                'user_id' => $customer->id,
                'body' => 'Recruiting for a Laravel team? We just onboarded a new backend engineer and the handoff docs from this project made it painless.',
                'created_at' => now()->subHour(),
            ],
        ];

        $created = [];

        foreach ($posts as $index => $post) {
            $created[] = FeedPost::query()->updateOrCreate(
                ['user_id' => $post['user_id'], 'body' => $post['body']],
                ['created_at' => $post['created_at']]
            );
        }

        // A reaction set + a comment thread with a reply on the admin post
        $post = $created[0];

        $post->reactions()->updateOrCreate(
            ['user_id' => $alex->id, 'likeable_type' => FeedPost::class, 'likeable_id' => $post->id],
            ['reaction_type' => 'love']
        );
        $post->reactions()->updateOrCreate(
            ['user_id' => $customer->id, 'likeable_type' => FeedPost::class, 'likeable_id' => $post->id],
            ['reaction_type' => 'like']
        );

        $comment = $post->comments()->updateOrCreate(
            ['user_id' => $customer->id, 'body' => 'Would love to see a deep-dive post on the theme token system!'],
            ['created_at' => now()->subHours(4)]
        );

        $comment->replies()->updateOrCreate(
            ['user_id' => $admin->id, 'body' => 'Coming soon — I will publish it on the blog next week. 👀'],
            ['feed_post_id' => $post->id, 'created_at' => now()->subHours(2)]
        );

        $comment->reactions()->updateOrCreate(
            ['user_id' => $alex->id, 'likeable_type' => FeedComment::class, 'likeable_id' => $comment->id],
            ['reaction_type' => 'haha']
        );

        $this->attachDemoFeedImage($post);
    }

    /**
     * Attach a generated gradient image to a feed post so media rendering is
     * visible out of the box. Skips silently when GD is unavailable.
     */
    private function attachDemoFeedImage(FeedPost $post): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $width = 960;
        $height = 540;
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            $r = (int) (46 + ($y / $height) * 50);
            $g = (int) (58 + ($y / $height) * 46);
            $b = (int) (230 - ($y / $height) * 30);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        $path = 'feed/demo/post-'.$post->id.'.png';
        Storage::disk('local')->put($path, $png);

        FeedMedia::query()->updateOrCreate(
            ['path' => $path],
            ['feed_post_id' => $post->id, 'type' => FeedMedia::TYPE_IMAGE, 'position' => 0]
        );
    }

    private function seedClientProjects(): void
    {
        $customer = User::query()->where('email', 'client@scifi-hud.test')->first();
        $admin = User::query()->where('role', User::ROLE_ADMIN)->first();

        if (! $customer || ! $admin) {
            return;
        }

        $invoice = Invoice::query()->where('user_id', $customer->id)->where('status', 'paid')->first();

        $project = ClientProject::query()->updateOrCreate(
            ['user_id' => $customer->id, 'title' => 'Neon Commerce Platform'],
            [
                'invoice_id' => $invoice?->id,
                'description' => 'A conversion-focused commerce platform with admin console, checkout flow and a live project tracker.',
                'status' => ClientProject::STATUS_IN_PROGRESS,
                'start_date' => now()->subDays(4),
                'deadline' => now()->addDays(10),
                'remind_at' => now()->addDays(3),
                'requirements' => "1. Customer-facing storefront with product catalog\n2. Secure checkout with multiple payment methods\n3. Customer panel with order history\n4. Admin console for orders, inventory and content",
                'agreement' => "This agreement covers the build of the Neon Commerce Platform. Revisions are included within the plan; additional scope beyond the requirements above is quoted separately.",
                'policy' => "Work updates are posted to your project tracker daily. Communication happens via the support chat and your project page.",
                'cancel_policy' => "Orders cancelled before work starts are refunded in full. After work begins, cancellation is charged for completed milestones at the plan's hourly-equivalent rate.",
                'return_policy' => "Completed deliverables are covered by a 14-day revision window. Refunds are issued only for defects in delivered code, per the agreement.",
            ]
        );

        $tasks = [
            ['title' => 'Scope & requirements lock', 'is_complete' => true, 'sort_order' => 0],
            ['title' => 'Design system & UI kit', 'is_complete' => true, 'sort_order' => 1],
            ['title' => 'Storefront build', 'is_complete' => true, 'sort_order' => 2],
            ['title' => 'Checkout integration', 'is_complete' => false, 'sort_order' => 3],
            ['title' => 'Customer panel', 'is_complete' => false, 'sort_order' => 4],
            ['title' => 'Admin console', 'is_complete' => false, 'sort_order' => 5],
            ['title' => 'QA & launch', 'is_complete' => false, 'sort_order' => 6],
        ];

        foreach ($tasks as $task) {
            \App\Models\ProjectTask::query()->updateOrCreate(
                ['client_project_id' => $project->id, 'title' => $task['title']],
                $task
            );
        }

        $features = [
            ['title' => 'Responsive storefront', 'is_complete' => true, 'sort_order' => 0],
            ['title' => 'Multi-method checkout', 'is_complete' => false, 'sort_order' => 1],
            ['title' => 'Order history & invoices', 'is_complete' => true, 'sort_order' => 2],
            ['title' => 'Realtime project tracking', 'is_complete' => true, 'sort_order' => 3],
        ];

        foreach ($features as $feature) {
            \App\Models\ProjectFeature::query()->updateOrCreate(
                ['client_project_id' => $project->id, 'title' => $feature['title']],
                $feature
            );
        }

        $updates = [
            ['type' => 'milestone', 'message' => 'Design system approved — storefront build underway.', 'created_at' => now()->subDays(3)],
            ['type' => 'update', 'message' => 'Storefront homepage and catalog are live on staging.', 'created_at' => now()->subDays(2)],
            ['type' => 'update', 'message' => 'Checkout flow integrated with all payment methods. Testing now.', 'created_at' => now()->subHours(6)],
        ];

        foreach ($updates as $update) {
            \App\Models\ProjectUpdate::query()->updateOrCreate(
                ['client_project_id' => $project->id, 'message' => $update['message']],
                ['type' => $update['type'], 'user_id' => $admin->id, 'created_at' => $update['created_at']]
            );
        }

        // Demo: a customer-submitted order brief awaiting review, with a
        // pending budget offer so the negotiation loop is visible out of the box.
        $package = TechPackage::query()->whereNotNull('price')->first();

        ClientProject::query()->updateOrCreate(
            ['user_id' => $customer->id, 'title' => 'Brand refresh & landing page rebuild'],
            [
                'status' => ClientProject::STATUS_PENDING,
                'source' => ClientProject::SOURCE_CLIENT,
                'submitted_at' => now()->subHours(20),
                'tech_package_id' => $package?->id,
                'description' => 'A complete visual refresh of our current site plus a new high-converting landing page for our flagship product.',
                'requirements' => "1. New hero with product value props\n2. Services overview section with cards\n3. Contact + quote request form\n4. Mobile responsive and fast on Core Web Vitals",
                'example_url' => 'https://stripe.com',
                'demo_url' => 'https://current-site.example.com',
                'design_notes' => 'Clean and premium. Prefer a light theme with bold accent colors, subtle glassmorphism and generous whitespace. Avoid dark sci-fi styling for this one.',
                'color_palette' => ['#6366f1', '#0f172a', '#f59e0b'],
                'budget_from' => 500,
                'budget_to' => 1200,
                'timeline_preference' => 'within_2_weeks',
                'preferred_deadline' => now()->addDays(14)->toDateString(),
                'offer_price' => 950,
                'offer_note' => "I can deliver this in 10 working days. The price includes two revision rounds and a full handoff package — happy to adjust scope if you'd like to reduce the budget.",
                'offer_status' => ClientProject::OFFER_PENDING,
            ]
        );
    }

    private function seedSupport(): void
    {
        $customer = User::query()->where('email', 'client@scifi-hud.test')->first();

        if (! $customer) {
            return;
        }

        $conversation = SupportConversation::query()->updateOrCreate(
            ['user_id' => $customer->id, 'source' => 'panel'],
            [
                'title' => 'Support Chat',
                'status' => SupportConversation::STATUS_OPEN,
                'last_message_at' => now()->subHours(3),
            ]
        );

        $conversation->messages()->updateOrCreate(
            ['body' => 'When will my Pro package build go live?'],
            ['role' => SupportMessage::ROLE_USER]
        );

        $conversation->messages()->updateOrCreate(
            ['body' => 'Your build is in QA — launch is scheduled within 24 hours.'],
            ['role' => SupportMessage::ROLE_ASSISTANT]
        );
    }
}
